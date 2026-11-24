<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Contabilidad y Finanzas: personas que consultan las solvencias autorizadas
 * y registran el pago (comprobante + fecha).
 */
class SolvenciaPagador extends Model
{
    protected $table = 'solvencia_pagadores';

    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
