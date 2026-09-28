@extends('adminlte::page')

@section('title', 'Unidades vehiculares')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="mb-0"><i class="fas fa-car mr-2"></i>Unidades vehiculares</h1>
            <small class="text-muted">
                Logística y Entregas ·
                {{ $stats['activos'] }} activa{{ $stats['activos'] != 1 ? 's' : '' }}
                de {{ $stats['total'] }} registrada{{ $stats['total'] != 1 ? 's' : '' }}
            </small>
        </div>
        <button type="button" class="btn btn-primary mt-2 mt-md-0" id="btn-nueva-unidad">
            <i class="fas fa-plus mr-1"></i> Nueva unidad
        </button>
    </div>
@stop

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <form method="GET" action="{{ route('adquisiciones.logistica.vehiculos.index') }}" class="form-row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                           placeholder="Buscar por nombre, placas, marca o modelo…">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select name="estado" class="form-control form-control-sm">
                        <option value="">Todas las unidades</option>
                        <option value="activos" @selected(request('estado') === 'activos')>Solo activas</option>
                        <option value="inactivos" @selected(request('estado') === 'inactivos')>Solo inactivas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-secondary">
                        <i class="fas fa-search mr-1"></i> Filtrar
                    </button>
                    @if (request()->filled('q') || request()->filled('estado'))
                        <a href="{{ route('adquisiciones.logistica.vehiculos.index') }}" class="btn btn-sm btn-link">Limpiar</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Unidad</th>
                            <th>Vehículo</th>
                            <th>Placas</th>
                            <th>Combustible</th>
                            <th class="text-right">Rendimiento</th>
                            <th class="text-right">Km actual</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center" style="width:90px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vehiculos as $v)
                            <tr class="{{ $v->activo ? '' : 'text-muted' }}">
                                <td class="align-middle">
                                    <span class="d-inline-block rounded-circle mr-2 align-middle"
                                          style="width:12px;height:12px;background:{{ $v->color_etiqueta }}"></span>
                                    <strong>{{ $v->nombre }}</strong>
                                </td>
                                <td class="align-middle">
                                    {{ $v->descripcion ?: '—' }}
                                    @if ($v->color)
                                        <small class="d-block text-muted">{{ $v->color }}</small>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $v->placas ?: '—' }}</td>
                                <td class="align-middle">
                                    <span class="badge {{ $v->combustible === 'diesel' ? 'badge-dark' : 'badge-warning' }}">
                                        {{ $v->combustible_label }}
                                    </span>
                                </td>
                                <td class="align-middle text-right">
                                    {{ $v->rendimiento_km_l ? number_format((float) $v->rendimiento_km_l, 1) . ' km/L' : '—' }}
                                </td>
                                <td class="align-middle text-right">
                                    {{ $v->km_actual !== null ? number_format($v->km_actual) . ' km' : '—' }}
                                    @if ($v->km_actualizado_at)
                                        <small class="d-block text-muted">{{ $v->km_actualizado_at->diffForHumans() }}</small>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge badge-{{ $v->activo ? 'success' : 'secondary' }}">
                                        {{ $v->activo ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="align-middle text-center text-nowrap">
                                    <button type="button" class="btn btn-warning btn-xs btn-editar" title="Editar"
                                            data-vehiculo="{{ json_encode($v->only(['id', 'nombre', 'marca', 'modelo', 'anio', 'placas', 'numero_serie', 'color', 'combustible', 'rendimiento_km_l', 'km_actual', 'color_etiqueta', 'observaciones', 'activo'])) }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @can('logistica.eliminar')
                                        <form action="{{ route('adquisiciones.logistica.vehiculos.destroy', $v) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar la unidad ' + @js($v->nombre) + '? Su historial se conserva.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-car-side fa-2x d-block mb-2"></i>
                                    No hay unidades que coincidan con el filtro.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($vehiculos->hasPages())
            <div class="card-footer clearfix">
                {{ $vehiculos->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>

    {{-- Modal alta / edición --}}
    <div class="modal fade" id="modal-vehiculo" tabindex="-1" role="dialog" aria-labelledby="modal-vehiculo-titulo" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="form-vehiculo" method="POST" action="{{ route('adquisiciones.logistica.vehiculos.store') }}"
                  class="modal-content" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="vehiculo-method" value="POST">
                <input type="hidden" name="vehiculo_id" id="vehiculo-id" value="{{ old('vehiculo_id') }}">

                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="modal-vehiculo-titulo">Nueva unidad</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger js-alerta-errores">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Revisa los campos marcados.
                        </div>
                    @endif

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="f-nombre">Nombre / alias <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="f-nombre" maxlength="60" required
                                   class="form-control text-uppercase @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre') }}" placeholder="GOL, HILUX, TORNADO 1…">
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label for="f-marca">Marca</label>
                            <input type="text" name="marca" id="f-marca" maxlength="60"
                                   class="form-control text-uppercase @error('marca') is-invalid @enderror"
                                   value="{{ old('marca') }}">
                            @error('marca') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label for="f-modelo">Modelo</label>
                            <input type="text" name="modelo" id="f-modelo" maxlength="60"
                                   class="form-control text-uppercase @error('modelo') is-invalid @enderror"
                                   value="{{ old('modelo') }}">
                            @error('modelo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="f-anio">Año</label>
                            <input type="number" name="anio" id="f-anio" min="1980" max="{{ now()->year + 1 }}"
                                   class="form-control @error('anio') is-invalid @enderror"
                                   value="{{ old('anio') }}">
                            @error('anio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="f-placas">Placas</label>
                            <input type="text" name="placas" id="f-placas" maxlength="20"
                                   class="form-control text-uppercase @error('placas') is-invalid @enderror"
                                   value="{{ old('placas') }}">
                            @error('placas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="f-serie">Núm. de serie</label>
                            <input type="text" name="numero_serie" id="f-serie" maxlength="40"
                                   class="form-control text-uppercase @error('numero_serie') is-invalid @enderror"
                                   value="{{ old('numero_serie') }}">
                            @error('numero_serie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="f-color">Color</label>
                            <input type="text" name="color" id="f-color" maxlength="30"
                                   class="form-control text-uppercase @error('color') is-invalid @enderror"
                                   value="{{ old('color') }}">
                            @error('color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="f-combustible">Combustible <span class="text-danger">*</span></label>
                            <select name="combustible" id="f-combustible"
                                    class="form-control @error('combustible') is-invalid @enderror">
                                @foreach ($combustibles as $clave => $label)
                                    <option value="{{ $clave }}" @selected(old('combustible', 'gasolina') === $clave)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('combustible') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="f-rendimiento">Rendimiento (km/L)</label>
                            <input type="number" name="rendimiento_km_l" id="f-rendimiento" min="0" max="99.99" step="0.01"
                                   class="form-control @error('rendimiento_km_l') is-invalid @enderror"
                                   value="{{ old('rendimiento_km_l') }}">
                            @error('rendimiento_km_l') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="f-km">Km actual</label>
                            <input type="number" name="km_actual" id="f-km" min="0"
                                   class="form-control @error('km_actual') is-invalid @enderror"
                                   value="{{ old('km_actual') }}">
                            @error('km_actual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="f-etiqueta">Color en tableros</label>
                            <input type="color" name="color_etiqueta" id="f-etiqueta"
                                   class="form-control @error('color_etiqueta') is-invalid @enderror"
                                   value="{{ old('color_etiqueta', '#1e3a5f') }}">
                            @error('color_etiqueta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="f-observaciones">Observaciones</label>
                        <textarea name="observaciones" id="f-observaciones" rows="2" maxlength="1000"
                                  class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones') }}</textarea>
                        @error('observaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <input type="hidden" name="activo" value="0">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="f-activo" name="activo" value="1"
                               @checked(old('activo', '1') == '1')>
                        <label class="custom-control-label" for="f-activo">
                            Unidad activa (aparece en solicitudes y mantenimiento)
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
$(function () {
    const $modal = $('#modal-vehiculo');
    const $form  = $('#form-vehiculo');
    const urlStore  = @js(route('adquisiciones.logistica.vehiculos.store'));
    const urlUpdate = @js(route('adquisiciones.logistica.vehiculos.update', '__ID__'));

    const defaults = {
        nombre: '', marca: '', modelo: '', anio: '', placas: '', numero_serie: '',
        color: '', combustible: 'gasolina', rendimiento_km_l: '', km_actual: '',
        color_etiqueta: '#1e3a5f', observaciones: '', activo: true
    };

    function modoCrear() {
        $form.attr('action', urlStore);
        $('#vehiculo-method').val('POST');
        $('#vehiculo-id').val('');
        $('#modal-vehiculo-titulo').text('Nueva unidad');
    }

    function modoEditar(id, nombre) {
        $form.attr('action', urlUpdate.replace('__ID__', id));
        $('#vehiculo-method').val('PUT');
        $('#vehiculo-id').val(id);
        $('#modal-vehiculo-titulo').text('Editar unidad · ' + (nombre || ''));
    }

    function limpiarErrores() {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback, .js-alerta-errores').remove();
    }

    function llenar(datos) {
        Object.keys(defaults).forEach(function (campo) {
            let valor = datos[campo];
            if (valor === null || valor === undefined) valor = defaults[campo];

            if (campo === 'activo') {
                $('#f-activo').prop('checked', !!valor);
            } else {
                $form.find('[name="' + campo + '"]').val(valor);
            }
        });
    }

    $('#btn-nueva-unidad').on('click', function () {
        limpiarErrores();
        llenar({});
        modoCrear();
        $modal.modal('show');
    });

    $(document).on('click', '.btn-editar', function () {
        const datos = $(this).data('vehiculo');
        limpiarErrores();
        llenar(datos);
        modoEditar(datos.id, datos.nombre);
        $modal.modal('show');
    });

    // Si la validación falló, reabrir el modal en el modo correcto (los valores
    // capturados ya vienen de old()).
    @if ($errors->any())
        @if (old('vehiculo_id'))
            modoEditar(@js(old('vehiculo_id')), @js(old('nombre')));
        @else
            modoCrear();
        @endif
        $modal.modal('show');
    @endif
});
</script>
@stop