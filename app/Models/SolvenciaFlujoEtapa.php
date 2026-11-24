<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolvenciaFlujoEtapa extends Model
{
    protected $table = 'solvencia_flujo_etapas';

    protected $fillable = ['flujo_id', 'orden', 'verbo'];

    protected $casts = ['orden' => 'integer'];

    public function flujo()
    {
        return $this->belongsTo(SolvenciaFlujo::class, 'flujo_id');
    }

    /**
     * Personas (usuarios del ERP) que pueden atender esta etapa.
     * Si hay más de una, quien envía elige a cuál se la entrega.
     */
    public function personas()
    {
        return $this->belongsToMany(User::class, 'solvencia_flujo_etapa_user', 'etapa_id', 'user_id')
                    ->withTimestamps()
                    ->orderBy('users.name');
    }

    public function getNombreAttribute(): string
    {
        return config('solvencias.etapa_de.' . $this->verbo, $this->verbo);
    }

    public function esFija(int $totalEtapas): bool
    {
        return $this->orden === 0 || $this->orden === $totalEtapas - 1;
    }
}
