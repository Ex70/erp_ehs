@extends('adminlte::page')
@section('title', 'Detalle del rol')

@section('content_header')
    <h1>Rol — {{ ucfirst(str_replace('_', ' ', $rol->name)) }}</h1>
@stop

@section('content')
<div class="row">
    {{-- Permisos por módulo --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permisos asignados</h3>
            </div>
            <div class="card-body">
                @forelse($permisosPorModulo as $modulo => $lista)
                    <p class="text-uppercase font-weight-bold text-muted mb-1">
                        {{ $modulo }}
                    </p>
                    <div class="mb-3">
                        @foreach($lista as $permiso)
                            <span class="badge badge-success mr-1 mb-1">
                                <i class="fas fa-check mr-1"></i>
                                {{ explode('.', $permiso->name)[1] ?? $permiso->name }}
                            </span>
                        @endforeach
                    </div>
                @empty
                    <p class="text-muted">Sin permisos asignados.</p>
                @endforelse
            </div>
            <div class="card-footer">
                <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Regresar
                </a>
                @can('roles.editar')
                    <a href="{{ route('roles.edit', $rol) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Usuarios con este rol --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Usuarios con este rol</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->name }}</td>
                                <td>{{ $usuario->username }}</td>
                                <td>
                                    @if($usuario->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">
                                    Sin usuarios asignados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($usuarios->hasPages())
                <div class="card-footer">{{ $usuarios->links() }}</div>
            @endif
        </div>
    </div>
</div>
@stop
@section('css')@stop
@section('js')@stop