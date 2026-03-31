@extends('adminlte::page')
@section('title', 'Nuevo permiso')

@section('content_header')
    <h1>Nuevo permiso</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del permiso</h3>
        </div>
        <form action="{{ route('permisos.store') }}" method="POST">
            @csrf
            <div class="card-body">

                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Módulo <span class="text-danger">*</span></label>
                            <input type="text" name="modulo" list="modulos-list"
                                   class="form-control @error('modulo') is-invalid @enderror"
                                   value="{{ old('modulo') }}"
                                   placeholder="Ej. reportes"
                                   id="modulo-input">
                            <datalist id="modulos-list">
                                @foreach($modulos as $modulo)
                                    <option value="{{ $modulo }}">
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
                                   value="{{ old('accion') }}"
                                   placeholder="Ej. exportar"
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

                {{-- Vista previa del permiso --}}
                <div class="form-group">
                    <label>Vista previa</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-key"></i>
                            </span>
                        </div>
                        <input type="text" id="preview" class="form-control bg-light"
                               readonly placeholder="modulo.accion">
                    </div>
                </div>

            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('permisos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar permiso
                </button>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
// Vista previa en tiempo real
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