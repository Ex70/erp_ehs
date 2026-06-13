<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EHS ERP — Bienvenido</title>

    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="login-page">

<div class="login-wrapper">

    {{-- Panel izquierdo --}}
    <div class="login-panel-left">
        <div class="login-brand-icon">
            <i class="fas fa-bolt"></i>
        </div>
        <p class="login-brand-name">Eléctrica Hidráulica<br>Del Sureste</p>
        <p class="login-brand-tagline">Sistema de Gestión Empresarial</p>

        <ul class="login-modules-list" style="margin-top: 2rem;">
            <li><i class="fas fa-shopping-cart"></i> Adquisiciones</li>
            <li><i class="fas fa-headset"></i> Mesa de Ayuda</li>
            <li><i class="fas fa-users"></i> Recursos Humanos</li>
            <li><i class="fas fa-network-wired"></i> Sistemas</li>
            <li><i class="fas fa-file-invoice-dollar"></i> Solvencias</li>
        </ul>
    </div>

    {{-- Panel derecho --}}
    <div class="login-panel-right">
        <div class="login-card" style="text-align: center;">

            <div style="
                width: 72px; height: 72px;
                border-radius: 50%;
                background: rgba(13,44,110,0.07);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 30px;
                color: #0d2c6e;
                margin-bottom: 1.25rem;
            ">
                <i class="fas fa-th-large"></i>
            </div>

            <h2 class="login-card-title">Bienvenido al ERP</h2>
            <p class="login-card-subtitle" style="margin-bottom: 2rem;">
                Plataforma interna de gestión empresarial.<br>
                Accede con tus credenciales corporativas.
            </p>

            <a href="{{ route('login') }}" class="btn-login-ehs" style="text-decoration: none;">
                <i class="fas fa-sign-in-alt"></i>
                Iniciar sesión
            </a>

            <div class="login-divider">EHS ERP · {{ config('app.version', 'v1.5.0') }}</div>

            <p class="login-footer-text">
                Acceso exclusivo para personal autorizado<br>
                <span class="badge-version">
                    <i class="fas fa-shield-alt"></i> Entorno privado
                </span>
            </p>

        </div>
    </div>

</div>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

</body>
</html>