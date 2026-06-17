<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación OTP — EHS ERP</title>
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-ehs.png') }}">
    <style>
        .otp-input {
            letter-spacing: 0.5rem;
            font-size: 1.8rem;
            text-align: center;
            font-weight: 700;
        }
        .otp-card {
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,.12);
        }
        .otp-icon {
            font-size: 3rem;
            color: #e8782a;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="login-page" style="background: #f4f6f9;">

<div class="d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="card otp-card p-4" style="width:100%;max-width:420px;">
        <div class="card-body text-center">

            <div class="otp-icon"><i class="fas fa-shield-alt"></i></div>
            <h4 class="mb-1 font-weight-bold">Verificación en dos pasos</h4>
            <p class="text-muted mb-4">
                Ingresa el código de 6 dígitos de tu aplicación autenticadora
                <br><small>(Google Authenticator / Authy)</small>
            </p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('two-factor.verify') }}">
                @csrf
                <div class="form-group">
                    <input
                        type="text"
                        name="code"
                        class="form-control otp-input @error('code') is-invalid @enderror"
                        maxlength="6"
                        autocomplete="one-time-code"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        autofocus
                        placeholder="000000"
                        required
                    >
                </div>
                <button type="submit" class="btn btn-block mt-3" style="background:#e8782a;color:#fff;font-weight:600;">
                    <i class="fas fa-check-circle mr-2"></i>Verificar código
                </button>
            </form>

            <hr>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-link text-muted">
                    <i class="fas fa-sign-out-alt mr-1"></i>Cancelar e iniciar sesión de nuevo
                </button>
            </form>

        </div>
    </div>
</div>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script>
    // Auto-submit al completar 6 dígitos
    document.querySelector('input[name="code"]').addEventListener('input', function () {
        if (this.value.length === 6) {
            this.closest('form').submit();
        }
    });
</script>
</body>
</html>