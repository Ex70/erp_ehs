<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RequerimientoProveedor extends Model
{
    protected $table    = 'requerimiento_proveedores';
    protected $fillable = [
        'requerimiento_id', 'proveedor_id', 'monto',
        'tiempo_entrega', 'costo_envio', 'disponibilidad',
        'url', 'notas', 'ganador',
    ];

    protected $casts = ['ganador' => 'boolean'];

    public function requerimiento()
    {
        return $this->belongsTo(Requerimiento::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
}