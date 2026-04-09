<?php
namespace App\Http\Controllers\Adquisiciones;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Proveedor;
use App\Models\UnidadMedida;

class CatalogoController extends Controller
{
    public function index()
    {
        $clientes   = Cliente::orderBy('nombre')->get();
        $empresas   = Empresa::orderBy('clave')->get();
        $proveedores= Proveedor::orderBy('nombre')->get();
        $unidades   = UnidadMedida::orderBy('clave')->get();

        return view('adquisiciones.catalogos.index', compact(
            'clientes', 'empresas', 'proveedores', 'unidades'
        ));
    }
}