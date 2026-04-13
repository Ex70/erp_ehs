<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedorSolvencia extends Model
{
    protected $table    = 'proveedores_solvencia';
    protected $fillable = [
        'nombre', 'rfc', 'giro', 'contacto',
        'telefono', 'facturacion', 'tiempo_entrega', 'activo',
    ];
    protected $casts = ['activo' => 'boolean'];

    public function cuentasBancarias()
    {
        return $this->hasMany(ProveedorCuentaBancaria::class);
    }

    public function cuentaPrincipal()
    {
        return $this->hasOne(ProveedorCuentaBancaria::class)
                    ->where('principal', true);
    }
}