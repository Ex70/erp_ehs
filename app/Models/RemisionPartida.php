<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemisionPartida extends Model
{
    protected $table = 'remision_partidas';

    protected $fillable = [
        'remision_id',
        'orden',
        'numero',
        'descripcion',
        'unidad',
        'cantidad',
        'imagen_path',
    ];

    protected $casts = [
        'orden'    => 'integer',
        'cantidad' => 'decimal:2',
    ];

    public function remision()
    {
        return $this->belongsTo(Remision::class);
    }

    public function getImagenUrlAttribute(): ?string
    {
        return $this->imagen_path ? asset('storage/' . $this->imagen_path) : null;
    }
}