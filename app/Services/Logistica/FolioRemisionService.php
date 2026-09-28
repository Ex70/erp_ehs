<?php

namespace App\Services\Logistica;

use App\Models\Empresa;
use App\Models\Remision;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Folios de remisión según config('logistica.remisiones.empresas.*.formato_folio').
 *   {ddmmaaaa} fecha de la remisión
 *   {init}     3 letras: de la 1a partida o del cliente
 *   {seq}      siguiente número libre con el mismo prefijo (3 dígitos)
 */
class FolioRemisionService
{
    private const STOP_CLIENTE = ['DE', 'DEL', 'LA', 'EL', 'Y', 'LOS', 'LAS', 'SA', 'CV', 'H'];

    private const STOP_PRODUCTO = ['DE', 'DEL', 'LA', 'EL', 'Y', 'LOS', 'LAS', 'CON', 'PARA', 'EN',
        'UN', 'UNA', 'UNOS', 'UNAS', 'AL', 'A', 'SIN', 'POR', 'SOBRE', 'BAJO'];

    public static function generar(Empresa $empresa, $fecha, ?string $primeraPartida, ?string $cliente): string
    {
        $cfg     = config("logistica.remisiones.empresas.{$empresa->clave}", []);
        $formato = $cfg['formato_folio'] ?? ($empresa->clave . '-{seq}');

        $init = ! empty($cfg['init_de_producto']) ? static::inicialesProducto($primeraPartida) : null;
        $init ??= static::inicialesCliente($cliente);

        $base = strtr($formato, [
            '{ddmmaaaa}' => Carbon::parse($fecha)->format('dmY'),
            '{init}'     => $init,
        ]);

        return str_contains($base, '{seq}')
            ? static::conConsecutivo($empresa->id, $base)
            : static::sinDuplicar($empresa->id, $base);
    }

    public static function existe(int $empresaId, string $folio): bool
    {
        return Remision::withTrashed()
            ->where('empresa_id', $empresaId)
            ->where('folio', $folio)
            ->exists();
    }

    public static function inicialesCliente(?string $nombre): string
    {
        $palabras = static::palabras($nombre, self::STOP_CLIENTE, 1, '');

        if (! $palabras) {
            return 'XXX';
        }

        if (count($palabras) === 1) {
            return str_pad(substr($palabras[0], 0, 3), 3, 'X');
        }

        $iniciales = '';
        foreach (array_slice($palabras, 0, 3) as $palabra) {
            $iniciales .= $palabra[0];
        }

        return str_pad($iniciales, 3, 'X');
    }

    public static function inicialesProducto(?string $descripcion): ?string
    {
        $palabras = array_values(array_filter(
            static::palabras($descripcion, self::STOP_PRODUCTO, 3, ' '),
            fn ($p) => ! ctype_digit($p)
        ));

        return $palabras ? str_pad(substr($palabras[0], 0, 3), 3, 'X') : null;
    }

    private static function conConsecutivo(int $empresaId, string $base): string
    {
        [$antes, $despues] = explode('{seq}', $base, 2);
        $patron = '/^' . preg_quote($antes, '/') . '(\d+)' . preg_quote($despues, '/') . '$/';

        $max = Remision::withTrashed()
            ->where('empresa_id', $empresaId)
            ->where('folio', 'like', static::escaparLike($antes) . '%' . static::escaparLike($despues))
            ->pluck('folio')
            ->map(fn ($folio) => preg_match($patron, $folio, $m) ? (int) $m[1] : 0)
            ->max() ?? 0;

        return $antes . str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT) . $despues;
    }

    private static function sinDuplicar(int $empresaId, string $base): string
    {
        $folio = $base;
        $n = 2;

        while (static::existe($empresaId, $folio)) {
            $folio = "{$base}-{$n}";
            $n++;
        }

        return $folio;
    }

    private static function palabras(?string $texto, array $stop, int $minimo, string $reemplazo): array
    {
        $limpio = preg_replace('/[^A-Z0-9\s]/', $reemplazo, Str::upper(Str::ascii((string) $texto)));

        return array_values(array_filter(
            preg_split('/\s+/', $limpio, -1, PREG_SPLIT_NO_EMPTY),
            fn ($p) => strlen($p) >= $minimo && ! in_array($p, $stop, true)
        ));
    }

    private static function escaparLike(string $valor): string
    {
        return addcslashes($valor, '\\%_');
    }
}