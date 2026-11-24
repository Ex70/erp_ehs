/* ==========================================================================
   Solvencias económicas · Trazabilidad
   - Visor de Excel (.xlsx/.xlsm) sin dependencias (lector del prototipo):
     muestra el formato tal como quedó guardado, por hojas, y permite copiar
     la hoja para pegarla en Excel.
   - Modales (pago, no se anexa), carga de archivos y anti doble envío.
   Requiere jQuery + Bootstrap 4 (incluidos en AdminLTE).
   ========================================================================== */
(function () {
  'use strict';

  /* ---------- utilidades ---------- */
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  function toast(msg, tipo) {
    if (window.toastr) { (tipo === 'error' ? toastr.error : toastr.info)(msg); return; }
    alert(msg);
  }

  /* ---------- lector de .xlsx (ZIP + XML) ---------- */
  async function unzip(bytes) {
    const dv = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength), files = {};
    let e = bytes.length - 22;
    while (e >= 0 && dv.getUint32(e, true) !== 0x06054b50) e--;
    if (e < 0) throw new Error('No es un archivo .xlsx válido');
    const n = dv.getUint16(e + 10, true);
    let p = dv.getUint32(e + 16, true);
    const dec = new TextDecoder();
    for (let i = 0; i < n; i++) {
      const method = dv.getUint16(p + 10, true), csize = dv.getUint32(p + 20, true),
            nlen = dv.getUint16(p + 28, true), xlen = dv.getUint16(p + 30, true),
            clen = dv.getUint16(p + 32, true), off = dv.getUint32(p + 42, true);
      const name = dec.decode(bytes.subarray(p + 46, p + 46 + nlen));
      const start = off + 30 + dv.getUint16(off + 26, true) + dv.getUint16(off + 28, true);
      files[name] = {method, data: bytes.subarray(start, start + csize)};
      p += 46 + nlen + xlen + clen;
    }
    return async name => {
      const f = files[name];
      if (!f) return null;
      if (f.method === 0) return dec.decode(f.data);
      if (typeof DecompressionStream === 'undefined') throw new Error('Tu navegador no puede leer Excel; descarga el original.');
      const ds = new Blob([f.data]).stream().pipeThrough(new DecompressionStream('deflate-raw'));
      return await new Response(ds).text();
    };
  }

  const xmlDoc = t => new DOMParser().parseFromString(t, 'application/xml');
  const kids = (el, tag) => el ? [...el.getElementsByTagName(tag)] : [];
  function colNum(ref) { let n = 0; for (const ch of ref.replace(/\d+/g, '')) n = n * 26 + ch.charCodeAt(0) - 64; return n; }
  function colName(n) { let s = ''; while (n > 0) { const m = (n - 1) % 26; s = String.fromCharCode(65 + m) + s; n = Math.floor((n - 1) / 26); } return s; }

  function fmtNum(x, id, fmts) {
    const code = fmts[id] || '';
    const isDate = (id >= 14 && id <= 22) || (id >= 45 && id <= 47) ||
      (/[dmy]/i.test(code.replace(/"[^"]*"|\[[^\]]*\]/g, '')) && !/0\.0|#/.test(code));
    if (isDate && x > 0 && x < 100000) {
      const dt = new Date(Math.round((x - 25569) * 86400000));
      const dd = String(dt.getUTCDate()).padStart(2, '0'), mm = String(dt.getUTCMonth() + 1).padStart(2, '0');
      return (id >= 18 && id <= 21)
        ? `${String(dt.getUTCHours()).padStart(2, '0')}:${String(dt.getUTCMinutes()).padStart(2, '0')}`
        : `${dd}/${mm}/${dt.getUTCFullYear()}`;
    }
    if (code.includes('$') || id === 7 || id === 8 || id === 44) return x.toLocaleString('es-MX', {style: 'currency', currency: 'MXN'});
    if (code.includes('%') || id === 9 || id === 10) return (x * 100).toFixed(code.includes('.0') || id === 10 ? 2 : 0) + '%';
    if (/0\.00/.test(code) || id === 2 || id === 4) return x.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    return Number.isInteger(x) ? String(x) : String(Math.round(x * 10000) / 10000);
  }

  async function readXlsx(bytes) {
    const get = await unzip(bytes);
    const ssTxt = await get('xl/sharedStrings.xml');
    const ss = ssTxt ? kids(xmlDoc(ssTxt), 'si').map(si => kids(si, 't').map(t => t.textContent).join('')) : [];
    const st = await get('xl/styles.xml');
    let xfs = [], fontsB = [], fmts = {};
    if (st) {
      const d = xmlDoc(st);
      kids(d, 'numFmt').forEach(f => fmts[f.getAttribute('numFmtId')] = f.getAttribute('formatCode'));
      fontsB = kids(kids(d, 'fonts')[0], 'font').map(f => kids(f, 'b').length > 0);
      xfs = kids(kids(d, 'cellXfs')[0], 'xf').map(x => ({fmt: +x.getAttribute('numFmtId') || 0, b: fontsB[+x.getAttribute('fontId') || 0]}));
    }
    const wb = xmlDoc(await get('xl/workbook.xml')), rels = xmlDoc(await get('xl/_rels/workbook.xml.rels'));
    const relMap = {};
    kids(rels, 'Relationship').forEach(r => relMap[r.getAttribute('Id')] = r.getAttribute('Target'));
    const sheets = [];
    for (const sh of kids(wb, 'sheet')) {
      if (sh.getAttribute('state') === 'hidden') continue;
      const rid = sh.getAttribute('r:id') || sh.getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');
      let target = relMap[rid] || '';
      target = target.startsWith('/') ? target.slice(1) : 'xl/' + target.replace(/^\.\//, '');
      const txt = await get(target);
      if (!txt) continue;
      const d = xmlDoc(txt), cells = {};
      let maxR = 0, maxC = 0;
      for (const c of kids(d, 'c')) {
        const ref = c.getAttribute('r'), t = c.getAttribute('t'), si = +c.getAttribute('s') || 0;
        const v = kids(c, 'v')[0]?.textContent;
        let val = null;
        if (t === 's') val = ss[+v];
        else if (t === 'inlineStr') val = kids(c, 't').map(x => x.textContent).join('');
        else if (t === 'b') val = v === '1' ? 'VERDADERO' : 'FALSO';
        else if (t === 'str' || t === 'e') val = v;
        else if (v != null) val = fmtNum(+v, xfs[si]?.fmt, fmts);
        if (val == null || val === '') continue;
        const r = +ref.replace(/[A-Z]+/, ''), cn = colNum(ref);
        cells[ref] = {v: String(val), n: (t == null || t === 'n') && v != null, b: !!xfs[si]?.b};
        maxR = Math.max(maxR, r); maxC = Math.max(maxC, cn);
      }
      const merges = kids(d, 'mergeCell').map(m => m.getAttribute('ref'));
      const widths = {};
      kids(d, 'col').forEach(c => { for (let i = +c.getAttribute('min'); i <= +c.getAttribute('max') && i <= 200; i++) widths[i] = +c.getAttribute('width'); });
      sheets.push({name: sh.getAttribute('name'), cells, merges, widths, maxR: Math.min(maxR, 2000), maxC: Math.min(maxC, 60)});
    }
    return sheets;
  }

  function sheetHtml(sh) {
    const span = {}, hidden = new Set();
    for (const m of sh.merges) {
      const [a, b] = m.split(':');
      if (!b) continue;
      const r1 = +a.replace(/[A-Z]+/, ''), c1 = colNum(a), r2 = +b.replace(/[A-Z]+/, ''), c2 = colNum(b);
      span[`${r1}:${c1}`] = {rs: r2 - r1 + 1, cs: c2 - c1 + 1};
      for (let r = r1; r <= r2; r++) for (let c = c1; c <= c2; c++) if (r !== r1 || c !== c1) hidden.add(`${r}:${c}`);
    }
    let h = `<table class="sol-xl"><thead><tr><th></th>${Array.from({length: sh.maxC}, (_, i) =>
      `<th style="min-width:${Math.max(40, Math.min(320, (sh.widths[i + 1] || 9) * 7))}px">${colName(i + 1)}</th>`).join('')}</tr></thead><tbody>`;
    for (let r = 1; r <= sh.maxR; r++) {
      h += `<tr><td class="rh">${r}</td>`;
      for (let c = 1; c <= sh.maxC; c++) {
        if (hidden.has(`${r}:${c}`)) continue;
        const cell = sh.cells[colName(c) + r], sp = span[`${r}:${c}`];
        h += `<td${sp ? ` rowspan="${sp.rs}" colspan="${sp.cs}"` : ''} class="${cell?.n ? 'n' : ''} ${cell?.b ? 'b' : ''}">${cell ? esc(cell.v) : ''}</td>`;
      }
      h += '</tr>';
    }
    return h + '</tbody></table>';
  }

  function sheetTSV(sh) {
    const lines = [];
    for (let r = 1; r <= sh.maxR; r++) {
      const row = [];
      for (let c = 1; c <= sh.maxC; c++) {
        const v = sh.cells[colName(c) + r]?.v ?? '';
        row.push(/[\t\n"]/.test(v) ? `"${v.replace(/"/g, '""')}"` : v);
      }
      lines.push(row.join('\t'));
    }
    while (lines.length && !lines[lines.length - 1].replace(/\t/g, '')) lines.pop();
    return lines.join('\n');
  }

  async function copyText(txt) {
    try { await navigator.clipboard.writeText(txt); return true; }
    catch (e) {
      const ta = document.createElement('textarea'); ta.value = txt; document.body.appendChild(ta); ta.select();
      const ok = document.execCommand('copy'); ta.remove(); return ok;
    }
  }

  /* ---------- visor ---------- */
  let XL = null;

  function pintarVisor() {
    const $m = $('#solVisorExcel');
    $m.find('[data-xl-tabs]').html(XL.sheets.map((sh, k) =>
      `<button type="button" class="${k === XL.i ? 'on' : ''}" data-xl-tab="${k}">${esc(sh.name)}</button>`).join(''));
    $m.find('[data-xl-hoja]').html(XL.sheets[XL.i] ? sheetHtml(XL.sheets[XL.i]) : '<div class="p-3 text-muted">Hoja vacía.</div>');
  }

  async function abrirExcel(btn) {
    const $m = $('#solVisorExcel');
    $m.find('[data-xl-titulo]').text(btn.dataset.titulo || 'Formato de solvencia');
    $m.find('[data-xl-sub]').text(btn.dataset.sub || '');
    $m.find('[data-xl-descargar]').attr('href', btn.dataset.url);
    $m.find('[data-xl-tabs]').empty();
    $m.find('[data-xl-hoja]').html('<div class="p-4 text-center text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Leyendo el archivo…</div>');
    $m.modal('show');

    try {
      const resp = await fetch(btn.dataset.url, {credentials: 'same-origin'});
      if (!resp.ok) throw new Error(resp.status === 403 ? 'No tienes acceso a este archivo.' : 'El archivo no está disponible (' + resp.status + ').');
      const bytes = new Uint8Array(await resp.arrayBuffer());
      XL = {sheets: await readXlsx(bytes), i: 0};
      pintarVisor();
    } catch (e) {
      $m.find('[data-xl-hoja]').html(`<div class="p-4 text-danger">No se pudo leer el Excel: ${esc(e.message)}<br><small class="text-muted">Usa «Descargar original».</small></div>`);
    }
  }

  /* ---------- eventos ---------- */
  document.addEventListener('click', function (ev) {
    const t = ev.target.closest('[data-sol-xlsx],[data-xl-tab],[data-xl-copiar],[data-sol-pagar],[data-sol-exceptuar]');
    if (!t) return;

    if (t.dataset.solXlsx !== undefined) { ev.preventDefault(); abrirExcel(t); return; }

    if (t.dataset.xlTab !== undefined) { XL.i = +t.dataset.xlTab; pintarVisor(); return; }

    if (t.dataset.xlCopiar !== undefined) {
      if (!XL || !XL.sheets[XL.i]) return;
      copyText(sheetTSV(XL.sheets[XL.i])).then(ok =>
        toast(ok ? 'Hoja copiada. Pégala en Excel con Ctrl+V.' : 'No se pudo copiar; selecciona las celdas y usa Ctrl+C.'));
      return;
    }

    if (t.dataset.solPagar !== undefined) {
      ev.preventDefault();
      const $m = $('#solModalPago');
      $m.find('form').attr('action', t.dataset.url)[0].reset();
      $m.find('[data-pago-folio]').text(t.dataset.folio || '');
      $m.find('[data-pago-resumen]').text(t.dataset.resumen || '');
      $m.find('.custom-file-label').text('Selecciona el comprobante…');
      $m.modal('show');
      return;
    }

    if (t.dataset.solExceptuar !== undefined) {
      ev.preventDefault();
      const $m = $('#solModalExceptuar');
      $m.find('input[name=tipo]').val(t.dataset.tipo);
      $m.find('[data-exc-nombre]').text(t.dataset.nombre || '');
      $m.find('textarea').val('');
      $m.find('[data-exc-contador]').text('0');
      $m.modal('show');
    }
  });

  // Contador de la observación
  document.addEventListener('input', function (ev) {
    if (ev.target.matches('#solModalExceptuar textarea')) {
      document.querySelector('[data-exc-contador]').textContent = ev.target.value.trim().length;
    }
  });

  // Carga de archivo: se envía en cuanto se elige
  document.addEventListener('change', function (ev) {
    const t = ev.target;

    if (t.matches('[data-sol-upload]') && t.files.length) {
      const label = t.closest('label');
      if (label) label.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo…';
      t.form.submit();
      return;
    }

    if (t.matches('.custom-file-input') && t.files.length) {
      const lbl = t.parentElement.querySelector('.custom-file-label');
      if (lbl) lbl.textContent = t.files[0].name;
    }
  });

  // Anti doble envío: se deshabilita el botón al enviar
  document.addEventListener('submit', function (ev) {
    const f = ev.target;
    if (!f.matches('[data-sol-once]')) return;
    if (f.dataset.enviado) { ev.preventDefault(); return; }
    f.dataset.enviado = '1';
    f.querySelectorAll('button[type=submit]').forEach(b => {
      b.disabled = true;
      b.dataset.textoOriginal = b.innerHTML;
      b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + b.textContent.trim();
    });
  });

  // Filas clicables (tablero / contabilidad)
  document.addEventListener('click', function (ev) {
    const tr = ev.target.closest('tr[data-href]');
    if (!tr || ev.target.closest('a,button,form,input,label')) return;
    window.location = tr.dataset.href;
  });
})();
