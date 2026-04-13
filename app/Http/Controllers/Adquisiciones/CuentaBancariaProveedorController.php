<?php
namespace App\Http\Controllers\Adquisiciones;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use App\Models\ProveedorCuentaBancaria;
use Illuminate\Http\Request;

class CuentaBancariaProveedorController extends Controller
{
    // Devuelve cuentas en JSON para el formulario dinámico de solvencias
    public function porProveedor(Proveedor $proveedor)
    {
        return response()->json(
            $proveedor->cuentasBancarias()->get()
        );
    }

    public function store(Request $request, Proveedor $proveedor)
    {
        $request->validate([
            'banco'          => 'required|string|max:60',
            'clabe'          => 'nullable|string|max:25',
            'cuenta'         => 'nullable|string|max:30',
            'referencia'     => 'nullable|string|max:60',
            'tiempo_entrega' => 'nullable|string|max:60',
        ]);

        ProveedorCuentaBancaria::create([
            'proveedor_id'   => $proveedor->id,
            'banco'          => $request->banco,
            'clabe'          => $request->clabe,
            'cuenta'         => $request->cuenta,
            'referencia'     => $request->referencia,
            'tiempo_entrega' => $request->tiempo_entrega,
            'principal'      => $proveedor->cuentasBancarias()->count() === 0,
        ]);

        return redirect()
            ->route('adquisiciones.proveedores.index')
            ->with('success', 'Cuenta bancaria agregada correctamente.');
    }

    public function destroy(ProveedorCuentaBancaria $cuenta)
    {
        $cuenta->delete();

        return redirect()
            ->route('adquisiciones.proveedores.index')
            ->with('success', 'Cuenta bancaria eliminada.');
    }
}