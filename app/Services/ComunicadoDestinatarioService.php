<?php

namespace App\Services;

use App\Models\Comunicado;
use App\Models\User;
use Illuminate\Support\Collection;

class ComunicadoDestinatarioService
{
    /**
     * Resuelve los destinatarios de un comunicado ya persistido.
     */
    public function resolver(Comunicado $comunicado): Collection
    {
        return $this->resolverPorCriterios(
            $comunicado->esSegmentado(),
            $comunicado->departamentos()->pluck('departamentos.id')->all(),
            $comunicado->usuariosIncluidos()->pluck('users.id')->all(),
            $comunicado->usuariosExcluidos()->pluck('users.id')->all(),
            $comunicado->user_id
        );
    }

    /**
     * Motor de resolución. Reglas:
     *  - segmentado = false -> todos los usuarios activos
     *  - segmentado = true  -> usuarios de los departamentos elegidos
     *                          UNION usuarios individuales incluidos
     *  - Siempre se restan los excluidos (aplica también en alcance "todos")
     *  - Nunca se notifica al autor del comunicado
     */
    public function resolverPorCriterios(
        bool $segmentado,
        array $departamentos = [],
        array $incluidos = [],
        array $excluidos = [],
        ?int $autorId = null
    ): Collection {
        $departamentos = array_filter(array_map('intval', $departamentos));
        $incluidos     = array_filter(array_map('intval', $incluidos));
        $excluidos     = array_filter(array_map('intval', $excluidos));

        if ($segmentado && empty($departamentos) && empty($incluidos)) {
            return collect();
        }

        $query = User::query()
            ->where('activo', 1)
            ->whereNotNull('email')
            ->where('email', '!=', '');

        if ($segmentado) {
            $query->where(function ($q) use ($departamentos, $incluidos) {
                $q->whereIn('departamento_id', $departamentos)
                  ->orWhereIn('id', $incluidos);
            });
        }

        if (! empty($excluidos)) {
            $query->whereNotIn('id', $excluidos);
        }

        if ($autorId) {
            $query->where('id', '!=', $autorId);
        }

        return $query->get();
    }

    /**
     * Etiqueta legible del alcance, para el modal de lectura.
     */
    public function etiqueta(Comunicado $comunicado): string
    {
        $nExc = $comunicado->usuariosExcluidos()->count();

        if (! $comunicado->esSegmentado()) {
            return $nExc
                ? 'Toda la organización (−' . $nExc . ')'
                : 'Toda la organización';
        }

        $partes = [];

        $deptos = $comunicado->departamentos()->pluck('departamentos.nombre')->all();
        if ($deptos) {
            $partes[] = implode(', ', $deptos);
        }

        $nInd = $comunicado->usuariosIncluidos()->count();
        if ($nInd) {
            $partes[] = $nInd . ' usuario' . ($nInd > 1 ? 's' : '');
        }

        $texto = $partes ? implode(' + ', $partes) : 'Sin destinatarios';

        return $nExc ? $texto . ' (−' . $nExc . ')' : $texto;
    }
}