<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dependencia extends Model
{
    protected $fillable = ['nombre', 'activo'];
    protected $casts    = ['activo' => 'boolean'];

    public function destinatarios()
    {
        return $this->hasMany(Destinatario::class);
    }
}