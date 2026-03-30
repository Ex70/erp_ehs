<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Puesto;

class PuestosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void{
        $puestos = [
            ['nombre' => 'Director General',     'descripcion' => 'Máxima autoridad'],
            ['nombre' => 'Jefe de Sistemas',      'descripcion' => 'Responsable de TI'],
            ['nombre' => 'Coordinador Operativo', 'descripcion' => 'Coordinación de procesos'],
            ['nombre' => 'Auxiliar Administrativo','descripcion' => 'Apoyo administrativo'],
        ];

        foreach ($puestos as $puesto) {
            Puesto::firstOrCreate(['nombre' => $puesto['nombre']], $puesto);
        }
    }
}
