@extends('adminlte::page')

@section('title', 'Detalle del puesto')

@section('content_header')
    <h1>Puesto — {{ $puesto->nombre }}</h1>
@stop

@section('content')
    <div class="row">

        {{-- Info del puesto --}}
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <table class="table table-bordered mb-0">
                        <tr>
                            <th>Nombre</th>
                            <td>{{ $puesto->nombre }}</td>
                        </tr>
                        <tr>
                            <th>Descripción</th>
                            <td>{{ $puesto->descripcion ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td>
                                @if($puesto->activo)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Total usuarios</th>
                            <td>
                                <span class="badge badge-info">
                                    {{ $puesto->users_count }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('puestos.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Regresar
                    </a>
                    @can('puestos.editar')
                        <a href="{{ route('puestos.edit', $puesto) }}"
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Usuarios del puesto --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Usuarios asignados a este puesto
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($puesto->users as $usuario)
                                <tr>
                                    <td>{{ $usuario->name }}</td>
                                    <td>{{ $usuario->username }}</td>
                                    <td>{{ $usuario->email }}</td>
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        Sin usuarios asignados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@stop

@section('css')@stop
@section('js')@stop