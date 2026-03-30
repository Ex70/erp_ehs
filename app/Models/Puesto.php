<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puesto extends Model{
    protected $fillable = ['nombre', 'descripcion', 'activo'];

    public function users(){
        return $this->hasMany(User::class);
    }
}
