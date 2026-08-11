<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VersionNormativa extends Model
{
    use HasFactory;

    protected $table = 'normatividad_versiones';

    protected $fillable = [
        'documento_id',
        'version',
        'archivo',
        'archivo_nombre',
        'archivo_tamano',
        'archivo_mime',
        'vigencia',
        'notas',
        'reemplazado_por',
    ];

    protected $casts = [
        'vigencia'       => 'date',
        'archivo_tamano' => 'integer',
    ];

    public function documento(): BelongsTo
    {
        return $this->belongsTo(DocumentoNormativo::class, 'documento_id');
    }

    public function reemplazadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reemplazado_por');
    }

    public function getTamanoLegibleAttribute(): ?string
    {
        return DocumentoNormativo::formatearTamano($this->archivo_tamano);
    }

    public function borrarArchivo(): void
    {
        $disco = config('normatividad.archivo.disco', 'local');

        if ($this->archivo && Storage::disk($disco)->exists($this->archivo)) {
            Storage::disk($disco)->delete($this->archivo);
        }
    }
}
