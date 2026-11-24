<?php

namespace App\Http\Controllers\Solvencias\Trazabilidad;

use App\Http\Controllers\Controller;
use App\Models\SolvenciaExpediente;
use App\Services\Solvencias\FlujoService;
use Illuminate\Http\Request;

/**
 * Mi bandeja: lo que tengo que acusar, lo que está en mi poder,
 * lo que envié y no han acusado, y (Contabilidad) lo autorizado por pagar.
 */
class BandejaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $con  = ['departamento:id,nombre', 'holder:id,name', 'enviadoPor:id,name', 'documentos'];

        $porAcusar = SolvenciaExpediente::activas()->with($con)
            ->where('holder_id', $user->id)->where('acuse', false)
            ->orderBy('enviado_en')->get();

        $enPoder = SolvenciaExpediente::activas()->with($con)
            ->where('holder_id', $user->id)->where('acuse', true)
            ->orderBy('desde')->get();

        $enviadas = SolvenciaExpediente::activas()->with($con)
            ->where('enviado_por_id', $user->id)->where('acuse', false)
            ->where('holder_id', '!=', $user->id)
            ->orderBy('enviado_en')->get();

        $esPagador = FlujoService::esPagador($user);

        $porPagar = $esPagador
            ? SolvenciaExpediente::with($con)->where('estado', SolvenciaExpediente::AUTORIZADA)->orderBy('autorizada_en')->get()
            : collect();

        return view('solvencias.trazabilidad.bandeja', [
            'porAcusar'       => $porAcusar,
            'enPoder'         => $enPoder,
            'enviadas'        => $enviadas,
            'porPagar'        => $porPagar,
            'esPagador'       => $esPagador,
            'puedeElaborar'   => FlujoService::puedeElaborar($user),
            'responsabilidad' => FlujoService::responsabilidadTexto($user->id),
        ]);
    }
}
