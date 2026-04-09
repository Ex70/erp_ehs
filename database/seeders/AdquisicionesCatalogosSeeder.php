<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Proveedor;
use App\Models\UnidadMedida;

class AdquisicionesCatalogosSeeder extends Seeder
{
    public function run(): void
    {
        // Empresas del grupo
        $empresas = [
            ['clave' => 'MHR',  'nombre' => 'Corporativo Maroher S.A. de C.V.'],
            ['clave' => 'EHS',  'nombre' => 'Eléctrica Hidráulica del Sureste S.A. de C.V.', 'rfc' => 'EHS150529ME8'],
            ['clave' => 'CEHS', 'nombre' => 'Comercializadora EHS S.A. de C.V.', 'rfc' => 'CMA-110105BN0'],
            ['clave' => 'AZA',  'nombre' => 'Rafael Aldair Azamar (AZA)'],
        ];

        foreach ($empresas as $e) {
            Empresa::firstOrCreate(['clave' => $e['clave']], $e);
        }

        // Clientes base
        $clientes = [
            'CLIENTE GENERAL',
            'PEMEX',
            'CFE',
            'GOBIERNO DEL ESTADO',
        ];

        foreach ($clientes as $nombre) {
            Cliente::firstOrCreate(['nombre' => $nombre]);
        }

        // Unidades de medida
        $unidades = [
            ['clave' => 'PZA',  'nombre' => 'Pieza'],
            ['clave' => 'KG',   'nombre' => 'Kilogramo'],
            ['clave' => 'LT',   'nombre' => 'Litro'],
            ['clave' => 'M',    'nombre' => 'Metro'],
            ['clave' => 'M2',   'nombre' => 'Metro cuadrado'],
            ['clave' => 'M3',   'nombre' => 'Metro cúbico'],
            ['clave' => 'JGO',  'nombre' => 'Juego'],
            ['clave' => 'SRV',  'nombre' => 'Servicio'],
            ['clave' => 'CJA',  'nombre' => 'Caja'],
            ['clave' => 'PAQ',  'nombre' => 'Paquete'],
            ['clave' => 'TON',  'nombre' => 'Tonelada'],
            ['clave' => 'GL',   'nombre' => 'Galón'],
        ];

        foreach ($unidades as $u) {
            UnidadMedida::firstOrCreate(['clave' => $u['clave']], $u);
        }
    }
}