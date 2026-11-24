<?php

namespace App\Http\Controllers\Solvencias\Trazabilidad;

use App\Http\Controllers\Controller;
use App\Models\SolvenciaExpediente as Expediente;
use App\Services\Solvencias\AccionNoPermitida;
use App\Services\Solvencias\ExpedienteService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Movimientos de la cadena de custodia. Todos son POST con redirect
 * (sin AJAX) y cada regla se valida en ExpedienteService.
 */
class ExpedienteAccionController extends Controller
{
    public function __construct(private ExpedienteService $servicio)
    {
    }

    public function acusar(Request $request, Expediente $expediente): RedirectResponse
    {
        return $this->ejecutar($expediente, fn () => $this->servicio->acusar($expediente, $request->user()),
            "Acuse registrado: {$expediente->folio} está ahora en tu poder.");
    }

    public function recordar(Request $request, Expediente $expediente): RedirectResponse
    {
        return $this->ejecutar($expediente, fn () => $this->servicio->recordar($expediente, $request->user()),
            'Recordatorio enviado y registrado en la bitácora.');
    }

    public function enviar(Request $request, Expediente $expediente): RedirectResponse
    {
        $datos = $request->validate([
            'para_id'    => 'required|integer',
            'comentario' => 'nullable|string|max:500',
        ], ['para_id.required' => 'Elige a quién se la entregas.']);

        $para = null;

        return $this->ejecutar($expediente, function () use ($request, $expediente, $datos, &$para) {
            $para = $this->servicio->enviar($expediente, $request->user(), (int) $datos['para_id'], $datos['comentario'] ?? null);
        }, function () use (&$para) {
            return "Enviada a {$para->name}. Queda «Sin acuse» hasta que confirme que la recibió.";
        });
    }

    public function devolver(Request $request, Expediente $expediente): RedirectResponse
    {
        $datos = $request->validate(['motivo' => 'required|string|max:1000']);

        return $this->ejecutar($expediente, fn () => $this->servicio->devolver($expediente, $request->user(), $datos['motivo']),
            "{$expediente->folio} devuelta a quien la elaboró para corrección.");
    }

    public function cancelar(Request $request, Expediente $expediente): RedirectResponse
    {
        $datos = $request->validate(['motivo' => 'required|string|max:1000']);

        return $this->ejecutar($expediente, fn () => $this->servicio->cancelar($expediente, $request->user(), $datos['motivo']),
            "{$expediente->folio} cancelada. Conserva su folio y toda su bitácora.");
    }

    public function autorizar(Request $request, Expediente $expediente): RedirectResponse
    {
        $datos = $request->validate(['comentario' => 'nullable|string|max:500']);

        return $this->ejecutar($expediente, fn () => $this->servicio->autorizar($expediente, $request->user(), $datos['comentario'] ?? null),
            "{$expediente->folio} autorizada. Pasa a Contabilidad y Finanzas para pago.");
    }

    public function pagar(Request $request, Expediente $expediente): RedirectResponse
    {
        $ext   = implode(',', config('solvencias.documentos.comprobante.extensiones'));
        $datos = $request->validate([
            'comprobante' => "required|file|max:" . config('solvencias.archivo.max_kb') . "|extensions:{$ext}",
            'fecha'       => 'required|date|before_or_equal:today',
        ], [
            'comprobante.required'  => 'Adjunta el comprobante de pago.',
            'comprobante.extensions' => "El comprobante debe ser: {$ext}.",
            'fecha.before_or_equal' => 'La fecha de pago no puede ser futura.',
        ]);

        return $this->ejecutar($expediente, fn () => $this->servicio->pagar($expediente, $request->user(), $request->file('comprobante'), $datos['fecha']),
            "{$expediente->folio} pagada. Solicitud de recurso finalizada.");
    }

    /**
     * @param Closure|string $exito mensaje, o closure que lo arma después de la acción
     */
    private function ejecutar(Expediente $expediente, Closure $accion, Closure|string $exito): RedirectResponse
    {
        try {
            $accion();
        } catch (AccionNoPermitida $e) {
            return redirect()->route('sol.expedientes.show', $expediente)->with('error', $e->getMessage());
        }

        return redirect()->back(fallback: route('sol.expedientes.show', $expediente))
            ->with('success', $exito instanceof Closure ? $exito() : $exito);
    }
}
