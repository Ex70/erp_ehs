@extends('adminlte::page')
@section('title', 'Registrar incidencia')

@section('content_header')
    <div>
        <h1 class="mb-0">Registrar nueva incidencia</h1>
        <small class="text-muted">
            Departamento de Sistemas — {{ config('app.name') }}
        </small>
    </div>
@stop

@section('content')

    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-1"></i>
        Llena el formulario para registrar tu incidencia.
        Todos los campos marcados con <strong>*</strong> son obligatorios.
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <form action="{{ route('helpdesk.tickets.store') }}"
              method="POST" enctype="multipart/form-data" id="form-ticket">
            @csrf
            <div class="card-body">

                <div class="row">
                    {{-- Nombre (solo lectura, viene del usuario) --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $usuario->name }}" readonly>
                        </div>
                    </div>

                    {{-- Departamento (puesto del usuario) --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Departamento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $usuario->puesto?->nombre ?? 'Sin departamento' }}"
                                   readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Tipo de falla --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tipo de falla <span class="text-danger">*</span></label>
                            <select name="tipo_falla_id"
                                    class="form-control @error('tipo_falla_id') is-invalid @enderror"
                                    required>
                                <option value="">Selecciona...</option>
                                @foreach($tiposFalla as $t)
                                    <option value="{{ $t->id }}"
                                        {{ old('tipo_falla_id') == $t->id ? 'selected' : '' }}>
                                        {{ $t->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_falla_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Prioridad --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Prioridad <span class="text-danger">*</span></label>
                            <select name="prioridad"
                                    class="form-control @error('prioridad') is-invalid @enderror"
                                    required>
                                <option value="">Selecciona...</option>
                                <option value="baja"    {{ old('prioridad') == 'baja'    ? 'selected' : '' }}>Baja</option>
                                <option value="media"   {{ old('prioridad') == 'media'   ? 'selected' : '' }}>Media</option>
                                <option value="alta"    {{ old('prioridad') == 'alta'    ? 'selected' : '' }}>Alta</option>
                                <option value="urgente" {{ old('prioridad') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            @error('prioridad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Categoría de servicio --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Categoría de servicio</label>
                            <select name="categoria_servicio_id" class="form-control">
                                <option value="">Selecciona...</option>
                                @foreach($categorias as $c)
                                    <option value="{{ $c->id }}"
                                        {{ old('categoria_servicio_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Descripción --}}
                <div class="form-group">
                    <label>
                        Descripción de la incidencia
                        <span class="text-danger">*</span>
                        <small class="text-muted">(mínimo 10 caracteres)</small>
                    </label>
                    <textarea name="descripcion" rows="4"
                              class="form-control @error('descripcion') is-invalid @enderror"
                              placeholder='Ej: "Problemas con la conexión a la red de wifi planta baja"'
                              minlength="10" required
                              id="desc-input">{{ old('descripcion') }}</textarea>
                    <small class="text-muted">
                        <span id="char-count">0</span> caracteres
                    </small>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Evidencia --}}
                <div class="form-group">
                    <label>Evidencia (imagen o archivo)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input"
                               id="evidencia" name="evidencia"
                               accept="image/*,.pdf,.doc,.docx">
                        <label class="custom-file-label" for="evidencia">
                            Seleccionar archivo
                        </label>
                    </div>
                    <small class="text-muted">
                        Formatos aceptados: imágenes, PDF, Word. Máx. 5 MB.
                    </small>
                    @error('evidencia')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('helpdesk.tickets.index') }}"
                   class="btn btn-secondary"
                   onclick="return confirm('¿Deseas cancelar? Los cambios no guardados se perderán.')">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane mr-1"></i> Enviar incidencia
                </button>
            </div>
        </form>
    </div>

@stop

@section('js')
<script>
// Contador de caracteres
const desc = document.getElementById('desc-input');
const count = document.getElementById('char-count');
desc.addEventListener('input', function() {
    count.textContent = this.value.length;
    count.className = this.value.length < 10 ? 'text-danger' : 'text-success';
});
count.textContent = desc.value.length;

// Label del archivo
document.getElementById('evidencia').addEventListener('change', function(e) {
    const label = document.querySelector('.custom-file-label');
    label.textContent = e.target.files[0]?.name || 'Seleccionar archivo';
});
</script>
@stop