<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'departamentos';

    protected $fillable = [
        'nombre',
        'clave',
        'descripcion',
        'responsable_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Puestos habilitados en este departamento.
     * Un mismo puesto puede existir en varios departamentos.
     */
    public function puestos()
    {
        return $this->belongsToMany(Puesto::class, 'departamento_puesto')
                    ->withPivot('activo')
                    ->withTimestamps()
                    ->orderBy('puestos.nombre');
    }

    public function puestosActivos()
    {
        return $this->puestos()->wherePivot('activo', true);
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'departamento_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}