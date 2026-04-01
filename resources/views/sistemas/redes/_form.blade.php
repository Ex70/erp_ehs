<div class="row">

    {{-- Usuario del sistema (opcional) --}}
    <div class="col-md-6">
        <div class="form-group">
            <label>Vincular con usuario del sistema
                <small class="text-muted">(opcional)</small>
            </label>
            <select name="user_id"
                    class="form-control @error('user_id') is-invalid @enderror">
                <option value="">— Sin vincular —</option>
                @foreach($usuarios as $u)
                    <option value="{{ $u->id }}"
                        {{ old('user_id', $asignacion_ip->user_id ?? '') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }} ({{ $u->username }})
                    </option>
                @endforeach
            </select>
            @error('user_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Nombre --}}
    <div class="col-md-6">
        <div class="form-group">
            <label>Nombre del usuario <span class="text-danger">*</span></label>
            <input type="text" name="nombre"
                   class="form-control @error('nombre') is-invalid @enderror"
                   value="{{ old('nombre', $asignacion_ip->nombre ?? '') }}"
                   placeholder="Ej: Eder García">
            @error('nombre')
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
                    <input type="text" name="direccion_ip"
                           class="form-control @error('direccion_ip') is-invalid @enderror"
                           value="{{ old('direccion_ip', $asignacion_ip->direccion_ip ?? '') }}"
                           placeholder="192.168.0.111"
                           id="input-ip">
                    <small class="text-muted">Formato: 192.168.0.0 — 192.168.0.255</small>
                    @error('direccion_ip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Dirección MAC <span class="text-danger">*</span></label>
                    <input type="text" name="direccion_mac"
                           class="form-control @error('direccion_mac') is-invalid @enderror"
                           value="{{ old('direccion_mac', $asignacion_ip->direccion_mac ?? '') }}"
                           placeholder="00:1e:c2:9e:28:6b"
                           id="input-mac"
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

{{-- Datos del dispositivo --}}
<div class="card card-outline card-warning mb-3">
    <div class="card-header py-2">
        <h3 class="card-title">
            <i class="fas fa-laptop mr-1"></i> Datos del dispositivo
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tipo de dispositivo <span class="text-danger">*</span></label>
                    <select name="dispositivo"
                            class="form-control @error('dispositivo') is-invalid @enderror">
                        <option value="">— Selecciona —</option>
                        @foreach($tiposDispositivo as $tipo)
                            <option value="{{ $tipo }}"
                                {{ old('dispositivo', $asignacion_ip->dispositivo ?? '') == $tipo ? 'selected' : '' }}>
                                {{ $tipo }}
                            </option>
                        @endforeach
                    </select>
                    @error('dispositivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Marca <span class="text-danger">*</span></label>
                    <input type="text" name="marca"
                           class="form-control @error('marca') is-invalid @enderror"
                           value="{{ old('marca', $asignacion_ip->marca ?? '') }}"
                           placeholder="Ej: ACER">
                    @error('marca')
                        <div class="invalid-feedback">{{ $message }}</div>
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