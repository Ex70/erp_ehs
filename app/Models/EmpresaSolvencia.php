<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaSolvencia extends Model
{
    protected $table    = 'empresas_solvencia';
    protected $fillable = ['nombre', 'rfc', 'activo'];
    protected $casts    = ['activo' => 'boolean'];

    public function solvencias()
    {
        return $this->hasMany(Solvencia::class);
    }
}