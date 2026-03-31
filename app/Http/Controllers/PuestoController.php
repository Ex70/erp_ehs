<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Puesto;
use App\Http\Requests\StorePuestoRequest;
use App\Http\Requests\UpdatePuestoRequest;

class PuestoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(){
        $puestos = Puesto::withCount('users')->orderBy('nombre')->paginate(10);
        return view('puestos.index', compact('puestos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('puestos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePuestoRequest $request)
    {
        $data           = $request->validated();
        $data['activo'] = $request->boolean('activo', true);

        Puesto::create($data);

        return redirect()
            ->route('puestos.index')
            ->with('success', 'Puesto creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Puesto $puesto)
    {
        $puesto->loadCount('users');
        $puesto->load(['users' => fn($q) => $q->with('roles')->orderBy('name')]);

        return view('puestos.show', compact('puesto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Puesto $puesto)
    {
        return view('puestos.edit', compact('puesto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePuestoRequest $request, Puesto $puesto)
    {
        $data           = $request->validated();
        $data['activo'] = $request->boolean('activo', false);

        $puesto->update($data);

        return redirect()
            ->route('puestos.index')
            ->with('success', 'Puesto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Puesto $puesto){
        // Evitar eliminar si tiene usuarios asignados
        if ($puesto->users()->count() > 0) {
            return redirect()
                ->route('puestos.index')
                ->with('error', 'No se puede eliminar un puesto con usuarios asignados.');
        }

        $puesto->delete();

        return redirect()
            ->route('puestos.index')
            ->with('success', 'Puesto eliminado correctamente.');
    }

}
