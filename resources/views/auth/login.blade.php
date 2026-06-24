<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión — EHS ERP</title>

    {{-- AdminLTE / Bootstrap --}}
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    {{-- CSS personalizado del login --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">

    <link rel="icon" type="image/png" href="{{ asset('images/logo-ehs.png') }}">
</head>
<body class="login-page">

<div class="login-wrapper">

    {{-- ════════ PANEL IZQUIERDO — BRANDING ════════ --}}
    <div class="login-panel-left">

        {{-- Si tienes el logo: --}}
        {{-- <img src="{{ asset('images/logo-ehs-blanco.png') }}" class="login-brand-logo" alt="EHS Tecnologías"> --}}

        {{-- Ícono provisional mientras subes el logo --}}
        <div class="login-brand-icon">
            <i class="fas fa-bolt"></i>
        </div>

        <p class="login-brand-name">Eléctrica Hidráulica<br>Del Sureste</p>
        <p class="login-brand-tagline">Sistema de Gestión Empresarial</p>

        <ul class="login-modules-list">
            <li><i class="fas fa-check-circle"></i> Gestión de adquisiciones</li>
            <li><i class="fas fa-check-circle"></i> Mesa de ayuda interna</li>
            <li><i class="fas fa-check-circle"></i> Recursos humanos</li>
            <li><i class="fas fa-check-circle"></i> Sistemas y conectividad</li>
            <li><i class="fas fa-check-circle"></i> Solvencias económicas</li>
        </ul>

    </div>

    {{-- ════════ PANEL DERECHO — FORMULARIO ════════ --}}
    <div class="login-panel-right">
        <div class="login-card">

            <h2 class="login-card-title">Bienvenido de regreso</h2>
            <p class="login-card-subtitle">Ingresa tus credenciales para acceder al sistema</p>

            {{-- Errores generales --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Mensaje de sesión expirada --}}
            @if (session('status'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Correo electrónico --}}
                <div class="login-form-group">
                    <label class="login-form-label" for="email">Correo electrónico</label>
                    <div class="login-input-wrapper">
                        <i class="fas fa-envelope login-input-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="login-form-control @error('email') is-invalid @enderror"
                            placeholder="usuario@ehstecnologias.com"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            autofocus
                        >
                    </div>
                    @error('email')
                        <span class="text-danger" style="font-size:0.78rem;">
                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div class="login-form-group">
                    <label class="login-form-label" for="password">Contraseña</label>
                    <div class="login-input-wrapper">
                        <i class="fas fa-lock login-input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="login-form-control @error('password') is-invalid @enderror"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <span class="toggle-password" onclick="togglePassword('password', this)" style="
                            position: absolute;
                            right: 13px;
                            cursor: pointer;
                            color: #9ca3af;
                            font-size: 15px;
                        ">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    @error('password')
                        <span class="text-danger" style="font-size:0.78rem;">
                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Recordar + olvidé contraseña --}}
                <div class="login-remember-row">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Recordarme</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="login-forgot-link">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                {{-- Botón iniciar sesión --}}
                <button type="submit" class="btn-login-ehs">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar sesión
                </button>

            </form>

            <div class="login-divider">EHS ERP · {{ config('app.version', 'v1.5.0') }}</div>

            <div class="login-footer-text">
                ¿Necesitas acceso? Contacta a <strong style="color:#0d2c6e;">soporte TI</strong><br>
                <span class="badge-version">
                    <i class="fas fa-shield-alt"></i> Acceso seguro y cifrado
                </span>
            </div>

        </div>
    </div>

</div>

{{-- Scripts --}}
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<script>
    // Mostrar/ocultar contraseña
    function togglePassword(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
            btn.style.color = '#0d2c6e';
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
            btn.style.color = '#9ca3af';
        }
    }
</script>

</body>
</html>