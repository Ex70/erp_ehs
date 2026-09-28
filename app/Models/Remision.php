<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Remision extends Model
{
    use SoftDeletes;

    protected $table = 'remisiones';

    protected $fillable = [
        'empresa_id',
        'dependencia_id',
        'folio',
        'referencia',
        'num_remision',
        'fecha',
        'entrega_user_id',
        'recibe_nombre',
        'recibe_cargo',
        'observaciones',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class);
    }

    public function entrega()
    {
        return $this->belongsTo(User::class, 'entrega_user_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function partidas()
    {
        return $this->hasMany(RemisionPartida::class)->orderBy('orden');
    }

    /**
     * Configuración del formato de la empresa (config/logistica.php).
     */
    public function getFormatoAttribute(): array
    {
        return config('logistica.remisiones.empresas.' . ($this->empresa?->clave ?? ''), []);
    }
}