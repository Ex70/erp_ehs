@extends('adminlte::page')
@section('title', 'Redes y Conectividad')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-network-wired mr-2"></i>Redes y Conectividad
        </h1>
        @can('redes.crear')
            <a href="{{ route('sistemas.redes.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo registro
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Tarjetas de resumen --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total registros</p>
                </div>
                <div class="icon"><i class="fas fa-server"></i></div>
                <span class="small-box-footer">Asignaciones activas</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['areas'] }}</h3>
                    <p>Áreas registradas</p>
                </div>
                <div class="icon"><i class="fas fa-building"></i></div>
                <span class="small-box-footer">Distintas áreas</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['tipos'] }}</h3>
                    <p>Tipos de dispositivo</p>
                </div>
                <div class="icon"><i class="fas fa-laptop"></i></div>
                <span class="small-box-footer">Clases de equipo</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['ultimo'] ? \Carbon\Carbon::parse($stats['ultimo'])->format('d/m/Y') : '—' }}</h3>
                    <p>Último registro</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                <span class="small-box-footer">Fecha de última asignación</span>
            </div>
        </div>
    </div>

    {{-- Filtros de búsqueda --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('sistemas.redes.index') }}"
                  class="row align-items-end">
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Dirección IP</label>
                    <input type="text" name="q_ip" class="form-control form-control-sm"
                           placeholder="192.168.0..."
                           value="{{ request('q_ip') }}">
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Usuario</label>
                    <input type="text" name="q_usuario" class="form-control form-control-sm"
                           placeholder="Nombre..."
                           value="{{ request('q_usuario') }}">
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Área</label>
                    <select name="q_area" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach($areas as $area)
                            <option value="{{ $area }}"
                                {{ request('q_area') == $area ? 'selected' : '' }}>
                                {{ $area }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Dispositivo</label>
                    <select name="q_dispositivo" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($tiposDispositivo as $tipo)
                            <option value="{{ $tipo }}"
                                {{ request('q_dispositivo') == $tipo ? 'selected' : '' }}>
                                {{ $tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Dirección MAC</label>
                    <input type="text" name="q_mac" class="form-control form-control-sm"
                           placeholder="00:1e:..."
                           value="{{ request('q_mac') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm mr-1">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="{{ route('sistemas.redes.index') }}"
                       class="btn btn-secondary btn-sm">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de registros --}}
    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0"
                   style="min-width:900px">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Dirección IP</th>
                        <th>Dispositivo</th>
                        <th>Área</th>
                        <th>Puesto</th>
                        <th>Marca / Modelo</th>
                        <th>N° Serie</th>
                        <th>MAC</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asignaciones as $a)
                        <tr>
                            <td>
                                <span class="badge badge-secondary">
                                    {{ $a->codigo }}
                                </span>
                            </td>
                            <td>{{ $a->nombre }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $a->direccion_ip }}
                                </span>
                            </td>
                            <td>{{ $a->dispositivo }}</td>
                            <td>
                                <span class="badge badge-success">
                                    {{ $a->area }}
                                </span>
                            </td>
                            <td>{{ $a->puesto }}</td>
                            <td>{{ $a->marca }} {{ $a->modelo }}</td>
                            <td>
                                <code style="font-size:11px">{{ $a->numero_serie }}</code>
                            </td>
                            <td>
                                <code style="font-size:11px">{{ $a->direccion_mac }}</code>
                            </td>
                            <td>
                                {{ $a->fecha_asignacion?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td>
                                <a href="{{ route('sistemas.redes.show', $a) }}"
                                   class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('redes.editar')
                                    <a href="{{ route('sistemas.redes.edit', $a) }}"
                                       class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('redes.eliminar')
                                    <form action="{{ route('sistemas.redes.destroy', $a) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar registro {{ $a->codigo }} — {{ $a->nombre }}?')">
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
                            <td colspan="11" class="text-center text-muted py-4">
                                No se encontraron registros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $asignaciones->withQueryString()->links() }}
        </div>
    </div>

@stop

@section('css')@stop
@section('js')@stop