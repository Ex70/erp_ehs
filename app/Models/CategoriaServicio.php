<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CategoriaServicio extends Model
{
    protected $table    = 'categorias_servicio';
    protected $fillable = ['nombre', 'activo'];
    protected $casts    = ['activo' => 'boolean'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}