<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Expediente de solvencia económica con cadena de custodia.
 * Las transiciones de estado viven en App\Services\Solvencias\ExpedienteService.
 */
class SolvenciaExpediente extends Model
{
    protected $table = 'solvencia_expedientes';

    public const BORRADOR   = 'borrador';
    public const SIN_ACUSE  = 'sin_acuse';
    public const EN_PODER   = 'en_poder';
    public const DEVUELTA   = 'devuelta';
    public const AUTORIZADA = 'autorizada';
    public const PAGADA     = 'pagada';
    public const CANCELADA  = 'cancelada';

    public const ESTADOS = [
        self::BORRADOR   => 'Borrador',
        self::SIN_ACUSE  => 'Sin acuse',
        self::EN_PODER   => 'En poder de',
        self::DEVUELTA   => 'Devuelta',
        self::AUTORIZADA => 'Autorizada',
        self::PAGADA     => 'Pagada',
        self::CANCELADA  => 'Cancelada',
    ];

    public const FINALES = [self::AUTORIZADA, self::PAGADA, self::CANCELADA];

    protected $fillable = [
        'folio', 'flujo_id', 'departamento_id',
        'tipo_proveedor', 'proveedor', 'monto', 'descripcion',
        'recorrido', 'participantes',
        'etapa', 'estado', 'holder_id', 'acuse', 'desde',
        'enviado_por_id', 'enviado_en', 'recordatorios',
        'creador_id', 'devuelta_desde', 'devuelta_por_id',
        'autorizada_en', 'pago_fecha', 'pago_por_id', 'pago_en', 'cancelada_en',
    ];

    protected $casts = [
        'monto'          => 'decimal:2',
        'recorrido'      => 'array',
        'participantes'  => 'array',
        'etapa'          => 'integer',
        'acuse'          => 'boolean',
        'desde'          => 'datetime',
        'enviado_en'     => 'datetime',
        'recordatorios'  => 'integer',
        'devuelta_desde' => 'integer',
        'autorizada_en'  => 'datetime',
        'pago_fecha'     => 'date',
        'pago_en'        => 'datetime',
        'cancelada_en'   => 'datetime',
    ];

    /** @var Collection<int, User>|null */
    protected ?Collection $personasCache = null;

    /* ───────── Relaciones ───────── */

    public function flujo()
    {
        return $this->belongsTo(SolvenciaFlujo::class, 'flujo_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function holder()
    {
        return $this->belongsTo(User::class, 'holder_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    public function enviadoPor()
    {
        return $this->belongsTo(User::class, 'enviado_por_id');
    }

    public function devueltaPor()
    {
        return $this->belongsTo(User::class, 'devuelta_por_id');
    }

    public function pagoPor()
    {
        return $this->belongsTo(User::class, 'pago_por_id');
    }

    public function documentos()
    {
        return $this->hasMany(SolvenciaDocumento::class, 'expediente_id')->orderBy('id');
    }

    public function movimientos()
    {
        return $this->hasMany(SolvenciaMovimiento::class, 'expediente_id')->orderBy('n');
    }

    /* ───────── Scopes ───────── */

    public function scopeActivas(Builder $query): Builder
    {
        return $query->whereNotIn('estado', self::FINALES);
    }

    /**
     * Expedientes que una persona puede consultar: los de su cadena, los que
     * elaboró o tuvo, los autorizados/pagados si es de Contabilidad, o todos
     * con el permiso solvencias_flujo.ver.todas.
     */
    public function scopeVisiblesPara(Builder $query, User $user): Builder
    {
        if ($user->can('solvencias_flujo.ver.todas')) {
            return $query;
        }

        $esPagador = SolvenciaPagador::where('user_id', $user->id)->exists();

        return $query->where(function (Builder $w) use ($user, $esPagador) {
            $w->whereJsonContains('participantes', (int) $user->id)
              ->orWhere('creador_id', $user->id)
              ->orWhere('holder_id', $user->id)
              ->orWhere('enviado_por_id', $user->id);

            if ($esPagador) {
                $w->orWhereIn('estado', [self::AUTORIZADA, self::PAGADA]);
            }
        });
    }

    /* ───────── Estado ───────── */

    public function esFinal(): bool
    {
        return in_array($this->estado, self::FINALES, true);
    }

    public function esDevuelta(): bool
    {
        return $this->estado === self::DEVUELTA;
    }

    public function totalEtapas(): int
    {
        return count($this->recorrido ?? []);
    }

    public function etapaActual(): array
    {
        return $this->recorrido[$this->etapa] ?? ['verbo' => '', 'nombre' => '', 'personas' => []];
    }

    public function verboActual(): string
    {
        return $this->etapaActual()['verbo'];
    }

    public function esUltimaEtapa(): bool
    {
        return $this->etapa === $this->totalEtapas() - 1;
    }

    public function siguienteEtapaIdx(): int
    {
        return $this->esDevuelta() ? (int) $this->devuelta_desde : $this->etapa + 1;
    }

    /**
     * Personas a las que se puede enviar en el siguiente paso.
     *
     * @return array<int, int>
     */
    public function candidatos(): array
    {
        if ($this->esDevuelta()) {
            return array_filter([(int) $this->devuelta_por_id]);
        }

        $siguiente = $this->recorrido[$this->siguienteEtapaIdx()] ?? null;

        if (! $siguiente) {
            return [];
        }

        // Fuera quien la tiene y quien ya no está activo en el ERP
        return array_values(array_filter(
            array_map('intval', $siguiente['personas']),
            fn ($id) => $id !== (int) $this->holder_id && $this->personas()->get($id)?->activo
        ));
    }

    public function editablePor(?User $user): bool
    {
        return $user
            && (int) $this->holder_id === (int) $user->id
            && $this->acuse
            && $this->etapa === 0
            && ! $this->esFinal();
    }

    public function esAutoridad(User $user): bool
    {
        $verbos = config('solvencias.verbos_autoridad', []);

        foreach ($this->recorrido ?? [] as $etapa) {
            if (in_array($etapa['verbo'], $verbos, true) && in_array((int) $user->id, array_map('intval', $etapa['personas']), true)) {
                return true;
            }
        }

        return false;
    }

    public function puedeVer(User $user): bool
    {
        if ($user->can('solvencias_flujo.ver.todas')) {
            return true;
        }

        $id = (int) $user->id;

        if (in_array($id, array_map('intval', $this->participantes ?? []), true)
            || in_array($id, [(int) $this->creador_id, (int) $this->holder_id, (int) $this->enviado_por_id], true)) {
            return true;
        }

        return in_array($this->estado, [self::AUTORIZADA, self::PAGADA], true)
            && SolvenciaPagador::where('user_id', $id)->exists();
    }

    /**
     * Responsabilidad(es) de una persona en este expediente: "Revisa y Valida".
     */
    public function rolDe(?int $userId): string
    {
        if (! $userId) {
            return '';
        }

        return collect($this->recorrido ?? [])
            ->filter(fn ($e) => in_array($userId, array_map('intval', $e['personas']), true))
            ->pluck('verbo')
            ->unique()
            ->implode(' y ');
    }

    /* ───────── Documentos ───────── */

    public function requeridos(): array
    {
        return config('solvencias.requeridos.' . $this->tipo_proveedor, []);
    }

    public function documentosActivos(): Collection
    {
        return $this->documentos->where('reemplazado', false)->values();
    }

    public function documentoActivo(string $tipo): ?SolvenciaDocumento
    {
        return $this->documentosActivos()->firstWhere('tipo', $tipo);
    }

    public function historialDe(string $tipo): Collection
    {
        return $this->documentos->where('tipo', $tipo)->where('reemplazado', true)->values();
    }

    /** @return array<int, string> tipos obligatorios sin documento (ni observación) */
    public function faltantes(): array
    {
        $activos = $this->documentosActivos()->pluck('tipo')->all();

        return array_values(array_diff($this->requeridos(), $activos));
    }

    public function exentos(): Collection
    {
        return $this->documentosActivos()->where('exento', true)->values();
    }

    public function formato(): ?SolvenciaDocumento
    {
        $doc = $this->documentoActivo('formato');

        return $doc && ! $doc->exento ? $doc : null;
    }

    public function comprobante(): ?SolvenciaDocumento
    {
        return $this->documentoActivo('comprobante');
    }

    /* ───────── Personas del recorrido ───────── */

    /**
     * Usuarios que aparecen en el expediente, indexados por id (una sola consulta).
     */
    public function personas(): Collection
    {
        if ($this->personasCache) {
            return $this->personasCache;
        }

        $ids = collect($this->participantes ?? [])
            ->merge([$this->creador_id, $this->holder_id, $this->enviado_por_id, $this->devuelta_por_id, $this->pago_por_id])
            ->filter()
            ->unique()
            ->values();

        return $this->personasCache = User::whereIn('id', $ids)->get()->keyBy('id');
    }

    public function nombreDe(?int $userId): string
    {
        if (! $userId) {
            return '(sin asignar)';
        }

        return $this->personas()->get($userId)?->name ?? '(usuario #' . $userId . ')';
    }

    public function nombresDe(array $ids, string $union = ' o '): string
    {
        return collect($ids)->map(fn ($id) => $this->nombreDe((int) $id))->implode($union);
    }

    /* ───────── Presentación ───────── */

    public function etiquetaEstado(): string
    {
        return match ($this->estado) {
            self::DEVUELTA   => $this->acuse ? 'Devuelta · en corrección' : 'Devuelta · sin acuse',
            self::AUTORIZADA => 'Autorizada · por pagar',
            self::PAGADA     => 'Pagada · finalizada',
            default          => self::ESTADOS[$this->estado] ?? $this->estado,
        };
    }

    public function claseEstado(): string
    {
        return [
            self::BORRADOR   => 'borrador',
            self::SIN_ACUSE  => 'sinacuse',
            self::EN_PODER   => 'enpoder',
            self::DEVUELTA   => 'devuelta',
            self::AUTORIZADA => 'autorizada',
            self::PAGADA     => 'pagada',
            self::CANCELADA  => 'cancelada',
        ][$this->estado] ?? 'borrador';
    }

    public function pendienteTexto(): string
    {
        if ($this->estado === self::AUTORIZADA) {
            return 'Pago';
        }

        return $this->esFinal() ? '—' : $this->verboActual();
    }

    public function retrasada(): bool
    {
        return ! $this->esFinal()
            && ! $this->acuse
            && $this->enviado_en
            && $this->enviado_en->diffInHours(now()) >= (int) config('solvencias.horas_alerta_acuse', 24);
    }

    /* ───────── Fechas para la vista ───────── */

    public static function fecha(?CarbonInterface $fecha, string $formato = 'd M Y H:i'): string
    {
        if (! $fecha) {
            return '—';
        }

        return $fecha->copy()
            ->timezone(config('solvencias.zona_horaria', 'America/Mexico_City'))
            ->locale('es')
            ->translatedFormat($formato);
    }

    /**
     * Fechas de calendario (columnas DATE como pago_fecha): sin conversión de
     * zona horaria, para que el 23 no se muestre como 22.
     */
    public static function dia(?CarbonInterface $fecha, string $formato = 'd M Y'): string
    {
        return $fecha ? $fecha->copy()->locale('es')->translatedFormat($formato) : '—';
    }

    /**
     * "hace 2 d 3 h" (como el prototipo), independiente del locale global.
     */
    public static function hace(?CarbonInterface $fecha): string
    {
        if (! $fecha) {
            return '—';
        }

        $min = max(0, (int) floor($fecha->diffInSeconds(now(), true) / 60));

        if ($min < 1) {
            return 'hace un momento';
        }
        if ($min < 60) {
            return "hace {$min} min";
        }

        $h = intdiv($min, 60);
        if ($h < 24) {
            return trim("hace {$h} h " . ($min % 60 ? ($min % 60) . ' min' : ''));
        }

        $d = intdiv($h, 24);

        return trim("hace {$d} d " . ($h % 24 ? ($h % 24) . ' h' : ''));
    }

    public static function duracion(?CarbonInterface $fecha): string
    {
        return str_replace('hace ', '', static::hace($fecha));
    }

    public function getMontoFormateadoAttribute(): string
    {
        return '$' . number_format((float) $this->monto, 2);
    }
}
