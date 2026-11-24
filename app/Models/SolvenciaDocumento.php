<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolvenciaDocumento extends Model
{
    protected $table = 'solvencia_documentos';

    protected $fillable = [
        'expediente_id', 'tipo', 'archivo', 'nombre', 'tamano', 'mime',
        'exento', 'observacion', 'reemplazado', 'user_id',
    ];

    protected $casts = [
        'tamano'      => 'integer',
        'exento'      => 'boolean',
        'reemplazado' => 'boolean',
    ];

    /** Tipos que el navegador puede mostrar en línea con seguridad */
    public const EN_LINEA = [
        'pdf'  => 'application/pdf',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
    ];

    public function expediente()
    {
        return $this->belongsTo(SolvenciaExpediente::class, 'expediente_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getEtiquetaAttribute(): string
    {
        return config("solvencias.documentos.{$this->tipo}.nombre", $this->tipo);
    }

    public function extension(): string
    {
        return strtolower(pathinfo($this->nombre, PATHINFO_EXTENSION));
    }

    public function tieneArchivo(): bool
    {
        return ! $this->exento && filled($this->archivo);
    }

    /** Excel que el visor del navegador puede leer (formato OOXML) */
    public function esExcelLegible(): bool
    {
        return $this->tieneArchivo() && in_array($this->extension(), ['xlsx', 'xlsm'], true);
    }

    public function seMuestraEnLinea(): bool
    {
        return $this->tieneArchivo() && array_key_exists($this->extension(), self::EN_LINEA);
    }

    public function getTamanoLegibleAttribute(): string
    {
        $kb = $this->tamano / 1024;

        return $kb >= 1024
            ? number_format($kb / 1024, 1) . ' MB'
            : number_format(max($kb, 1), 0) . ' KB';
    }
}
