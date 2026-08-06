@extends('adminlte::page')

@section('title', 'Departamentos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Departamentos</h1>
        @can('departamentos.crear')
            <a href="{{ route('departamentos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo departamento
            </a>
        @endcan
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Catálogo de departamentos</h3>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th style="width:80px;">Clave</th>
                        <th>Departamento</th>
                        <th>Responsable</th>
                        <th class="text-center" style="width:100px;">Puestos</th>
                        <th class="text-center" style="width:110px;">Colaboradores</th>
                        <th class="text-center" style="width:90px;">Estatus</th>
                        <th class="text-right" style="width:170px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departamentos as $departamento)
                        <tr>
                            <td>
                                @if($departamento->clave)
                                    <span class="badge badge-secondary">{{ $departamento->clave }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $departamento->nombre }}</strong>
                                @if($departamento->descripcion)
                                    <br><small class="text-muted">
                                        {{ Str::limit($departamento->descripcion, 60) }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                {{ $departamento->responsable->name ?? '—' }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $departamento->puestos_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-primary">{{ $departamento->usuarios_count }}</span>
                            </td>
                            <td class="text-center">
                                @if($departamento->activo)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('departamentos.show', $departamento) }}"
                                   class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @can('departamentos.editar')
                                    <a href="{{ route('departamentos.edit', $departamento) }}"
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan

                                @can('departamentos.eliminar')
                                    <form action="{{ route('departamentos.destroy', $departamento) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar el departamento {{ $departamento->nombre }}?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No hay departamentos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer clearfix">
            {{ $departamentos->links() }}
        </div>
    </div>
@stop