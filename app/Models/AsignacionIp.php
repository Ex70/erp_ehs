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
        'nombre',
        'direccion_ip',
        'direccion_mac',
        'dispositivo',
        'marca',
        'modelo',
        'numero_serie',
        'area',
        'puesto',
        'fecha_asignacion',
    ];

    protected $casts = [
        'fecha_asignacion' => 'date',
    ];

    // Relación opcional con el usuario del sistema
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Generar código automático tipo GQZ0001
    public static function generarCodigo(): string
    {
        $ultimo = static::withTrashed()
                        ->orderByDesc('id')
                        ->value('codigo');

        if (!$ultimo) {
            return 'GQZ0001';
        }

        $numero = (int) substr($ultimo, 3);
        return 'GQZ' . str_pad($numero + 1, 4, '0', STR_PAD_LEFT);
    }

    // Tipos de dispositivo disponibles
    public static function tiposDispositivo(): array
    {
        return [
            'Laptop', 'Desktop', 'Impresora',
            'Servidor', 'Switch', 'Router',
            'Tablet', 'Otro',
        ];
    }
}