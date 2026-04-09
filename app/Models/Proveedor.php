<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'rfc',
        'giro',
        'ciudad',
        'correo',
        'telefono',
        'telefono_secundario',
        'condiciones_pago',
        'tiempo_entrega',
        'direccion',
        'observaciones',
        'activo',
    ];

    protected $casts = ['activo' => 'boolean'];

    public function requerimientoProveedores()
    {
        return $this->hasMany(RequerimientoProveedor::class);
    }

    // Conteo de requerimientos en los que ha participado
    public function getTotalRequerimientosAttribute(): int
    {
        return $this->requerimientoProveedores()->count();
    }

    // Conteo de veces que fue proveedor ganador
    public function getTotalGanadosAttribute(): int
    {
        return $this->requerimientoProveedores()
                    ->where('ganador', true)
                    ->count();
    }
}