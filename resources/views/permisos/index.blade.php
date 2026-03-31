@extends('adminlte::page')
@section('title', 'Permisos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Permisos del sistema</h1>
        @can('permisos.crear')
            <a href="{{ route('permisos.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo permiso
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

    @foreach($porModulo as $modulo => $lista)
        <div class="card card-outline card-primary mb-3">
            <div class="card-header">
                <h3 class="card-title text-uppercase">
                    <i class="fas fa-layer-group mr-2"></i>{{ $modulo }}
                </h3>
                <div class="card-tools">
                    <span class="badge badge-primary">
                        {{ $lista->count() }} permisos
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Permiso</th>
                            <th>Roles que lo tienen</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lista as $permiso)
                            <tr>
                                <td>
                                    <code>{{ $permiso->name }}</code>
                                </td>
                                <td>
                                    @foreach($permiso->roles as $rol)
                                        <span class="badge badge-secondary">
                                            {{ $rol->name }}
                                        </span>
                                    @endforeach
                                </td>
                                <td>
                                    @can('permisos.editar')
                                        <a href="{{ route('permisos.edit', $permiso) }}"
                                           class="btn btn-warning btn-xs">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('permisos.eliminar')
                                        <form action="{{ route('permisos.destroy', $permiso) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar permiso {{ $permiso->name }}?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-xs">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@stop

@section('css')@stop
@section('js')@stop