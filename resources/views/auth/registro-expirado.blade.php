<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace expirado — {{ config('app.name') }}</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { background: #f4f6f9; }</style>
</head>
<body>

<div style="max-width:440px;margin:80px auto">
    <div class="card elevation-3 text-center">
        <div class="card-body p-5">
            <i class="fas fa-clock fa-4x text-warning mb-3"></i>
            <h4>Enlace expirado</h4>
            <p class="text-muted">
                El enlace de registro para
                <strong>{{ $usuario->name }}</strong>
                ha expirado (válido por 72 horas).
            </p>
            <p class="text-muted small">
                Contacta al administrador del sistema para que
                genere un nuevo enlace de acceso.
            </p>
            <a href="{{ route('login') }}" class="btn btn-primary mt-2">
                <i class="fas fa-sign-in-alt mr-1"></i> Ir al inicio de sesión
            </a>
        </div>
    </div>
</div>

</body>
</html>