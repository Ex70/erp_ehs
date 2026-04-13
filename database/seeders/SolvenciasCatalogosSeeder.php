<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmpresaSolvencia;

class SolvenciasCatalogosSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = [
            ['nombre' => 'CORPORATIVO MAROHER S.A. DE C.V.',          'rfc' => ''],
            ['nombre' => 'ELÉCTRICA HIDRÁULICA DEL SURESTE S.A. DE C.V.', 'rfc' => 'EHS150529ME8'],
            ['nombre' => 'COMERCIALIZADORA EHS S.A. DE C.V.',          'rfc' => 'CMA-110105BN0'],
            ['nombre' => 'RAFAEL ALDAIR AZAMAR',                       'rfc' => ''],
        ];

        foreach ($empresas as $e) {
            EmpresaSolvencia::firstOrCreate(['nombre' => $e['nombre']], $e);
        }
    }
}