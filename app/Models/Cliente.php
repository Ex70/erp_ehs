<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = ['nombre', 'contacto', 'correo', 'telefono', 'activo'];
    protected $casts    = ['activo' => 'boolean'];

    public function requerimientos()
    {
        return $this->hasMany(Requerimiento::class);
    }
}