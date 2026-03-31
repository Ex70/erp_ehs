@extends('adminlte::page')
@section('title', 'Editar permiso')

@section('content_header')
    <h1>Editar permiso — <code>{{ $permiso->name }}</code></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del permiso</h3>
        </div>
        <form action="{{ route('permisos.update', $permiso) }}" method="POST">
            @csrf @method('PUT')
            <div class="card-body">

                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Módulo <span class="text-danger">*</span></label>
                            <input type="text" name="modulo" list="modulos-list"
                                   class="form-control @error('modulo') is-invalid @enderror"
                                   value="{{ old('modulo', $modulo) }}"
                                   id="modulo-input">
                            <datalist id="modulos-list">
                                @foreach($modulos as $m)
                                    <option value="{{ $m }}">
                                @endforeach
                            </datalist>
                            @error('modulo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-2 text-center pt-4 mt-2">
                        <span class="text-muted font-weight-bold">.</span>
                    </div>

                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Acción <span class="text-danger">*</span></label>
                            <input type="text" name="accion" list="acciones-list"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('accion', $accion) }}"
                                   id="accion-input">
                            <datalist id="acciones-list">
                                <option value="ver">
                                <option value="crear">
                                <option value="editar">
                                <option value="eliminar">
                                <option value="exportar">
                                <option value="importar">
                            </datalist>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Vista previa</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-key"></i>
                            </span>
                        </div>
                        <input type="text" id="preview" class="form-control bg-light" readonly
                               value="{{ $permiso->name }}">
                    </div>
                </div>

            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('permisos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Actualizar permiso
                </button>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
const modulo = document.getElementById('modulo-input');
const accion = document.getElementById('accion-input');
const prev   = document.getElementById('preview');

function actualizarPreview() {
    const m = modulo.value.trim().toLowerCase();
    const a = accion.value.trim().toLowerCase();
    prev.value = m && a ? m + '.' + a : (m || a || '');
}

modulo.addEventListener('input', actualizarPreview);
accion.addEventListener('input', actualizarPreview);
</script>
@stop