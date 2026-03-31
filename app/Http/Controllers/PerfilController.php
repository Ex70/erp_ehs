<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdatePerfilRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateAvatarRequest;

class PerfilController extends Controller
{
    /**
     * Vista principal del perfil
     */
    public function show()
    {
        $usuario = Auth::user()->load(['puesto', 'roles', 'permissions']);

        return view('perfil.show', compact('usuario'));
    }

    /**
     * Formulario de edición de datos personales
     */
    public function edit()
    {
        $usuario = Auth::user()->load('puesto');

        return view('perfil.edit', compact('usuario'));
    }

    /**
     * Actualizar datos personales
     */
    public function update(UpdatePerfilRequest $request)
    {
        Auth::user()->update($request->validated());

        return redirect()
            ->route('perfil.show')
            ->with('success', 'Datos actualizados correctamente.');
    }

    /**
     * Cambiar contraseña
     */
    public function password(UpdatePasswordRequest $request)
    {
        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()
            ->route('perfil.show')
            ->with('success', 'Contraseña actualizada correctamente.');
    }

    /**
     * Actualizar avatar
     */
    public function avatar(UpdateAvatarRequest $request)
    {
        $usuario = Auth::user();

        // Eliminar avatar anterior si existe
        if ($usuario->avatar) {
            Storage::disk('public')->delete($usuario->avatar);
        }

        $ruta = $request->file('avatar')->store('avatars', 'public');
        $usuario->update(['avatar' => $ruta]);

        return redirect()
            ->route('perfil.show')
            ->with('success', 'Foto de perfil actualizada correctamente.');
    }

    /**
     * Eliminar avatar
     */
    public function eliminarAvatar()
    {
        $usuario = Auth::user();

        if ($usuario->avatar) {
            Storage::disk('public')->delete($usuario->avatar);
            $usuario->update(['avatar' => null]);
        }

        return redirect()
            ->route('perfil.show')
            ->with('success', 'Foto de perfil eliminada.');
    }
}