<?php

namespace App\Http\Controllers\Solvencias\Trazabilidad;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use App\Models\SolvenciaExpediente as Expediente;
use App\Models\SolvenciaFlujo;
use App\Models\SolvenciaMovimiento;
use App\Models\User;
use App\Services\Solvencias\AccionNoPermitida;
use App\Services\Solvencias\ExpedienteService;
use App\Services\Solvencias\FlujoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ExpedienteController extends Controller
{
    public function __construct(private ExpedienteService $servicio)
    {
    }

    /**
     * Tablero de trazabilidad: quién tiene cada solvencia y desde cuándo.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $base = fn () => Expediente::query()->visiblesPara($user);

        $sinAcuse = $base()->activas()->where('acuse', false);

        $kpis = [
            'sin'         => (clone $sinAcuse)->count(),
            'mas_antigua' => (clone $sinAcuse)->min('enviado_en'),
            'pod'         => $base()->activas()->where('acuse', true)->where('estado', '!=', Expediente::BORRADOR)->count(),
            'dev'         => $base()->where('estado', Expediente::DEVUELTA)->count(),
            'aut'         => $base()->where('estado', Expediente::AUTORIZADA)->count(),
            'pag'         => $base()->where('estado', Expediente::PAGADA)->count(),
            'monto'       => (float) $base()->whereNotIn('estado', [Expediente::BORRADOR, Expediente::PAGADA, Expediente::CANCELADA])->sum('monto'),
        ];

        $consulta = $base()->with(['departamento:id,nombre', 'holder:id,name', 'documentos']);

        if ($request->filled('estado') && array_key_exists($request->estado, Expediente::ESTADOS)) {
            $consulta->where('estado', $request->estado);
        }

        if ($request->filled('departamento')) {
            $consulta->where('departamento_id', (int) $request->departamento);
        }

        if ($request->filled('q')) {
            $q = '%' . trim($request->q) . '%';
            $consulta->where(function ($w) use ($q) {
                $w->where('folio', 'like', $q)
                  ->orWhere('proveedor', 'like', $q)
                  ->orWhere('descripcion', 'like', $q)
                  ->orWhereHas('holder', fn ($h) => $h->where('name', 'like', $q));
            });
        }

        $expedientes = $consulta->orderByDesc('updated_at')->paginate(25)->withQueryString();

        $departamentos = SolvenciaFlujo::with('departamento:id,nombre')->get()
            ->pluck('departamento')->filter()->sortBy('nombre');

        return view('solvencias.trazabilidad.tablero', compact('kpis', 'expedientes', 'departamentos'));
    }

    public function create(Request $request)
    {
        $flujos = FlujoService::flujosElaborables($request->user());

        if ($flujos->isEmpty()) {
            return redirect()->route('sol.bandeja')
                ->with('error', 'No tienes asignada la elaboración de solvencias en ningún flujo completo.');
        }

        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->pluck('nombre');
        $token       = (string) Str::uuid();

        return view('solvencias.trazabilidad.create', compact('flujos', 'proveedores', 'token'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas() + [
            'flujo_id' => 'required|integer',
            'token'    => 'required|uuid',
        ], $this->mensajes());

        // Doble clic / reintento del navegador: el mismo token solo crea un expediente
        if (! Cache::add('solvencias:crear:' . $datos['token'], true, now()->addMinutes(10))) {
            return redirect()->route('sol.bandeja')
                ->with('warning', 'Esa solvencia ya se estaba creando; revisa tu bandeja antes de volver a capturarla.');
        }

        try {
            $expediente = $this->servicio->crear($request->user(), $datos);
        } catch (AccionNoPermitida $e) {
            Cache::forget('solvencias:crear:' . $datos['token']);

            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sol.expedientes.show', $expediente)
            ->with('success', "Expediente {$expediente->folio} creado. Carga el soporte para poder enviarlo.");
    }

    public function show(Request $request, Expediente $expediente)
    {
        $user = $request->user();
        abort_unless($expediente->puedeVer($user), 403, 'No participas en el flujo de esta solvencia.');

        $expediente->load(['departamento:id,nombre', 'documentos.user:id,name', 'movimientos']);

        return view('solvencias.trazabilidad.show', [
            'e'         => $expediente,
            'a'         => $this->acciones($expediente, $user),
            'integra'   => SolvenciaMovimiento::verificarCadena($expediente),
            'esPagador' => FlujoService::esPagador($user),
            'pagadores' => FlujoService::nombresPagadores(),
        ]);
    }

    /**
     * Corregir datos (solo en elaboración). Cada cambio queda en bitácora.
     */
    public function update(Request $request, Expediente $expediente)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());

        try {
            $n = $this->servicio->corregirDatos($expediente, $request->user(), $datos);
        } catch (AccionNoPermitida $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('sol.expedientes.show', $expediente)
            ->with('success', $n ? "{$n} cambio(s) registrados en la bitácora." : 'Sin cambios.');
    }

    /* ───────── Helpers ───────── */

    private function reglas(): array
    {
        return [
            'tipo_proveedor' => 'required|in:validado,nuevo',
            'proveedor'      => 'required|string|max:200',
            'monto'          => 'required|numeric|min:0.01|max:999999999999.99',
            'descripcion'    => 'required|string|max:300',
        ];
    }

    private function mensajes(): array
    {
        return [
            'proveedor.required'   => 'Escribe el proveedor.',
            'monto.required'       => 'Captura cuánto se paga.',
            'monto.min'            => 'El monto debe ser mayor a cero.',
            'descripcion.required' => 'Describe de qué se trata.',
        ];
    }

    /**
     * Qué puede hacer la persona sobre el expediente (botones, bloqueos y avisos).
     * Las reglas definitivas se vuelven a validar en ExpedienteService.
     */
    private function acciones(Expediente $e, User $user): array
    {
        $mio      = (int) $e->holder_id === (int) $user->id;
        $faltan   = $e->faltantes();
        $nombresF = collect($faltan)->map(fn ($t) => config("solvencias.documentos.{$t}.nombre", $t))->implode(', ');

        $a = [
            'acusar'     => false,
            'recordar'   => false,
            'enviar'     => false,
            'autorizar'  => false,
            'devolver'   => false,
            'cancelar'   => $this->servicio->puedeCancelar($e, $user),
            'editable'   => $e->editablePor($user),
            'pagar'      => $e->estado === Expediente::AUTORIZADA && FlujoService::esPagador($user),
            'faltan'     => $faltan,
            'candidatos' => [],
            'etiqueta'   => '',
            'bloqueo'    => '',
            'aviso'      => '',
        ];

        if ($e->esFinal()) {
            return $a;
        }

        if ($mio && ! $e->acuse) {
            $a['acusar']  = true;
            $a['bloqueo'] = 'Mientras no acuses de recibido no puedes aprobar, devolver ni modificar esta solvencia.';
        }

        if (! $e->acuse && (int) $e->enviado_por_id === (int) $user->id && ! $mio) {
            $a['recordar'] = true;
        }

        if ($mio && $e->acuse) {
            if ($e->esUltimaEtapa()) {
                $a['autorizar'] = true;
                if ($faltan) {
                    $a['bloqueo'] = 'No se puede autorizar: faltan ' . count($faltan) . " documento(s) del checklist ({$nombresF}).";
                }
            } else {
                $ids = $e->candidatos();
                $a['enviar']     = true;
                $a['candidatos'] = collect($ids)->mapWithKeys(fn ($id) => [(int) $id => $e->nombreDe((int) $id)])->all();

                $destino = $ids ? $e->nombresDe($ids) : '(sin persona asignada)';
                $a['etiqueta'] = match (true) {
                    $e->esDevuelta() => "Reenviar a {$destino}",
                    $e->etapa === 0  => "Enviar a {$destino}",
                    default          => '✓ ' . config('solvencias.pasado.' . $e->verboActual()) . " · enviar a {$destino}",
                };

                if ($faltan) {
                    $a['bloqueo'] = 'Envío bloqueado: faltan ' . count($faltan) . " documento(s) del checklist ({$nombresF}).";
                } elseif (! $ids) {
                    $a['bloqueo'] = 'No hay otra persona asignada a la siguiente etapa. Pide que se ajuste el flujo en «Personas y flujos».';
                }
            }

            $a['devolver'] = $e->etapa > 0;
        }

        $tieneBotones = $a['acusar'] || $a['recordar'] || $a['enviar'] || $a['autorizar'] || $a['devolver'] || $a['cancelar'];
        if (! $tieneBotones) {
            $a['aviso'] = 'Solo ' . $e->nombreDe($e->holder_id) . ' puede actuar sobre esta solvencia en este momento.';
        }

        return $a;
    }
}
