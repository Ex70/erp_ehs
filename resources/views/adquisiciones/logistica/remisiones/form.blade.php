@extends('adminlte::page')

@section('plugins.Select2', true)

@php
    $accion = $editando
        ? route('adquisiciones.logistica.remisiones.update', $remision)
        : route('adquisiciones.logistica.remisiones.store');
    $empresaSel = (int) old('empresa_id', $remision->empresa_id);
@endphp

@section('title', $editando ? 'Editar remisión' : 'Nueva remisión')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="mb-0">
            <i class="fas fa-file-signature mr-2"></i>
            {{ $editando ? 'Editar nota de remisión' : 'Nueva nota de remisión' }}
            @if ($editando)
                <small class="text-muted">· {{ $remision->folio }}</small>
            @endif
        </h1>
        <a href="{{ $editando ? route('adquisiciones.logistica.remisiones.show', $remision) : route('adquisiciones.logistica.remisiones.index') }}"
           class="btn btn-default mt-2 mt-md-0">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
@stop

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong><i class="fas fa-exclamation-triangle mr-1"></i> No se pudo guardar:</strong>
            <ul class="mb-0 mt-1">
                @foreach (collect($errors->all())->unique() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <small class="d-block mt-1">Si habías elegido imágenes nuevas, vuelve a seleccionarlas.</small>
        </div>
    @endif

    <form method="POST" action="{{ $accion }}" enctype="multipart/form-data" id="form-remision" autocomplete="off">
        @csrf
        @if ($editando)
            @method('PUT')
        @endif

        {{-- Empresa emisora --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-building mr-1"></i> Empresa que emite</h3>
            </div>
            <div class="card-body">
                <div class="btn-group btn-group-toggle d-flex flex-wrap" data-toggle="buttons">
                    @foreach ($empresas as $e)
                        <label class="btn btn-outline-secondary flex-fill {{ $empresaSel === $e->id ? 'active' : '' }}">
                            <input type="radio" name="empresa_id" value="{{ $e->id }}" data-clave="{{ $e->clave }}"
                                   @checked($empresaSel === $e->id)>
                            <span class="d-inline-block rounded-circle mr-1 align-middle"
                                  style="width:10px;height:10px;background:{{ $formatos[$e->clave]['color'] ?? '#6c757d' }}"></span>
                            {{ $e->nombre }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Datos generales --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clipboard-list mr-1"></i> Datos generales</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="f-dependencia">Cliente (dependencia) <span class="text-danger">*</span></label>
                    <select name="dependencia_id" id="f-dependencia"
                            class="form-control @error('dependencia_id') is-invalid @enderror">
                        <option value="">-- Selecciona el cliente --</option>
                        @foreach ($dependencias as $d)
                            <option value="{{ $d->id }}" @selected((int) old('dependencia_id', $remision->dependencia_id) === $d->id)>
                                {{ $d->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">¿No aparece? Agrégalo en Adquisiciones → Catálogos → Dependencias.</small>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="f-folio" id="lbl-folio">Folio</label>
                        <input type="text" name="folio" id="f-folio" maxlength="40"
                               class="form-control text-uppercase @error('folio') is-invalid @enderror"
                               value="{{ old('folio', $remision->folio) }}">
                        <small class="form-text text-muted" id="help-folio"></small>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="f-referencia">Referencia</label>
                        <input type="text" name="referencia" id="f-referencia" maxlength="80"
                               class="form-control text-uppercase @error('referencia') is-invalid @enderror"
                               value="{{ old('referencia', $remision->referencia) }}" placeholder="Folio de la cotización">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="f-fecha">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" id="f-fecha" required
                               class="form-control @error('fecha') is-invalid @enderror"
                               value="{{ old('fecha', $remision->fecha?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="form-row solo-num-remision">
                    <div class="form-group col-md-4">
                        <label for="f-num-remision">Número de remisión</label>
                        <input type="text" name="num_remision" id="f-num-remision" maxlength="40"
                               class="form-control text-uppercase"
                               value="{{ old('num_remision', $remision->num_remision) }}" placeholder="CEHS-XXX">
                        <small class="form-text text-muted">Si se deja vacío, en el PDF se imprime el folio.</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Responsables --}}
        <div class="card card-outline card-primary solo-responsables">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-check mr-1"></i> Responsables para firma</h3>
            </div>
            <div class="card-body">
                @if ($responsables->isEmpty())
                    <div class="alert alert-warning py-2">
                        No hay colaboradores activos en el departamento configurado para Logística.
                    </div>
                @endif
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="f-entrega">Entrega</label>
                        <select name="entrega_user_id" id="f-entrega" class="form-control @error('entrega_user_id') is-invalid @enderror">
                            <option value="">-- Selecciona --</option>
                            @foreach ($responsables as $u)
                                <option value="{{ $u->id }}" @selected((int) old('entrega_user_id', $remision->entrega_user_id) === $u->id)>
                                    {{ mb_strtoupper($u->name) }}{{ $u->puesto ? ' · ' . $u->puesto->nombre : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="f-recibe">Recibe (persona de la dependencia)</label>
                        <input type="text" name="recibe_nombre" id="f-recibe" list="lista-recibe" maxlength="120"
                               class="form-control text-uppercase"
                               value="{{ old('recibe_nombre', $remision->recibe_nombre) }}">
                        <datalist id="lista-recibe"></datalist>
                        <small class="form-text text-muted">Sugerencias tomadas de los Destinatarios del cliente.</small>
                    </div>
                    <div class="form-group col-md-4 solo-cargo-recibe">
                        <label for="f-cargo">Cargo de quien recibe</label>
                        <input type="text" name="recibe_cargo" id="f-cargo" maxlength="120"
                               class="form-control text-uppercase"
                               value="{{ old('recibe_cargo', $remision->recibe_cargo) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Partidas --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-boxes mr-1"></i> Partidas</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" id="btn-agregar-partida">
                        <i class="fas fa-plus mr-1"></i> Agregar partida
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:80px">No.</th>
                                <th>Descripción</th>
                                <th style="width:170px">U/M</th>
                                <th style="width:120px">Cantidad</th>
                                <th style="width:210px" class="col-imagen">Imagen</th>
                                <th style="width:45px"></th>
                            </tr>
                        </thead>
                        <tbody id="tbody-partidas"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Observaciones --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-sticky-note mr-1"></i> Observaciones / nota</h3>
            </div>
            <div class="card-body">
                <textarea name="observaciones" rows="3" maxlength="2000" class="form-control"
                          placeholder="Observaciones, notas o instrucciones especiales…">{{ old('observaciones', $remision->observaciones) }}</textarea>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-4">
            <a href="{{ $editando ? route('adquisiciones.logistica.remisiones.show', $remision) : route('adquisiciones.logistica.remisiones.index') }}"
               class="btn btn-secondary mr-2">Cancelar</a>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save mr-1"></i> Guardar remisión
            </button>
        </div>
    </form>
@stop

@section('js')
<script>
$(function () {
    const formatos          = @js($formatos);
    const unidades          = @js($unidades);
    const destinatarios     = @js($destinatarios);
    const partidasIniciales = @js($partidas);
    const editando          = @js($editando);
    const etiquetasFolio    = { AZA: 'Recibo (folio)', CEHS: 'Folio interno', EHS: 'Folio / cotización', MHR: 'Número de remisión' };
    const unidadDefault     = unidades.includes('PIEZA') ? 'PIEZA' : (unidades[0] || '');
    const $tbody            = $('#tbody-partidas');
    let indice = 0;

    function formatoActual() {
        const clave = $('input[name="empresa_id"]:checked').data('clave') || '';
        return { clave: clave, cfg: formatos[clave] || {} };
    }

    function aplicarFormato() {
        const { clave, cfg } = formatoActual();
        $('.solo-responsables').toggle(!!cfg.responsables);
        $('.solo-num-remision').toggle(!!cfg.num_remision);
        $('.solo-cargo-recibe').toggle(!!cfg.cargo_recibe);
        $('.col-imagen').toggle(!!cfg.imagenes);
        $('#lbl-folio').text(etiquetasFolio[clave] || 'Folio');
        $('#help-folio').text(editando
            ? 'Vacío = conserva el folio actual (o genera uno nuevo si cambias de empresa).'
            : 'Vacío = automático con formato ' + (cfg.formato_folio || '') + '.');
    }

    function crearFila(p) {
        const i = indice++;
        const $tr = $(`
            <tr>
                <td>
                    <input type="hidden" data-campo="id">
                    <input type="text" class="form-control form-control-sm text-center" data-campo="numero" maxlength="20">
                </td>
                <td><textarea class="form-control form-control-sm" data-campo="descripcion" rows="2" maxlength="2000" required></textarea></td>
                <td><select class="form-control form-control-sm" data-campo="unidad" required></select></td>
                <td><input type="number" class="form-control form-control-sm text-right" data-campo="cantidad" min="0.01" step="0.01" required></td>
                <td class="col-imagen">
                    <div class="d-flex align-items-center">
                        <img class="img-thumbnail mr-2 js-preview" style="max-width:60px;max-height:60px;display:none" alt="">
                        <div class="flex-fill" style="min-width:0">
                            <input type="file" class="form-control-file" data-campo="imagen" accept="image/*" style="font-size:12px">
                            <div class="custom-control custom-checkbox js-quitar mt-1" style="display:none">
                                <input type="checkbox" class="custom-control-input" data-campo="quitar_imagen" value="1" id="quitar-${i}">
                                <label class="custom-control-label small" for="quitar-${i}">Quitar imagen</label>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-outline-danger btn-xs js-eliminar" title="Eliminar partida">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`);

        $tr.find('[data-campo]').each(function () {
            $(this).attr('name', 'partidas[' + i + '][' + $(this).data('campo') + ']');
        });

        const $um = $tr.find('[data-campo="unidad"]');
        const opciones = unidades.slice();
        if (p.unidad && !opciones.includes(p.unidad)) opciones.push(p.unidad);
        opciones.forEach(function (u) { $um.append($('<option>').val(u).text(u)); });
        $um.val(p.unidad || unidadDefault);

        $tr.find('[data-campo="id"]').val(p.id || '');
        $tr.find('[data-campo="numero"]').val(p.numero ?? ($tbody.children('tr').length + 1));
        $tr.find('[data-campo="descripcion"]').val(p.descripcion || '');
        $tr.find('[data-campo="cantidad"]').val(p.cantidad ?? 1);

        if (p.imagen_url) {
            $tr.find('.js-preview').attr('src', p.imagen_url).show();
            $tr.find('.js-quitar').show();
        }

        $tr.find('.col-imagen').toggle(!!formatoActual().cfg.imagenes);
        $tbody.append($tr);
    }

    function actualizarDestinatarios() {
        const lista = destinatarios[$('#f-dependencia').val()] || [];
        const $dl = $('#lista-recibe').empty();
        lista.forEach(function (d) { $dl.append($('<option>').val(d.nombre).text(d.cargo || '')); });
    }

    // ── Eventos ──────────────────────────────────────────────────────────
    $('input[name="empresa_id"]').on('change', aplicarFormato);
    $('#f-dependencia').on('change', actualizarDestinatarios);
    $('#btn-agregar-partida').on('click', function () { crearFila({}); });

    $tbody.on('click', '.js-eliminar', function () {
        if ($tbody.children('tr').length === 1) {
            alert('La remisión necesita al menos una partida.');
            return;
        }
        $(this).closest('tr').remove();
    });

    $tbody.on('change', '[data-campo="imagen"]', function () {
        const archivo = this.files && this.files[0];
        if (archivo) {
            $(this).closest('td').find('.js-preview').attr('src', URL.createObjectURL(archivo)).css('opacity', 1).show();
        }
    });

    $tbody.on('change', '[data-campo="quitar_imagen"]', function () {
        $(this).closest('td').find('.js-preview').css('opacity', this.checked ? .3 : 1);
    });

    // Al elegir un destinatario conocido, completa su cargo
    $('#f-recibe').on('change', function () {
        const lista = destinatarios[$('#f-dependencia').val()] || [];
        const d = lista.find(function (x) { return x.nombre === $('#f-recibe').val().trim().toUpperCase(); });
        if (d && d.cargo && !$('#f-cargo').val()) $('#f-cargo').val(d.cargo);
    });

    $('#form-remision').on('submit', function () {
        $(this).find('button[type="submit"]').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando…');
    });

    if ($.fn.select2) {
        $('#f-dependencia').select2({ width: '100%', placeholder: '-- Selecciona el cliente --' });
    }

    // ── Estado inicial ───────────────────────────────────────────────────
    partidasIniciales.forEach(function (p) { crearFila(p); });
    if (!partidasIniciales.length) crearFila({});
    aplicarFormato();
    actualizarDestinatarios();
});
</script>
@stop