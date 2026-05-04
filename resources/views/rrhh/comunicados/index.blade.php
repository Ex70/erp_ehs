@extends('adminlte::page')

@section('title', 'Comunicados y Noticias')

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
                    <div class="d-flex justify-content-center align-items-center"
                         style="height:120px; background-color:{{ $com->color_fondo }};">
                        <span style="font-size:3rem;">{{ $com->icono_emoji }}</span>
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
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-uppercase" id="verTitulo"></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <hr class="mx-3 mt-2">
                <div class="modal-body pt-0">
                    <small class="text-muted" id="verMeta"></small>
                    <div class="mt-3" id="verContenido"></div>

                    {{-- Archivo adjunto --}}
                    <div id="verArchivo" class="mt-3 d-none">
                        <a id="verArchivoLink" href="#" target="_blank"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-paperclip mr-1"></i> Ver adjunto
                        </a>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                    @can('comunicados.editar')
                        <button class="btn btn-warning btn-sm" id="btnEditarDesdeVer">
                            <i class="fas fa-pencil-alt mr-1"></i> Editar
                        </button>
                    @endcan
                    @can('comunicados.eliminar')
                        <button class="btn btn-danger btn-sm" id="btnEliminarDesdeVer">
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
</style>
@endsection

@section('js')
<script>
// ── Rutas Laravel → JS ────────────────────────────
const routeIndex    = "{{ route('rrhh.comunicados.index') }}";
const routeStore    = "{{ route('rrhh.comunicados.store') }}";
const routeDefaults = "{{ route('rrhh.comunicados.defaults') }}";

// ── Filtros en tiempo real ─────────────────────────
function aplicarFiltros() {
    const buscar   = document.getElementById('filtroBuscar').value.toLowerCase();
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
    // Trigger primer emoji/color
    document.getElementById('fCategoria').dispatchEvent(new Event('change'));
}

document.getElementById('modalPublicar')?.addEventListener('show.bs.modal', function (e) {
    if (!modoEdicion) resetModalPublicar();
});

document.getElementById('btnGuardar').addEventListener('click', function () {
    document.getElementById('formComunicado').submit();
});

// ── Ver comunicado (modal) ────────────────────────
function verComunicado(id) {
    fetch(`${routeIndex}/../${id}`)
        .then(r => r.json())
        .then(com => {
            document.getElementById('verTitulo').textContent = com.titulo.toUpperCase();
            document.getElementById('verMeta').textContent   =
                `${com.categoria} · ${com.autor} · ${com.fecha_publicacion}`;
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

            // Botón Editar
            const btnEditar = document.getElementById('btnEditarDesdeVer');
            if (btnEditar) {
                btnEditar.onclick = () => abrirEditar(com);
            }

            // Botón Eliminar
            const btnEliminar = document.getElementById('btnEliminarDesdeVer');
            if (btnEliminar) {
                btnEliminar.onclick = () => eliminarComunicado(com.id);
            }

            $('#modalVer').modal('show');
        });
}

// ── Abrir modal en modo edición ───────────────────
function abrirEditar(com) {
    modoEdicion = true;
    idEdicion   = com.id;

    const url = `${routeIndex}/${com.id}`;
    document.getElementById('formComunicado').action = url;
    document.getElementById('formMethod').value      = 'PUT';
    document.getElementById('modalPublicarTitulo').innerHTML =
        '<i class="fas fa-pencil-alt text-warning mr-2"></i> Editar Publicación';
    document.getElementById('btnGuardar').innerHTML =
        '<i class="fas fa-save mr-1"></i> Guardar cambios';

    // Rellenar campos
    document.getElementById('fTitulo').value    = com.titulo;
    document.getElementById('fCategoria').value = com.categoria;
    document.getElementById('fEmoji').value     = com.icono_emoji ?? '';
    document.getElementById('fColor').value     = com.color_fondo ?? '#F3F4F6';
    document.getElementById('colorPicker').value= com.color_fondo ?? '#F3F4F6';
    document.getElementById('fFecha').value     = com.fecha_publicacion;
    document.getElementById('fAutor').value     = com.autor;
    document.getElementById('fExtracto').value  = com.extracto ?? '';
    document.getElementById('fContenido').value = com.contenido_completo ?? '';

    if (com.archivo) {
        document.getElementById('archivoActualWrap').classList.remove('d-none');
        document.getElementById('archivoActualLink').href = `/storage/${com.archivo}`;
    }

    $('#modalVer').modal('hide');
    setTimeout(() => $('#modalPublicar').modal('show'), 400);
}

// ── Eliminar comunicado ───────────────────────────
function eliminarComunicado(id) {
    if (!confirm('¿Eliminar este comunicado?')) return;
    const form = document.getElementById('formEliminar');
    form.action = `${routeIndex}/${id}`;
    form.submit();
}
</script>
@endsection