@extends('adminlte::page')

@section('title', 'Solicitudes de combustible')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="mb-0"><i class="fas fa-gas-pump mr-2"></i>Solicitudes de combustible</h1>
            <small class="text-muted">
                Logística y Entregas · {{ $solicitudes->total() }} resultado{{ $solicitudes->total() != 1 ? 's' : '' }}
            </small>
        </div>
        @can('logistica.crear')
            <a href="{{ route('adquisiciones.logistica.combustible.create') }}" class="btn btn-primary mt-2 mt-md-0">
                <i class="fas fa-plus mr-1"></i> Nueva solicitud
            </a>
        @endcan
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

    {{-- Resumen del mes --}}
    <div class="row">
        <div class="col-md-4 col-lg-3">
            <div class="info-box bg-gradient-primary">
                <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Solicitado este mes</span>
                    <span class="info-box-number">$ {{ number_format($totalMes, 2) }}</span>
                </div>
            </div>
        </div>
        @foreach ($vehiculos->filter(fn ($v) => $v->activo || isset($gastoPorUnidad[$v->id])) as $v)
            <div class="col-6 col-md-4 col-lg-2">
                <div class="info-box" style="border-left: 4px solid {{ $v->color_etiqueta }};">
                    <div class="info-box-content">
                        <span class="info-box-text">{{ $v->nombre }}</span>
                        <span class="info-box-number">$ {{ number_format((float) ($gastoPorUnidad[$v->id] ?? 0), 2) }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <form method="GET" action="{{ route('adquisiciones.logistica.combustible.index') }}">
                <div class="form-row">
                    <div class="col-md-4 mb-2">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                               placeholder="Folio u observaciones…">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select name="vehiculo_id" class="form-control form-control-sm">
                            <option value="">Todas las unidades</option>
                            @foreach ($vehiculos as $v)
                                <option value="{{ $v->id }}" @selected((string) request('vehiculo_id') === (string) $v->id)>{{ $v->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm" title="Desde">
                    </div>
                    <div class="col-md-2 mb-2">
                        <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm" title="Hasta">
                    </div>
                </div>
                <button type="submit" class="btn btn-sm btn-secondary">
                    <i class="fas fa-search mr-1"></i> Filtrar
                </button>
                @if (collect(['q', 'vehiculo_id', 'desde', 'hasta'])->contains(fn ($k) => request()->filled($k)))
                    <a href="{{ route('adquisiciones.logistica.combustible.index') }}" class="btn btn-sm btn-link">Limpiar</a>
                @endif
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Cargas</th>
                            <th class="text-right">Monto</th>
                            <th>Destinatario</th>
                            <th>Elaboró</th>
                            <th class="text-center" style="width:120px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($solicitudes as $s)
                            <tr>
                                <td class="align-middle">
                                    <a href="{{ route('adquisiciones.logistica.combustible.show', $s) }}"><strong>{{ $s->folio }}</strong></a>
                                </td>
                                <td class="align-middle text-nowrap">{{ $s->fecha->format('d/m/Y') }}</td>
                                <td class="align-middle">
                                    @foreach ($s->conceptos as $c)
                                        <span class="badge badge-light border mr-1">
                                            {{ $c->vehiculo?->nombre ?? 'N/A' }} · ${{ number_format((float) $c->total, 0) }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="align-middle text-right font-weight-bold">$ {{ number_format((float) $s->monto, 2) }}</td>
                                <td class="align-middle">{{ $s->destinatario?->name ?? '—' }}</td>
                                <td class="align-middle">{{ $s->elaboro?->name ?? '—' }}</td>
                                <td class="align-middle text-center text-nowrap">
                                    <a href="{{ route('adquisiciones.logistica.combustible.pdf', $s) }}" target="_blank"
                                       class="btn btn-success btn-xs" title="Ver PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    @can('logistica.editar')
                                        <a href="{{ route('adquisiciones.logistica.combustible.edit', $s) }}"
                                           class="btn btn-warning btn-xs" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('logistica.eliminar')
                                        <form action="{{ route('adquisiciones.logistica.combustible.destroy', $s) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar la solicitud ' + @js($s->folio) + '? El folio no se volverá a usar.');">
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
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-gas-pump fa-2x d-block mb-2"></i>
                                    No hay solicitudes que coincidan con el filtro.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($solicitudes->hasPages())
            <div class="card-footer clearfix">
                {{ $solicitudes->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@stop