<?php
namespace App\Http\Controllers\Solvencias;

use App\Http\Controllers\Controller;
use App\Models\EmpresaSolvencia;
use Illuminate\Http\Request;

class EmpresaSolvenciaController extends Controller
{
    public function index()
    {
        $empresas = EmpresaSolvencia::withCount('solvencias')
                                    ->orderBy('nombre')
                                    ->paginate(20);

        return view('solvencias.empresas.index', compact('empresas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150|unique:empresas_solvencia,nombre',
            'rfc'    => 'nullable|string|max:20',
        ]);

        $empresa = EmpresaSolvencia::create([
            'nombre' => $request->nombre,
            'rfc'    => $request->rfc,
            'activo' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json($empresa);
        }

        return redirect()
            ->route('solvencias.empresas.index')
            ->with('success', 'Empresa agregada.');
    }

    public function update(Request $request, EmpresaSolvencia $empresa)
    {
        $request->validate([
            'nombre' => "required|string|max:150|unique:empresas_solvencia,nombre,{$empresa->id}",
            'rfc'    => 'nullable|string|max:20',
            'activo' => 'boolean',
        ]);

        $empresa->update([
            'nombre' => $request->nombre,
            'rfc'    => $request->rfc,
            'activo' => $request->boolean('activo'),
        ]);

        return redirect()
            ->route('solvencias.empresas.index')
            ->with('success', 'Empresa actualizada.');
    }

    public function destroy(EmpresaSolvencia $empresa)
    {
        if ($empresa->solvencias()->count() > 0) {
            return redirect()
                ->route('solvencias.empresas.index')
                ->with('error', 'No se puede eliminar, tiene solvencias asociadas.');
        }

        $empresa->delete();

        return redirect()
            ->route('solvencias.empresas.index')
            ->with('success', 'Empresa eliminada.');
    }
}