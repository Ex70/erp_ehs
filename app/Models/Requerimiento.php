<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Requerimiento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'folio', 'cliente_id', 'empresa_emisora_id', 'empresa_realiza',
        'analista_id', 'tipo', 'linea_negocio',
        'fecha_solicitud', 'fecha_entrega', 'fecha_max_entrega_aut',
        'margen', 'indirectos', 'monto_estimado',
        'monto_autorizado', 'costo_proveedor',
        'status', 'autorizado', 'observaciones',
    ];

    protected $casts = [
        'fecha_solicitud'       => 'date',
        'fecha_entrega'         => 'date',
        'fecha_max_entrega_aut' => 'date',
        'autorizado'            => 'boolean',
        'margen'                => 'decimal:2',
        'indirectos'            => 'decimal:2',
        'monto_estimado'        => 'decimal:2',
        'monto_autorizado'      => 'decimal:2',
        'costo_proveedor'       => 'decimal:2',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function empresaEmisora()
    {
        return $this->belongsTo(Empresa::class, 'empresa_emisora_id');
    }

    public function analista()
    {
        return $this->belongsTo(User::class, 'analista_id');
    }

    public function partidas()
    {
        return $this->hasMany(RequerimientoPartida::class);
    }

    public function proveedores()
    {
        return $this->hasMany(RequerimientoProveedor::class);
    }

    public function notas()
    {
        return $this->hasMany(RequerimientoNota::class)->orderBy('created_at');
    }

    // Helpers
    public function getGananciaAttribute(): float
    {
        return ($this->monto_autorizado ?? 0) - ($this->costo_proveedor ?? 0);
    }

    public function getMargenRealAttribute(): float
    {
        if (!$this->monto_autorizado) return 0;
        return round(($this->ganancia / $this->monto_autorizado) * 100, 2);
    }

    public static function generarFolio(string $claveEmpresa): string
    {
        $anio    = now()->format('Y');
        $mes     = now()->format('m');
        $ultimo  = static::withTrashed()
                         ->where('folio', 'like', "{$claveEmpresa}-{$anio}{$mes}-%")
                         ->count();
        $secuencia = str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
        return "{$claveEmpresa}-{$anio}{$mes}-{$secuencia}";
    }

    public static function tipos(): array
    {
        return ['normal' => 'Normal', 'urgente' => 'Urgente', 'critico' => 'Crítico'];
    }

    public static function estatuses(): array
    {
        return [
            'pendiente'  => 'Pendiente',
            'cotizando'  => 'Cotizando',
            'enviado'    => 'Enviado',
            'autorizado' => 'Autorizado',
            'cancelado'  => 'Cancelado',
        ];
    }
}