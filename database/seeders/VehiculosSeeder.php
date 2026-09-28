<?php

namespace Database\Seeders;

use App\Models\Vehiculo;
use Illuminate\Database\Seeder;

class VehiculosSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            ['nombre' => 'GOL',       'marca' => 'VOLKSWAGEN', 'combustible' => 'gasolina', 'km_actual' => 111733, 'color_etiqueta' => '#1976d2'],
            ['nombre' => 'HILUX',     'marca' => 'TOYOTA',     'combustible' => 'diesel',   'km_actual' => 142049, 'color_etiqueta' => '#388e3c'],
            ['nombre' => 'TORNADO 1', 'marca' => 'CHEVROLET',  'combustible' => 'gasolina', 'km_actual' => 25539,  'color_etiqueta' => '#f57c00'],
            ['nombre' => 'TORNADO 2', 'marca' => 'CHEVROLET',  'combustible' => 'gasolina', 'km_actual' => 15918,  'color_etiqueta' => '#7b1fa2'],
        ];

        foreach ($unidades as $u) {
            Vehiculo::withTrashed()->firstOrCreate(
                ['nombre' => $u['nombre']],
                $u + [
                    'rendimiento_km_l'  => 9,
                    'km_actualizado_at' => now(),
                    'activo'            => true,
                ]
            );
        }
    }
}