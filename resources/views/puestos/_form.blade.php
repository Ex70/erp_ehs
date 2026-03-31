{{-- Nombre --}}
<div class="form-group">
    <label>Nombre del puesto <span class="text-danger">*</span></label>
    <input type="text" name="nombre"
           class="form-control @error('nombre') is-invalid @enderror"
           value="{{ old('nombre', $puesto->nombre ?? '') }}"
           placeholder="Ej. Coordinador Operativo">
    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Descripción --}}
<div class="form-group">
    <label>Descripción</label>
    <textarea name="descripcion" rows="3"
              class="form-control @error('descripcion') is-invalid @enderror"
              placeholder="Descripción breve del puesto (opcional)">{{ old('descripcion', $puesto->descripcion ?? '') }}</textarea>
    @error('descripcion')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Estado --}}
<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="hidden" name="activo" value="0">
        <input type="checkbox" class="custom-control-input" id="activo"
               name="activo" value="1"
               {{ old('activo', $puesto->activo ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="activo">Puesto activo</label>
    </div>
</div>