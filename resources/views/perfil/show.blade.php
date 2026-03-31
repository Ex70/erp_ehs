@extends('adminlte::page')
@section('title', 'Mi perfil')

@section('content_header')
    <h1>Mi perfil</h1>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">

        {{-- Columna izquierda: Avatar + datos básicos --}}
        <div class="col-md-4">

            {{-- Tarjeta de avatar --}}
            <div class="card card-primary card-outline">
                <div class="card-body text-center">

                    @if($usuario->avatar)
                        <img src="{{ asset('storage/'.$usuario->avatar) }}"
                             class="profile-user-img img-fluid img-circle elevation-2 mb-3"
                             style="width:120px;height:120px;object-fit:cover;"
                             id="preview-avatar"
                             alt="Avatar">
                    @else
                        <div class="img-circle elevation-2 mb-3 mx-auto bg-secondary
                                    d-flex align-items-center justify-content-center"
                             style="width:120px;height:120px;" id="avatar-placeholder">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                    @endif

                    <h3 class="profile-username mb-0">{{ $usuario->name }}</h3>
                    <p class="text-muted">{{ '@'.$usuario->username }}</p>

                    @foreach($usuario->getRoleNames() as $rol)
                        <span class="badge badge-primary">
                            {{ ucfirst(str_replace('_', ' ', $rol)) }}
                        </span>
                    @endforeach

                    <div class="mt-2">
                        @if($usuario->activo)
                            <span class="badge badge-success">Activo</span>
                        @else
                            <span class="badge badge-secondary">Inactivo</span>
                        @endif
                    </div>
                </div>

                {{-- Subir foto --}}
                <div class="card-footer">
                    <form action="{{ route('perfil.avatar') }}"
                          method="POST" enctype="multipart/form-data" id="form-avatar">
                        @csrf
                        <div class="input-group mb-2">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input"
                                       id="avatar" name="avatar"
                                       accept="image/jpg,image/jpeg,image/png">
                                <label class="custom-file-label" for="avatar">
                                    Seleccionar imagen
                                </label>
                            </div>
                        </div>
                        @error('avatar')
                            <div class="text-danger small mb-2">{{ $message }}</div>
                        @enderror
                        <button type="submit" class="btn btn-primary btn-block btn-sm">
                            <i class="fas fa-upload"></i> Actualizar foto
                        </button>
                    </form>

                    @if($usuario->avatar)
                        <form action="{{ route('perfil.avatar.eliminar') }}"
                              method="POST" class="mt-2"
                              onsubmit="return confirm('¿Eliminar foto de perfil?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-block btn-sm">
                                <i class="fas fa-trash"></i> Eliminar foto
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Info del puesto --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-briefcase mr-1"></i> Puesto
                    </h3>
                </div>
                <div class="card-body">
                    <p class="mb-1">
                        <strong>{{ $usuario->puesto?->nombre ?? 'Sin puesto asignado' }}</strong>
                    </p>
                    @if($usuario->puesto?->descripcion)
                        <small class="text-muted">{{ $usuario->puesto->descripcion }}</small>
                    @endif
                </div>
            </div>

        </div>

        {{-- Columna derecha: Datos + contraseña --}}
        <div class="col-md-8">

            {{-- Datos personales --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-user-edit mr-1"></i> Datos personales
                    </h3>
                    <a href="{{ route('perfil.edit') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered mb-0">
                        <tr>
                            <th style="width:35%">Nombre completo</th>
                            <td>{{ $usuario->name }}</td>
                        </tr>
                        <tr>
                            <th>Usuario</th>
                            <td>{{ $usuario->username }}</td>
                        </tr>
                        <tr>
                            <th>Correo electrónico</th>
                            <td>{{ $usuario->email }}</td>
                        </tr>
                        <tr>
                            <th>Miembro desde</th>
                            <td>{{ $usuario->created_at->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Cambiar contraseña --}}
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-lock mr-1"></i> Cambiar contraseña
                    </h3>
                </div>
                <form action="{{ route('perfil.password') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="card-body">

                        <div class="form-group">
                            <label>Contraseña actual <span class="text-danger">*</span></label>
                            <input type="password" name="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   autocomplete="current-password">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nueva contraseña <span class="text-danger">*</span></label>
                                    <input type="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           autocomplete="new-password"
                                           id="nueva-password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Confirmar contraseña <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation"
                                           class="form-control @error('password_confirmation') is-invalid @enderror"
                                           autocomplete="new-password"
                                           id="confirmar-password">
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Indicador de fortaleza --}}
                        <div class="form-group mb-0">
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar" id="fuerza-bar"
                                     role="progressbar" style="width:0%"></div>
                            </div>
                            <small id="fuerza-texto" class="text-muted"></small>
                        </div>

                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Actualizar contraseña
                        </button>
                    </div>
                </form>
            </div>

            {{-- Permisos del usuario --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shield-alt mr-1"></i> Mis permisos
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool"
                                data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $todosMisPermisos = Auth::user()
                            ->getAllPermissions()
                            ->groupBy(fn($p) => explode('.', $p->name)[0]);
                    @endphp

                    @forelse($todosMisPermisos as $modulo => $lista)
                        <div class="mb-2">
                            <span class="text-uppercase text-muted small font-weight-bold">
                                {{ $modulo }}
                            </span>
                            <div>
                                @foreach($lista as $permiso)
                                    <span class="badge badge-success mr-1">
                                        <i class="fas fa-check mr-1"></i>
                                        {{ explode('.', $permiso->name)[1] ?? $permiso->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Sin permisos asignados.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
@stop

@section('css')@stop

@section('js')
<script>
// Preview de avatar antes de subir
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const label = document.querySelector('.custom-file-label');
    label.textContent = file.name;

    const reader = new FileReader();
    reader.onload = function(event) {
        const placeholder = document.getElementById('avatar-placeholder');
        let preview = document.getElementById('preview-avatar');

        if (!preview) {
            preview = document.createElement('img');
            preview.id = 'preview-avatar';
            preview.className = 'profile-user-img img-fluid img-circle elevation-2 mb-3';
            preview.style.cssText = 'width:120px;height:120px;object-fit:cover;';
            if (placeholder) {
                placeholder.parentNode.replaceChild(preview, placeholder);
            } else {
                document.querySelector('.card-body .img-circle, .card-body img')
                        ?.replaceWith(preview);
            }
        }
        preview.src = event.target.result;
    };
    reader.readAsDataURL(file);
});

// Indicador de fortaleza de contraseña
document.getElementById('nueva-password').addEventListener('input', function() {
    const val   = this.value;
    const bar   = document.getElementById('fuerza-bar');
    const texto = document.getElementById('fuerza-texto');

    let fuerza = 0;
    if (val.length >= 8)                        fuerza++;
    if (/[A-Z]/.test(val))                      fuerza++;
    if (/[0-9]/.test(val))                      fuerza++;
    if (/[^A-Za-z0-9]/.test(val))               fuerza++;

    const niveles = [
        { pct: '0%',   cls: '',          label: '' },
        { pct: '25%',  cls: 'bg-danger', label: 'Muy débil' },
        { pct: '50%',  cls: 'bg-warning',label: 'Débil' },
        { pct: '75%',  cls: 'bg-info',   label: 'Aceptable' },
        { pct: '100%', cls: 'bg-success',label: 'Fuerte' },
    ];

    const nivel = val.length === 0 ? niveles[0] : niveles[fuerza];
    bar.style.width = nivel.pct;
    bar.className   = 'progress-bar ' + nivel.cls;
    texto.textContent = nivel.label;
});
</script>
@stop