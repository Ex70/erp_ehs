@extends('adminlte::page')

@php
    $accion = $editando
        ? route('adquisiciones.logistica.combustible.update', $solicitud)
        : route('adquisiciones.logistica.combustible.store');
    $firmantes = [
        'reviso1'  => 'Revisó #1',
        'reviso2'  => 'Revisó #2',
        'autorizo' => 'Autorizó',
    ];
@endphp

@section('title', $editando ? 'Editar solicitud de combustible' : 'Nueva solicitud de combustible')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="mb-0">
            <i class="fas fa-gas-pump mr-2"></i>
            {{ $editando ? 'Editar solicitud de combustible' : 'Nueva solicitud de combustible' }}
            @if ($editando)
                <small class="text-muted">· {{ $solicitud->folio }}</small>
            @endif
        </h1>
        <a href="{{ $editando ? route('adquisiciones.logistica.combustible.show', $solicitud) : route('adquisiciones.logistica.combustible.index') }}"
           class="btn btn-default mt-2 mt-md-0">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
@stop

@section('content')

    @if (session('error'))
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong><i class="fas fa-exclamation-triangle mr-1"></i> No se pudo guardar:</strong>
            <ul class="mb-0 mt-1">
                @foreach (collect($errors->all())->unique() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! $empresa)
        <div class="alert alert-warning">
            No existe en el catálogo de Empresas la clave <strong>{{ config('logistica.combustible.empresa_clave') }}</strong>
            configurada en <code>config/logistica.php</code>.
        </div>
    @endif

    <form method="POST" action="{{ $accion }}" id="form-solicitud" autocomplete="off">
        @csrf
        @if ($editando)
            @method('PUT')
        @endif

        {{-- Datos generales --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clipboard-list mr-1"></i> Datos generales</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Empresa que destina el recurso</label>
                        <input type="text" class="form-control" value="{{ $empresa?->nombre }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Folio</label>
                        <input type="text" class="form-control font-weight-bold text-primary"
                               value="{{ $solicitud->folio ?? 'Se asigna al guardar' }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="f-fecha">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" id="f-fecha" required
                               class="form-control @error('fecha') is-invalid @enderror"
                               value="{{ old('fecha', $solicitud->fecha?->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Nº de cotización</label>
                        <input type="text" class="form-control" value="{{ $solicitud->cotizacion }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Cliente</label>
                        <input type="text" class="form-control" value="{{ $solicitud->cliente }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Monto solicitado</label>
                        <input type="text" id="f-monto" class="form-control font-weight-bold text-right" readonly>
                    </div>
                </div>
            </div>
        </div>

        {{-- Unidades y cargas --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-car mr-1"></i> Unidades y cargas</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" id="btn-agregar-carga">
                        <i class="fas fa-plus mr-1"></i> Agregar carga
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                @if ($vehiculos->isEmpty())
                    <div class="alert alert-warning m-3">
                        No hay unidades activas. Regístralas en
                        <a href="{{ route('adquisiciones.logistica.vehiculos.index') }}">Unidades vehiculares</a>.
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:50px" class="text-center">Nº</th>
                                <th style="width:180px">Descripción</th>
                                <th>Unidad</th>
                                <th style="width:170px">Total</th>
                                <th style="width:45px"></th>
                            </tr>
                        </thead>
                        <tbody id="tbody-conceptos"></tbody>
                        <tfoot>
                            <tr style="background:#92d050">
                                <td colspan="3" class="text-right font-weight-bold">SUBTOTAL</td>
                                <td class="font-weight-bold text-right" id="subtotal">$ 0.00</td>
                                <td></td>
                            </tr>
                            <tr style="background:#00b050;color:#fff">
                                <td colspan="3" class="text-right font-weight-bold">TOTAL</td>
                                <td class="font-weight-bold text-right" id="total">$ 0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Destinatario --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user mr-1"></i> Datos del destinatario</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="f-destinatario">Nombre <span class="text-danger">*</span></label>
                        <select name="destinatario_user_id" id="f-destinatario"
                                class="form-control @error('destinatario_user_id') is-invalid @enderror">
                            <option value="">-- Selecciona --</option>
                            @foreach ($responsables as $u)
                                <option value="{{ $u->id }}" @selected((int) old('destinatario_user_id', $solicitud->destinatario_user_id) === $u->id)>
                                    {{ mb_strtoupper($u->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="f-banco">Banco</label>
                        <input type="text" name="banco" id="f-banco" maxlength="80" class="form-control text-uppercase"
                               value="{{ old('banco', $solicitud->banco) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="f-cuenta">Cuenta</label>
                        <input type="text" name="cuenta" id="f-cuenta" maxlength="40" class="form-control text-uppercase"
                               value="{{ old('cuenta', $solicitud->cuenta) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="f-clabe">CLABE</label>
                        <input type="text" name="clabe" id="f-clabe" maxlength="18" inputmode="numeric"
                               class="form-control @error('clabe') is-invalid @enderror"
                               value="{{ old('clabe', $solicitud->clabe) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Forma de pago</label>
                        <input type="text" class="form-control" value="{{ $solicitud->forma_pago }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="f-observaciones">Observaciones</label>
                        <input type="text" name="observaciones" id="f-observaciones" maxlength="255"
                               class="form-control text-uppercase"
                               value="{{ old('observaciones', $solicitud->observaciones) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Firmas --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-signature mr-1"></i> Firmas de autorización</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="f-elaboro">Elaboró — nombre <span class="text-danger">*</span></label>
                        <select name="elaboro_user_id" id="f-elaboro"
                                class="form-control @error('elaboro_user_id') is-invalid @enderror">
                            <option value="">-- Selecciona --</option>
                            @foreach ($responsables as $u)
                                <option value="{{ $u->id }}"
                                        data-cargo="{{ \App\Services\Logistica\ResponsableService::cargoDe($u) }}"
                                        @selected((int) old('elaboro_user_id', $solicitud->elaboro_user_id) === $u->id)>
                                    {{ mb_strtoupper($u->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="f-elaboro-cargo">Elaboró — cargo</label>
                        <input type="text" name="elaboro_cargo" id="f-elaboro-cargo" maxlength="120"
                               class="form-control text-uppercase"
                               value="{{ old('elaboro_cargo', $solicitud->elaboro_cargo) }}">
                        <small class="form-text text-muted">Se llena con el puesto; puedes ajustarlo.</small>
                    </div>
                </div>

                @foreach ($firmantes as $clave => $etiqueta)
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="f-{{ $clave }}-nombre">{{ $etiqueta }} — nombre</label>
                            <input type="text" name="{{ $clave }}_nombre" id="f-{{ $clave }}-nombre" maxlength="120"
                                   class="form-control text-uppercase"
                                   value="{{ old($clave . '_nombre', $solicitud->{$clave . '_nombre'}) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="f-{{ $clave }}-cargo">{{ $etiqueta }} — cargo</label>
                            <input type="text" name="{{ $clave }}_cargo" id="f-{{ $clave }}-cargo" maxlength="120"
                                   class="form-control text-uppercase"
                                   value="{{ old($clave . '_cargo', $solicitud->{$clave . '_cargo'}) }}">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="d-flex justify-content-end mb-4">
            <a href="{{ $editando ? route('adquisiciones.logistica.combustible.show', $solicitud) : route('adquisiciones.logistica.combustible.index') }}"
               class="btn btn-secondary mr-2">Cancelar</a>
            <button type="submit" class="btn btn-success" @disabled(! $empresa)>
                <i class="fas fa-save mr-1"></i> Guardar solicitud
            </button>
        </div>
    </form>
@stop

@section('js')
<script>
$(function () {
    const vehiculos          = @js($vehiculos);
    const conceptosIniciales = @js($conceptos);
    const $tbody             = $('#tbody-conceptos');
    let indice = 0;

    function dinero(n) {
        return '$ ' + Number(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalcular() {
        let suma = 0;
        $tbody.find('[data-campo="total"]').each(function () { suma += parseFloat(this.value) || 0; });
        $('#subtotal, #total').text(dinero(suma));
        $('#f-monto').val(dinero(suma));
        $tbody.children('tr').each(function (i) { $(this).find('.js-num').text(i + 1); });
    }

    function crearFila(c) {
        const i = indice++;
        const $tr = $(`
            <tr>
                <td class="text-center align-middle font-weight-bold js-num"></td>
                <td class="align-middle">
                    <input type="hidden" data-campo="id">
                    <input type="text" class="form-control form-control-sm" value="COMBUSTIBLE" readonly>
                </td>
                <td><select class="form-control form-control-sm" data-campo="vehiculo_id" required></select></td>
                <td><input type="number" class="form-control form-control-sm text-right" data-campo="total" min="0.01" step="0.01" required></td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-outline-danger btn-xs js-eliminar" title="Eliminar carga">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`);

        $tr.find('[data-campo]').each(function () {
            $(this).attr('name', 'conceptos[' + i + '][' + $(this).data('campo') + ']');
        });

        const $sel = $tr.find('[data-campo="vehiculo_id"]');
        $sel.append($('<option>').val('').text('-- Selecciona la unidad --'));
        vehiculos.forEach(function (v) { $sel.append($('<option>').val(v.id).text(v.nombre)); });
        $sel.val(c.vehiculo_id ? String(c.vehiculo_id) : '');

        $tr.find('[data-campo="id"]').val(c.id || '');
        $tr.find('[data-campo="total"]').val(c.total ?? '');

        $tbody.append($tr);
        recalcular();
    }

    $('#btn-agregar-carga').on('click', function () { crearFila({}); });

    $tbody.on('click', '.js-eliminar', function () {
        if ($tbody.children('tr').length === 1) {
            alert('La solicitud necesita al menos una carga.');
            return;
        }
        $(this).closest('tr').remove();
        recalcular();
    });

    $tbody.on('input', '[data-campo="total"]', recalcular);

    // El cargo de "Elaboró" se toma del puesto del colaborador
    $('#f-elaboro').on('change', function () {
        const cargo = $(this).find(':selected').data('cargo');
        if (cargo) $('#f-elaboro-cargo').val(cargo);
    });

    $('#form-solicitud').on('submit', function () {
        $(this).find('button[type="submit"]').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando…');
    });

    conceptosIniciales.forEach(function (c) { crearFila(c); });
    if (!conceptosIniciales.length) crearFila({});
});
</script>
@stop