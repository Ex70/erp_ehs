<?php

namespace App\Http\Controllers\Adquisiciones\Logistica;

use App\Http\Controllers\Controller;
use App\Models\Dependencia;
use App\Models\Destinatario;
use App\Models\Empresa;
use App\Models\Remision;
use App\Models\UnidadMedida;
use App\Services\Logistica\FolioRemisionService;
use App\Services\Logistica\ImagenPartidaService;
use App\Services\Logistica\PdfLogistica;
use App\Services\Logistica\ResponsableService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RemisionController extends Controller
{
    /** Caja máxima (ancho, alto en px) de la imagen de partida por formato. */
    private const CAJA_IMAGEN = ['EHS' => [120, 90], 'MHR' => [160, 110]];

    public function index(Request $request)
    {
        $query = Remision::with(['empresa', 'dependencia', 'entrega'])
            ->withCount('partidas')
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('folio', 'like', "%{$q}%")
                  ->orWhere('referencia', 'like', "%{$q}%")
                  ->orWhere('num_remision', 'like', "%{$q}%")
                  ->orWhereHas('dependencia', fn ($d) => $d->where('nombre', 'like', "%{$q}%"))
                  ->orWhereHas('partidas', fn ($p) => $p->where('descripcion', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        if ($request->filled('dependencia_id')) {
            $query->where('dependencia_id', $request->dependencia_id);
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha', '<=', $request->hasta);
        }

        $remisiones = $query->paginate(20)->withQueryString();

        $delMes = Remision::selectRaw('empresa_id, COUNT(*) as total')
            ->whereYear('fecha', now()->year)
            ->whereMonth('fecha', now()->month)
            ->groupBy('empresa_id')
            ->pluck('total', 'empresa_id');

        return view('adquisiciones.logistica.remisiones.index', [
            'remisiones'   => $remisiones,
            'empresas'     => $this->empresasHabilitadas(),
            'dependencias' => Dependencia::orderBy('nombre')->get(['id', 'nombre']),
            'formatos'     => config('logistica.remisiones.empresas'),
            'delMes'       => $delMes,
        ]);
    }

    public function create(Request $request)
    {
        $empresas = $this->empresasHabilitadas();
        $elegida  = $empresas->firstWhere('clave', strtoupper((string) $request->query('empresa')));

        $remision = new Remision([
            'fecha'      => today(),
            'empresa_id' => $elegida?->id ?? $empresas->first()?->id,
        ]);

        $partidas = [[
            'id' => null, 'numero' => '1', 'descripcion' => '',
            'unidad' => '', 'cantidad' => 1, 'imagen_url' => null,
        ]];

        return view('adquisiciones.logistica.remisiones.form', $this->datosFormulario($remision, $partidas));
    }

    public function store(Request $request)
    {
        $datos       = $this->validar($request);
        $empresa     = Empresa::findOrFail($datos['empresa_id']);
        $dependencia = Dependencia::findOrFail($datos['dependencia_id']);

        $remision = DB::transaction(function () use ($datos, $empresa, $dependencia, $request) {
            $datos['folio'] = $datos['folio'] ?: FolioRemisionService::generar(
                $empresa,
                $datos['fecha'],
                data_get(collect($request->input('partidas', []))->first(), 'descripcion'),
                $dependencia->nombre
            );

            $remision = Remision::create($this->camposRemision($datos, $empresa) + [
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->sincronizarPartidas($remision, $request);

            return $remision;
        });

        return redirect()
            ->route('adquisiciones.logistica.remisiones.show', $remision)
            ->with('success', "Nota de remisión {$remision->folio} guardada.");
    }

    public function show(Remision $remision)
    {
        $remision->load(['empresa', 'dependencia', 'entrega', 'creador', 'partidas']);

        return view('adquisiciones.logistica.remisiones.show', [
            'remision' => $remision,
            'formato'  => $remision->formato,
        ]);
    }

    public function edit(Remision $remision)
    {
        $remision->load('partidas');

        $partidas = $remision->partidas->map(fn ($p) => [
            'id'          => $p->id,
            'numero'      => $p->numero,
            'descripcion' => $p->descripcion,
            'unidad'      => $p->unidad,
            'cantidad'    => (float) $p->cantidad,
            'imagen_url'  => $p->imagen_url,
        ])->all();

        return view('adquisiciones.logistica.remisiones.form', $this->datosFormulario($remision, $partidas));
    }

    public function update(Request $request, Remision $remision)
    {
        $datos       = $this->validar($request, $remision);
        $empresa     = Empresa::findOrFail($datos['empresa_id']);
        $dependencia = Dependencia::findOrFail($datos['dependencia_id']);

        DB::transaction(function () use ($datos, $empresa, $dependencia, $request, $remision) {
            if (! $datos['folio']) {
                // Vacío: se conserva el actual; si cambió de empresa, se genera uno nuevo
                $datos['folio'] = (int) $remision->empresa_id === (int) $empresa->id
                    ? $remision->folio
                    : FolioRemisionService::generar(
                        $empresa,
                        $datos['fecha'],
                        data_get(collect($request->input('partidas', []))->first(), 'descripcion'),
                        $dependencia->nombre
                    );
            }

            $remision->update($this->camposRemision($datos, $empresa) + [
                'updated_by' => auth()->id(),
            ]);

            $this->sincronizarPartidas($remision, $request);
        });

        return redirect()
            ->route('adquisiciones.logistica.remisiones.show', $remision)
            ->with('success', "Nota de remisión {$remision->folio} actualizada.");
    }

    public function destroy(Remision $remision)
    {
        $folio = $remision->folio;

        // Soft delete: el folio queda reservado y las imágenes se conservan
        $remision->delete();

        return redirect()
            ->route('adquisiciones.logistica.remisiones.index')
            ->with('success', "Nota de remisión {$folio} eliminada.");
    }

    public function pdf(Request $request, Remision $remision)
    {
        $remision->load(['empresa', 'dependencia', 'entrega', 'partidas']);

        $vista = 'adquisiciones.logistica.remisiones.pdf.' . strtolower($remision->empresa->clave);
        abort_unless(view()->exists($vista), 404, 'La empresa no tiene formato de remisión.');

        $pdf = Pdf::loadView($vista, $this->datosPdf($remision))->setPaper('letter', 'portrait');

        $archivo = 'REMISION_' . $remision->empresa->clave . '_'
            . trim(preg_replace('/[^A-Za-z0-9]+/', '_', $remision->folio), '_') . '.pdf';

        return $request->boolean('descargar') ? $pdf->download($archivo) : $pdf->stream($archivo);
    }

    // ─────────────────────────────────────────────────────────────────────

    private function validar(Request $request, ?Remision $remision = null): array
    {
        return $request->validate([
            'empresa_id'      => ['required', Rule::exists('empresas', 'id')
                                    ->where('activo', true)
                                    ->whereIn('clave', array_keys(config('logistica.remisiones.empresas', [])))],
            'dependencia_id'  => ['required', Rule::exists('dependencias', 'id')],
            'folio'           => ['nullable', 'string', 'max:40',
                                  Rule::unique('remisiones', 'folio')
                                      ->where('empresa_id', $request->input('empresa_id'))
                                      ->ignore($remision?->id)],
            'referencia'      => ['nullable', 'string', 'max:80'],
            'num_remision'    => ['nullable', 'string', 'max:40'],
            'fecha'           => ['required', 'date'],
            'entrega_user_id' => ['nullable', Rule::in(ResponsableService::idsDisponibles($remision?->entrega_user_id))],
            'recibe_nombre'   => ['nullable', 'string', 'max:120'],
            'recibe_cargo'    => ['nullable', 'string', 'max:120'],
            'observaciones'   => ['nullable', 'string', 'max:2000'],

            'partidas'                 => ['required', 'array', 'min:1'],
            'partidas.*.id'            => ['nullable', 'integer'],
            'partidas.*.numero'        => ['nullable', 'string', 'max:20'],
            'partidas.*.descripcion'   => ['required', 'string', 'max:2000'],
            'partidas.*.unidad'        => ['required', 'string', 'max:40'],
            'partidas.*.cantidad'      => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'partidas.*.imagen'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'partidas.*.quitar_imagen' => ['nullable', 'boolean'],
        ], [
            'empresa_id.required'             => 'Selecciona la empresa que emite.',
            'dependencia_id.required'         => 'Selecciona el cliente (dependencia).',
            'folio.unique'                    => 'Ese folio ya existe para esta empresa.',
            'fecha.required'                  => 'La fecha es obligatoria.',
            'entrega_user_id.in'              => 'El responsable de entrega no pertenece al departamento.',
            'partidas.required'               => 'Agrega al menos una partida.',
            'partidas.*.descripcion.required' => 'Todas las partidas necesitan descripción.',
            'partidas.*.unidad.required'      => 'Todas las partidas necesitan unidad de medida.',
            'partidas.*.cantidad.required'    => 'Todas las partidas necesitan cantidad.',
            'partidas.*.cantidad.gt'          => 'La cantidad de cada partida debe ser mayor a cero.',
            'partidas.*.imagen.image'         => 'Una de las imágenes no es válida.',
            'partidas.*.imagen.mimes'         => 'Las imágenes deben ser JPG, PNG o WEBP.',
            'partidas.*.imagen.max'           => 'Cada imagen puede pesar máximo 5 MB.',
        ]);
    }

    /**
     * Campos de la remisión, limpiando lo que el formato de la empresa no usa.
     */
    private function camposRemision(array $d, Empresa $empresa): array
    {
        $fmt   = config("logistica.remisiones.empresas.{$empresa->clave}", []);
        $mayus = fn ($v) => ($v !== null && trim($v) !== '') ? mb_strtoupper(trim($v)) : null;

        return [
            'empresa_id'      => $empresa->id,
            'dependencia_id'  => $d['dependencia_id'],
            'folio'           => $mayus($d['folio']),
            'referencia'      => $mayus($d['referencia'] ?? null),
            'num_remision'    => ! empty($fmt['num_remision']) ? $mayus($d['num_remision'] ?? null) : null,
            'fecha'           => $d['fecha'],
            'entrega_user_id' => ! empty($fmt['responsables']) ? ($d['entrega_user_id'] ?? null) : null,
            'recibe_nombre'   => ! empty($fmt['responsables']) ? $mayus($d['recibe_nombre'] ?? null) : null,
            'recibe_cargo'    => ! empty($fmt['cargo_recibe']) ? $mayus($d['recibe_cargo'] ?? null) : null,
            'observaciones'   => $d['observaciones'] ?? null,
        ];
    }

    /**
     * Crea, actualiza y elimina partidas según lo que viene del formulario.
     */
    private function sincronizarPartidas(Remision $remision, Request $request): void
    {
        $existentes  = $remision->partidas()->get()->keyBy('id');
        $conservadas = [];
        $orden       = 0;

        foreach ($request->input('partidas', []) as $clave => $fila) {
            $orden++;

            $partida = (! empty($fila['id']) && $existentes->has((int) $fila['id']))
                ? $existentes->get((int) $fila['id'])
                : $remision->partidas()->make();

            $partida->fill([
                'orden'       => $orden,
                'numero'      => trim((string) ($fila['numero'] ?? '')) ?: (string) $orden,
                'descripcion' => trim($fila['descripcion']),
                'unidad'      => mb_strtoupper(trim($fila['unidad'])),
                'cantidad'    => $fila['cantidad'],
            ]);

            if (! empty($fila['quitar_imagen']) && $partida->imagen_path) {
                ImagenPartidaService::eliminar($partida->imagen_path);
                $partida->imagen_path = null;
            }

            if ($archivo = $request->file("partidas.{$clave}.imagen")) {
                ImagenPartidaService::eliminar($partida->imagen_path);
                $partida->imagen_path = ImagenPartidaService::guardar($archivo, $remision->id);
            }

            $partida->save();
            $conservadas[] = $partida->id;
        }

        // Las que ya no vienen en el formulario se eliminan con su imagen
        $existentes->except($conservadas)->each(function ($partida) {
            ImagenPartidaService::eliminar($partida->imagen_path);
            $partida->delete();
        });
    }

    private function datosFormulario(Remision $remision, array $partidas): array
    {
        // Si la validación falló, mandan las partidas capturadas
        $old = old('partidas');

        if (is_array($old)) {
            $imagenes = $remision->exists
                ? $remision->partidas->pluck('imagen_url', 'id')
                : collect();

            $partidas = collect($old)->values()->map(fn ($p) => [
                'id'          => $p['id'] ?? null,
                'numero'      => $p['numero'] ?? '',
                'descripcion' => $p['descripcion'] ?? '',
                'unidad'      => $p['unidad'] ?? '',
                'cantidad'    => $p['cantidad'] ?? '',
                'imagen_url'  => (! empty($p['id']) && empty($p['quitar_imagen']))
                    ? $imagenes->get((int) $p['id'])
                    : null,
            ])->all();
        }

        $destinatarios = Destinatario::where('activo', true)
            ->orderBy('dirigido_a')
            ->get(['dependencia_id', 'dirigido_a', 'cargo'])
            ->groupBy('dependencia_id')
            ->map(fn ($grupo) => $grupo->map(fn ($d) => [
                'nombre' => mb_strtoupper($d->dirigido_a),
                'cargo'  => mb_strtoupper((string) $d->cargo),
            ])->values());

        return [
            'remision'      => $remision,
            'partidas'      => $partidas,
            'editando'      => $remision->exists,
            'empresas'      => $this->empresasHabilitadas(),
            'formatos'      => config('logistica.remisiones.empresas'),
            'dependencias'  => Dependencia::where('activo', true)
                                ->orWhere('id', $remision->dependencia_id)
                                ->orderBy('nombre')
                                ->get(['id', 'nombre']),
            'responsables'  => ResponsableService::disponiblesCon($remision->entrega_user_id),
            'unidades'      => $this->unidadesMedida($partidas),
            'destinatarios' => $destinatarios,
        ];
    }

    private function datosPdf(Remision $r): array
    {
        $clave = $r->empresa->clave;
        $f     = $r->formato;
        [$ancho, $alto] = self::CAJA_IMAGEN[$clave] ?? [120, 90];

        return [
            'r'          => $r,
            'f'          => $f,
            'logos'      => PdfLogistica::logosEmpresa($clave),
            'cliente'    => mb_strtoupper($r->dependencia->nombre),
            'entrega'    => mb_strtoupper((string) $r->entrega?->name),
            'fechaLetra' => PdfLogistica::fechaLetra($r->fecha),
            'fechaCorta' => $r->fecha->format('d/m/Y'),
            'dia'        => $r->fecha->format('d'),
            'mes'        => $r->fecha->format('m'),
            'anio'       => $r->fecha->format('Y'),
            'partidas'   => $r->partidas->map(fn ($p) => [
                'numero'      => $p->numero,
                'descripcion' => $p->descripcion,
                'unidad'      => $p->unidad,
                'cantidad'    => PdfLogistica::cantidad($p->cantidad),
                'imagen'      => ! empty($f['imagenes'])
                    ? PdfLogistica::imagenPartida($p->imagen_path, $ancho, $alto)
                    : null,
            ]),
        ];
    }

    private function empresasHabilitadas(): Collection
    {
        return Empresa::where('activo', true)
            ->whereIn('clave', array_keys(config('logistica.remisiones.empresas', [])))
            ->orderBy('nombre')
            ->get(['id', 'clave', 'nombre']);
    }

    /**
     * Nombres del catálogo de unidades de medida en mayúsculas, más cualquier
     * unidad histórica que traigan las partidas.
     */
    private function unidadesMedida(array $partidas = []): array
    {
        return UnidadMedida::where('activo', true)
            ->orderBy('nombre')
            ->pluck('nombre')
            ->map(fn ($nombre) => mb_strtoupper($nombre))
            ->merge(collect($partidas)->pluck('unidad')->filter())
            ->unique()
            ->values()
            ->all();
    }
}