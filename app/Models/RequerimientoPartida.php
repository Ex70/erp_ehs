<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RequerimientoPartida extends Model
{
    protected $table    = 'requerimiento_partidas';
    protected $fillable = [
        'requerimiento_id', 'descripcion', 'cantidad',
        'unidad_medida_id', 'precio_proveedor', 'precio_cliente', 'notas',
    ];

    public function requerimiento()
    {
        return $this->belongsTo(Requerimiento::class);
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    public function getSubtotalClienteAttribute(): float
    {
        return ($this->precio_cliente ?? 0) * $this->cantidad;
    }
}