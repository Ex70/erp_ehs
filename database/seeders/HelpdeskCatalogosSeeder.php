<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoFalla;
use App\Models\CategoriaServicio;

class HelpdeskCatalogosSeeder extends Seeder
{
    public function run(): void
    {
        $tiposFalla = [
            ['nombre' => 'Hardware',      'color' => 'warning'],
            ['nombre' => 'Software',      'color' => 'primary'],
            ['nombre' => 'Conectividad',  'color' => 'info'],
            ['nombre' => 'Seguridad',     'color' => 'danger'],
            ['nombre' => 'Otros',         'color' => 'secondary'],
        ];

        foreach ($tiposFalla as $t) {
            TipoFalla::firstOrCreate(['nombre' => $t['nombre']], $t);
        }

        $categorias = [
            'Soporte técnico',
            'Mantenimiento',
            'Instalación',
            'Configuración',
            'Otro',
        ];

        foreach ($categorias as $nombre) {
            CategoriaServicio::firstOrCreate(['nombre' => $nombre]);
        }
    }
}