<?php

namespace App\Http\Controllers\Solvencias\Trazabilidad;

use App\Http\Controllers\Controller;
use App\Models\SolvenciaExpediente as Expediente;
use Illuminate\Http\Request;

/**
 * Contabilidad y Finanzas: autorizadas por pagar y pagadas.
 * Acceso: Gate "solvencias-pagar" (personas en solvencia_pagadores).
 */
class ContabilidadController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $filtro = function ($consulta) use ($q) {
            if ($q !== '') {
                $like = "%{$q}%";
                $consulta->where(fn ($w) => $w->where('folio', 'like', $like)
                    ->orWhere('proveedor', 'like', $like)
                    ->orWhere('descripcion', 'like', $like));
            }

            return $consulta;
        };

        $con = ['departamento:id,nombre', 'documentos', 'pagoPor:id,name'];

        $porPagar = $filtro(Expediente::with($con)->where('estado', Expediente::AUTORIZADA))
            ->orderBy('autorizada_en')->get();

        $pagadas = $filtro(Expediente::with($con)->where('estado', Expediente::PAGADA))
            ->orderByDesc('pago_en')->paginate(20, ['*'], 'pagadas')->withQueryString();

        return view('solvencias.trazabilidad.contabilidad', compact('porPagar', 'pagadas', 'q'));
    }
}
