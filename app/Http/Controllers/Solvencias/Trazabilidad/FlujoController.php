<?php

namespace App\Http\Controllers\Solvencias\Trazabilidad;

use App\Http\Controllers\Controller;
use App\Models\SolvenciaFlujo;
use App\Models\SolvenciaFlujoEtapa;
use App\Models\SolvenciaFolio;
use App\Models\SolvenciaPagador;
use App\Models\User;
use App\Services\Solvencias\FlujoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Personas y flujos (permiso solvencias_flujo.configurar).
 * Los cambios aplican a solvencias NUEVAS: cada expediente conserva el
 * recorrido con el que nació.
 */
class FlujoController extends Controller
{
    private const MAX_ETAPAS = 6;

    public function index()
    {
        $flujos = SolvenciaFlujo::with(['departamento:id,nombre,clave', 'etapas.personas'])
            ->withCount('expedientes')
            ->get()
            ->sortBy(fn ($f) => $f->nombre)
            ->values();

        $participantes = $flujos->flatMap(fn ($f) => $f->etapas->flatMap->personas)
            ->merge(FlujoService::pagadores())
            ->unique('id')
            ->sortBy('name')
            ->values();

        return view('solvencias.trazabilidad.flujos', [
            'flujos'        => $flujos,
            'sinFlujo'      => FlujoService::departamentosSinFlujo(),
            'usuarios'      => FlujoService::usuariosPorDepartamento(),
            'pagadores'     => FlujoService::pagadores(),
            'participantes' => $participantes,
            'folios'        => SolvenciaFolio::orderByDesc('serie')->get(),
            'anio'          => (int) now(config('solvencias.zona_horaria'))->format('Y'),
        ]);
    }

    /**
     * Habilita el flujo para un departamento con la cadena estándar vacía.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'departamento_id' => ['required', 'integer', 'exists:departamentos,id', Rule::unique('solvencia_flujos', 'departamento_id')],
            'prefijo_folio'   => ['required', 'regex:/^[A-Za-z0-9]{2,10}$/', Rule::unique('solvencia_flujos', 'prefijo_folio')],
        ], [
            'prefijo_folio.regex'      => 'El prefijo lleva de 2 a 10 letras o números, sin espacios.',
            'prefijo_folio.unique'     => 'Ese prefijo ya lo usa otro flujo.',
            'departamento_id.unique'   => 'Ese departamento ya tiene flujo.',
        ]);

        DB::transaction(function () use ($datos) {
            $flujo = SolvenciaFlujo::create([
                'departamento_id' => $datos['departamento_id'],
                'prefijo_folio'   => strtoupper($datos['prefijo_folio']),
                'activo'          => true,
                'por_confirmar'   => true,
            ]);

            foreach (['Elabora', 'Revisa', 'Valida', 'Autoriza'] as $i => $verbo) {
                $flujo->etapas()->create(['orden' => $i, 'verbo' => $verbo]);
            }
        });

        return back()->with('success', 'Flujo habilitado. Asigna una persona a cada etapa y márcalo como confirmado.');
    }

    public function update(Request $request, SolvenciaFlujo $flujo)
    {
        $datos = $request->validate([
            'prefijo_folio' => ['required', 'regex:/^[A-Za-z0-9]{2,10}$/', Rule::unique('solvencia_flujos', 'prefijo_folio')->ignore($flujo->id)],
            'activo'        => 'nullable|boolean',
        ], [
            'prefijo_folio.regex'  => 'El prefijo lleva de 2 a 10 letras o números, sin espacios.',
            'prefijo_folio.unique' => 'Ese prefijo ya lo usa otro flujo.',
        ]);

        $flujo->update([
            'prefijo_folio' => strtoupper($datos['prefijo_folio']),
            'activo'        => $request->boolean('activo'),
        ]);

        return back()->with('success', "Flujo de {$flujo->nombre} actualizado. Los folios ya emitidos no cambian.");
    }

    public function confirmar(SolvenciaFlujo $flujo)
    {
        if ($vacias = $flujo->etapasVacias()) {
            return back()->with('error', "No se puede confirmar {$flujo->nombre}: asigna persona a " . implode(', ', $vacias) . '.');
        }

        $flujo->update(['por_confirmar' => false]);

        return back()->with('success', "Flujo de {$flujo->nombre} confirmado.");
    }

    public function agregarEtapa(SolvenciaFlujo $flujo)
    {
        $etapas = $flujo->etapas()->get();

        if ($etapas->count() >= self::MAX_ETAPAS) {
            return back()->with('error', 'Un flujo admite como máximo ' . self::MAX_ETAPAS . ' etapas.');
        }

        DB::transaction(function () use ($flujo, $etapas) {
            $ultima = $etapas->last();
            $ultima->update(['orden' => $ultima->orden + 1]);
            $flujo->etapas()->create(['orden' => $ultima->orden - 1, 'verbo' => 'Revisa']);
        });

        return back()->with('success', 'Etapa agregada antes de autorizar; ajusta la responsabilidad y asigna persona.');
    }

    public function cambiarVerbo(Request $request, SolvenciaFlujoEtapa $etapa)
    {
        $datos = $request->validate(['verbo' => ['required', Rule::in(config('solvencias.verbos_intermedios'))]]);

        if ($etapa->esFija($etapa->flujo->etapas()->count())) {
            return back()->with('error', 'La primera (Elabora) y la última (Autoriza) etapa no cambian.');
        }

        $etapa->update(['verbo' => $datos['verbo']]);

        return back()->with('success', 'Responsabilidad actualizada.');
    }

    public function quitarEtapa(SolvenciaFlujoEtapa $etapa)
    {
        $flujo = $etapa->flujo;

        if ($etapa->esFija($flujo->etapas()->count())) {
            return back()->with('error', 'La primera (Elabora) y la última (Autoriza) etapa no se quitan.');
        }

        DB::transaction(function () use ($etapa, $flujo) {
            $etapa->delete();
            $flujo->etapas()->get()->values()->each(fn ($e, $i) => $e->orden !== $i ? $e->update(['orden' => $i]) : null);
        });

        return back()->with('success', 'Etapa eliminada del flujo.');
    }

    public function agregarPersona(Request $request, SolvenciaFlujoEtapa $etapa)
    {
        $datos = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('activo', true)],
        ], ['user_id.exists' => 'La persona no existe o está inactiva.']);

        $etapa->personas()->syncWithoutDetaching([$datos['user_id']]);

        return back()->with('success', User::find($datos['user_id'])->name . " agregada a «{$etapa->verbo}».");
    }

    public function quitarPersona(SolvenciaFlujoEtapa $etapa, User $user)
    {
        $etapa->personas()->detach($user->id);

        return back()->with('success', "{$user->name} ya no participa en «{$etapa->verbo}».");
    }

    public function agregarPagador(Request $request)
    {
        $datos = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('activo', true)],
        ]);

        SolvenciaPagador::firstOrCreate(['user_id' => $datos['user_id']]);

        return back()->with('success', 'Persona agregada a Contabilidad y Finanzas.');
    }

    public function quitarPagador(User $user)
    {
        if (SolvenciaPagador::count() <= 1) {
            return back()->with('error', 'Debe quedar al menos una persona en Contabilidad y Finanzas.');
        }

        SolvenciaPagador::where('user_id', $user->id)->delete();

        return back()->with('success', "{$user->name} quitada de Contabilidad y Finanzas.");
    }

    /**
     * Fija el último folio usado de una serie (para continuar una numeración
     * previa). Solo puede subir: bajar provocaría folios duplicados.
     */
    public function guardarFolio(Request $request)
    {
        $datos = $request->validate([
            'prefijo' => ['required', Rule::in(SolvenciaFlujo::pluck('prefijo_folio')->all())],
            'anio'    => 'required|integer|min:2020|max:2100',
            'ultimo'  => 'required|integer|min:0|max:999999',
        ], ['prefijo.in' => 'El prefijo debe pertenecer a un flujo.']);

        $serie = SolvenciaFolio::serie($datos['prefijo'], (int) $datos['anio']);

        $resultado = DB::transaction(function () use ($serie, $datos) {
            $folio = SolvenciaFolio::where('serie', $serie)->lockForUpdate()->first()
                ?? new SolvenciaFolio(['serie' => $serie, 'ultimo' => 0]);

            if ((int) $datos['ultimo'] < $folio->ultimo) {
                return "La serie {$serie} ya va en " . str_pad((string) $folio->ultimo, 4, '0', STR_PAD_LEFT) . '; el consecutivo no puede bajar.';
            }

            $folio->ultimo = (int) $datos['ultimo'];
            $folio->save();

            return null;
        });

        return $resultado
            ? back()->with('error', $resultado)
            : back()->with('success', "Serie {$serie}: el siguiente folio será el " . str_pad((string) ($datos['ultimo'] + 1), 4, '0', STR_PAD_LEFT) . '.');
    }
}
