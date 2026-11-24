<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Bitácora inmutable del expediente (solo se agregan registros).
 * Cada movimiento guarda una huella sha256 que incluye la huella anterior.
 */
class SolvenciaMovimiento extends Model
{
    protected $table = 'solvencia_movimientos';

    public $timestamps = false;

    public const HUELLA_INICIAL = '0000000000000000000000000000000000000000000000000000000000000000';

    protected $fillable = [
        'expediente_id', 'n', 'user_id', 'nombre', 'rol',
        'accion', 'detalle', 'tipo', 'etapa', 'hash', 'created_at',
    ];

    protected $casts = [
        'n'          => 'integer',
        'etapa'      => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('La bitácora de solvencias no se edita.'));
        static::deleting(fn () => throw new LogicException('La bitácora de solvencias no se borra.'));
    }

    public function expediente()
    {
        return $this->belongsTo(SolvenciaExpediente::class, 'expediente_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Agrega un movimiento. Debe llamarse dentro de la transacción que ya
     * bloqueó el expediente (lockForUpdate), así el consecutivo "n" es seguro.
     */
    public static function registrar(
        SolvenciaExpediente $expediente,
        ?User $user,
        string $accion,
        string $detalle = '',
        ?string $tipo = null,
        ?int $etapa = null
    ): self {
        $ultimo = static::where('expediente_id', $expediente->id)->orderByDesc('n')->first();

        $mov = new static([
            'expediente_id' => $expediente->id,
            'n'             => ($ultimo?->n ?? 0) + 1,
            'user_id'       => $user?->id,
            'nombre'        => $user?->name ?? 'Sistema',
            'rol'           => $user ? ($expediente->rolDe((int) $user->id) ?: null) : null,
            'accion'        => $accion,
            'detalle'       => $detalle !== '' ? $detalle : null,
            'tipo'          => $tipo,
            'etapa'         => $etapa,
            'created_at'    => now()->startOfSecond(),
        ]);

        $mov->hash = hash('sha256', ($ultimo?->hash ?? self::HUELLA_INICIAL) . $mov->contenido());
        $mov->save();

        return $mov;
    }

    /**
     * Representación canónica (todo como texto) sobre la que se calcula la huella.
     */
    public function contenido(): string
    {
        return json_encode([
            (string) $this->expediente_id,
            (string) $this->n,
            $this->created_at?->format('Y-m-d H:i:s') ?? '',
            (string) ($this->user_id ?? ''),
            (string) $this->nombre,
            (string) ($this->rol ?? ''),
            (string) $this->accion,
            (string) ($this->detalle ?? ''),
            (string) ($this->tipo ?? ''),
            (string) ($this->etapa ?? ''),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * true si ningún movimiento fue alterado ni eliminado.
     */
    public static function verificarCadena(SolvenciaExpediente $expediente): bool
    {
        $previa   = self::HUELLA_INICIAL;
        $esperado = 1;

        foreach (static::where('expediente_id', $expediente->id)->orderBy('n')->cursor() as $mov) {
            if ($mov->n !== $esperado || ! hash_equals($mov->hash, hash('sha256', $previa . $mov->contenido()))) {
                return false;
            }
            $previa = $mov->hash;
            $esperado++;
        }

        return true;
    }
}
