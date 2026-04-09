<?php
namespace App\Http\Controllers\Adquisiciones;

use App\Http\Controllers\Controller;
use App\Models\Requerimiento;
use Illuminate\Http\Request;

class AdjudicacionController extends Controller
{
    public function store(Request $request, Requerimiento $requerimiento)
    {
        $request->validate([
            'monto_autorizado'      => 'required|numeric|min:0',
            'costo_proveedor'       => 'required|numeric|min:0',
            'fecha_max_entrega_aut' => 'nullable|date',
        ], [
            'monto_autorizado.required' => 'El monto autorizado es obligatorio.',
            'costo_proveedor.required'  => 'El costo del proveedor es obligatorio.',
        ]);

        $requerimiento->update([
            'monto_autorizado'      => $request->monto_autorizado,
            'costo_proveedor'       => $request->costo_proveedor,
            'fecha_max_entrega_aut' => $request->fecha_max_entrega_aut,
            'status'                => 'autorizado',
            'autorizado'            => true,
        ]);

        // Marcar proveedor ganador si se especificó
        if ($request->proveedor_ganador_id) {
            $requerimiento->proveedores()->update(['ganador' => false]);
            $requerimiento->proveedores()
                ->where('proveedor_id', $request->proveedor_ganador_id)
                ->update(['ganador' => true]);
        }

        return redirect()
            ->route('adquisiciones.requerimientos.show', $requerimiento)
            ->with('success', 'Requerimiento adjudicado correctamente.');
    }
}