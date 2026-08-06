<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puesto extends Model{
    protected $fillable = ['nombre', 'descripcion', 'activo'];

    public function users(){
        return $this->hasMany(User::class);
    }

    public function departamentos()
    {
        return $this->belongsToMany(Departamento::class, 'departamento_puesto')
                    ->withPivot('activo')
                    ->withTimestamps();
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'puesto_id');
    }
}
