<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Puesto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartamentoController extends Controller
{
    public function index()
    {
        $departamentos = Departamento::with('responsable')
            ->withCount(['usuarios', 'puestos'])
            ->orderBy('nombre')
            ->paginate(15);

        return view('departamentos.index', compact('departamentos'));
    }

    public function create()
    {
        $puestos     = Puesto::where('activo', true)->orderBy('nombre')->get();
        $responsables = User::where('activo', true)->orderBy('name')->get();
        $departamento = new Departamento();

        return view('departamentos.create', compact('departamento', 'puestos', 'responsables'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'         => ['required', 'string', 'max:120', 'unique:departamentos,nombre'],
            'clave'          => ['nullable', 'string', 'max:20', 'unique:departamentos,clave'],
            'descripcion'    => ['nullable', 'string'],
            'responsable_id' => ['nullable', 'exists:users,id'],
            'puestos'        => ['nullable', 'array'],
            'puestos.*'      => ['exists:puestos,id'],
        ], [
            'nombre.unique' => 'Ya existe un departamento con ese nombre.',
            'clave.unique'  => 'Ya existe un departamento con esa clave.',
        ]);

        $data['activo'] = $request->boolean('activo', true);

        $departamento = Departamento::create($data);
        $departamento->puestos()->sync($request->input('puestos', []));

        return redirect()
            ->route('departamentos.index')
            ->with('success', 'Departamento creado correctamente.');
    }

    public function show(Departamento $departamento)
    {
        $departamento->load(['responsable', 'puestos']);

        // Plantilla del departamento agrupada por puesto
        $plantilla = $departamento->usuarios()
            ->with('puesto')
            ->where('activo', true)
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($u) => $u->puesto->nombre ?? 'Sin puesto asignado');

        return view('departamentos.show', compact('departamento', 'plantilla'));
    }

    public function edit(Departamento $departamento)
    {
        $puestos      = Puesto::where('activo', true)->orderBy('nombre')->get();
        $responsables = User::where('activo', true)->orderBy('name')->get();
        $asignados    = $departamento->puestos->pluck('id')->toArray();

        return view('departamentos.edit', compact('departamento', 'puestos', 'responsables', 'asignados'));
    }

    public function update(Request $request, Departamento $departamento)
    {
        $data = $request->validate([
            'nombre'         => ['required', 'string', 'max:120',
                                 Rule::unique('departamentos', 'nombre')->ignore($departamento->id)],
            'clave'          => ['nullable', 'string', 'max:20',
                                 Rule::unique('departamentos', 'clave')->ignore($departamento->id)],
            'descripcion'    => ['nullable', 'string'],
            'responsable_id' => ['nullable', 'exists:users,id'],
            'puestos'        => ['nullable', 'array'],
            'puestos.*'      => ['exists:puestos,id'],
        ], [
            'nombre.unique' => 'Ya existe un departamento con ese nombre.',
            'clave.unique'  => 'Ya existe un departamento con esa clave.',
        ]);

        $data['activo'] = $request->boolean('activo', false);

        $seleccionados = $request->input('puestos', []);

        // Protección: no permitir desvincular un puesto que aún tiene
        // usuarios asignados dentro de este departamento
        $enUso = $departamento->usuarios()
            ->whereNotNull('puesto_id')
            ->pluck('puesto_id')
            ->unique();

        $conflicto = $enUso->diff($seleccionados);

        if ($conflicto->isNotEmpty()) {
            $nombres = Puesto::whereIn('id', $conflicto)->pluck('nombre')->implode(', ');

            return back()
                ->withInput()
                ->with('error', "No se pueden quitar los puestos con personal asignado: {$nombres}.");
        }

        $departamento->update($data);
        $departamento->puestos()->sync($seleccionados);

        return redirect()
            ->route('departamentos.index')
            ->with('success', 'Departamento actualizado correctamente.');
    }

    public function destroy(Departamento $departamento)
    {
        if ($departamento->usuarios()->exists()) {
            return back()->with('error',
                'No se puede eliminar: hay usuarios asignados a este departamento.');
        }

        $departamento->puestos()->detach();
        $departamento->delete(); // SoftDeletes

        return redirect()
            ->route('departamentos.index')
            ->with('success', 'Departamento eliminado correctamente.');
    }

    /**
     * Endpoint JSON para el select encadenado departamento → puesto.
     */
    public function puestos(Departamento $departamento)
    {
        return response()->json(
            $departamento->puestosActivos()->get(['puestos.id', 'puestos.nombre'])
        );
    }
}