<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EHS Tecnologías — Sistema de Gestión Empresarial</title>

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --azul:      #0d2c6e;
            --azul-med:  #1a4da0;
            --naranja:   #f07f1a;
            --gris-bg:   #f4f6f9;
            --gris-text: #6c757d;
            --blanco:    #ffffff;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Nunito', sans-serif;
            background: var(--gris-bg);
            color: #1a1a2e;
            overflow-x: hidden;
        }

        /* ── NAVBAR ── */
        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 3rem;
            background: rgba(13, 44, 110, 0.96);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            animation: slideDown 0.6s ease forwards;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .navbar-icon {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: rgba(240,127,26,0.2);
            border: 1.5px solid rgba(240,127,26,0.4);
            display: flex; align-items: center; justify-content: center;
            color: var(--naranja);
            font-size: 18px;
        }

        .navbar-name {
            font-size: 0.88rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.3;
        }

        .navbar-name span {
            display: block;
            font-size: 0.7rem;
            font-weight: 400;
            color: rgba(255,255,255,0.55);
        }

        .navbar-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.5rem 1.4rem;
            background: var(--naranja);
            color: #fff;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s, transform 0.15s;
        }

        .navbar-login:hover {
            background: #d96e0e;
            transform: translateY(-1px);
        }

        /* ── HERO ── */
        .hero {
            min-height: 100vh;
            background: linear-gradient(150deg, #0a1f52 0%, #0d2c6e 50%, #1a4da0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 7rem 2rem 4rem;
            position: relative;
            overflow: hidden;
        }

        /* Círculos decorativos hero */
        .hero::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: rgba(240,127,26,0.08);
            animation: pulse 6s ease-in-out infinite;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -150px; left: -100px;
            width: 600px; height: 600px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            animation: pulse 8s ease-in-out infinite reverse;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 780px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(240,127,26,0.15);
            border: 1px solid rgba(240,127,26,0.3);
            color: #f9a45c;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            animation: fadeInUp 0.8s ease forwards;
        }

        .hero-title {
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 1.2rem;
            animation: fadeInUp 0.9s ease forwards;
        }

        .hero-title span {
            color: var(--naranja);
        }

        .hero-subtitle {
            font-size: 1.05rem;
            color: rgba(255,255,255,0.65);
            line-height: 1.7;
            margin-bottom: 2.5rem;
            max-width: 580px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeInUp 1s ease forwards;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1.1s ease forwards;
        }

        .btn-hero-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.85rem 2rem;
            background: var(--naranja);
            color: #fff;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 20px rgba(240,127,26,0.35);
        }

        .btn-hero-primary:hover {
            background: #d96e0e;
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(240,127,26,0.45);
        }

        .btn-hero-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.85rem 2rem;
            background: rgba(255,255,255,0.08);
            color: #fff;
            border: 1.5px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-hero-secondary:hover {
            background: rgba(255,255,255,0.14);
            transform: translateY(-2px);
        }

        /* Stats bajo el hero */
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 4rem;
            padding-top: 2.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            animation: fadeInUp 1.2s ease forwards;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--naranja);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.5);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ── MÓDULOS ── */
        .section {
            padding: 5rem 2rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--naranja);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.5rem;
        }

        .section-title {
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 800;
            color: var(--azul);
            margin-bottom: 0.75rem;
        }

        .section-subtitle {
            font-size: 0.95rem;
            color: var(--gris-text);
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.7;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            max-width: 1100px;
            margin: 0 auto;
        }

        .module-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.75rem;
            border: 1px solid rgba(13,44,110,0.06);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            cursor: default;
            opacity: 0;
            animation: fadeInUp 0.7s ease forwards;
        }

        .module-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(13,44,110,0.1);
        }

        .module-card:nth-child(1) { animation-delay: 0.1s; }
        .module-card:nth-child(2) { animation-delay: 0.2s; }
        .module-card:nth-child(3) { animation-delay: 0.3s; }
        .module-card:nth-child(4) { animation-delay: 0.4s; }
        .module-card:nth-child(5) { animation-delay: 0.5s; }
        .module-card:nth-child(6) { animation-delay: 0.6s; }

        .module-icon {
            width: 52px; height: 52px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            margin-bottom: 1rem;
        }

        .module-icon.azul    { background: rgba(13,44,110,0.08);  color: var(--azul); }
        .module-icon.naranja { background: rgba(240,127,26,0.1);  color: var(--naranja); }
        .module-icon.verde   { background: rgba(29,158,117,0.1);  color: #1d9e75; }
        .module-icon.morado  { background: rgba(83,74,183,0.1);   color: #534ab7; }
        .module-icon.rojo    { background: rgba(226,75,74,0.1);   color: #e24b4a; }
        .module-icon.teal    { background: rgba(15,110,86,0.1);   color: #0f6e56; }

        .module-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--azul);
            margin-bottom: 0.4rem;
        }

        .module-desc {
            font-size: 0.83rem;
            color: var(--gris-text);
            line-height: 1.6;
        }

        .module-tag {
            display: inline-block;
            margin-top: 0.85rem;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            background: rgba(13,44,110,0.06);
            color: var(--azul);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ── CTA FINAL ── */
        .cta-section {
            background: linear-gradient(135deg, #0d2c6e 0%, #1a4da0 100%);
            padding: 5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(240,127,26,0.1);
        }

        .cta-title {
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 800;
            color: #fff;
            margin-bottom: 0.75rem;
            position: relative;
        }

        .cta-subtitle {
            font-size: 0.95rem;
            color: rgba(255,255,255,0.6);
            margin-bottom: 2rem;
            position: relative;
        }

        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 1rem 2.5rem;
            background: var(--naranja);
            color: #fff;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 20px rgba(240,127,26,0.4);
            position: relative;
        }

        .btn-cta:hover {
            background: #d96e0e;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(240,127,26,0.5);
        }

        /* ── FOOTER ── */
        .footer {
            background: #060f24;
            padding: 1.75rem 3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-text {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.35);
        }

        .footer-text span {
            color: var(--naranja);
        }

        .footer-version {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.25);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ── ANIMACIONES ── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50%       { transform: scale(1.08); opacity: 0.7; }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .navbar { padding: 1rem 1.5rem; }
            .hero-stats { gap: 1.5rem; }
            .footer { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

{{-- ════ NAVBAR ════ --}}
<nav class="navbar">
    <a href="/" class="navbar-brand">
        <div class="navbar-icon"><i class="fas fa-bolt"></i></div>
        <div class="navbar-name">
            EHS Tecnologías
            <span>Sistema de Gestión Empresarial</span>
        </div>
    </a>
    <a href="{{ route('login') }}" class="navbar-login">
        <i class="fas fa-sign-in-alt"></i>
        Iniciar sesión
    </a>
</nav>

{{-- ════ HERO ════ --}}
<section class="hero">
    <div class="hero-content">

        <div class="hero-badge">
            <i class="fas fa-shield-alt"></i>
            Plataforma empresarial segura
        </div>

        <h1 class="hero-title">
            Gestión inteligente para<br>
            <span>Eléctrica Hidráulica Del Sureste</span>
        </h1>

        <p class="hero-subtitle">
            Sistema ERP centralizado que integra adquisiciones, recursos humanos,
            mesa de ayuda, sistemas y solvencias en una sola plataforma.
        </p>

        <div class="hero-buttons">
            <a href="{{ route('login') }}" class="btn-hero-primary">
                <i class="fas fa-sign-in-alt"></i>
                Acceder al sistema
            </a>
            <a href="#modulos" class="btn-hero-secondary">
                <i class="fas fa-th-large"></i>
                Ver módulos
            </a>
        </div>

        <div class="hero-stats">
            <div class="stat-item">
                <div class="stat-number">6+</div>
                <div class="stat-label">Módulos activos</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">4</div>
                <div class="stat-label">Roles de acceso</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">100%</div>
                <div class="stat-label">En la nube</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Disponibilidad</div>
            </div>
        </div>

    </div>
</section>

{{-- ════ MÓDULOS ════ --}}
<section class="section" id="modulos">
    <div class="section-header">
        <p class="section-label">Funcionalidades</p>
        <h2 class="section-title">Módulos del sistema</h2>
        <p class="section-subtitle">
            Cada módulo está diseñado para cubrir un área clave de la operación empresarial,
            con roles y permisos diferenciados.
        </p>
    </div>

    <div class="modules-grid">

        <div class="module-card">
            <div class="module-icon azul"><i class="fas fa-shopping-cart"></i></div>
            <p class="module-name">Adquisiciones</p>
            <p class="module-desc">Gestión de requisiciones, proveedores, productos, servicios y solvencias económicas.</p>
            <span class="module-tag">Activo</span>
        </div>

        <div class="module-card">
            <div class="module-icon naranja"><i class="fas fa-headset"></i></div>
            <p class="module-name">Mesa de Ayuda</p>
            <p class="module-desc">Tickets internos con asignación, seguimiento, notificaciones y calificación de servicio.</p>
            <span class="module-tag">Activo</span>
        </div>

        <div class="module-card">
            <div class="module-icon verde"><i class="fas fa-users"></i></div>
            <p class="module-name">Recursos Humanos</p>
            <p class="module-desc">Colaboradores, comunicados, noticias internas y gestión de áreas y puestos.</p>
            <span class="module-tag">Activo</span>
        </div>

        <div class="module-card">
            <div class="module-icon morado"><i class="fas fa-network-wired"></i></div>
            <p class="module-name">Sistemas</p>
            <p class="module-desc">Inventario de dispositivos, redes, conectividad y gestión de equipos tecnológicos.</p>
            <span class="module-tag">Activo</span>
        </div>

        <div class="module-card">
            <div class="module-icon rojo"><i class="fas fa-file-invoice-dollar"></i></div>
            <p class="module-name">Solvencias</p>
            <p class="module-desc">Solicitudes de solvencia económica con folio automático y generación de PDF.</p>
            <span class="module-tag">Activo</span>
        </div>

        <div class="module-card">
            <div class="module-icon teal"><i class="fas fa-chart-line"></i></div>
            <p class="module-name">Dashboard</p>
            <p class="module-desc">Panel de métricas en tiempo real con gráficas y resumen ejecutivo por módulo.</p>
            <span class="module-tag">Activo</span>
        </div>

    </div>
</section>

{{-- ════ CTA FINAL ════ --}}
<section class="cta-section">
    <h2 class="cta-title">¿Listo para comenzar?</h2>
    <p class="cta-subtitle">Accede con tus credenciales corporativas asignadas por el área de TI.</p>
    <a href="{{ route('login') }}" class="btn-cta">
        <i class="fas fa-sign-in-alt"></i>
        Iniciar sesión ahora
    </a>
</section>

{{-- ════ FOOTER ════ --}}
<footer class="footer">
    <p class="footer-text">
        © {{ date('Y') }} <span>Eléctrica Hidráulica Del Sureste</span> — Todos los derechos reservados
    </p>
    <p class="footer-version">
        <i class="fas fa-code-branch"></i>
        EHS ERP {{ config('app.version', 'v1.5.0') }}
    </p>
</footer>

</body>
</html>