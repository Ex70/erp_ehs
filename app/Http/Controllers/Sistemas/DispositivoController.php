<?php

namespace App\Http\Controllers\Sistemas;

use App\Http\Controllers\Controller;
use App\Models\Dispositivo;
use Illuminate\Http\Request;

class DispositivoController extends Controller
{
    public function index()
    {
        $dispositivos = Dispositivo::orderBy('nombre')->paginate(15);
        return view('sistemas.catalogos.dispositivos.index', compact('dispositivos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:60|unique:dispositivos,nombre',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique'   => 'Ya existe ese tipo de dispositivo.',
        ]);

        Dispositivo::create([
            'nombre' => $request->nombre,
            'activo' => true,
        ]);

        return redirect()
            ->route('sistemas.dispositivos.index')
            ->with('success', 'Dispositivo agregado correctamente.');
    }

    public function update(Request $request, Dispositivo $dispositivo)
    {
        $request->validate([
            'nombre' => "required|string|max:60|unique:dispositivos,nombre,{$dispositivo->id}",
            'activo' => 'boolean',
        ]);

        $dispositivo->update([
            'nombre' => $request->nombre,
            'activo' => $request->boolean('activo'),
        ]);

        return redirect()
            ->route('sistemas.dispositivos.index')
            ->with('success', 'Dispositivo actualizado.');
    }

    public function destroy(Dispositivo $dispositivo)
    {
        if ($dispositivo->asignaciones()->count() > 0) {
            return redirect()
                ->route('sistemas.dispositivos.index')
                ->with('error', 'No se puede eliminar, tiene asignaciones activas.');
        }

        $dispositivo->delete();

        return redirect()
            ->route('sistemas.dispositivos.index')
            ->with('success', 'Dispositivo eliminado.');
    }
}