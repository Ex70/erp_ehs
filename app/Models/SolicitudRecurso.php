<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class SolicitudRecurso extends Model
{
    use SoftDeletes;

    public const TIPO_COMBUSTIBLE = 'combustible';

    protected $table = 'solicitudes_recurso';

    protected $fillable = [
        'tipo',
        'folio',
        'empresa_id',
        'cliente',
        'cotizacion',
        'fecha',
        'monto',
        'destinatario_user_id',
        'banco',
        'cuenta',
        'clabe',
        'forma_pago',
        'observaciones',
        'elaboro_user_id',
        'elaboro_cargo',
        'reviso1_nombre',
        'reviso1_cargo',
        'reviso2_nombre',
        'reviso2_cargo',
        'autorizo_nombre',
        'autorizo_cargo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function destinatario()
    {
        return $this->belongsTo(User::class, 'destinatario_user_id');
    }

    public function elaboro()
    {
        return $this->belongsTo(User::class, 'elaboro_user_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function conceptos()
    {
        return $this->hasMany(SolicitudRecursoConcepto::class, 'solicitud_id')->orderBy('orden');
    }

    /**
     * Siguiente folio con el prefijo del tipo: SOL-COMB-2026-0001.
     * Incluye eliminadas para no reutilizar folios.
     */
    public static function siguienteFolio(string $prefijo, $fecha): string
    {
        $base = $prefijo . '-' . Carbon::parse($fecha)->year . '-';

        $max = static::withTrashed()
            ->where('folio', 'like', addcslashes($base, '\\%_') . '%')
            ->pluck('folio')
            ->map(fn ($folio) => (int) substr($folio, strlen($base)))
            ->max() ?? 0;

        return $base . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }
}