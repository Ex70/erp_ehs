@php
    $seleccionados = old('puestos', $asignados ?? []);
@endphp

<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label>Nombre del departamento <span class="text-danger">*</span></label>
            <input type="text" name="nombre"
                   class="form-control @error('nombre') is-invalid @enderror"
                   value="{{ old('nombre', $departamento->nombre ?? '') }}">
            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Clave</label>
            <input type="text" name="clave" maxlength="20"
                   class="form-control text-uppercase @error('clave') is-invalid @enderror"
                   value="{{ old('clave', $departamento->clave ?? '') }}"
                   placeholder="Ej. ADQ, SIS">
            @error('clave')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="form-text text-muted">Abreviatura para reportes.</small>
        </div>
    </div>
</div>

<div class="form-group">
    <label>Descripción</label>
    <textarea name="descripcion" rows="2"
              class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $departamento->descripcion ?? '') }}</textarea>
    @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label>Responsable del departamento</label>
    <select name="responsable_id"
            class="form-control @error('responsable_id') is-invalid @enderror">
        <option value="">-- Sin asignar --</option>
        @foreach($responsables as $r)
            <option value="{{ $r->id }}"
                @selected(old('responsable_id', $departamento->responsable_id ?? '') == $r->id)>
                {{ $r->name }}
            </option>
        @endforeach
    </select>
    @error('responsable_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<hr>

<div class="form-group">
    <label>Puestos habilitados en este departamento</label>
    <p class="text-muted mb-2">
        <small>
            Un mismo puesto puede pertenecer a varios departamentos. Solo los puestos
            marcados aquí aparecerán al asignar usuarios a este departamento.
        </small>
    </p>

    <div class="border rounded p-3" style="max-height:320px;overflow-y:auto;">
        <div class="row">
            @forelse($puestos as $p)
                <div class="col-md-4">
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input"
                               id="puesto_{{ $p->id }}" name="puestos[]"
                               value="{{ $p->id }}"
                               @checked(in_array($p->id, $seleccionados))>
                        <label class="custom-control-label" for="puesto_{{ $p->id }}">
                            {{ $p->nombre }}
                        </label>
                    </div>
                </div>
            @empty
                <div class="col-12 text-muted">
                    No hay puestos activos en el catálogo.
                    <a href="{{ route('puestos.create') }}">Crear uno</a>.
                </div>
            @endforelse
        </div>
    </div>

    @error('puestos')<div class="text-danger mt-1"><small>{{ $message }}</small></div>@enderror
</div>

<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="hidden" name="activo" value="0">
        <input type="checkbox" class="custom-control-input" id="activo"
               name="activo" value="1"
               {{ old('activo', $departamento->activo ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="activo">Departamento activo</label>
    </div>
</div>