<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Flujo de solvencias de un departamento (unidad de negocio).
 * Etapas ordenadas: la primera siempre "Elabora", la última siempre "Autoriza".
 */
class SolvenciaFlujo extends Model
{
    protected $table = 'solvencia_flujos';

    protected $fillable = ['departamento_id', 'prefijo_folio', 'activo', 'por_confirmar'];

    protected $casts = [
        'activo'        => 'boolean',
        'por_confirmar' => 'boolean',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function etapas()
    {
        return $this->hasMany(SolvenciaFlujoEtapa::class, 'flujo_id')->orderBy('orden');
    }

    public function expedientes()
    {
        return $this->hasMany(SolvenciaExpediente::class, 'flujo_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function getNombreAttribute(): string
    {
        return $this->departamento?->nombre ?? 'Departamento #' . $this->departamento_id;
    }

    /**
     * Copia congelada del flujo para un expediente nuevo.
     * Solo se consideran personas activas.
     *
     * @return array<int, array{verbo:string, nombre:string, personas:array<int,int>}>
     */
    public function recorrido(): array
    {
        $this->loadMissing('etapas.personas');

        return $this->etapas->map(fn (SolvenciaFlujoEtapa $etapa) => [
            'verbo'    => $etapa->verbo,
            'nombre'   => config('solvencias.etapa_de.' . $etapa->verbo, $etapa->verbo),
            'personas' => $etapa->personas
                ->where('activo', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
        ])->values()->all();
    }

    /**
     * Un flujo está completo si tiene al menos Elabora + Autoriza y cada
     * etapa tiene al menos una persona activa.
     */
    public function estaCompleto(): bool
    {
        $recorrido = $this->recorrido();

        if (count($recorrido) < 2) {
            return false;
        }

        foreach ($recorrido as $etapa) {
            if (empty($etapa['personas'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Etapas sin personas activas (para avisos en la configuración).
     */
    public function etapasVacias(): array
    {
        return collect($this->recorrido())
            ->filter(fn ($e) => empty($e['personas']))
            ->pluck('verbo')
            ->all();
    }
}
