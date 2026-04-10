<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegistroController extends Controller
{
    /**
     * Mostrar formulario de completar registro
     */
    public function completar(string $token)
    {
        $usuario = User::where('registro_token', $token)->firstOrFail();

        // Si ya completó el registro, redirigir al login
        if ($usuario->registro_completado_at) {
            return redirect()
                ->route('login')
                ->with('info', 'Este enlace ya fue usado. Inicia sesión normalmente.');
        }

        // Verificar que el token no tenga más de 72 horas
        $creadoHace = $usuario->created_at->diffInHours(now());
        if ($creadoHace > 72) {
            return view('auth.registro-expirado', compact('usuario'));
        }

        return view('auth.completar-registro', compact('usuario', 'token'));
    }

    /**
     * Guardar la nueva contraseña y activar la cuenta
     */
    public function guardar(Request $request, string $token)
    {
        $usuario = User::where('registro_token', $token)->firstOrFail();

        if ($usuario->registro_completado_at) {
            return redirect()
                ->route('login')
                ->with('info', 'Este enlace ya fue usado.');
        }

        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:password_temporal',
            ],
            'password_confirmation' => 'required',
        ], [
            'password.required'    => 'La nueva contraseña es obligatoria.',
            'password.min'         => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'   => 'Las contraseñas no coinciden.',
            'password.different'   => 'La nueva contraseña debe ser diferente a la temporal.',
        ]);

        $usuario->update([
            'password'                => Hash::make($request->password),
            'registro_token'          => null,
            'registro_completado_at'  => now(),
            'activo'                  => true,
        ]);

        // Iniciar sesión automáticamente
        Auth::login($usuario);

        return redirect()
            ->route('dashboard')
            ->with('success', '¡Bienvenido! Tu registro ha sido completado correctamente.');
    }
}