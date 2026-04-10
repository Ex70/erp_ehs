{{-- Nombre --}}
<div class="form-group">
    <label>Nombre del producto / servicio <span class="text-danger">*</span></label>
    <input type="text" name="nombre"
           class="form-control @error('nombre') is-invalid @enderror"
           value="{{ old('nombre', $producto->nombre ?? '') }}"
           placeholder="DESCRIPCIÓN DEL PRODUCTO"
           required>
    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    {{-- Categoría --}}
    <div class="col-md-6">
        <div class="form-group">
            <label>Categoría</label>
            <div class="input-group">
                <select name="categoria_id" id="select-categoria"
                        class="form-control @error('categoria_id') is-invalid @enderror">
                    <option value="">EJ: PAPELERÍA, TI, FERRETERÍA...</option>
                    @foreach($categorias as $c)
                        <option value="{{ $c->id }}"
                            {{ old('categoria_id', $producto->categoria_id ?? '') == $c->id ? 'selected' : '' }}>
                            {{ $c->nombre }}
                        </option>
                    @endforeach
                </select>
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                            onclick="nuevaCategoria()"
                            title="Agregar categoría">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            @error('categoria_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Unidad de medida --}}
    <div class="col-md-6">
        <div class="form-group">
            <label>Unidad de medida</label>
            <select name="unidad_medida_id"
                    class="form-control @error('unidad_medida_id') is-invalid @enderror">
                <option value="">PZA, KG, LT, SERVICIO...</option>
                @foreach($unidades as $u)
                    <option value="{{ $u->id }}"
                        {{ old('unidad_medida_id', $producto->unidad_medida_id ?? '') == $u->id ? 'selected' : '' }}>
                        {{ $u->clave }} — {{ $u->nombre }}
                    </option>
                @endforeach
            </select>
            @error('unidad_medida_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    {{-- Precio de referencia --}}
    <div class="col-md-6">
        <div class="form-group">
            <label>Precio de referencia ($)</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">$</span>
                </div>
                <input type="number" name="precio_referencia"
                       class="form-control @error('precio_referencia') is-invalid @enderror"
                       value="{{ old('precio_referencia', $producto->precio_referencia ?? '') }}"
                       step="0.01" min="0" placeholder="0.00">
            </div>
            @error('precio_referencia')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Proveedores sugeridos --}}
    <div class="col-md-6">
        <div class="form-group">
            <label>Proveedor(es) sugerido(s)</label>
            <select name="proveedor_ids[]" id="select-proveedores"
                    class="form-control @error('proveedor_ids') is-invalid @enderror"
                    multiple>
                @foreach($proveedores as $pv)
                    <option value="{{ $pv->id }}"
                        @if(isset($producto->proveedores))
                            {{ $producto->proveedores->contains($pv->id) ? 'selected' : '' }}
                        @endif
                        {{ is_array(old('proveedor_ids')) && in_array($pv->id, old('proveedor_ids', [])) ? 'selected' : '' }}>
                        {{ $pv->nombre }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">
                Mantén <kbd>Ctrl</kbd> para seleccionar varios
            </small>
            @error('proveedor_ids')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- Especificaciones --}}
<div class="form-group">
    <label>Especificaciones / Notas</label>
    <textarea name="especificaciones" rows="3"
              class="form-control @error('especificaciones') is-invalid @enderror"
              placeholder="CARACTERÍSTICAS, MARCA, MODELO, ETC.">{{ old('especificaciones', $producto->especificaciones ?? '') }}</textarea>
    @error('especificaciones')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    {{-- Imagen --}}
    <div class="col-md-6">
        <div class="form-group">
            <label>Imagen del producto</label>
            @if(isset($producto->imagen) && $producto->imagen)
                <div class="mb-2">
                    <img src="{{ asset('storage/'.$producto->imagen) }}"
                         style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6"
                         id="preview-imagen">
                </div>
            @else
                <div class="mb-2" id="preview-imagen-container" style="display:none">
                    <img id="preview-imagen" style="width:80px;height:80px;object-fit:cover;border-radius:8px">
                </div>
            @endif
            <input type="file" name="imagen" id="input-imagen"
                   class="form-control-file @error('imagen') is-invalid @enderror"
                   accept="image/*">
            <small class="text-muted">JPG, PNG, WEBP — máx. 4MB</small>
            @error('imagen')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Ficha técnica --}}
    <div class="col-md-6">
        <div class="form-group">
            <label>Ficha técnica (PDF)</label>
            @if(isset($producto->ficha_tecnica) && $producto->ficha_tecnica)
                <div class="mb-2">
                    <a href="{{ asset('storage/'.$producto->ficha_tecnica) }}"
                       target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-file-pdf text-danger"></i>
                        Ver ficha actual
                    </a>
                </div>
            @endif
            <input type="file" name="ficha_tecnica"
                   class="form-control-file @error('ficha_tecnica') is-invalid @enderror"
                   accept=".pdf">
            <small class="text-muted">Solo PDF — máx. 10MB</small>
            @error('ficha_tecnica')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@isset($producto->id)
    <div class="form-group">
        <div class="custom-control custom-switch">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" class="custom-control-input"
                   id="activo" name="activo" value="1"
                   {{ old('activo', $producto->activo ?? true) ? 'checked' : '' }}>
            <label class="custom-control-label" for="activo">Producto activo</label>
        </div>
    </div>
@endisset

{{-- Modal rápido nueva categoría --}}
<div class="modal fade" id="modal-cat-rapida" tabindex="-1" style="z-index:1060">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva categoría</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-0">
                    <label>Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="cat-rapida-nombre"
                           class="form-control" placeholder="Ej: Ferretería">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm"
                        data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm"
                        onclick="guardarCategoriaRapida()">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>