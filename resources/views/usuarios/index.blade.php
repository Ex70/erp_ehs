@extends('adminlte::page')

@section('title', 'Usuarios')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Usuarios del sistema</h1>
        @can('usuarios.crear')
            <a href="{{ route('usuarios.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo usuario
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

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Puesto</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->id }}</td>
                            <td>
                                @if($usuario->avatar)
                                    <img src="{{ asset('storage/'.$usuario->avatar) }}"
                                         class="img-circle elevation-1 mr-1"
                                         style="width:28px;height:28px;object-fit:cover;">
                                @endif
                                {{ $usuario->name }}
                            </td>
                            <td>{{ $usuario->username }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td>{{ $usuario->puesto?->nombre ?? '—' }}</td>
                            <td>
                                @foreach($usuario->getRoleNames() as $rol)
                                    <span class="badge badge-primary">{{ $rol }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($usuario->activo)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('usuarios.show', $usuario) }}"
                                   class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('usuarios.editar')
                                    <a href="{{ route('usuarios.edit', $usuario) }}"
                                       class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('usuarios.eliminar')
                                    <form action="{{ route('usuarios.destroy', $usuario) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Desactivar este usuario?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No hay usuarios registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $usuarios->links() }}
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop