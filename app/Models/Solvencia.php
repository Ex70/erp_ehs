<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Solvencia extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'folio', 'empresa_solvencia_id', 'user_id',
        'fecha', 'numero_cotizacion', 'cliente', 'departamento',
        'subtotal', 'iva', 'total', 'monto_solicitado', 'monto_autorizado',
        'elaboro_nombre', 'elaboro_cargo',
        'valido_nombre', 'valido_cargo',
        'autorizo_nombre', 'autorizo_cargo',
        'estatus', 'observaciones',
    ];

    protected $casts = [
        'fecha'            => 'date',
        'subtotal'         => 'decimal:2',
        'iva'              => 'decimal:2',
        'total'            => 'decimal:2',
        'monto_solicitado' => 'decimal:2',
        'monto_autorizado' => 'decimal:2',
    ];

    public function empresa()
    {
        return $this->belongsTo(EmpresaSolvencia::class, 'empresa_solvencia_id');
    }

    public function elaborador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function partidas()
    {
        return $this->hasMany(SolvenciaPartida::class)->orderBy('numero');
    }

    public static function generarFolio(): string
    {
        $ultimo = static::withTrashed()->orderByDesc('id')->value('folio');
        if (!$ultimo) return 'SIST-001';
        $num = (int) substr($ultimo, 5);
        return 'SIST-' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }

    public static function estatuses(): array
    {
        return [
            'borrador'  => 'Borrador',
            'pendiente' => 'Pendiente',
            'aprobada'  => 'Aprobada',
            'rechazada' => 'Rechazada',
            'pagada'    => 'Pagada',
        ];
    }

    public function recalcularTotales(): void
    {
        $subtotal = $this->partidas()->sum('importe');
        $iva      = round($subtotal * 0.16, 2);
        $total    = round($subtotal + $iva, 2);

        $this->update([
            'subtotal'         => $subtotal,
            'iva'              => $iva,
            'total'            => $total,
            'monto_solicitado' => $total,
            'monto_autorizado' => $total,
        ]);
    }
}