<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar registro — {{ config('app.name') }}</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; }
        .register-card {
            max-width: 480px;
            margin: 60px auto;
        }
        .fuerza-bar { height: 6px; border-radius: 3px; transition: width .3s, background .3s; }
    </style>
</head>
<body>

<div class="register-card">
    <div class="card elevation-3">
        <div class="card-header text-center bg-primary text-white py-4">
            <h4 class="mb-0">
                <i class="fas fa-user-check mr-2"></i>
                Completar registro
            </h4>
            <small>{{ config('app.name') }}</small>
        </div>
        <div class="card-body p-4">

            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-1"></i>
                Hola, <strong>{{ $usuario->name }}</strong>.
                Establece tu contraseña definitiva para activar tu cuenta.
            </div>

            {{-- Info del usuario --}}
            <div class="bg-light rounded p-3 mb-4">
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted d-block">Usuario</small>
                        <strong>{{ $usuario->username }}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Correo</small>
                        <strong>{{ $usuario->email }}</strong>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('registro.guardar', $token) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>
                        <i class="fas fa-lock mr-1"></i>
                        Nueva contraseña <span class="text-danger">*</span>
                    </label>
                    <input type="password" name="password"
                           class="form-control" id="input-password"
                           autocomplete="new-password"
                           placeholder="Mínimo 8 caracteres"
                           required>
                    <div class="mt-2">
                        <div class="progress" style="height:6px">
                            <div id="fuerza-bar" class="progress-bar fuerza-bar"
                                 style="width:0%"></div>
                        </div>
                        <small id="fuerza-texto" class="text-muted"></small>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-lock mr-1"></i>
                        Confirmar contraseña <span class="text-danger">*</span>
                    </label>
                    <input type="password" name="password_confirmation"
                           class="form-control" id="input-confirm"
                           autocomplete="new-password"
                           placeholder="Repite tu contraseña"
                           required>
                    <small id="match-texto" class="mt-1 d-block"></small>
                </div>

                {{-- Campo oculto para validar que sea diferente a la temporal --}}
                <input type="hidden" name="password_temporal"
                       value="{{ old('password_temporal') }}">

                <button type="submit" class="btn btn-primary btn-block mt-3"
                        id="btn-submit">
                    <i class="fas fa-check-circle mr-1"></i>
                    Activar mi cuenta
                </button>
            </form>

        </div>
        <div class="card-footer text-center text-muted small py-3">
            {{ config('app.name') }} &copy; {{ date('Y') }}
        </div>
    </div>
</div>

<script>
// Indicador de fortaleza
document.getElementById('input-password').addEventListener('input', function() {
    const val   = this.value;
    const bar   = document.getElementById('fuerza-bar');
    const texto = document.getElementById('fuerza-texto');

    let fuerza = 0;
    if (val.length >= 8)             fuerza++;
    if (/[A-Z]/.test(val))           fuerza++;
    if (/[0-9]/.test(val))           fuerza++;
    if (/[^A-Za-z0-9]/.test(val))   fuerza++;

    const niveles = [
        { pct: '0%',   cls: '',             label: '' },
        { pct: '25%',  cls: 'bg-danger',    label: 'Muy débil' },
        { pct: '50%',  cls: 'bg-warning',   label: 'Débil' },
        { pct: '75%',  cls: 'bg-info',      label: 'Aceptable' },
        { pct: '100%', cls: 'bg-success',   label: 'Fuerte' },
    ];

    const nivel = val.length === 0 ? niveles[0] : niveles[fuerza];
    bar.style.width   = nivel.pct;
    bar.className     = 'progress-bar fuerza-bar ' + nivel.cls;
    texto.textContent = nivel.label;

    verificarCoincidencia();
});

// Verificar coincidencia
document.getElementById('input-confirm').addEventListener('input', verificarCoincidencia);

function verificarCoincidencia() {
    const pass    = document.getElementById('input-password').value;
    const confirm = document.getElementById('input-confirm').value;
    const texto   = document.getElementById('match-texto');
    const btn     = document.getElementById('btn-submit');

    if (!confirm) {
        texto.textContent = '';
        return;
    }

    if (pass === confirm) {
        texto.textContent = '✓ Las contraseñas coinciden';
        texto.className   = 'mt-1 d-block text-success small';
        btn.disabled      = false;
    } else {
        texto.textContent = '✗ Las contraseñas no coinciden';
        texto.className   = 'mt-1 d-block text-danger small';
        btn.disabled      = true;
    }
}
</script>

</body>
</html>