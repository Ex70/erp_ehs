<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nueva Contraseña — EHS ERP</title>

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
            <li><i class="fas fa-shield-alt"></i> Restablece tu acceso de forma segura</li>
            <li><i class="fas fa-lock"></i> Usa una contraseña robusta</li>
            <li><i class="fas fa-check-circle"></i> Mínimo 8 caracteres recomendados</li>
            <li><i class="fas fa-user-shield"></i> Tu sesión permanece protegida</li>
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
                    <i class="fas fa-lock"></i>
                </div>
            </div>

            <h2 class="login-card-title">Nueva contraseña</h2>
            <p class="login-card-subtitle">
                Ingresa y confirma tu nueva contraseña para recuperar el acceso al sistema.
            </p>

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

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                {{-- Token oculto --}}
                <input type="hidden" name="token" value="{{ $token }}">

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
                            value="{{ $email ?? old('email') }}"
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

                {{-- Nueva contraseña --}}
                <div class="login-form-group">
                    <label class="login-form-label" for="password">Nueva contraseña</label>
                    <div class="login-input-wrapper">
                        <i class="fas fa-lock login-input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="login-form-control @error('password') is-invalid @enderror"
                            placeholder="••••••••"
                            autocomplete="new-password"
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

                {{-- Confirmar contraseña --}}
                <div class="login-form-group">
                    <label class="login-form-label" for="password_confirmation">Confirmar contraseña</label>
                    <div class="login-input-wrapper">
                        <i class="fas fa-lock login-input-icon"></i>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="login-form-control"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            required
                        >
                        <span class="toggle-password" onclick="togglePassword('password_confirmation', this)" style="
                            position: absolute;
                            right: 13px;
                            cursor: pointer;
                            color: #9ca3af;
                            font-size: 15px;
                        ">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                {{-- Indicador de fortaleza --}}
                <div style="margin-bottom: 1.5rem;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                        <span style="font-size:0.75rem; color:#6c757d;">Fortaleza de la contraseña</span>
                        <span id="strength-text" style="font-size:0.75rem; font-weight:600; color:#9ca3af;">—</span>
                    </div>
                    <div style="height:5px; background:#e9ecef; border-radius:10px; overflow:hidden;">
                        <div id="strength-bar" style="height:100%; width:0%; border-radius:10px; transition: width 0.3s ease, background 0.3s ease;"></div>
                    </div>
                </div>

                {{-- Botón restablecer --}}
                <button type="submit" class="btn-login-ehs">
                    <i class="fas fa-check-circle"></i>
                    Restablecer contraseña
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

    // Indicador de fortaleza de contraseña
    document.getElementById('password').addEventListener('input', function () {
        const val = this.value;
        const bar = document.getElementById('strength-bar');
        const text = document.getElementById('strength-text');

        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { width: '0%',   color: '#e9ecef', label: '—' },
            { width: '25%',  color: '#e24b4a', label: 'Muy débil' },
            { width: '50%',  color: '#f07f1a', label: 'Débil' },
            { width: '75%',  color: '#1a4da0', label: 'Buena' },
            { width: '100%', color: '#1d9e75', label: 'Excelente' },
        ];

        const level = levels[score];
        bar.style.width = level.width;
        bar.style.background = level.color;
        text.textContent = level.label;
        text.style.color = level.color;
    });
</script>

</body>
</html>