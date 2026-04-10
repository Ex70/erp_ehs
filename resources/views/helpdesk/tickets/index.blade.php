@extends('adminlte::page')
@section('title', 'Tickets de incidencia')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                @can('tickets.ver.todos')
                    Todos los tickets de incidencia
                @else
                    Mis tickets de incidencia
                @endcan
            </h1>
            <small class="text-muted">
                Departamento de Sistemas — {{ config('app.name') }}
            </small>
        </div>
        <div>
            @can('tickets.dashboard')
                <a href="{{ route('helpdesk.dashboard') }}"
                   class="btn btn-secondary btn-sm mr-1">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
            @endcan
            <a href="{{ route('helpdesk.tickets.create') }}"
               class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nueva incidencia
            </a>
        </div>
    </div>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('helpdesk.tickets.index') }}"
                  class="row align-items-end">
                <div class="col-md-3">
                    <label class="small text-muted mb-1">Buscar nombre / ID</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="Buscar nombre / ID..."
                           value="{{ request('q') }}">
                </div>
                @can('tickets.ver.todos')
                    <div class="col-md-2">
                        <label class="small text-muted mb-1">Departamento</label>
                        <input type="text" name="q_departamento"
                               class="form-control form-control-sm"
                               placeholder="Todos los departamentos"
                               value="{{ request('q_departamento') }}">
                    </div>
                @endcan
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Estado</label>
                    <select name="q_seguimiento" class="form-control form-control-sm">
                        <option value="">Todos los estados</option>
                        @foreach($estatuses as $key => $label)
                            <option value="{{ $key }}"
                                {{ request('q_seguimiento') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Tipo</label>
                    <select name="q_tipo" class="form-control form-control-sm">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposFalla as $t)
                            <option value="{{ $t->id }}"
                                {{ request('q_tipo') == $t->id ? 'selected' : '' }}>
                                {{ $t->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Prioridad</label>
                    <select name="q_prioridad" class="form-control form-control-sm">
                        <option value="">Todas las prioridades</option>
                        <option value="baja"    {{ request('q_prioridad') == 'baja'    ? 'selected' : '' }}>Baja</option>
                        <option value="media"   {{ request('q_prioridad') == 'media'   ? 'selected' : '' }}>Media</option>
                        <option value="alta"    {{ request('q_prioridad') == 'alta'    ? 'selected' : '' }}>Alta</option>
                        <option value="urgente" {{ request('q_prioridad') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm mr-1">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('helpdesk.tickets.index') }}"
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
            <table class="table table-hover mb-0" style="min-width:900px">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Departamento</th>
                        <th>Tipo falla</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                        @php
                            $clsPrioridad  = Ticket::coloresPrioridad()[$t->prioridad] ?? 'secondary';
                            $clsSeguimiento= Ticket::coloresSeguimiento()[$t->seguimiento] ?? 'secondary';
                            $lblSeguimiento= Ticket::etiquetasSeguimiento()[$t->seguimiento] ?? $t->seguimiento;
                        @endphp
                        <tr>
                            <td>
                                <span class="badge badge-dark">{{ $t->folio }}</span>
                            </td>
                            <td>{{ $t->solicitante?->name ?? '—' }}</td>
                            <td>{{ $t->solicitante?->puesto?->nombre ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $t->tipoFalla?->color ?? 'secondary' }}">
                                    {{ $t->tipoFalla?->nombre ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $clsPrioridad }}">
                                    {{ ucfirst($t->prioridad) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $clsSeguimiento }}">
                                    {{ $lblSeguimiento }}
                                </span>
                            </td>
                            <td>{{ $t->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('helpdesk.tickets.show', $t) }}"
                                   class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                @if(auth()->user()->can('tickets.editar.todos') || $t->user_id === auth()->id())
                                    <a href="{{ route('helpdesk.tickets.edit', $t) }}"
                                       class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                @endif
                                @can('tickets.eliminar')
                                    <form action="{{ route('helpdesk.tickets.destroy', $t) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar ticket {{ $t->folio }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-ticket-alt fa-2x mb-2 d-block"></i>
                                No hay tickets registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">{{ $tickets->total() }} ticket(s)</small>
            {{ $tickets->withQueryString()->links() }}
        </div>
    </div>

@stop
@section('css')@stop
@section('js')@stop