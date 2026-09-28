<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehiculo extends Model
{
    use SoftDeletes;

    protected $table = 'vehiculos';

    protected $fillable = [
        'nombre',
        'marca',
        'modelo',
        'anio',
        'placas',
        'numero_serie',
        'color',
        'combustible',
        'rendimiento_km_l',
        'km_actual',
        'km_actualizado_at',
        'color_etiqueta',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'anio'              => 'integer',
        'rendimiento_km_l'  => 'decimal:2',
        'km_actual'         => 'integer',
        'km_actualizado_at' => 'datetime',
        'activo'            => 'boolean',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * "CHEVROLET TORNADO 2022" — vacío si no hay datos.
     */
    public function getDescripcionAttribute(): string
    {
        return implode(' ', array_filter([$this->marca, $this->modelo, $this->anio]));
    }

    public function getCombustibleLabelAttribute(): string
    {
        return config("logistica.combustibles.{$this->combustible}", ucfirst((string) $this->combustible));
    }

    /**
     * Actualiza el odómetro solo si el nuevo valor es mayor, para que una
     * captura con error no haga "retroceder" la unidad. Lo usarán
     * mantenimiento y correctivos.
     */
    public function registrarKilometraje(?int $km): void
    {
        if ($km !== null && $km > (int) $this->km_actual) {
            $this->forceFill([
                'km_actual'         => $km,
                'km_actualizado_at' => now(),
            ])->save();
        }
    }
}