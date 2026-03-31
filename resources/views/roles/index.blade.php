@extends('adminlte::page')
@section('title', 'Roles')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Roles del sistema</h1>
        @can('roles.crear')
            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo rol
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
                        <th>Rol</th>
                        <th>Permisos asignados</th>
                        <th>Usuarios</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $rol)
                        <tr>
                            <td>
                                <span class="badge badge-primary badge-lg px-3 py-2">
                                    {{ ucfirst(str_replace('_', ' ', $rol->name)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    {{ $rol->permissions_count }} permisos
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $rol->users_count }} usuarios
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('roles.show', $rol) }}"
                                   class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('roles.editar')
                                    <a href="{{ route('roles.edit', $rol) }}"
                                       class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('roles.eliminar')
                                    <form action="{{ route('roles.destroy', $rol) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar el rol {{ $rol->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs"
                                                {{ $rol->users_count > 0 ? 'disabled title=Tiene usuarios asignados' : '' }}>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No hay roles registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $roles->links() }}
        </div>
    </div>
@stop
@section('css')@stop
@section('js')@stop