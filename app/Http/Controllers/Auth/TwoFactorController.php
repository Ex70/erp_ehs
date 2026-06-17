<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    /**
     * Mostrar pantalla de configuración 2FA (en perfil de usuario).
     */
    public function setup(Request $request)
{
    $user   = $request->user();
    $google = new Google2FA();

    if (! $user->two_factor_secret) {
        $user->two_factor_secret = $google->generateSecretKey();
        $user->save();
    }

    $qrUrl = $google->getQRCodeUrl(
        config('app.name'),
        $user->email,
        $user->two_factor_secret
    );

    // Generar QR en el controlador
    $renderer = new \BaconQrCode\Renderer\ImageRenderer(
        new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
        new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
    );
    $writer = new \BaconQrCode\Writer($renderer);
    $qrSvg  = $writer->writeString($qrUrl);

    return view('auth.two-factor-setup', compact('user', 'qrUrl', 'qrSvg'));
}

    /**
     * Activar 2FA tras escanear el QR y confirmar primer código.
     */
    public function enable(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $user   = $request->user();
        $google = new Google2FA();

        $valid = $google->verifyKey($user->two_factor_secret, $request->code);

        if (! $valid) {
            return back()->withErrors(['code' => 'El código OTP no es válido. Intenta de nuevo.']);
        }

        $user->two_factor_enabled      = true;
        $user->two_factor_confirmed_at = now();
        $user->save();

        return redirect()->route('profile')->with('success', 'Autenticación de dos factores activada correctamente.');
    }

    /**
     * Desactivar 2FA.
     */
    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);

        $user = $request->user();
        $user->two_factor_enabled      = false;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_secret       = null;
        $user->save();

        return redirect()->route('profile')->with('success', 'Autenticación de dos factores desactivada.');
    }

    /**
     * Mostrar pantalla del challenge OTP al hacer login.
     */
    public function challenge()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verificar el código OTP del challenge.
     */
    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $user   = $request->user();
        $google = new Google2FA();

        $valid = $google->verifyKey($user->two_factor_secret, $request->code);

        if (! $valid) {
            return back()->withErrors(['code' => 'Código incorrecto. Verifica tu aplicación autenticadora.']);
        }

        $request->session()->put('auth.two_factor_confirmed', true);

        return redirect()->intended(route('home'));
    }
}