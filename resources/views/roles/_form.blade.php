{{-- Nombre del rol --}}
<div class="form-group">
    <label for="name">Nombre del rol</label>
    <input type="text" name="name" id="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $rol->name ?? '') }}" required>
    @error('name')
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror
</div>

<hr>
<h5 class="mb-3">Permisos</h5>

@foreach($permisos as $modulo => $permisosModulo)
    <div class="card card-outline card-primary">
        <div class="card-header py-2">
            <div class="custom-control custom-checkbox">
                <input type="checkbox"
                       class="custom-control-input check-modulo"
                       id="modulo_{{ $modulo }}"
                       data-modulo="{{ $modulo }}">
                <label class="custom-control-label font-weight-bold text-uppercase"
                       for="modulo_{{ $modulo }}">
                    {{ $modulo }}
                </label>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($permisosModulo as $permiso)
                    @php
                        $partes  = explode('.', $permiso->name);
                        $accion  = ucfirst($partes[1] ?? '');
                        $alcance = isset($partes[2])
                            ? ($partes[2] === 'propio' ? ' propios' : ' ' . $partes[2])
                            : '';
                        $etiqueta = trim($accion . $alcance);
                    @endphp
                    <div class="col-md-3 mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox"
                                   name="permisos[]"
                                   value="{{ $permiso->name }}"
                                   id="permiso_{{ $permiso->id }}"
                                   class="custom-control-input check-permiso check-{{ $modulo }}"
                                   {{ in_array($permiso->name, $permisosActivos ?? []) ? 'checked' : '' }}>
                            <label class="custom-control-label"
                                   for="permiso_{{ $permiso->id }}"
                                   title="{{ $permiso->name }}">
                                {{ $etiqueta }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach