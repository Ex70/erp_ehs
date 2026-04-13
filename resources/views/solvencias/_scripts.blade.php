<script>
let idxPartida = document.querySelectorAll('.partida-row').length;

// ── Agregar fila de partida ──────────────────────────────────────
function agregarPartida() {
    const idx  = idxPartida++;
    const opts = PROVEEDORES_DATA.map(p =>
        `<option value="${p.id}">${p.nombre}</option>`
    ).join('');

    const fila = `
    <tr class="partida-row" data-idx="${idx}">
        <td class="text-center num-partida"></td>
        <td><input type="text" name="partidas[${idx}][descripcion]"
                   class="form-control form-control-sm"
                   placeholder="Descripción" required></td>
        <td><input type="number" name="partidas[${idx}][cantidad]"
                   class="form-control form-control-sm text-right"
                   value="1" min="0" step="0.01"
                   title="Solo informativo"></td>
        <td><input type="number" name="partidas[${idx}][importe]"
                   class="form-control form-control-sm text-right input-importe"
                   value="0" min="0" step="0.01"
                   oninput="recalcularTotales()" required></td>
        <td>
            <select name="partidas[${idx}][proveedor_id]"
                    class="form-control form-control-sm select-proveedor"
                    data-idx="${idx}"
                    onchange="cargarCuentas(this, ${idx})">
                <option value="">— Proveedor —</option>
                ${opts}
            </select>
        </td>
        <td>
            <select name="partidas[${idx}][cuenta_bancaria_id]"
                    class="form-control form-control-sm select-cuenta"
                    id="cuentas-${idx}">
                <option value="">— Cuenta —</option>
            </select>
        </td>
        <td><input type="text" name="partidas[${idx}][concepto]"
                   class="form-control form-control-sm"
                   placeholder="Ej: PAGO CON TARJETA"></td>
        <td>
            <button type="button" class="btn btn-danger btn-xs"
                    onclick="eliminarPartida(this)">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>`;

    document.getElementById('partidas-body').insertAdjacentHTML('beforeend', fila);
    renumerarPartidas();
}

// ── Eliminar fila ────────────────────────────────────────────────
function eliminarPartida(btn) {
    btn.closest('tr').remove();
    renumerarPartidas();
    recalcularTotales();
}

// ── Renumerar ────────────────────────────────────────────────────
function renumerarPartidas() {
    document.querySelectorAll('.num-partida').forEach((td, i) => {
        td.textContent = i + 1;
    });
}

// ── Cargar cuentas bancarias de un proveedor ─────────────────────
function cargarCuentas(select, idx) {
    const provId  = parseInt(select.value);
    const cuentaSel = document.getElementById('cuentas-' + idx);

    cuentaSel.innerHTML = '<option value="">— Cuenta —</option>';

    if (!provId) return;

    const prov = PROVEEDORES_DATA.find(p => p.id === provId);
    if (!prov) return;

    prov.cuentas.forEach(c => {
        const label = [c.banco, c.clabe, c.cuenta, c.ref]
                        .filter(Boolean).join(' — ');
        const opt   = new Option(label, c.id);
        cuentaSel.appendChild(opt);
    });

    // Seleccionar la primera automáticamente si solo hay una
    if (prov.cuentas.length === 1) {
        cuentaSel.value = prov.cuentas[0].id;
    }
}

// ── Recalcular totales ───────────────────────────────────────────
function recalcularTotales() {
    let subtotal = 0;

    document.querySelectorAll('.input-importe').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });

    const iva   = subtotal * 0.16;
    const total = subtotal + iva;

    const fmt = n => n.toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    document.getElementById('resumen-subtotal').value = fmt(subtotal);
    document.getElementById('resumen-iva').value      = fmt(iva);
    document.getElementById('resumen-total').value    = fmt(total);
}

// ── Nueva empresa rápida ─────────────────────────────────────────
function nuevaEmpresaRapida() {
    $('#modal-empresa-rapida').modal('show');
}

function guardarEmpresaRapida() {
    const nombre = document.getElementById('emp-rapida-nombre').value.trim();
    const rfc    = document.getElementById('emp-rapida-rfc').value.trim();

    if (!nombre) return;

    fetch('{{ url('solvencias/empresas') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept'      : 'application/json',
        },
        body: JSON.stringify({ nombre, rfc }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.id) {
            const sel = document.querySelector('select[name="empresa_solvencia_id"]');
            sel.appendChild(new Option(data.nombre, data.id, true, true));
            $('#modal-empresa-rapida').modal('hide');
            document.getElementById('emp-rapida-nombre').value = '';
            document.getElementById('emp-rapida-rfc').value    = '';
        }
    })
    .catch(() => alert('Error al guardar la empresa.'));
}

// Calcular al cargar en edición
document.addEventListener('DOMContentLoaded', recalcularTotales);
</script>