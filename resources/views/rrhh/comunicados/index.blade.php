@extends('adminlte::page')

@section('title', 'Comunicados y Noticias')

@section('plugins.Select2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">
            <i class="fas fa-bullhorn text-danger mr-2"></i>
            <span style="font-variant: small-caps; font-weight: 700;">Comunicados y Noticias</span>
        </h1>
        @can('comunicados.crear')
            <button class="btn btn-danger btn-sm font-weight-bold px-3"
                    data-toggle="modal" data-target="#modalPublicar">
                + Publicar
            </button>
        @endcan
    </div>
@endsection

@section('content')

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="d-flex flex-wrap gap-2 mb-4" style="gap:.75rem;">
        <div class="input-group" style="max-width:250px;">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white border-right-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
            </div>
            <input type="text" id="filtroBuscar" class="form-control border-left-0"
                   placeholder="Buscar comunicado..."
                   value="{{ request('buscar') }}">
        </div>

        <select id="filtroCategoria" class="form-control" style="max-width:200px;">
            <option value="">Todas las categorías</option>
            @foreach($categorias as $cat)
                <option value="{{ $cat }}" {{ request('categoria') === $cat ? 'selected' : '' }}>
                    {{ $cat }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Grid de tarjetas --}}
    <div class="row" id="gridComunicados">
        @forelse($comunicados as $com)
            <div class="col-md-4 mb-4 card-comunicado"
                 data-categoria="{{ $com->categoria }}"
                 data-titulo="{{ strtolower($com->titulo) }}"
                 data-extracto="{{ strtolower($com->extracto) }}">
                <div class="card h-100 border-0 shadow-sm comunicado-card"
                     style="border-radius:16px; overflow:hidden; cursor:pointer;"
                     onclick="verComunicado({{ $com->id }})">

                    {{-- Banner emoji --}}
                    <div class="d-flex justify-content-center align-items-center position-relative"
                         style="height:120px; background-color:{{ $com->color_fondo }};">
                        <span style="font-size:3rem;">{{ $com->icono_emoji }}</span>

                        @if($com->notificados_count)
                            <span class="badge badge-light position-absolute"
                                  style="top:10px; right:10px; font-weight:600; opacity:.9;"
                                  title="Personas notificadas">
                                <i class="fas fa-bell mr-1"></i>{{ $com->notificados_count }}
                            </span>
                        @endif
                    </div>

                    <div class="card-body pt-3 pb-2">
                        <small class="font-weight-bold text-uppercase"
                               style="color:#C0392B; letter-spacing:.08em; font-size:.7rem;">
                            {{ $com->categoria }}
                        </small>
                        <h6 class="mt-1 mb-1 font-weight-bold" style="font-size:.95rem;">
                            {{ $com->titulo }}
                        </h6>
                        @if($com->extracto)
                            <p class="text-muted mb-0" style="font-size:.82rem; line-height:1.4;">
                                {{ Str::limit($com->extracto, 90) }}
                            </p>
                        @endif
                    </div>

                    <div class="card-footer bg-white border-0 d-flex justify-content-between"
                         style="font-size:.78rem; color:#999; padding-top:0;">
                        <span>{{ $com->autor }}</span>
                        <span>{{ $com->fecha_publicacion->format('Y-m-d') }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>No hay comunicados publicados aún.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- ══════════════════════════════════════════
                MODAL: Ver comunicado
    ══════════════════════════════════════════ --}}
    <div class="modal fade" id="modalVer" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:16px; border:none;">

                <div class="modal-header border-0 pb-0 pt-3 px-4">
                    <h5 class="modal-title font-weight-bold text-uppercase"
                        id="verTitulo" style="font-size:1rem; line-height:1.4;"></h5>
                    <button type="button" class="close" data-dismiss="modal"
                            style="font-size:1.2rem;">&times;</button>
                </div>

                <hr class="mx-4 mt-2 mb-0">

                <div class="modal-body px-4 pt-2 pb-3">
                    {{-- Meta: categoría · autor · fecha --}}
                    <small id="verMeta" class="d-block mb-1" style="color:#999;"></small>

                    {{-- Alcance de la notificación --}}
                    <small id="verAlcance" class="d-block mb-3" style="color:#999;"></small>

                    {{-- Contenido --}}
                    <div id="verContenido"
                        style="font-size:.92rem; line-height:1.7; color:#333;"></div>

                    {{-- Archivo adjunto --}}
                    <div id="verArchivo" class="mt-3 d-none">
                        <a id="verArchivoLink" href="#" target="_blank"
                        class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-paperclip mr-1"></i> Ver adjunto
                        </a>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-secondary btn-sm px-3"
                            data-dismiss="modal">Cerrar</button>
                    @can('comunicados.editar')
                        <button class="btn btn-sm px-3 font-weight-bold" id="btnEditarDesdeVer"
                                style="background:#f0ad4e;color:#fff;border:none;">
                            <i class="fas fa-pencil-alt mr-1"></i> Editar
                        </button>
                    @endcan
                    @can('comunicados.eliminar')
                        <button class="btn btn-danger btn-sm px-3 font-weight-bold"
                                id="btnEliminarDesdeVer">
                            <i class="fas fa-trash mr-1"></i> Eliminar
                        </button>
                    @endcan
                </div>

            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         MODAL: Publicar / Editar comunicado
    ══════════════════════════════════════════ --}}
    @can('comunicados.crear')
    <div class="modal fade" id="modalPublicar" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:16px; border:none;">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title font-weight-bold" id="modalPublicarTitulo">
                        <i class="fas fa-bullhorn text-danger mr-2"></i> Nueva Publicación
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="formComunicado" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod" value="POST">

                        {{-- Título --}}
                        <div class="form-group">
                            <label class="small font-weight-bold text-uppercase text-muted">
                                Título <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="titulo" id="fTitulo"
                                   class="form-control" placeholder="Título del comunicado" required>
                        </div>

                        <div class="row">
                            {{-- Categoría --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-uppercase text-muted">Categoría</label>
                                    <select name="categoria" id="fCategoria" class="form-control">
                                        @foreach($categorias as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- Ícono/Emoji --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-uppercase text-muted">Ícono / Emoji</label>
                                    <input type="text" name="icono_emoji" id="fEmoji"
                                           class="form-control" maxlength="10" placeholder="📢">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Color de fondo --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-uppercase text-muted">Color de fondo (HEX)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text p-1">
                                                <input type="color" id="colorPicker"
                                                       style="width:30px;height:28px;border:none;cursor:pointer;">
                                            </span>
                                        </div>
                                        <input type="text" name="color_fondo" id="fColor"
                                               class="form-control" placeholder="#F3F4F6" maxlength="7">
                                    </div>
                                </div>
                            </div>
                            {{-- Fecha --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-uppercase text-muted">Fecha de publicación</label>
                                    <input type="date" name="fecha_publicacion" id="fFecha"
                                           class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- Autor --}}
                        <div class="form-group">
                            <label class="small font-weight-bold text-uppercase text-muted">Autor</label>
                            <input type="text" name="autor" id="fAutor"
                                   class="form-control" value="Capital Humano" required>
                        </div>

                        {{-- Extracto --}}
                        <div class="form-group">
                            <label class="small font-weight-bold text-uppercase text-muted">Extracto (resumen corto)</label>
                            <textarea name="extracto" id="fExtracto" class="form-control" rows="2"
                                      placeholder="Resumen breve visible en la tarjeta..."></textarea>
                        </div>

                        {{-- Contenido completo --}}
                        <div class="form-group">
                            <label class="small font-weight-bold text-uppercase text-muted">Contenido completo</label>
                            <textarea name="contenido_completo" id="fContenido" class="form-control" rows="4"
                                      placeholder="Contenido detallado del comunicado..."></textarea>
                        </div>

                        {{-- ══════ DESTINATARIOS DE LA NOTIFICACIÓN ══════ --}}
                        <div class="form-group">
                            <label class="small font-weight-bold text-uppercase text-muted">
                                <i class="fas fa-users mr-1"></i> Destinatarios de la notificación
                            </label>

                            <div class="p-3" style="background:#FAF7F4; border:1px solid #E8E0D8; border-radius:10px;">

                                <div class="custom-control custom-radio mb-1">
                                    <input type="radio" class="custom-control-input alcance-radio"
                                           id="alcanceTodos" name="alcance" value="todos" checked>
                                    <label class="custom-control-label" for="alcanceTodos"
                                           style="font-size:.88rem;">
                                        Toda la organización
                                    </label>
                                </div>

                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" class="custom-control-input alcance-radio"
                                           id="alcanceSegmentado" name="alcance" value="segmentado">
                                    <label class="custom-control-label" for="alcanceSegmentado"
                                           style="font-size:.88rem;">
                                        Seleccionar destinatarios
                                    </label>
                                </div>

                                <div id="bloqueSegmentado" class="d-none pt-2">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-1">Departamentos</label>
                                        <select name="departamentos[]" id="fDepartamentos"
                                                class="form-control select-destinatarios" multiple
                                                data-placeholder="Elige uno o más departamentos">
                                            @foreach($departamentos as $depto)
                                                <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-1">Usuarios adicionales</label>
                                        <select name="usuarios[]" id="fUsuarios"
                                                class="form-control select-destinatarios" multiple
                                                data-placeholder="Agrega personas puntuales">
                                            @foreach($usuarios->groupBy(fn($u) => $u->departamento->nombre ?? 'Sin departamento') as $grupo => $lista)
                                                <optgroup label="{{ $grupo }}">
                                                    @foreach($lista as $u)
                                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group mb-2">
                                    <label class="small text-muted mb-1">
                                        Excluir a <span class="text-muted">(opcional)</span>
                                    </label>
                                    <select name="excluidos[]" id="fExcluidos"
                                            class="form-control select-destinatarios" multiple
                                            data-placeholder="Personas que NO recibirán el aviso">
                                        @foreach($usuarios->groupBy(fn($u) => $u->departamento->nombre ?? 'Sin departamento') as $grupo => $lista)
                                            <optgroup label="{{ $grupo }}">
                                                @foreach($lista as $u)
                                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>

                                <div id="previewDestinatarios" class="mt-2"
                                     style="font-size:.82rem; color:#6c757d;">
                                    <i class="fas fa-user-friends mr-1"></i>
                                    <span id="previewTexto">Calculando destinatarios...</span>
                                </div>
                            </div>
                        </div>

                        {{-- Archivo adjunto --}}
                        <div class="form-group">
                            <label class="small font-weight-bold text-uppercase text-muted">
                                <i class="fas fa-paperclip mr-1"></i> Adjuntar infografía o archivo
                            </label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="archivo"
                                       id="fArchivo" accept=".jpg,.jpeg,.png,.gif,.pdf">
                                <label class="custom-file-label" for="fArchivo">Subir imagen o PDF</label>
                            </div>
                            <div id="archivoActualWrap" class="d-none mt-2">
                                <small class="text-muted">Archivo actual: </small>
                                <a id="archivoActualLink" href="#" target="_blank" class="small">Ver</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger btn-sm font-weight-bold" id="btnGuardar">
                        <i class="fas fa-paper-plane mr-1"></i> Publicar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- Form oculto para eliminar --}}
    <form id="formEliminar" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

@endsection

@section('css')
<style>
    .comunicado-card {
        transition: transform .2s, box-shadow .2s;
    }
    .comunicado-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,.12) !important;
    }
    .gap-2 { gap: .5rem; }

    /* Select2 (tema default) alineado a la estética del modal */
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 6px;
        min-height: 38px;
        padding: 2px 4px;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #E43022;
        box-shadow: 0 0 0 .2rem rgba(228,48,34,.15);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #E43022;
        border: 1px solid #C0392B;
        border-radius: 4px;
        color: #fff;
        font-size: .82rem;
        padding: 1px 8px;
        margin-top: 5px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
        margin-right: 5px;
        opacity: .85;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fff;
        opacity: 1;
    }
    .select2-container--default .select2-results__group {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #999;
    }
    /* Chips grises para el select de exclusiones */
    #fExcluidos + .select2-container--default .select2-selection__choice {
        background-color: #6c757d;
        border-color: #5a6268;
    }
    /* El dropdown debe quedar por encima del modal */
    .select2-container { z-index: 1060; }
</style>
@endsection

@section('js')
<script>
// ── Rutas Laravel → JS ────────────────────────────
const routeIndex    = "{{ route('rrhh.comunicados.index') }}";
const routeStore    = "{{ route('rrhh.comunicados.store') }}";
const routeDefaults = "{{ route('rrhh.comunicados.defaults') }}";
const routeShow     = "{{ url('rrhh/comunicados') }}/"; // se concatena el ID
@can('comunicados.crear')
const routePreview  = "{{ route('rrhh.comunicados.preview-destinatarios') }}";
@endcan

// ── Filtros en tiempo real ─────────────────────────
function aplicarFiltros() {
    const buscar    = document.getElementById('filtroBuscar').value.toLowerCase();
    const categoria = document.getElementById('filtroCategoria').value;

    document.querySelectorAll('.card-comunicado').forEach(card => {
        const matchCat = !categoria || card.dataset.categoria === categoria;
        const matchBus = !buscar
            || card.dataset.titulo.includes(buscar)
            || card.dataset.extracto.includes(buscar);
        card.style.display = (matchCat && matchBus) ? '' : 'none';
    });
}

document.getElementById('filtroBuscar').addEventListener('input', aplicarFiltros);
document.getElementById('filtroCategoria').addEventListener('change', aplicarFiltros);

@can('comunicados.crear')

// ── Sincronizar color picker ───────────────────────
const colorPicker = document.getElementById('colorPicker');
const fColor      = document.getElementById('fColor');

colorPicker.addEventListener('input', () => { fColor.value = colorPicker.value; });
fColor.addEventListener('input', () => {
    if (/^#[0-9A-Fa-f]{6}$/.test(fColor.value)) colorPicker.value = fColor.value;
});

// ── Cambio de categoría → autocompletar emoji/color ─
document.getElementById('fCategoria').addEventListener('change', function () {
    fetch(routeDefaults + '?categoria=' + encodeURIComponent(this.value))
        .then(r => r.json())
        .then(data => {
            document.getElementById('fEmoji').value = data.emoji;
            fColor.value = data.color;
            colorPicker.value = data.color;
        });
});

// ── Custom file label ──────────────────────────────
document.getElementById('fArchivo')?.addEventListener('change', function () {
    const label = this.nextElementSibling;
    label.textContent = this.files[0]?.name ?? 'Subir imagen o PDF';
});

// ── Select2 en los selectores de destinatarios ─────
$(function () {
    $('.select-destinatarios').each(function () {
        $(this).select2({
            width: '100%',
            placeholder: $(this).data('placeholder'),
            closeOnSelect: false,
            dropdownParent: $('#modalPublicar')
        });
    });

    $('.select-destinatarios').on('change', previewDebounced);
    $('.alcance-radio').on('change', function () {
        toggleAlcance();
        previewDebounced();
    });
});

// ── Mostrar/ocultar bloque segmentado ──────────────
function toggleAlcance() {
    const segmentado = document.getElementById('alcanceSegmentado').checked;
    document.getElementById('bloqueSegmentado').classList.toggle('d-none', !segmentado);
}

// ── Contador en vivo de destinatarios ──────────────
let previewTimer = null;

function previewDebounced() {
    clearTimeout(previewTimer);
    previewTimer = setTimeout(actualizarPreview, 350);
}

function valoresDe(id) {
    return $('#' + id).val() || [];
}

function actualizarPreview() {
    const texto = document.getElementById('previewTexto');
    if (!texto) return;

    texto.textContent = 'Calculando destinatarios...';

    fetch(routePreview, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('#formComunicado input[name=_token]').value
        },
        body: JSON.stringify({
            alcance:       document.querySelector('input[name=alcance]:checked').value,
            departamentos: valoresDe('fDepartamentos'),
            usuarios:      valoresDe('fUsuarios'),
            excluidos:     valoresDe('fExcluidos')
        })
    })
    .then(r => r.json())
    .then(d => {
        if (d.total === 0) {
            texto.innerHTML = '<span class="text-danger">Nadie recibirá notificación con esta selección.</span>';
            return;
        }
        const muestra = d.nombres.join(', ');
        const resto   = d.total > d.nombres.length ? ' y ' + (d.total - d.nombres.length) + ' más' : '';
        texto.innerHTML = '<strong>Se notificará a ' + d.total + ' persona(s).</strong> '
                        + '<span class="text-muted">' + muestra + resto + '</span>';
    })
    .catch(() => { texto.textContent = 'No se pudo calcular el total de destinatarios.'; });
}

// ── Estado del modal publicar ─────────────────────
let modoEdicion = false;
let idEdicion   = null;

function resetModalPublicar() {
    modoEdicion = false;
    idEdicion   = null;
    document.getElementById('formComunicado').action = routeStore;
    document.getElementById('formMethod').value      = 'POST';
    document.getElementById('modalPublicarTitulo').innerHTML =
        '<i class="fas fa-bullhorn text-danger mr-2"></i> Nueva Publicación';
    document.getElementById('btnGuardar').innerHTML =
        '<i class="fas fa-paper-plane mr-1"></i> Publicar';
    document.getElementById('formComunicado').reset();
    document.getElementById('fFecha').value = new Date().toISOString().slice(0,10);
    document.getElementById('fAutor').value = 'Capital Humano';
    document.getElementById('archivoActualWrap').classList.add('d-none');

    // Destinatarios a estado inicial
    document.getElementById('alcanceTodos').checked = true;
    $('.select-destinatarios').val(null).trigger('change.select2');
    toggleAlcance();
    actualizarPreview();

    // Trigger primer emoji/color
    document.getElementById('fCategoria').dispatchEvent(new Event('change'));
}

// NOTA: se usa jQuery porque Bootstrap 4 emite eventos jQuery,
// que NO son capturados por addEventListener nativo.
$('#modalPublicar').on('show.bs.modal', function () {
    if (!modoEdicion) resetModalPublicar();
});

document.getElementById('btnGuardar').addEventListener('click', function () {
    document.getElementById('formComunicado').submit();
});

@endcan

// ── Ver comunicado (modal) ────────────────────────
function verComunicado(id) {
    fetch(routeShow + id, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(com => {
        document.getElementById('verTitulo').textContent = com.titulo.toUpperCase();

        document.getElementById('verMeta').innerHTML =
            `<span class="text-uppercase font-weight-bold" style="color:#C0392B;font-size:.75rem;">${com.categoria}</span>`
            + ` &middot; <span>${com.autor}</span>`
            + ` &middot; <span>${com.fecha_publicacion.toString().slice(0, 10)}</span>`;

        const alcanceTxt = com.notificados_count
            ? `<i class="fas fa-bell mr-1"></i> ${com.alcance_label} — ${com.notificados_count} notificado(s)`
            : `<i class="fas fa-bell-slash mr-1"></i> ${com.alcance_label} — sin envíos registrados`;
        document.getElementById('verAlcance').innerHTML =
            `<span style="font-size:.78rem;">${alcanceTxt}</span>`;

        document.getElementById('verContenido').textContent =
            com.contenido_completo ?? com.extracto ?? '';

        // Archivo adjunto
        const archivoDiv  = document.getElementById('verArchivo');
        const archivoLink = document.getElementById('verArchivoLink');
        if (com.archivo) {
            archivoLink.href = `/storage/${com.archivo}`;
            archivoDiv.classList.remove('d-none');
        } else {
            archivoDiv.classList.add('d-none');
        }

        // Botones condicionales
        const btnEditar   = document.getElementById('btnEditarDesdeVer');
        const btnEliminar = document.getElementById('btnEliminarDesdeVer');

        if (btnEditar)   btnEditar.onclick   = () => abrirEditar(com);
        if (btnEliminar) btnEliminar.onclick = () => eliminarComunicado(com.id);

        $('#modalVer').modal('show');
    })
    .catch(() => alert('No se pudo cargar el comunicado.'));
}

@can('comunicados.editar')
// ── Abrir modal en modo edición ───────────────────
function abrirEditar(com) {
    modoEdicion = true;
    idEdicion   = com.id;

    document.getElementById('formComunicado').action = routeShow + com.id;
    document.getElementById('formMethod').value      = 'PUT';

    document.getElementById('modalPublicarTitulo').innerHTML =
        '<i class="fas fa-pencil-alt text-warning mr-2"></i> Editar Publicación';
    document.getElementById('btnGuardar').innerHTML =
        '<i class="fas fa-save mr-1"></i> Guardar cambios';

    document.getElementById('fTitulo').value     = com.titulo;
    document.getElementById('fCategoria').value  = com.categoria;
    document.getElementById('fEmoji').value      = com.icono_emoji   ?? '';
    document.getElementById('fColor').value      = com.color_fondo   ?? '#F3F4F6';
    document.getElementById('colorPicker').value = com.color_fondo   ?? '#F3F4F6';
    document.getElementById('fAutor').value      = com.autor;
    document.getElementById('fExtracto').value   = com.extracto           ?? '';
    document.getElementById('fContenido').value  = com.contenido_completo ?? '';

    // ── Fecha: limpiar cualquier componente de hora ──
    const fecha = com.fecha_publicacion
        ? com.fecha_publicacion.toString().slice(0, 10)
        : '';
    document.getElementById('fFecha').value = fecha;

    // ── Destinatarios ──
    const alcance = com.alcance ?? 'todos';
    document.querySelector(`input[name=alcance][value="${alcance}"]`).checked = true;

    $('#fDepartamentos').val((com.departamentos_ids ?? []).map(String)).trigger('change.select2');
    $('#fUsuarios').val((com.usuarios_ids ?? []).map(String)).trigger('change.select2');
    $('#fExcluidos').val((com.excluidos_ids ?? []).map(String)).trigger('change.select2');

    toggleAlcance();
    actualizarPreview();

    if (com.archivo) {
        document.getElementById('archivoActualWrap').classList.remove('d-none');
        document.getElementById('archivoActualLink').href = `/storage/${com.archivo}`;
    } else {
        document.getElementById('archivoActualWrap').classList.add('d-none');
    }

    $('#modalVer').modal('hide');
    setTimeout(() => $('#modalPublicar').modal('show'), 400);
}
@endcan

@can('comunicados.eliminar')
// ── Eliminar comunicado ───────────────────────────
function eliminarComunicado(id) {
    if (!confirm('¿Eliminar este comunicado?')) return;
    const form = document.getElementById('formEliminar');
    form.action = `${routeIndex}/${id}`;
    form.submit();
}
@endcan
</script>
@endsection