<div class="row">

    {{-- Usuario del sistema (obligatorio) --}}
    <div class="form-group">
        <label>Usuario <span class="text-danger">*</span></label>
        <select name="user_id"
                class="form-control @error('user_id') is-invalid @enderror"
                id="select-usuario"
                onchange="cargarDatosUsuario(this)">
            <option value="">— Selecciona un usuario —</option>
            @foreach($usuarios as $u)
                <option value="{{ $u->id }}"
                        data-puesto="{{ $u->puesto?->nombre ?? '—' }}"
                        data-area="{{ $u->puesto?->nombre ?? '' }}"
                        {{ old('user_id', $asignacion_ip->user_id ?? '') == $u->id ? 'selected' : '' }}>
                    {{ $u->name }} — {{ $u->puesto?->nombre ?? 'Sin puesto' }}
                </option>
            @endforeach
        </select>
        @error('user_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Info autocompletada del usuario --}}
    <div class="row" id="info-usuario" style="display:none">
        <div class="col-md-6">
            <div class="form-group">
                <label>Puesto</label>
                <input type="text" id="show-puesto" class="form-control bg-light" readonly>
                <small class="text-muted">Se toma del perfil del usuario seleccionado.</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Área (editable)</label>
                <input type="text" name="area" list="areas-list"
                    id="input-area"
                    class="form-control @error('area') is-invalid @enderror"
                    value="{{ old('area', $asignacion_ip->area ?? '') }}"
                    placeholder="Ej: Adquisiciones">
                <datalist id="areas-list">
                    @foreach($areas as $area)
                        <option value="{{ $area }}">
                    @endforeach
                </datalist>
                @error('area')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    {{-- Datos de red --}}
    <div class="card card-outline card-primary mb-3">
        <div class="card-header py-2">
            <h3 class="card-title">
                <i class="fas fa-network-wired mr-1"></i> Datos de red
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Dirección IP <span class="text-danger">*</span></label>
                        <input type="text" name="direccion_ip" id="input-ip"
                            class="form-control @error('direccion_ip') is-invalid @enderror"
                            value="{{ old('direccion_ip', $asignacion_ip->direccion_ip ?? '') }}"
                            placeholder="192.168.0.111">
                        <small class="text-muted">Formato: 192.168.0.X</small>
                        @error('direccion_ip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Dirección MAC <span class="text-danger">*</span></label>
                        <input type="text" name="direccion_mac" id="input-mac"
                            class="form-control @error('direccion_mac') is-invalid @enderror"
                            value="{{ old('direccion_mac', $asignacion_ip->direccion_mac ?? '') }}"
                            placeholder="00:1e:c2:9e:28:6b"
                            maxlength="17">
                        <small class="text-muted">Formato: XX:XX:XX:XX:XX:XX</small>
                        @error('direccion_mac')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- Dispositivo --}}
<div class="card card-outline card-warning mb-3">
    <div class="card-header py-2">
        <h3 class="card-title">
            <i class="fas fa-laptop mr-1"></i> Dispositivo
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tipo de dispositivo <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="dispositivo_id"
                                class="form-control @error('dispositivo_id') is-invalid @enderror">
                            <option value="">— Selecciona —</option>
                            @foreach($dispositivos as $d)
                                <option value="{{ $d->id }}"
                                    {{ old('dispositivo_id', $asignacion_ip->dispositivo_id ?? '') == $d->id ? 'selected' : '' }}>
                                    {{ $d->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <a href="{{ url('sistemas/dispositivos') }}"
                               target="_blank"
                               class="btn btn-outline-secondary btn-sm"
                               title="Gestionar catálogo">
                                <i class="fas fa-cog"></i>
                            </a>
                        </div>
                    </div>
                    @error('dispositivo_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Marca <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="marca_id"
                                class="form-control @error('marca_id') is-invalid @enderror">
                            <option value="">— Selecciona —</option>
                            @foreach($marcas as $m)
                                <option value="{{ $m->id }}"
                                    {{ old('marca_id', $asignacion_ip->marca_id ?? '') == $m->id ? 'selected' : '' }}>
                                    {{ $m->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <a href="{{ url('sistemas/marcas') }}"
                               target="_blank"
                               class="btn btn-outline-secondary btn-sm"
                               title="Gestionar catálogo">
                                <i class="fas fa-cog"></i>
                            </a>
                        </div>
                    </div>
                    @error('marca_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Modelo <span class="text-danger">*</span></label>
                    <input type="text" name="modelo"
                           class="form-control @error('modelo') is-invalid @enderror"
                           value="{{ old('modelo', $asignacion_ip->modelo ?? '') }}"
                           placeholder="Ej: Aspire A13">
                    @error('modelo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Número de serie <span class="text-danger">*</span></label>
                    <input type="text" name="numero_serie"
                           class="form-control @error('numero_serie') is-invalid @enderror"
                           value="{{ old('numero_serie', $asignacion_ip->numero_serie ?? '') }}"
                           placeholder="Ej: MXN5609TK9">
                    @error('numero_serie')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Fecha de asignación</label>
                    <input type="date" name="fecha_asignacion"
                           class="form-control @error('fecha_asignacion') is-invalid @enderror"
                           value="{{ old('fecha_asignacion', isset($asignacion_ip->fecha_asignacion) ? $asignacion_ip->fecha_asignacion->format('Y-m-d') : '') }}">
                    @error('fecha_asignacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Datos de ubicación --}}
<div class="card card-outline card-success mb-3">
    <div class="card-header py-2">
        <h3 class="card-title">
            <i class="fas fa-map-marker-alt mr-1"></i> Ubicación
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Área <span class="text-danger">*</span></label>
                    <input type="text" name="area" list="areas-list"
                           class="form-control @error('area') is-invalid @enderror"
                           value="{{ old('area', $asignacion_ip->area ?? '') }}"
                           placeholder="Ej: Adquisiciones">
                    <datalist id="areas-list">
                        @foreach($areas as $area)
                            <option value="{{ $area }}">
                        @endforeach
                    </datalist>
                    @error('area')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Puesto <span class="text-danger">*</span></label>
                    <input type="text" name="puesto"
                           class="form-control @error('puesto') is-invalid @enderror"
                           value="{{ old('puesto', $asignacion_ip->puesto ?? '') }}"
                           placeholder="Ej: Gerente de Ventas">
                    @error('puesto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>