<?php
namespace App\Http\Controllers\Solvencias;

use App\Http\Controllers\Controller;
use App\Models\ProveedorSolvencia;
use App\Models\ProveedorCuentaBancaria;
use Illuminate\Http\Request;

class CuentaBancariaController extends Controller
{
    // Devuelve las cuentas de un proveedor en JSON (para el formulario dinámico)
    public function porProveedor(ProveedorSolvencia $proveedor)
    {
        return response()->json(
            $proveedor->cuentasBancarias()->get()
        );
    }
}