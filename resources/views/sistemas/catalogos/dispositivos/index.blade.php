@extends('adminlte::page')
@section('title', 'Catálogo de Dispositivos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-laptop mr-2"></i>Dispositivos</h1>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-nuevo">
            <i class="fas fa-plus"></i> Nuevo dispositivo
        </button>
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
                        <th>Estado</th>
                        <th>Asignaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dispositivos as $d)
                        <tr>
                            <td>{{ $d->id }}</td>
                            <td>{{ $d->nombre }}</td>
                            <td>
                                @if($d->activo)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $d->asignaciones()->count() }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-xs"
                                        onclick="abrirEditar({{ $d->id }}, '{{ $d->nombre }}', {{ $d->activo ? 1 : 0 }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('sistemas.dispositivos.destroy', $d) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar {{ $d->nombre }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-xs"
                                            {{ $d->asignaciones()->count() > 0 ? 'disabled title=Tiene asignaciones activas' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No hay dispositivos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $dispositivos->links() }}</div>
    </div>

    {{-- Modal Nuevo --}}
    <div class="modal fade" id="modal-nuevo" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('sistemas.dispositivos.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nuevo dispositivo</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre"
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre') }}"
                                   placeholder="Ej: Laptop">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Editar --}}
    <div class="modal fade" id="modal-editar" tabindex="-1">
        <div class="modal-dialog">
            <form id="form-editar" method="POST">
                @csrf @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar dispositivo</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="edit-nombre"
                                   class="form-control" required>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" class="custom-control-input"
                                       id="edit-activo" name="activo" value="1">
                                <label class="custom-control-label" for="edit-activo">
                                    Activo
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Actualizar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@stop

@section('js')
<script>
function abrirEditar(id, nombre, activo) {
    document.getElementById('form-editar').action =
        '/sistemas/dispositivos/' + id;
    document.getElementById('edit-nombre').value = nombre;
    document.getElementById('edit-activo').checked = activo === 1;
    $('#modal-editar').modal('show');
}

// Reabrir modal si hay error de validación
@if($errors->any())
    $('#modal-nuevo').modal('show');
@endif
</script>
@stop