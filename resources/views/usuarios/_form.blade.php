{{-- Nombre --}}
<div class="form-group">
    <label>Nombre completo <span class="text-danger">*</span></label>
    <input type="text" name="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $usuario->name ?? '') }}">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

{{-- Username --}}
<div class="form-group">
    <label>Nombre de usuario <span class="text-danger">*</span></label>
    <input type="text" name="username"
           class="form-control @error('username') is-invalid @enderror"
           value="{{ old('username', $usuario->username ?? '') }}">
    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

{{-- Correo --}}
<div class="form-group">
    <label>Correo electrónico <span class="text-danger">*</span></label>
    <input type="email" name="email"
           class="form-control @error('email') is-invalid @enderror"
           value="{{ old('email', $usuario->email ?? '') }}">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

{{-- Contraseña --}}
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Contraseña
                @isset($usuario)
                    <small class="text-muted">(dejar vacío para no cambiar)</small>
                @endisset
                @unless(isset($usuario))
                    <span class="text-danger">*</span>
                @endunless
            </label>
            <input type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   autocomplete="new-password">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Confirmar contraseña</label>
            <input type="password" name="password_confirmation"
                   class="form-control" autocomplete="new-password">
        </div>
    </div>
</div>

{{-- Puesto y Rol --}}
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Puesto <span class="text-danger">*</span></label>
            <select name="puesto_id"
                    class="form-control @error('puesto_id') is-invalid @enderror">
                <option value="">-- Selecciona --</option>
                @foreach($puestos as $puesto)
                    <option value="{{ $puesto->id }}"
                        {{ old('puesto_id', $usuario->puesto_id ?? '') == $puesto->id ? 'selected' : '' }}>
                        {{ $puesto->nombre }}
                    </option>
                @endforeach
            </select>
            @error('puesto_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Rol <span class="text-danger">*</span></label>
            <select name="role"
                    class="form-control @error('role') is-invalid @enderror">
                <option value="">-- Selecciona --</option>
                @foreach($roles as $rol)
                    <option value="{{ $rol->name }}"
                        {{ old('role', $usuario->getRoleNames()->first() ?? '') == $rol->name ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $rol->name)) }}
                    </option>
                @endforeach
            </select>
            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

{{-- Avatar --}}
<div class="form-group">
    <label>Foto de perfil</label>
    @isset($usuario)
        @if($usuario->avatar)
            <div class="mb-2">
                <img src="{{ asset('storage/'.$usuario->avatar) }}"
                     style="width:64px;height:64px;object-fit:cover;border-radius:50%;">
            </div>
        @endif
    @endisset
    <input type="file" name="avatar"
           class="form-control-file @error('avatar') is-invalid @enderror"
           accept="image/*">
    @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

{{-- Estado activo --}}
<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="hidden" name="activo" value="0">
        <input type="checkbox" class="custom-control-input" id="activo"
               name="activo" value="1"
               {{ old('activo', $usuario->activo ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="activo">Usuario activo</label>
    </div>
</div>