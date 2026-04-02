<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsignacionIp extends Model
{
    use SoftDeletes;

    protected $table = 'asignaciones_ip';

    protected $fillable = [
        'codigo',
        'user_id',
        'dispositivo_id',
        'marca_id',
        'direccion_ip',
        'direccion_mac',
        'modelo',
        'numero_serie',
        'area',
        'fecha_asignacion',
    ];

    protected $casts = [
        'fecha_asignacion' => 'date',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dispositivo()
    {
        return $this->belongsTo(Dispositivo::class);
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    // Helpers
    // El nombre viene del usuario vinculado
    public function getNombreAttribute(): string
    {
        return $this->usuario?->name ?? '—';
    }

    // El puesto viene del usuario vinculado
    public function getPuestoAttribute(): string
    {
        return $this->usuario?->puesto?->nombre ?? '—';
    }

    public static function generarCodigo(): string
    {
        $ultimo = static::withTrashed()->orderByDesc('id')->value('codigo');
        if (!$ultimo) return 'GQZ0001';
        $numero = (int) substr($ultimo, 3);
        return 'GQZ' . str_pad($numero + 1, 4, '0', STR_PAD_LEFT);
    }
}