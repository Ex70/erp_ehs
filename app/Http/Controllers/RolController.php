<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\StoreRolRequest;
use App\Http\Requests\UpdateRolRequest;

class RolController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])
                     ->orderBy('name')
                     ->paginate(10);

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        // Agrupar permisos por módulo (todo lo que está antes del punto)
        $permisos = Permission::orderBy('name')->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0]);

        return view('roles.create', compact('permisos'));
    }

    public function store(StoreRolRequest $request)
    {
        $rol = Role::create(['name' => $request->name]);
        $rol->syncPermissions($request->permissions ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', "Rol '{$rol->name}' creado correctamente.");
    }

    public function show(Role $rol)
    {
        $rol->load('permissions');

        $permisosPorModulo = $rol->permissions
            ->groupBy(fn($p) => explode('.', $p->name)[0]);

        $usuarios = $rol->users()->paginate(5);

        return view('roles.show', compact('rol', 'permisosPorModulo', 'usuarios'));
    }

    public function edit(Role $rol){
        $permisos = Permission::orderBy('name')->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0]);

        $permisosActivos = $rol->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('rol', 'permisos', 'permisosActivos'));
    }

    public function update(UpdateRolRequest $request, Role $rol)
    {
        $rol->update(['name' => $request->name]);
        $rol->syncPermissions($request->permissions ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', "Rol '{$rol->name}' actualizado correctamente.");
    }

    public function destroy(Role $rol)
    {
        if ($rol->users()->count() > 0) {
            return redirect()
                ->route('roles.index')
                ->with('error', 'No se puede eliminar un rol con usuarios asignados.');
        }

        $nombre = $rol->name;
        $rol->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', "Rol '{$nombre}' eliminado correctamente.");
    }
}