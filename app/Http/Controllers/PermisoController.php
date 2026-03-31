<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use App\Http\Requests\StorePermisoRequest;
use App\Http\Requests\UpdatePermisoRequest;

class PermisoController extends Controller
{
    public function index()
    {
        $permisos = Permission::orderBy('name')
                              ->paginate(15);

        // Para la vista agrupamos por módulo
        $porModulo = Permission::orderBy('name')->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0]);

        return view('permisos.index', compact('permisos', 'porModulo'));
    }

    public function create()
    {
        // Módulos existentes para el datalist
        $modulos = Permission::orderBy('name')->get()
            ->map(fn($p) => explode('.', $p->name)[0])
            ->unique()
            ->values();

        return view('permisos.create', compact('modulos'));
    }

    public function store(StorePermisoRequest $request)
    {
        Permission::create(['name' => $request->name]);

        return redirect()
            ->route('permisos.index')
            ->with('success', "Permiso '{$request->name}' creado correctamente.");
    }

    public function show(Permission $permiso)
    {
        $roles = $permiso->roles()->get();
        return view('permisos.show', compact('permiso', 'roles'));
    }

    public function edit(Permission $permiso)
    {
        $modulos = Permission::orderBy('name')->get()
            ->map(fn($p) => explode('.', $p->name)[0])
            ->unique()
            ->values();

        // Separar módulo y acción del nombre actual
        [$modulo, $accion] = array_pad(explode('.', $permiso->name, 2), 2, '');

        return view('permisos.edit', compact('permiso', 'modulos', 'modulo', 'accion'));
    }

    public function update(UpdatePermisoRequest $request, Permission $permiso)
    {
        $permiso->update(['name' => $request->name]);

        return redirect()
            ->route('permisos.index')
            ->with('success', "Permiso actualizado correctamente.");
    }

    public function destroy(Permission $permiso)
    {
        $nombre = $permiso->name;
        $permiso->delete();

        return redirect()
            ->route('permisos.index')
            ->with('success', "Permiso '{$nombre}' eliminado correctamente.");
    }
}