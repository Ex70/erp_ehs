<?php
namespace App\Http\Controllers\Adquisiciones;

use App\Http\Controllers\Controller;
use App\Models\Requerimiento;
use App\Models\RequerimientoNota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotaController extends Controller
{
    public function store(Request $request, Requerimiento $requerimiento)
    {
        $request->validate([
            'texto' => 'required|string|max:1000',
        ]);

        RequerimientoNota::create([
            'requerimiento_id' => $requerimiento->id,
            'user_id'          => Auth::id(),
            'texto'            => $request->texto,
        ]);

        return back()->with('success', 'Nota agregada.');
    }

    public function destroy(RequerimientoNota $nota)
    {
        $requerimiento = $nota->requerimiento;
        $nota->delete();

        return redirect()
            ->route('adquisiciones.requerimientos.show', $requerimiento)
            ->with('success', 'Nota eliminada.');
    }
}