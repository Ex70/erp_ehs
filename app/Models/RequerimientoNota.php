<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RequerimientoNota extends Model
{
    protected $table    = 'requerimiento_notas';
    protected $fillable = ['requerimiento_id', 'user_id', 'texto'];

    public function requerimiento()
    {
        return $this->belongsTo(Requerimiento::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}