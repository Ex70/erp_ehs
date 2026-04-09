<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Destinatario extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'dirigido_a',
        'cargo',
        'dependencia_id',
        'atencion_a',
        'lugar',
        'correo',
        'telefono',
        'telefono_secundario',
        'direccion',
        'observaciones',
        'activo',
    ];

    protected $casts = ['activo' => 'boolean'];

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class);
    }
}