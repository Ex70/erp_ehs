{{-- Nombre del rol --}}
<div class="form-group">
    <label>Nombre del rol <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $rol->name ?? '') }}" placeholder="Ej. supervisor">
    <small class="text-muted">Usa minúsculas y guiones bajos. Ej: jefe_area</small>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Permisos agrupados por módulo --}}
<div class="form-group">
    <label>Permisos asignados</label>

    @foreach($permisos as $modulo => $lista)
        <div class="card card-outline card-secondary mb-2">
            <div class="card-header py-2">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input check-modulo" id="modulo_{{ $modulo }}" data-modulo="{{ $modulo }}">
                    <label class="custom-control-label font-weight-bold text-uppercase" for="modulo_{{ $modulo }}">
                        {{ $modulo }}
                    </label>
                </div>
            </div>
            <div class="card-body py-2">
                <div class="row">
                    @foreach($lista as $permiso)
                        <div class="col-md-3 col-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input check-permiso check-{{ $modulo }}" id="perm_{{ $permiso->id }}" name="permissions[]" value="{{ $permiso->name }}" {{ isset($permisosActivos) && in_array($permiso->name, $permisosActivos) ? 'checked' : '' }} {{ old('permissions') && in_array($permiso->name, old('permissions', [])) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="perm_{{ $permiso->id }}">
                                    {{ implode('.', array_slice(explode('.', $permiso->name), 1)) }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>