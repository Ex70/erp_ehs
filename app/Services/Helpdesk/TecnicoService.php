<?php

namespace App\Services\Helpdesk;

use App\Models\Departamento;
use App\Models\User;
use Illuminate\Support\Collection;

class TecnicoService
{
    /**
     * Departamento configurado para atender la Mesa de Ayuda.
     */
    public static function departamento(): ?Departamento
    {
        $clave  = config('helpdesk.departamento_soporte.clave');
        $nombre = config('helpdesk.departamento_soporte.nombre');

        $depto = $clave
            ? Departamento::where('clave', $clave)->first()
            : null;

        return $depto ?? Departamento::where('nombre', $nombre)->first();
    }

    /**
     * Usuarios activos del departamento de soporte, listos para el select.
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
     * IDs válidos para asignación. Usado por la validación del POST.
     */
    public static function idsDisponibles(): array
    {
        return static::disponibles()->pluck('id')->all();
    }
}