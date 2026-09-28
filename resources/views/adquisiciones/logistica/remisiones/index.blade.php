@extends('adminlte::page')

@section('title', 'Notas de remisión')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="mb-0"><i class="fas fa-file-signature mr-2"></i>Notas de remisión</h1>
            <small class="text-muted">
                Logística y Entregas · {{ $remisiones->total() }} resultado{{ $remisiones->total() != 1 ? 's' : '' }}
            </small>
        </div>
        @can('logistica.crear')
            <a href="{{ route('adquisiciones.logistica.remisiones.create') }}" class="btn btn-primary mt-2 mt-md-0">
                <i class="fas fa-plus mr-1"></i> Nueva remisión
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

    {{-- Resumen del mes por empresa --}}
    <div class="row">
        @foreach ($empresas as $e)
            <div class="col-6 col-lg-3">
                <div class="small-box" style="background: {{ $formatos[$e->clave]['color'] ?? '#6c757d' }}; color: #fff;">
                    <div class="inner">
                        <h3>{{ $delMes[$e->id] ?? 0 }}</h3>
                        <p class="mb-0 text-truncate" title="{{ $e->nombre }}">{{ $e->nombre }}</p>
                        <small>este mes</small>
                    </div>
                    <div class="icon"><i class="fas fa-truck-loading"></i></div>
                    @can('logistica.crear')
                        <a href="{{ route('adquisiciones.logistica.remisiones.create', ['empresa' => $e->clave]) }}" class="small-box-footer">
                            Nueva remisión <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <form method="GET" action="{{ route('adquisiciones.logistica.remisiones.index') }}">
                <div class="form-row">
                    <div class="col-md-3 mb-2">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                               placeholder="Folio, referencia, cliente o producto…">
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="empresa_id" class="form-control form-control-sm">
                            <option value="">Todas las empresas</option>
                            @foreach ($empresas as $e)
                                <option value="{{ $e->id }}" @selected((string) request('empresa_id') === (string) $e->id)>{{ $e->clave }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select name="dependencia_id" class="form-control form-control-sm">
                            <option value="">Todos los clientes</option>
                            @foreach ($dependencias as $d)
                                <option value="{{ $d->id }}" @selected((string) request('dependencia_id') === (string) $d->id)>{{ $d->nombre }}</option>
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
                @if (collect(['q', 'empresa_id', 'dependencia_id', 'desde', 'hasta'])->contains(fn ($k) => request()->filled($k)))
                    <a href="{{ route('adquisiciones.logistica.remisiones.index') }}" class="btn btn-sm btn-link">Limpiar</a>
                @endif
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Folio</th>
                            <th>Empresa</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Referencia</th>
                            <th class="text-center">Partidas</th>
                            <th>Entrega</th>
                            <th class="text-center" style="width:120px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($remisiones as $r)
                            <tr>
                                <td class="align-middle">
                                    <a href="{{ route('adquisiciones.logistica.remisiones.show', $r) }}"><strong>{{ $r->folio }}</strong></a>
                                    @if ($r->num_remision)
                                        <small class="d-block text-muted">{{ $r->num_remision }}</small>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <span class="badge" style="background: {{ $formatos[$r->empresa->clave]['color'] ?? '#6c757d' }}; color: #fff;">
                                        {{ $r->empresa->clave }}
                                    </span>
                                </td>
                                <td class="align-middle text-nowrap">{{ $r->fecha->format('d/m/Y') }}</td>
                                <td class="align-middle">{{ $r->dependencia->nombre }}</td>
                                <td class="align-middle">{{ $r->referencia ?: '—' }}</td>
                                <td class="align-middle text-center">{{ $r->partidas_count }}</td>
                                <td class="align-middle">{{ $r->entrega?->name ?? '—' }}</td>
                                <td class="align-middle text-center text-nowrap">
                                    <a href="{{ route('adquisiciones.logistica.remisiones.pdf', $r) }}" target="_blank"
                                       class="btn btn-success btn-xs" title="Ver PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    @can('logistica.editar')
                                        <a href="{{ route('adquisiciones.logistica.remisiones.edit', $r) }}"
                                           class="btn btn-warning btn-xs" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('logistica.eliminar')
                                        <form action="{{ route('adquisiciones.logistica.remisiones.destroy', $r) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar la remisión ' + @js($r->folio) + '? El folio no se volverá a usar.');">
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
                                    <i class="fas fa-file-signature fa-2x d-block mb-2"></i>
                                    No hay remisiones que coincidan con el filtro.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($remisiones->hasPages())
            <div class="card-footer clearfix">
                {{ $remisiones->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@stop