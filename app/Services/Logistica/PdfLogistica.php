<?php

namespace App\Services\Logistica;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Storage;

/**
 * Utilidades para los PDF del módulo (DomPDF). Las imágenes se incrustan
 * como data URI para no depender de chroot ni de isRemoteEnabled.
 */
class PdfLogistica
{
    private const MESES = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO',
        'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];

    /** Archivos en public/images/logistica por clave de empresa. */
    private const LOGOS = [
        'AZA'   => ['logo' => 'aza_logo.png', 'marca_agua' => 'aza_marca_agua.png'],
        'CEHS'  => ['encabezado' => 'cehs_header.png', 'marca_agua' => 'cehs_marca_agua.png'],
        'EHS'   => ['logo' => 'ehs_logo.png'],
        'MHR'   => ['logo' => 'mhr_logo.png'],
        'GRUPO' => ['logo' => 'grupo_quetzalcoatl_logo.jpg'],
    ];

    public static function logosEmpresa(string $clave): array
    {
        return array_map(fn ($archivo) => static::logo($archivo), self::LOGOS[$clave] ?? []);
    }

    public static function logo(string $archivo): ?string
    {
        return static::dataUri(public_path("images/logistica/{$archivo}"));
    }

    /**
     * Imagen de partida ajustada a una caja (px) conservando la proporción.
     * Devuelve ['src', 'w', 'h'] o null.
     */
    public static function imagenPartida(?string $ruta, int $cajaAncho, int $cajaAlto): ?array
    {
        if (! $ruta || ! Storage::disk('public')->exists($ruta)) {
            return null;
        }

        $archivo = Storage::disk('public')->path($ruta);
        $dimensiones = @getimagesize($archivo);

        if (! $dimensiones) {
            return null;
        }

        $escala = min($cajaAncho / $dimensiones[0], $cajaAlto / $dimensiones[1], 1);

        return [
            'src' => static::dataUri($archivo),
            'w'   => (int) round($dimensiones[0] * $escala),
            'h'   => (int) round($dimensiones[1] * $escala),
        ];
    }

    public static function fechaLetra(?CarbonInterface $fecha): string
    {
        return $fecha
            ? $fecha->day . ' DE ' . self::MESES[$fecha->month - 1] . ' DE ' . $fecha->year
            : '';
    }

    /** 10.00 → "10", 2.50 → "2.5", 1000 → "1,000" */
    public static function cantidad($valor): string
    {
        return rtrim(rtrim(number_format((float) $valor, 2, '.', ','), '0'), '.');
    }

    private static function dataUri(string $ruta): ?string
    {
        if (! is_file($ruta)) {
            return null;
        }

        $mime = mime_content_type($ruta) ?: 'image/png';

        return "data:{$mime};base64," . base64_encode(file_get_contents($ruta));
    }
}