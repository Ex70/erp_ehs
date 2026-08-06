<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Puesto;
use App\Models\Departamento;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Notifications\BienvenidoUsuario;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    public function index(){
        $usuarios = User::with(['puesto', 'departamento', 'roles'])
                    ->orderBy('name')
                    ->paginate(10);
        return view('usuarios.index', compact('usuarios'));
    }

    public function create(){
        $departamentos = Departamento::activos()->orderBy('nombre')->get();

        // El select de puestos se llena por AJAX según el departamento elegido
        $puestos = collect();

        $roles = Role::orderBy('name')->get();
        $usuario = new \App\Models\User(); // instancia vacía, sin guardar
        return view('usuarios.create', compact('departamentos', 'puestos', 'roles', 'usuario'));
    }

    public function store(StoreUsuarioRequest $request){
        $data = $request->validated();

        // Guardar contraseña temporal antes de hashear
        $passwordTemporal = $data['password'];
        $data['password'] = Hash::make($data['password']);
        $data['activo']   = $request->boolean('activo', true);

        // Generar token único de registro
        $data['registro_token'] = Str::random(64);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')
                                    ->store('avatars', 'public');
        }

        $usuario = User::create($data);
        $usuario->assignRole($request->role);

        // Generar URL de completar registro
        $urlRegistro = route('registro.completar', [
            'token' => $usuario->registro_token,
        ]);

        // Enviar notificación
        try {
            $usuario->notify(new BienvenidoUsuario($passwordTemporal, $urlRegistro));
        } catch (\Exception $e) {
            logger()->error('Error al enviar correo de bienvenida: ' . $e->getMessage());
        }

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente. Se envió el correo de bienvenida.');
    }

    public function show(User $usuario){
        $usuario->load(['puesto', 'departamento', 'roles', 'permissions']);
        return view('usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario){
        $departamentos = Departamento::activos()->orderBy('nombre')->get();

        // Precargar solo los puestos del departamento actual del usuario,
        // para que el select llegue ya poblado y con la opción seleccionada
        $puestos = $usuario->departamento
            ? $usuario->departamento->puestosActivos()->get()
            : collect();

        $roles = Role::orderBy('name')->get();
        return view('usuarios.edit', compact('usuario', 'departamentos', 'puestos', 'roles'));
    }

    public function update(UpdateUsuarioRequest $request, User $usuario){
        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        $data['activo'] = $request->boolean('activo', false);
        if ($request->hasFile('avatar')) {
            // Eliminar avatar anterior si existe
            if ($usuario->avatar) {
                Storage::disk('public')->delete($usuario->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $usuario->update($data);
        $usuario->syncRoles($request->role);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        // Eliminar lógico: desactivar en lugar de borrar
        $usuario->update(['activo' => false]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario desactivado correctamente.');
    }

    public function forceDelete(User $usuario)
    {
        // Solo se puede eliminar permanentemente si ya está inactivo
        if ($usuario->activo) {
            return redirect()
                ->route('usuarios.index')
                ->with('error', 'Solo se pueden eliminar permanentemente usuarios inactivos.');
        }

        // Eliminar avatar del storage si existe
        if ($usuario->avatar) {
            Storage::disk('public')->delete($usuario->avatar);
        }

        // Revocar roles y permisos antes de eliminar
        $usuario->syncRoles([]);
        $usuario->syncPermissions([]);

        $usuario->delete(); // Eliminación real en BD

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario eliminado permanentemente.');
    }

    public function reenviarRegistro(User $usuario){
        if ($usuario->registro_completado_at) {
            return back()->with('info', 'Este usuario ya completó su registro.');
        }

        // Regenerar token si no tiene
        if (!$usuario->registro_token) {
            $usuario->update(['registro_token' => \Illuminate\Support\Str::random(64)]);
        }

        $urlRegistro = route('registro.completar', [
            'token' => $usuario->registro_token,
        ]);

        try {
            $usuario->notify(new \App\Notifications\BienvenidoUsuario(
                '(tu contraseña actual)',
                $urlRegistro
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al enviar el correo: ' . $e->getMessage());
        }

        return back()->with('success', 'Enlace de registro reenviado correctamente.');
    }
}