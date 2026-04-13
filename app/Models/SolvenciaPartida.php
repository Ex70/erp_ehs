<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolvenciaPartida extends Model
{
    protected $table    = 'solvencia_partidas';
    protected $fillable = [
        'solvencia_id', 'proveedor_solvencia_id', 'cuenta_bancaria_id',
        'numero', 'descripcion', 'cantidad', 'importe', 'concepto',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'importe'  => 'decimal:2',
    ];

    public function solvencia()
    {
        return $this->belongsTo(Solvencia::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(ProveedorSolvencia::class, 'proveedor_solvencia_id');
    }

    public function cuentaBancaria()
    {
        return $this->belongsTo(ProveedorCuentaBancaria::class, 'cuenta_bancaria_id');
    }
}