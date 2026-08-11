<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class DocumentoNormativo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'normatividad_documentos';

    protected $fillable = [
        'categoria',
        'titulo',
        'version',
        'vigencia',
        'responsable',
        'descripcion',
        'archivo',
        'archivo_nombre',
        'archivo_tamano',
        'archivo_mime',
        'fecha_publicacion',
        'activo',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'vigencia'          => 'date',
        'fecha_publicacion' => 'date',
        'activo'            => 'boolean',
        'archivo_tamano'    => 'integer',
    ];

    /* ───────── Relaciones ───────── */

    public function versiones(): HasMany
    {
        return $this->hasMany(VersionNormativa::class, 'documento_id')->latest();
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    /* ───────── Scopes ───────── */

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeDeCategoria(Builder $query, string $categoria): Builder
    {
        return $query->where('categoria', $categoria);
    }

    public function scopeBuscar(Builder $query, ?string $termino): Builder
    {
        if (blank($termino)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($termino) {
            $q->where('titulo', 'like', "%{$termino}%")
              ->orWhere('descripcion', 'like', "%{$termino}%")
              ->orWhere('responsable', 'like', "%{$termino}%")
              ->orWhere('version', 'like', "%{$termino}%");
        });
    }

    /* ───────── Catálogo de categorías ───────── */

    public static function categorias(): array
    {
        return config('normatividad.categorias', []);
    }

    public static function clavesCategorias(): array
    {
        return array_keys(static::categorias());
    }

    public function getCategoriaNombreAttribute(): string
    {
        return static::categorias()[$this->categoria]['singular'] ?? ucfirst($this->categoria);
    }

    public function getCategoriaIconoAttribute(): string
    {
        return static::categorias()[$this->categoria]['icono'] ?? '📄';
    }

    /* ───────── Estado de vigencia ───────── */

    public function getEstadoVigenciaAttribute(): string
    {
        if (! $this->vigencia) {
            return 'sin_fecha';
        }

        $hoy = Carbon::today();

        if ($this->vigencia->lt($hoy)) {
            return 'vencido';
        }

        $dias = (int) config('normatividad.dias_aviso_vencimiento', 30);

        return $this->vigencia->lte($hoy->copy()->addDays($dias))
            ? 'por_vencer'
            : 'vigente';
    }

    public function getEtiquetaVigenciaAttribute(): array
    {
        return match ($this->estado_vigencia) {
            'vencido'    => ['texto' => 'Vencido',    'clase' => 'badge-danger'],
            'por_vencer' => ['texto' => 'Por vencer', 'clase' => 'badge-warning'],
            'vigente'    => ['texto' => 'Vigente',    'clase' => 'badge-success'],
            default      => ['texto' => 'Sin fecha',  'clase' => 'badge-secondary'],
        };
    }

    /* ───────── Archivo ───────── */

    public function getTieneArchivoAttribute(): bool
    {
        return filled($this->archivo);
    }

    public function getTamanoLegibleAttribute(): ?string
    {
        return static::formatearTamano($this->archivo_tamano);
    }

    public static function formatearTamano(?int $bytes): ?string
    {
        if (! $bytes) {
            return null;
        }

        $unidades = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($unidades) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, $i === 0 ? 0 : 1) . ' ' . $unidades[$i];
    }

    public function borrarArchivo(): void
    {
        $disco = config('normatividad.archivo.disco', 'local');

        if ($this->archivo && Storage::disk($disco)->exists($this->archivo)) {
            Storage::disk($disco)->delete($this->archivo);
        }
    }
}
