<?php
namespace App\Http\Controllers\Adquisiciones;

use App\Http\Controllers\Controller;
use App\Models\Dependencia;
use Illuminate\Http\Request;

class DependenciaController extends Controller
{
    public function index()
    {
        $dependencias = Dependencia::withCount('destinatarios')
                                   ->orderBy('nombre')
                                   ->paginate(20);

        return view('adquisiciones.dependencias.index', compact('dependencias'));
    }

    public function store(Request $request){
        $request->validate([
            'nombre' => 'required|string|max:150|unique:dependencias,nombre',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique'   => 'Ya existe esa dependencia.',
        ]);

        $dependencia = Dependencia::create([
            'nombre' => $request->nombre,
            'activo' => true,
        ]);

        // Si la petición espera JSON (modal rápido), devolver el objeto
        if ($request->expectsJson()) {
            return response()->json($dependencia);
        }

        return redirect()
            ->route('adquisiciones.dependencias.index')
            ->with('success', 'Dependencia agregada correctamente.');
    }

    public function update(Request $request, Dependencia $dependencia)
    {
        $request->validate([
            'nombre' => "required|string|max:150|unique:dependencias,nombre,{$dependencia->id}",
            'activo' => 'boolean',
        ]);

        $dependencia->update([
            'nombre' => $request->nombre,
            'activo' => $request->boolean('activo'),
        ]);

        return redirect()
            ->route('adquisiciones.dependencias.index')
            ->with('success', 'Dependencia actualizada.');
    }

    public function destroy(Dependencia $dependencia)
    {
        if ($dependencia->destinatarios()->count() > 0) {
            return redirect()
                ->route('adquisiciones.dependencias.index')
                ->with('error', 'No se puede eliminar, tiene destinatarios asociados.');
        }

        $dependencia->delete();

        return redirect()
            ->route('adquisiciones.dependencias.index')
            ->with('success', 'Dependencia eliminada.');
    }
}