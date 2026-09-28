<?php

namespace App\Services\Logistica;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Imágenes de partidas: se guardan reducidas (600 px, JPG) para que los PDF
 * no revienten la memoria del Droplet.
 */
class ImagenPartidaService
{
    private const DISCO = 'public';
    private const LADO_MAXIMO = 600;

    public static function guardar(UploadedFile $archivo, int $remisionId): string
    {
        $directorio = "logistica/remisiones/{$remisionId}";
        $jpg = static::reducir($archivo->getRealPath());

        if ($jpg !== null) {
            $ruta = "{$directorio}/" . Str::uuid() . '.jpg';
            Storage::disk(self::DISCO)->put($ruta, $jpg);

            return $ruta;
        }

        // Sin GD: se guarda el original
        return $archivo->store($directorio, self::DISCO);
    }

    public static function eliminar(?string $ruta): void
    {
        if ($ruta && Storage::disk(self::DISCO)->exists($ruta)) {
            Storage::disk(self::DISCO)->delete($ruta);
        }
    }

    private static function reducir(string $ruta): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $contenido = @file_get_contents($ruta);
        $origen = $contenido ? @imagecreatefromstring($contenido) : false;

        if (! $origen) {
            return null;
        }

        $ancho  = imagesx($origen);
        $alto   = imagesy($origen);
        $escala = min(1, self::LADO_MAXIMO / max($ancho, $alto));
        $nuevoAncho = max(1, (int) round($ancho * $escala));
        $nuevoAlto  = max(1, (int) round($alto * $escala));

        $lienzo = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagefill($lienzo, 0, 0, imagecolorallocate($lienzo, 255, 255, 255)); // fondo blanco para PNG transparentes
        imagecopyresampled($lienzo, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

        ob_start();
        imagejpeg($lienzo, null, 85);
        $jpg = ob_get_clean();

        return $jpg ?: null;
    }
}