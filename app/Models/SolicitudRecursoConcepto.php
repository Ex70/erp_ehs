<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudRecursoConcepto extends Model
{
    protected $table = 'solicitud_recurso_conceptos';

    protected $fillable = [
        'solicitud_id',
        'orden',
        'descripcion',
        'vehiculo_id',
        'forma_pago',
        'total',
    ];

    protected $casts = [
        'orden' => 'integer',
        'total' => 'decimal:2',
    ];

    public function solicitud()
    {
        return $this->belongsTo(SolicitudRecurso::class, 'solicitud_id');
    }

    /**
     * Incluye unidades eliminadas para que el historial siga mostrando su nombre.
     */
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class)->withTrashed();
    }
}