<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CulturaItem extends Model
{
    use HasFactory;

    protected $table = 'cultura_items';

    public const TIPO_SLIDE    = 'slide';
    public const TIPO_VALOR    = 'valor';
    public const TIPO_OBJETIVO = 'objetivo';
    public const TIPO_EMPRESA  = 'empresa';

    /** Tipos administrables desde el modal "Editar Contenido" (listas). */
    public const TIPOS_LISTA = [
        self::TIPO_VALOR,
        self::TIPO_OBJETIVO,
        self::TIPO_EMPRESA,
    ];

    public const TIPOS = [
        self::TIPO_SLIDE,
        self::TIPO_VALOR,
        self::TIPO_OBJETIVO,
        self::TIPO_EMPRESA,
    ];

    protected $fillable = [
        'tipo',
        'titulo',
        'descripcion',
        'icono',
        'color',
        'imagen',
        'duracion',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo'   => 'boolean',
        'orden'    => 'integer',
        'duracion' => 'integer',
    ];

    /* ───────── Scopes ───────── */

    public function scopeDeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenados(Builder $query): Builder
    {
        return $query->orderBy('orden')->orderBy('id');
    }

    /* ───────── Accesores ───────── */

    public function getImagenUrlAttribute(): ?string
    {
        return $this->imagen ? Storage::disk('public')->url($this->imagen) : null;
    }

    public function getColorFondoAttribute(): string
    {
        return $this->color ?: '#1a1a3a';
    }

    /**
     * Borra el archivo físico asociado (si existe).
     */
    public function borrarImagen(): void
    {
        if ($this->imagen && Storage::disk('public')->exists($this->imagen)) {
            Storage::disk('public')->delete($this->imagen);
        }
    }
}
