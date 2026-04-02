<?php

namespace App\Http\Controllers\Sistemas;

use App\Http\Controllers\Controller;
use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = Marca::orderBy('nombre')->paginate(15);
        return view('sistemas.catalogos.marcas.index', compact('marcas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:60|unique:marcas,nombre',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique'   => 'Ya existe esa marca.',
        ]);

        Marca::create(['nombre' => $request->nombre, 'activo' => true]);

        return redirect()
            ->route('sistemas.marcas.index')
            ->with('success', 'Marca agregada correctamente.');
    }

    public function update(Request $request, Marca $marca)
    {
        $request->validate([
            'nombre' => "required|string|max:60|unique:marcas,nombre,{$marca->id}",
            'activo' => 'boolean',
        ]);

        $marca->update([
            'nombre' => $request->nombre,
            'activo' => $request->boolean('activo'),
        ]);

        return redirect()
            ->route('sistemas.marcas.index')
            ->with('success', 'Marca actualizada.');
    }

    public function destroy(Marca $marca)
    {
        if ($marca->asignaciones()->count() > 0) {
            return redirect()
                ->route('sistemas.marcas.index')
                ->with('error', 'No se puede eliminar, tiene asignaciones activas.');
        }

        $marca->delete();

        return redirect()
            ->route('sistemas.marcas.index')
            ->with('success', 'Marca eliminada.');
    }
}