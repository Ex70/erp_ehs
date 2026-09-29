<?php

namespace App\Services\Logistica;

use App\Models\Departamento;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Colaboradores del departamento que opera Logística y Entregas.
 * Mismo patrón que App\Services\Helpdesk\TecnicoService.
 */
class ResponsableService
{
    /**
     * Departamento configurado en config('logistica.departamento_responsables').
     */
    public static function departamento(): ?Departamento
    {
        $clave  = config('logistica.departamento_responsables.clave');
        $nombre = config('logistica.departamento_responsables.nombre');

        $depto = $clave
            ? Departamento::where('clave', $clave)->first()
            : null;

        return $depto ?? Departamento::where('nombre', $nombre)->first();
    }

    /**
     * Usuarios activos del departamento, listos para un <select>.
     */
    public static function disponibles(): Collection
    {
        $depto = static::departamento();

        if (! $depto) {
            return collect();
        }

        return User::query()
            ->where('departamento_id', $depto->id)
            ->where('activo', true)
            ->with('puesto')
            ->orderBy('name')
            ->get();
    }

    /**
     * Igual que disponibles(), pero garantiza que los usuarios ya guardados en
     * un registro sigan en la lista aunque hoy estén inactivos o en otro
     * departamento. Se usa en los formularios de edición.
     */
    public static function disponiblesCon(?int ...$userIds): Collection
    {
        $lista = static::disponibles();

        $faltantes = collect($userIds)
            ->filter()
            ->unique()
            ->reject(fn ($id) => $lista->contains('id', $id));

        if ($faltantes->isNotEmpty()) {
            $lista = $lista
                ->merge(User::with('puesto')->whereIn('id', $faltantes)->get())
                ->sortBy('name')
                ->values();
        }

        return $lista;
    }

    /**
     * IDs válidos para Rule::in() en la validación.
     */
    public static function idsDisponibles(?int ...$incluir): array
    {
        return static::disponiblesCon(...$incluir)->pluck('id')->all();
    }

    /**
     * Cargo que se imprime en firmas: el nombre del puesto en mayúsculas.
     */
    public static function cargoDe(?User $user): string
    {
        return mb_strtoupper($user?->puesto?->nombre ?? '');
    }
}