<?php
namespace App\Http\Controllers\Solvencias;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;  // tabla existente

class CuentaBancariaController extends Controller
{
    public function porProveedor(Proveedor $proveedor)
    {
        return response()->json(
            $proveedor->cuentasBancarias()->get()
        );
    }
}