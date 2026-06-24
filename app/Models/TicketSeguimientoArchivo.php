<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class TicketSeguimientoArchivo extends Model
{
    use SoftDeletes;

    protected $table = 'ticket_seguimiento_archivos';

    protected $fillable = [
        'ticket_seguimiento_id',
        'nombre_original',
        'ruta',
        'mime',
        'tamano',
        'subido_por',
    ];

    // ⚠️ Ajusta el namespace/clase si tu modelo de seguimiento es otro
    public function seguimiento()
    {
        return $this->belongsTo(TicketSeguimiento::class, 'ticket_seguimiento_id');
    }

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    /** URL pública para descargar/mostrar */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->ruta);
    }

    /** ¿Es imagen? (para mostrar miniatura en vez de ícono) */
    public function esImagen(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    /** Tamaño legible (KB / MB) */
    public function getTamanoLegibleAttribute(): string
    {
        $bytes = (int) $this->tamano;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}