<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Consecutivo de folios por serie {PREFIJO}-{AÑO}.
 * Formato final: {PREFIJO}-{AÑO}-{0000}. Asignado al crear y no editable.
 */
class SolvenciaFolio extends Model
{
    protected $table = 'solvencia_folios';

    protected $fillable = ['serie', 'ultimo'];

    protected $casts = ['ultimo' => 'integer'];

    public static function serie(string $prefijo, ?int $anio = null): string
    {
        $anio ??= (int) now(config('solvencias.zona_horaria'))->format('Y');

        return strtoupper($prefijo) . '-' . $anio;
    }

    /**
     * Siguiente folio de la serie. Bloquea el renglón de la serie para que
     * dos capturas simultáneas nunca obtengan el mismo número.
     */
    public static function siguiente(string $prefijo, ?int $anio = null): string
    {
        $serie   = static::serie($prefijo, $anio);
        $digitos = (int) config('solvencias.folio.digitos', 4);

        return DB::transaction(function () use ($serie, $digitos) {
            DB::table('solvencia_folios')->insertOrIgnore([
                'serie'      => $serie,
                'ultimo'     => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $fila = static::where('serie', $serie)->lockForUpdate()->firstOrFail();

            // Salvaguarda: si alguien bajó el consecutivo a mano, se salta lo ocupado
            do {
                $fila->ultimo++;
                $folio = $serie . '-' . str_pad((string) $fila->ultimo, $digitos, '0', STR_PAD_LEFT);
            } while (SolvenciaExpediente::where('folio', $folio)->exists());

            $fila->save();

            return $folio;
        });
    }
}
