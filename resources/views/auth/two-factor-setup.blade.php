@extends('adminlte::page')

@section('title', 'Configurar 2FA')

@section('content_header')
    <h1><i class="fas fa-shield-alt mr-2"></i>Autenticación de Dos Factores</h1>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-qrcode mr-2"></i>Vincular aplicación autenticadora</h3>
            </div>
            <div class="card-body">

                <p>Sigue estos pasos para activar la verificación en dos pasos en tu cuenta:</p>

                <ol class="mb-4">
                    <li>Descarga <strong>Google Authenticator</strong> o <strong>Authy</strong> en tu celular.</li>
                    <li>Abre la app y escanea el código QR siguiente.</li>
                    <li>Ingresa el código de 6 dígitos que te muestra la app para confirmar.</li>
                </ol>

                <div class="text-center mb-4">
                    {!! \BaconQrCode\Renderer\ImageRenderer::class ? '' : '' !!}
                    {{-- QR generado con bacon/bacon-qr-code --}}
                   {!! $qrSvg !!}
                </div>

                <p class="text-center text-muted mb-4">
                    ¿No puedes escanear el QR? Ingresa este código manualmente:<br>
                    <strong class="text-monospace" style="font-size:1.1rem;letter-spacing:.15rem;">
                        {{ $user->two_factor_secret }}
                    </strong>
                </p>

                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('two-factor.enable') }}">
                    @csrf
                    <div class="input-group mb-3">
                        <input
                            type="text"
                            name="code"
                            class="form-control text-center @error('code') is-invalid @enderror"
                            maxlength="6"
                            placeholder="Código de 6 dígitos"
                            inputmode="numeric"
                            autofocus
                            required
                        >
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-check mr-1"></i>Activar 2FA
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        {{-- Si ya tiene 2FA activo, opción para desactivar --}}
        @if($user->two_factor_enabled)
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-times-circle mr-2"></i>Desactivar autenticación de dos factores</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Ingresa tu contraseña actual para confirmar la desactivación.</p>
                <form method="POST" action="{{ route('two-factor.disable') }}">
                    @csrf
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" placeholder="Contraseña actual" required>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-danger"
                                onclick="return confirm('¿Seguro que deseas desactivar el 2FA?')">
                                Desactivar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <a href="{{ route('perfil.show') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Volver al perfil
        </a>
    </div>
</div>
@endsection
