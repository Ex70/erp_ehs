@extends('adminlte::page')

@section('title', 'Puestos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Puestos</h1>
        @can('puestos.crear')
            <a href="{{ route('puestos.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo puesto
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

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Usuarios</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($puestos as $puesto)
                        <tr>
                            <td>{{ $puesto->id }}</td>
                            <td>{{ $puesto->nombre }}</td>
                            <td>{{ $puesto->descripcion ?? '—' }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $puesto->users_count }}
                                    {{ Str::plural('usuario', $puesto->users_count) }}
                                </span>
                            </td>
                            <td>
                                @if($puesto->activo)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('puestos.show', $puesto) }}"
                                   class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('puestos.editar')
                                    <a href="{{ route('puestos.edit', $puesto) }}"
                                       class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('puestos.eliminar')
                                    <form action="{{ route('puestos.destroy', $puesto) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar este puesto?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs"
                                                {{ $puesto->users_count > 0 ? 'disabled title=Tiene usuarios asignados' : '' }}>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No hay puestos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $puestos->links() }}
        </div>
    </div>
@stop

@section('css')@stop
@section('js')@stop