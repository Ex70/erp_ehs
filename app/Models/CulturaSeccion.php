<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CulturaSeccion extends Model
{
    use HasFactory;

    protected $table = 'cultura_secciones';

    public const CLAVE_PRESENTACION = 'presentacion';
    public const CLAVE_MISION       = 'mision';
    public const CLAVE_VISION       = 'vision';
    public const CLAVE_HISTORIA     = 'historia';
    public const CLAVE_OBJETIVOS    = 'objetivos';

    public const CLAVES = [
        self::CLAVE_PRESENTACION,
        self::CLAVE_MISION,
        self::CLAVE_VISION,
        self::CLAVE_HISTORIA,
        self::CLAVE_OBJETIVOS,
    ];

    protected $fillable = [
        'clave',
        'titulo',
        'texto',
        'icono',
        'orden',
        'activo',
        'actualizado_por',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden'  => 'integer',
    ];

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    /**
     * Texto con saltos de línea convertidos a <br>, ya escapado.
     */
    public function getTextoHtmlAttribute(): string
    {
        return nl2br(e($this->texto ?? ''));
    }
}
