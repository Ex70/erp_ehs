<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comunicado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comunicados';

    /** Alcance de la notificación */
    public const ALCANCE_TODOS      = 'todos';
    public const ALCANCE_SEGMENTADO = 'segmentado';

    protected $fillable = [
        'titulo',
        'categoria',
        'icono_emoji',
        'color_fondo',
        'fecha_publicacion',
        'autor',
        'extracto',
        'contenido_completo',
        'archivo',
        'alcance',
        'user_id',
    ];

    protected $casts = [
        'fecha_publicacion' => 'date',
        'notificado_en'     => 'datetime',
        'notificados_count' => 'integer',
    ];

    // Categorías disponibles
    public static function categorias(): array
    {
        return [
            'Infografía',
            'Organización',
            'Cumpleaños',
            'Reconocimiento',
            'Promoción',
            'Comunicado',
            'Evento',
        ];
    }

    // Emoji por defecto según categoría
    public static function emojiPorCategoria(string $categoria): string
    {
        return match($categoria) {
            'Infografía'    => '📊',
            'Organización'  => '🏢',
            'Cumpleaños'    => '🎂',
            'Reconocimiento'=> '🏆',
            'Promoción'     => '📣',
            'Comunicado'    => '📢',
            'Evento'        => '📅',
            default         => '📌',
        };
    }

    // Color por defecto según categoría
    public static function colorPorCategoria(string $categoria): string
    {
        return match($categoria) {
            'Infografía'    => '#FEF3C7',
            'Organización'  => '#DBEAFE',
            'Cumpleaños'    => '#EDE9FE',
            'Reconocimiento'=> '#FEF9C3',
            'Promoción'     => '#DCFCE7',
            'Comunicado'    => '#FFE4E6',
            'Evento'        => '#E0F2FE',
            default         => '#F3F4F6',
        };
    }

    // ── Relaciones ────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Departamentos destinatarios (solo aplica si alcance = segmentado) */
    public function departamentos()
    {
        return $this->belongsToMany(Departamento::class, 'comunicado_departamento')
                    ->withTimestamps();
    }

    /** Usuarios agregados individualmente al alcance */
    public function usuariosIncluidos()
    {
        return $this->belongsToMany(User::class, 'comunicado_user')
                    ->wherePivot('tipo', 'incluido')
                    ->withPivot('tipo')
                    ->withTimestamps();
    }

    /** Usuarios excluidos explícitamente (aplica incluso con alcance = todos) */
    public function usuariosExcluidos()
    {
        return $this->belongsToMany(User::class, 'comunicado_user')
                    ->wherePivot('tipo', 'excluido')
                    ->withPivot('tipo')
                    ->withTimestamps();
    }

    // ── Helpers ───────────────────────────────────────

    public function esSegmentado(): bool
    {
        return $this->alcance === self::ALCANCE_SEGMENTADO;
    }

    public function yaNotificado(): bool
    {
        return ! is_null($this->notificado_en);
    }
}