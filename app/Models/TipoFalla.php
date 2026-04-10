<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TipoFalla extends Model
{
    protected $table    = 'tipos_falla';
    protected $fillable = ['nombre', 'color', 'activo'];
    protected $casts    = ['activo' => 'boolean'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}