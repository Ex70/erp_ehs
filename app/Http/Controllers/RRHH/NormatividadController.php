<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentoNormativoRequest;
use App\Models\DocumentoNormativo;
use App\Models\VersionNormativa;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NormatividadController extends Controller
{
    /**
     * Listado por categorías con búsqueda.
     */
    public function index(Request $request)
    {
        $termino  = $request->string('q')->trim()->value();
        $puedeVerInactivos = $request->user()->can('normatividad.editar');

        $consulta = DocumentoNormativo::query()
            ->with(['versiones' => fn ($q) => $q->limit(10)])
            ->buscar($termino)
            ->orderBy('titulo');

        if (! $puedeVerInactivos) {
            $consulta->activos();
        }

        $documentos = $consulta->get()->groupBy('categoria');

        $categorias = DocumentoNormativo::categorias();

        // Conteo por categoría para las pestañas
        $conteos = collect($categorias)->mapWithKeys(
            fn ($cfg, $clave) => [$clave => ($documentos[$clave] ?? collect())->count()]
        );

        $porVencer = DocumentoNormativo::activos()
            ->get()
            ->filter(fn ($d) => in_array($d->estado_vigencia, ['vencido', 'por_vencer'], true))
            ->count();

        return view('rrhh.normatividad.index', compact(
            'documentos', 'categorias', 'conteos', 'termino', 'porVencer'
        ));
    }

    public function store(DocumentoNormativoRequest $request)
    {
        $datos = $request->validated();

        $documento = new DocumentoNormativo();
        $documento->fill($this->camposBase($datos, $request));
        $documento->creado_por        = $request->user()->id;
        $documento->actualizado_por   = $request->user()->id;
        $documento->fecha_publicacion = now()->toDateString();

        if ($request->hasFile('archivo')) {
            $this->adjuntar($documento, $request->file('archivo'));
        }

        $documento->save();

        return redirect()
            ->route('rrhh.normatividad.index', ['cat' => $documento->categoria])
            ->with('success', 'Documento normativo creado correctamente.');
    }

    public function update(DocumentoNormativoRequest $request, DocumentoNormativo $documento)
    {
        $datos = $request->validated();

        DB::transaction(function () use ($request, $documento, $datos) {

            // Si llega un archivo nuevo y ya existía uno, la versión anterior
            // se archiva en el historial en lugar de perderse.
            if ($request->hasFile('archivo') && $documento->tiene_archivo) {
                VersionNormativa::create([
                    'documento_id'    => $documento->id,
                    'version'         => $documento->version,
                    'archivo'         => $documento->archivo,
                    'archivo_nombre'  => $documento->archivo_nombre,
                    'archivo_tamano'  => $documento->archivo_tamano,
                    'archivo_mime'    => $documento->archivo_mime,
                    'vigencia'        => $documento->vigencia,
                    'notas'           => $request->input('notas_version'),
                    'reemplazado_por' => $request->user()->id,
                ]);
            }

            $documento->fill($this->camposBase($datos, $request));
            $documento->actualizado_por = $request->user()->id;

            if ($request->hasFile('archivo')) {
                $this->adjuntar($documento, $request->file('archivo'));
            }

            $documento->save();
        });

        return redirect()
            ->route('rrhh.normatividad.index', ['cat' => $documento->categoria])
            ->with('success', 'Documento normativo actualizado correctamente.');
    }

    /**
     * Borrado lógico. El archivo y el historial se conservan: la normatividad
     * derogada suele necesitar consultarse después.
     */
    public function destroy(DocumentoNormativo $documento)
    {
        $categoria = $documento->categoria;
        $documento->delete();

        return redirect()
            ->route('rrhh.normatividad.index', ['cat' => $categoria])
            ->with('success', 'Documento eliminado. El archivo y su historial se conservan.');
    }

    /**
     * Descarga del archivo vigente. Se sirve por controlador (nunca por URL
     * pública) para que respete el permiso de lectura.
     */
    public function descargar(DocumentoNormativo $documento): StreamedResponse
    {
        abort_unless($documento->tiene_archivo, 404, 'Este documento no tiene archivo adjunto.');

        $disco = config('normatividad.archivo.disco', 'local');
        abort_unless(Storage::disk($disco)->exists($documento->archivo), 404);

        return Storage::disk($disco)->download($documento->archivo, $documento->archivo_nombre);
    }

    public function descargarVersion(VersionNormativa $version): StreamedResponse
    {
        $disco = config('normatividad.archivo.disco', 'local');
        abort_unless(Storage::disk($disco)->exists($version->archivo), 404);

        return Storage::disk($disco)->download($version->archivo, $version->archivo_nombre);
    }

    /* ───────── Helpers ───────── */

    private function camposBase(array $datos, Request $request): array
    {
        return [
            'categoria'   => $datos['categoria'],
            'titulo'      => $datos['titulo'],
            'version'     => $datos['version']     ?? null,
            'vigencia'    => $datos['vigencia']    ?? null,
            'responsable' => $datos['responsable'] ?? null,
            'descripcion' => $datos['descripcion'] ?? null,
            'activo'      => $request->boolean('activo', true),
        ];
    }

    private function adjuntar(DocumentoNormativo $documento, UploadedFile $archivo): void
    {
        $config = config('normatividad.archivo');

        $documento->archivo        = $archivo->store($config['directorio'], $config['disco']);
        $documento->archivo_nombre = $archivo->getClientOriginalName();
        $documento->archivo_tamano = $archivo->getSize();
        $documento->archivo_mime   = $archivo->getClientMimeType();
    }
}
