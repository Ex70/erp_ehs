<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Puesto;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsuarioController extends Controller
{
    public function index(){
        $usuarios = User::with(['puesto', 'roles'])
                    ->orderBy('name')
                    ->paginate(10);
        return view('usuarios.index', compact('usuarios'));
    }

    public function create(){
        $puestos = Puesto::where('activo', true)->orderBy('nombre')->get();
        $roles = Role::orderBy('name')->get();
        return view('usuarios.create', compact('puestos', 'roles'));
    }

    public function store(StoreUsuarioRequest $request){
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['activo']   = $request->boolean('activo', true);
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $usuario = User::create($data);
        $usuario->assignRole($request->role);
        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show(User $usuario){
        $usuario->load(['puesto', 'roles', 'permissions']);
        return view('usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario){
        $puestos = Puesto::where('activo', true)->orderBy('nombre')->get();
        $roles   = Role::orderBy('name')->get();
        return view('usuarios.edit', compact('usuario', 'puestos', 'roles'));
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
}