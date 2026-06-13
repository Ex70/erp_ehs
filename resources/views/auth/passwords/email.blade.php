<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recuperar Contraseña — EHS ERP</title>

    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">

    <link rel="icon" type="image/png" href="{{ asset('images/logo-ehs.png') }}">
</head>
<body class="login-page">

<div class="login-wrapper">

    {{-- ════════ PANEL IZQUIERDO — BRANDING ════════ --}}
    <div class="login-panel-left">

        <div class="login-brand-icon">
            <i class="fas fa-bolt"></i>
        </div>

        <p class="login-brand-name">Eléctrica Hidráulica<br>Del Sureste</p>
        <p class="login-brand-tagline">Sistema de Gestión Empresarial</p>

        <ul class="login-modules-list">
            <li><i class="fas fa-shield-alt"></i> Recuperación segura de acceso</li>
            <li><i class="fas fa-envelope"></i> Enlace enviado a tu correo</li>
            <li><i class="fas fa-clock"></i> El enlace expira en 60 minutos</li>
            <li><i class="fas fa-lock"></i> Tu cuenta permanece protegida</li>
        </ul>

    </div>

    {{-- ════════ PANEL DERECHO — FORMULARIO ════════ --}}
    <div class="login-panel-right">
        <div class="login-card">

            {{-- Ícono superior --}}
            <div style="text-align:center; margin-bottom: 1.25rem;">
                <div style="
                    width: 64px; height: 64px;
                    border-radius: 50%;
                    background: rgba(13, 44, 110, 0.08);
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 28px;
                    color: #0d2c6e;
                ">
                    <i class="fas fa-key"></i>
                </div>
            </div>

            <h2 class="login-card-title">Recuperar contraseña</h2>
            <p class="login-card-subtitle">
                Ingresa tu correo registrado y te enviaremos un enlace para restablecer tu contraseña.
            </p>

            {{-- Mensaje de éxito --}}
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-1"></i>
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Errores --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
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

                {{-- Botón enviar --}}
                <button type="submit" class="btn-login-ehs">
                    <i class="fas fa-paper-plane"></i>
                    Enviar enlace de recuperación
                </button>

            </form>

            {{-- Volver al login --}}
            <div style="text-align:center; margin-top: 1.5rem;">
                <a href="{{ route('login') }}" style="
                    font-size: 0.83rem;
                    color: #6c757d;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    transition: color 0.2s;
                " onmouseover="this.style.color='#0d2c6e'" onmouseout="this.style.color='#6c757d'">
                    <i class="fas fa-arrow-left" style="font-size:12px;"></i>
                    Regresar al inicio de sesión
                </a>
            </div>

            <div class="login-divider">EHS ERP · {{ config('app.version', 'v1.5.0') }}</div>

            <div class="login-footer-text">
                ¿Necesitas ayuda? Contacta a <strong style="color:#0d2c6e;">soporte TI</strong><br>
                <span class="badge-version">
                    <i class="fas fa-shield-alt"></i> Acceso seguro y cifrado
                </span>
            </div>

        </div>
    </div>

</div>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

</body>
</html>