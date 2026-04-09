@extends('adminlte::page')
@section('title', 'Requerimientos de Compra')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-shopping-cart mr-2"></i>Requerimientos</h1>
        @can('adquisiciones.crear')
            <a href="{{ route('adquisiciones.requerimientos.create') }}"
               class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo requerimiento
            </a>
        @endcan
    </div>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Tarjetas --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>{{ $stats['total'] }}</h3><p>Total</p></div>
                <div class="icon"><i class="fas fa-file-alt"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $stats['pendientes'] }}</h3><p>Pendientes</p></div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $stats['cotizando'] }}</h3><p>Cotizando</p></div>
                <div class="icon"><i class="fas fa-search-dollar"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $stats['autorizados'] }}</h3><p>Autorizados</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('adquisiciones.requerimientos.index') }}"
                  class="row align-items-end">
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Folio</label>
                    <input type="text" name="q_folio" class="form-control form-control-sm"
                           value="{{ request('q_folio') }}" placeholder="REQ-...">
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Cliente</label>
                    <input type="text" name="q_cliente" class="form-control form-control-sm"
                           value="{{ request('q_cliente') }}">
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Estatus</label>
                    <select name="q_status" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($estatuses as $key => $label)
                            <option value="{{ $key }}"
                                {{ request('q_status') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Empresa</label>
                    <select name="q_empresa" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach($empresas as $e)
                            <option value="{{ $e->id }}"
                                {{ request('q_empresa') == $e->id ? 'selected' : '' }}>
                                {{ $e->clave }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Tipo</label>
                    <select name="q_tipo" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($tipos as $key => $label)
                            <option value="{{ $key }}"
                                {{ request('q_tipo') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm mr-1">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('adquisiciones.requerimientos.index') }}"
                       class="btn btn-secondary btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0" style="min-width:900px">
                <thead class="thead-dark">
                    <tr>
                        <th>Folio</th>
                        <th>Cliente</th>
                        <th>Empresa</th>
                        <th>Tipo</th>
                        <th>Analista</th>
                        <th>Monto Est.</th>
                        <th>Fecha Sol.</th>
                        <th>Fecha Ent.</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requerimientos as $r)
                        <tr>
                            <td>
                                <span class="badge badge-dark">{{ $r->folio }}</span>
                            </td>
                            <td>{{ $r->cliente?->nombre ?? '—' }}</td>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $r->empresaEmisora?->clave ?? '—' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $tipoCls = ['normal'=>'secondary','urgente'=>'warning','critico'=>'danger'];
                                @endphp
                                <span class="badge badge-{{ $tipoCls[$r->tipo] ?? 'secondary' }}">
                                    {{ ucfirst($r->tipo) }}
                                </span>
                            </td>
                            <td>{{ $r->analista?->name ?? '—' }}</td>
                            <td>
                                {{ $r->monto_estimado
                                    ? '$'.number_format($r->monto_estimado, 2)
                                    : '—' }}
                            </td>
                            <td>{{ $r->fecha_solicitud?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $r->fecha_entrega?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @php
                                    $cls = [
                                        'pendiente'  => 'secondary',
                                        'cotizando'  => 'info',
                                        'enviado'    => 'warning',
                                        'autorizado' => 'success',
                                        'cancelado'  => 'danger',
                                    ];
                                @endphp
                                <span class="badge badge-{{ $cls[$r->status] ?? 'secondary' }}">
                                    {{ ucfirst($r->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('adquisiciones.requerimientos.show', $r) }}"
                                   class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('adquisiciones.editar')
                                    <a href="{{ route('adquisiciones.requerimientos.edit', $r) }}"
                                       class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('adquisiciones.adjudicar')
                                    @if(!$r->autorizado)
                                        <button class="btn btn-success btn-xs"
                                                onclick="abrirAdjudicacion({{ $r->id }}, '{{ $r->folio }}')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                @endcan
                                @can('adquisiciones.eliminar')
                                    <form action="{{ route('adquisiciones.requerimientos.destroy', $r) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Enviar a papelera?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                No hay requerimientos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $requerimientos->withQueryString()->links() }}
        </div>
    </div>

    {{-- Modal adjudicación --}}
    <div class="modal fade" id="modal-adjudicar" tabindex="-1">
        <div class="modal-dialog">
            <form id="form-adjudicar" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-check-circle mr-1"></i>
                            Adjudicar requerimiento
                        </h5>
                        <button type="button" class="close text-white"
                                data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            Folio: <strong id="adj-folio-label"></strong>
                        </p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Monto autorizado <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="monto_autorizado"
                                               class="form-control" step="0.01" min="0"
                                               required placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Costo proveedor <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="costo_proveedor"
                                               class="form-control" step="0.01" min="0"
                                               required placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Fecha máxima de entrega</label>
                            <input type="date" name="fecha_max_entrega_aut"
                                   class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Adjudicar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@stop

@section('js')
<script>
function abrirAdjudicacion(id, folio) {
    document.getElementById('form-adjudicar').action =
        '/adquisiciones/requerimientos/' + id + '/adjudicar';
    document.getElementById('adj-folio-label').textContent = folio;
    $('#modal-adjudicar').modal('show');
}
</script>
@stop