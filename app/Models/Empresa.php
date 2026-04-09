<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $fillable = ['clave', 'nombre', 'rfc', 'activo'];
    protected $casts    = ['activo' => 'boolean'];

    public function requerimientos()
    {
        return $this->hasMany(Requerimiento::class, 'empresa_emisora_id');
    }
}