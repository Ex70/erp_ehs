@extends('adminlte::page')

@section('title', 'Perfil de usuario')

@section('content_header')
    <h1>Perfil — {{ $usuario->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body text-center">
                    @if($usuario->avatar)
                        <img src="{{ asset('storage/'.$usuario->avatar) }}"
                             class="profile-user-img img-fluid img-circle elevation-2 mb-3"
                             style="width:100px;height:100px;object-fit:cover;">
                    @else
                        <div class="img-circle elevation-2 mb-3 mx-auto d-flex align-items-center
                                    justify-content-center bg-secondary"
                             style="width:100px;height:100px;">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                    @endif
                    <h3 class="profile-username">{{ $usuario->name }}</h3>
                    <p class="text-muted">{{ '@'.$usuario->username }}</p>

                    @foreach($usuario->getRoleNames() as $rol)
                        <span class="badge badge-primary">
                            {{ ucfirst(str_replace('_', ' ', $rol)) }}
                        </span>
                    @endforeach

                    <div class="mt-2">
                        @if($usuario->activo)
                            <span class="badge badge-success">Activo</span>
                        @else
                            <span class="badge badge-secondary">Inactivo</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del usuario</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width:35%">Nombre completo</th>
                            <td>{{ $usuario->name }}</td>
                        </tr>
                        <tr>
                            <th>Usuario</th>
                            <td>{{ $usuario->username }}</td>
                        </tr>
                        <tr>
                            <th>Correo</th>
                            <td>{{ $usuario->email }}</td>
                        </tr>
                        <tr>
                            <th>Puesto</th>
                            <td>{{ $usuario->puesto?->nombre ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Rol asignado</th>
                            <td>
                                @foreach($usuario->getRoleNames() as $rol)
                                    <span class="badge badge-primary">{{ $rol }}</span>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>Permisos directos</th>
                            <td>
                                @forelse($usuario->permissions as $perm)
                                    <span class="badge badge-secondary">{{ $perm->name }}</span>
                                @empty
                                    <span class="text-muted">Hereda permisos del rol</span>
                                @endforelse
                            </td>
                        </tr>
                        <tr>
                            <th>Miembro desde</th>
                            <td>{{ $usuario->created_at->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Regresar
                    </a>
                    @can('usuarios.editar')
                        <a href="{{ route('usuarios.edit', $usuario) }}"
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@stop