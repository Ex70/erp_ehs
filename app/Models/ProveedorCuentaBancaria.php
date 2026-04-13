<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedorCuentaBancaria extends Model
{
    protected $table    = 'proveedor_cuentas_bancarias';
    protected $fillable = [
        'proveedor_id', 'banco', 'clabe',
        'cuenta', 'referencia', 'tiempo_entrega', 'principal',
    ];
    protected $casts = ['principal' => 'boolean'];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
}