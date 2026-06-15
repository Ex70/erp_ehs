<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comunicado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comunicados';

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
        'user_id',
    ];

    protected $casts = [
        'fecha_publicacion' => 'date',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}