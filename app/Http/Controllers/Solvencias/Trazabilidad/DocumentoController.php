<?php

namespace App\Http\Controllers\Solvencias\Trazabilidad;

use App\Http\Controllers\Controller;
use App\Models\SolvenciaDocumento;
use App\Models\SolvenciaExpediente as Expediente;
use App\Services\Solvencias\AccionNoPermitida;
use App\Services\Solvencias\ExpedienteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Soporte del expediente: carga, "no se anexa" con observación, consulta
 * en línea y descarga. Los archivos viven en el disco privado y solo se
 * sirven a quien puede ver el expediente.
 */
class DocumentoController extends Controller
{
    public function __construct(private ExpedienteService $servicio)
    {
    }

    public function store(Request $request, Expediente $expediente)
    {
        $tiposCargables = array_values(array_diff(array_keys(config('solvencias.documentos')), ['comprobante']));

        $request->validate(['tipo' => ['required', Rule::in($tiposCargables)]]);

        $tipo = $request->input('tipo');
        $cfg  = config("solvencias.documentos.{$tipo}");
        $ext  = implode(',', $cfg['extensiones']);

        $request->validate([
            'archivo' => "required|file|max:" . config('solvencias.archivo.max_kb') . "|extensions:{$ext}",
        ], [
            'archivo.required'   => 'Selecciona un archivo.',
            'archivo.max'        => 'El archivo excede el tamaño máximo permitido.',
            'archivo.extensions' => "«{$cfg['nombre']}» acepta: {$ext}.",
            'archivo.uploaded'   => 'El archivo no se pudo subir (revisa el tamaño máximo del servidor).',
        ]);

        try {
            $this->servicio->cargarDocumento($expediente, $request->user(), $tipo, $request->file('archivo'));
        } catch (AccionNoPermitida $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "«{$cfg['nombre']}» registrado en el expediente.");
    }

    public function exceptuar(Request $request, Expediente $expediente)
    {
        $datos = $request->validate([
            'tipo'        => ['required', Rule::in(array_keys(config('solvencias.documentos')))],
            'observacion' => 'required|string|max:1000',
        ], ['observacion.required' => 'La observación es obligatoria.']);

        try {
            $this->servicio->exceptuarDocumento($expediente, $request->user(), $datos['tipo'], $datos['observacion']);
        } catch (AccionNoPermitida $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Documento registrado sin archivo, con su observación.');
    }

    /**
     * Abre en el navegador (PDF e imágenes). Lo demás se descarga.
     */
    public function ver(Request $request, SolvenciaDocumento $documento)
    {
        $this->autorizar($request, $documento);

        if (! $documento->seMuestraEnLinea()) {
            return $this->descargar($request, $documento);
        }

        return Storage::disk(config('solvencias.archivo.disco'))->response(
            $documento->archivo,
            $documento->nombre,
            [
                'Content-Type'           => SolvenciaDocumento::EN_LINEA[$documento->extension()],
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control'          => 'private, max-age=0, no-store',
            ],
            'inline'
        );
    }

    public function descargar(Request $request, SolvenciaDocumento $documento)
    {
        $this->autorizar($request, $documento);

        return Storage::disk(config('solvencias.archivo.disco'))->download(
            $documento->archivo,
            $documento->nombre,
            ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, max-age=0, no-store']
        );
    }

    private function autorizar(Request $request, SolvenciaDocumento $documento): void
    {
        $documento->loadMissing('expediente');

        abort_unless($documento->expediente->puedeVer($request->user()), 403, 'No participas en el flujo de esta solvencia.');
        abort_unless($documento->tieneArchivo(), 404, 'Este documento no tiene archivo.');
        abort_unless(Storage::disk(config('solvencias.archivo.disco'))->exists($documento->archivo), 404,
            'El archivo no se encuentra en el servidor.');
    }
}
