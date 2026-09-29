<?php

namespace App\Http\Controllers\Adquisiciones\Logistica;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\SolicitudRecurso;
use App\Models\SolicitudRecursoConcepto;
use App\Models\Vehiculo;
use App\Services\Logistica\PdfLogistica;
use App\Services\Logistica\ResponsableService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SolicitudCombustibleController extends Controller
{
    private const TIPO = SolicitudRecurso::TIPO_COMBUSTIBLE;

    private const FIRMANTES = ['reviso1', 'reviso2', 'autorizo'];

    public function index(Request $request)
    {
        $query = SolicitudRecurso::tipo(self::TIPO)
            ->with(['destinatario', 'elaboro', 'conceptos.vehiculo'])
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($w) => $w->where('folio', 'like', "%{$q}%")
                                       ->orWhere('observaciones', 'like', "%{$q}%"));
        }

        if ($request->filled('vehiculo_id')) {
            $query->whereHas('conceptos', fn ($c) => $c->where('vehiculo_id', $request->vehiculo_id));
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha', '<=', $request->hasta);
        }

        $solicitudes = $query->paginate(20)->withQueryString();

        // Resumen del mes por unidad
        $gastoPorUnidad = SolicitudRecursoConcepto::query()
            ->join('solicitudes_recurso as s', 's.id', '=', 'solicitud_recurso_conceptos.solicitud_id')
            ->where('s.tipo', self::TIPO)
            ->whereNull('s.deleted_at')
            ->whereBetween('s.fecha', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->groupBy('solicitud_recurso_conceptos.vehiculo_id')
            ->selectRaw('solicitud_recurso_conceptos.vehiculo_id, SUM(solicitud_recurso_conceptos.total) as total')
            ->pluck('total', 'vehiculo_id');

        return view('adquisiciones.logistica.combustible.index', [
            'solicitudes'    => $solicitudes,
            'vehiculos'      => Vehiculo::orderBy('nombre')->get(['id', 'nombre', 'color_etiqueta', 'activo']),
            'gastoPorUnidad' => $gastoPorUnidad,
            'totalMes'       => (float) $gastoPorUnidad->sum(),
        ]);
    }

    public function create()
    {
        $cfg = config('logistica.combustible');
        $yo  = ResponsableService::disponibles()->firstWhere('id', auth()->id());

        $solicitud = new SolicitudRecurso([
            'fecha'                => today(),
            'cliente'              => $cfg['cliente'],
            'cotizacion'           => $cfg['cotizacion'],
            'forma_pago'           => $cfg['forma_pago'],
            'banco'                => $cfg['banco'],
            'cuenta'               => $cfg['cuenta'],
            'destinatario_user_id' => $yo?->id,
            'elaboro_user_id'      => $yo?->id,
            'elaboro_cargo'        => $yo ? ResponsableService::cargoDe($yo) : null,
            'reviso1_nombre'       => $cfg['firmantes']['reviso_1']['nombre'],
            'reviso1_cargo'        => $cfg['firmantes']['reviso_1']['cargo'],
            'reviso2_nombre'       => $cfg['firmantes']['reviso_2']['nombre'],
            'reviso2_cargo'        => $cfg['firmantes']['reviso_2']['cargo'],
            'autorizo_nombre'      => $cfg['firmantes']['autorizo']['nombre'],
            'autorizo_cargo'       => $cfg['firmantes']['autorizo']['cargo'],
        ]);

        // Una fila por monto sugerido, asignando las unidades activas en orden
        $unidades  = Vehiculo::activos()->orderBy('nombre')->pluck('id')->values();
        $conceptos = collect($cfg['montos_sugeridos'])->values()->map(fn ($monto, $i) => [
            'id'          => null,
            'vehiculo_id' => $unidades[$i] ?? null,
            'total'       => $monto,
        ])->all();

        return view('adquisiciones.logistica.combustible.form', $this->datosFormulario($solicitud, $conceptos));
    }

    public function store(Request $request)
    {
        $datos   = $this->validar($request);
        $cfg     = config('logistica.combustible');
        $empresa = Empresa::where('clave', $cfg['empresa_clave'])->first();

        if (! $empresa) {
            return back()->withInput()->with('error', "No existe la empresa con clave {$cfg['empresa_clave']} en el catálogo de Empresas.");
        }

        $solicitud = DB::transaction(function () use ($datos, $cfg, $empresa) {
            $solicitud = SolicitudRecurso::create($this->campos($datos) + [
                'tipo'       => self::TIPO,
                'folio'      => SolicitudRecurso::siguienteFolio($cfg['prefijo_folio'], $datos['fecha']),
                'empresa_id' => $empresa->id,
                'cliente'    => $cfg['cliente'],
                'cotizacion' => $cfg['cotizacion'],
                'forma_pago' => $cfg['forma_pago'],
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->sincronizarConceptos($solicitud, $datos['conceptos']);

            return $solicitud;
        });

        return redirect()
            ->route('adquisiciones.logistica.combustible.show', $solicitud)
            ->with('success', "Solicitud {$solicitud->folio} guardada.");
    }

    public function show(SolicitudRecurso $solicitud)
    {
        $this->asegurarTipo($solicitud);
        $solicitud->load(['empresa', 'destinatario', 'elaboro', 'creador', 'conceptos.vehiculo']);

        return view('adquisiciones.logistica.combustible.show', compact('solicitud'));
    }

    public function edit(SolicitudRecurso $solicitud)
    {
        $this->asegurarTipo($solicitud);
        $solicitud->load(['empresa', 'conceptos']);

        $conceptos = $solicitud->conceptos->map(fn ($c) => [
            'id'          => $c->id,
            'vehiculo_id' => $c->vehiculo_id,
            'total'       => (float) $c->total,
        ])->all();

        return view('adquisiciones.logistica.combustible.form', $this->datosFormulario($solicitud, $conceptos));
    }

    public function update(Request $request, SolicitudRecurso $solicitud)
    {
        $this->asegurarTipo($solicitud);
        $datos = $this->validar($request, $solicitud);

        DB::transaction(function () use ($datos, $solicitud) {
            // Folio, empresa, cliente y cotización se conservan tal como se emitieron
            $solicitud->update($this->campos($datos) + ['updated_by' => auth()->id()]);
            $this->sincronizarConceptos($solicitud, $datos['conceptos']);
        });

        return redirect()
            ->route('adquisiciones.logistica.combustible.show', $solicitud)
            ->with('success', "Solicitud {$solicitud->folio} actualizada.");
    }

    public function destroy(SolicitudRecurso $solicitud)
    {
        $this->asegurarTipo($solicitud);
        $folio = $solicitud->folio;
        $solicitud->delete();

        return redirect()
            ->route('adquisiciones.logistica.combustible.index')
            ->with('success', "Solicitud {$folio} eliminada.");
    }

    public function pdf(Request $request, SolicitudRecurso $solicitud)
    {
        $this->asegurarTipo($solicitud);
        $solicitud->load(['empresa', 'destinatario', 'elaboro', 'conceptos.vehiculo']);

        $pdf = Pdf::loadView('adquisiciones.logistica.combustible.pdf', [
            's'            => $solicitud,
            'logo'         => PdfLogistica::logosEmpresa('GRUPO')['logo'] ?? null,
            'empresa'      => mb_strtoupper($solicitud->empresa->nombre),
            'fechaLetra'   => PdfLogistica::fechaLetra($solicitud->fecha),
            'destinatario' => mb_strtoupper((string) $solicitud->destinatario?->name),
            'elaboro'      => mb_strtoupper((string) $solicitud->elaboro?->name),
        ])->setPaper('letter', 'landscape');

        $archivo = $solicitud->folio . '.pdf';

        return $request->boolean('descargar') ? $pdf->download($archivo) : $pdf->stream($archivo);
    }

    // ─────────────────────────────────────────────────────────────────────

    private function asegurarTipo(SolicitudRecurso $solicitud): void
    {
        abort_unless($solicitud->tipo === self::TIPO, 404);
    }

    private function validar(Request $request, ?SolicitudRecurso $solicitud = null): array
    {
        $idsValidos = ResponsableService::idsDisponibles(
            $solicitud?->destinatario_user_id,
            $solicitud?->elaboro_user_id
        );

        $reglasFirmantes = [];
        foreach (self::FIRMANTES as $f) {
            $reglasFirmantes["{$f}_nombre"] = ['nullable', 'string', 'max:120'];
            $reglasFirmantes["{$f}_cargo"]  = ['nullable', 'string', 'max:120'];
        }

        return $request->validate([
            'fecha'                   => ['required', 'date'],
            'destinatario_user_id'    => ['required', Rule::in($idsValidos)],
            'banco'                   => ['nullable', 'string', 'max:80'],
            'cuenta'                  => ['nullable', 'string', 'max:40'],
            'clabe'                   => ['nullable', 'digits:18'],
            'observaciones'           => ['nullable', 'string', 'max:255'],
            'elaboro_user_id'         => ['required', Rule::in($idsValidos)],
            'elaboro_cargo'           => ['nullable', 'string', 'max:120'],
            'conceptos'               => ['required', 'array', 'min:1'],
            'conceptos.*.id'          => ['nullable', 'integer'],
            'conceptos.*.vehiculo_id' => ['required', Rule::exists('vehiculos', 'id')->whereNull('deleted_at')],
            'conceptos.*.total'       => ['required', 'numeric', 'gt:0', 'max:9999999999'],
        ] + $reglasFirmantes, [
            'fecha.required'                   => 'La fecha es obligatoria.',
            'destinatario_user_id.required'    => 'Selecciona a quién se destina el recurso.',
            'destinatario_user_id.in'          => 'El destinatario no pertenece al departamento.',
            'elaboro_user_id.required'         => 'Selecciona quién elabora la solicitud.',
            'elaboro_user_id.in'               => 'Quien elabora no pertenece al departamento.',
            'clabe.digits'                     => 'La CLABE debe tener 18 dígitos.',
            'conceptos.required'               => 'Agrega al menos una carga.',
            'conceptos.*.vehiculo_id.required' => 'Cada carga necesita una unidad.',
            'conceptos.*.vehiculo_id.exists'   => 'Una de las unidades ya no existe.',
            'conceptos.*.total.required'       => 'Cada carga necesita un monto.',
            'conceptos.*.total.gt'             => 'Los montos deben ser mayores a cero.',
        ]);
    }

    private function campos(array $d): array
    {
        $mayus = fn ($v) => ($v !== null && trim($v) !== '') ? mb_strtoupper(trim($v)) : null;

        $campos = [
            'fecha'                => $d['fecha'],
            'destinatario_user_id' => $d['destinatario_user_id'],
            'banco'                => $mayus($d['banco'] ?? null),
            'cuenta'               => $mayus($d['cuenta'] ?? null),
            'clabe'                => $d['clabe'] ?? null,
            'observaciones'        => $mayus($d['observaciones'] ?? null),
            'elaboro_user_id'      => $d['elaboro_user_id'],
            'elaboro_cargo'        => $mayus($d['elaboro_cargo'] ?? null),
        ];

        foreach (self::FIRMANTES as $f) {
            $campos["{$f}_nombre"] = $mayus($d["{$f}_nombre"] ?? null);
            $campos["{$f}_cargo"]  = $mayus($d["{$f}_cargo"] ?? null);
        }

        return $campos;
    }

    private function sincronizarConceptos(SolicitudRecurso $solicitud, array $filas): void
    {
        $existentes  = $solicitud->conceptos()->get()->keyBy('id');
        $conservados = [];
        $formaPago   = config('logistica.combustible.forma_pago');
        $orden       = 0;

        foreach ($filas as $fila) {
            $orden++;

            $concepto = (! empty($fila['id']) && $existentes->has((int) $fila['id']))
                ? $existentes->get((int) $fila['id'])
                : $solicitud->conceptos()->make();

            $concepto->fill([
                'orden'       => $orden,
                'descripcion' => 'COMBUSTIBLE',
                'vehiculo_id' => $fila['vehiculo_id'],
                'forma_pago'  => $formaPago,
                'total'       => $fila['total'],
            ])->save();

            $conservados[] = $concepto->id;
        }

        $existentes->except($conservados)->each->delete();

        $solicitud->update(['monto' => $solicitud->conceptos()->sum('total')]);
    }

    private function datosFormulario(SolicitudRecurso $solicitud, array $conceptos): array
    {
        // Si la validación falló, mandan las cargas capturadas
        $old = old('conceptos');

        if (is_array($old)) {
            $conceptos = collect($old)->values()->map(fn ($c) => [
                'id'          => $c['id'] ?? null,
                'vehiculo_id' => $c['vehiculo_id'] ?? null,
                'total'       => $c['total'] ?? '',
            ])->all();
        }

        $enUso = collect($conceptos)->pluck('vehiculo_id')->filter()->map(fn ($id) => (int) $id)->all();

        $vehiculos = Vehiculo::where(fn ($q) => $q->where('activo', true)->orWhereIn('id', $enUso))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'combustible'])
            ->map(fn ($v) => ['id' => $v->id, 'nombre' => $v->nombre . ' · ' . $v->combustible_label])
            ->values();

        return [
            'solicitud'    => $solicitud,
            'conceptos'    => $conceptos,
            'editando'     => $solicitud->exists,
            'vehiculos'    => $vehiculos,
            'responsables' => ResponsableService::disponiblesCon($solicitud->destinatario_user_id, $solicitud->elaboro_user_id),
            'empresa'      => $solicitud->exists
                ? $solicitud->empresa
                : Empresa::where('clave', config('logistica.combustible.empresa_clave'))->first(),
        ];
    }
}