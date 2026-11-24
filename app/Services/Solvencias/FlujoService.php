<?php

namespace App\Services\Solvencias;

use App\Models\Departamento;
use App\Models\SolvenciaFlujo;
use App\Models\SolvenciaPagador;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Consultas sobre la configuración de flujos y personas.
 * Memoriza por petición: el menú y las vistas lo consultan varias veces.
 */
class FlujoService
{
    private static array $elaborables = [];
    private static ?array $pagadores  = null;

    /**
     * Flujos activos y completos en los que la persona elabora.
     *
     * @return Collection<int, SolvenciaFlujo>
     */
    public static function flujosElaborables(User $user): Collection
    {
        return self::$elaborables[$user->id] ??= SolvenciaFlujo::activos()
            ->with(['departamento', 'etapas.personas'])
            ->get()
            ->filter(function (SolvenciaFlujo $flujo) use ($user) {
                $primera = $flujo->etapas->first();

                return $primera
                    && $primera->personas->contains('id', $user->id)
                    && $flujo->estaCompleto();
            })
            ->sortBy(fn ($f) => $f->nombre)
            ->values();
    }

    public static function puedeElaborar(User $user): bool
    {
        return self::flujosElaborables($user)->isNotEmpty();
    }

    /** @return array<int, int> */
    public static function idsPagadores(): array
    {
        return self::$pagadores ??= SolvenciaPagador::query()
            ->whereHas('user', fn ($q) => $q->where('activo', true))
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public static function esPagador(User|int|null $user): bool
    {
        $id = $user instanceof User ? $user->id : $user;

        return $id && in_array((int) $id, self::idsPagadores(), true);
    }

    /** @return Collection<int, User> */
    public static function pagadores(): Collection
    {
        return User::whereIn('id', self::idsPagadores())->orderBy('name')->get();
    }

    public static function nombresPagadores(): string
    {
        $nombres = self::pagadores()->pluck('name');

        return $nombres->isEmpty() ? 'Contabilidad y Finanzas (sin asignar)' : $nombres->implode(' o ');
    }

    /**
     * Responsabilidades de una persona en todos los flujos activos:
     * "Elabora (Adquisiciones) · Revisa (Taller, Adquisiciones)".
     */
    public static function responsabilidadTexto(int $userId, ?Collection $flujos = null): string
    {
        $flujos ??= SolvenciaFlujo::activos()->with(['departamento', 'etapas.personas'])->get();
        $porVerbo = [];

        foreach ($flujos as $flujo) {
            foreach ($flujo->etapas as $etapa) {
                if ($etapa->personas->contains('id', $userId)) {
                    $porVerbo[$etapa->verbo][] = $flujo->nombre;
                }
            }
        }

        $partes = [];
        foreach (config('solvencias.verbos') as $verbo) {
            if (! empty($porVerbo[$verbo])) {
                $partes[] = $verbo . ' (' . implode(', ', array_unique($porVerbo[$verbo])) . ')';
            }
        }

        if (self::esPagador($userId)) {
            $partes[] = 'Registra pagos (Contabilidad y Finanzas)';
        }

        return $partes ? implode(' · ', $partes) : 'Sin responsabilidad asignada en solvencias';
    }

    /**
     * Usuarios activos agrupados por departamento, para los selects de personas.
     *
     * @return Collection<string, Collection<int, User>>
     */
    public static function usuariosPorDepartamento(array $excluir = []): Collection
    {
        return User::query()
            ->where('activo', true)
            ->whereNotIn('id', $excluir)
            ->with('departamento:id,nombre')
            ->orderBy('name')
            ->get(['id', 'name', 'departamento_id'])
            ->groupBy(fn (User $u) => $u->departamento?->nombre ?? 'Sin departamento')
            ->sortKeys();
    }

    /**
     * Departamentos activos que aún no tienen flujo.
     */
    public static function departamentosSinFlujo(): Collection
    {
        return Departamento::activos()
            ->whereNotIn('id', SolvenciaFlujo::pluck('departamento_id'))
            ->orderBy('nombre')
            ->get();
    }

    public static function limpiarCache(): void
    {
        self::$elaborables = [];
        self::$pagadores   = null;
    }
}
