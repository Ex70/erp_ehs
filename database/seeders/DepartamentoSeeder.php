<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Puesto;
use Illuminate\Database\Seeder;

class DepartamentoSeeder extends Seeder
{
    /**
     * Catálogo de departamentos con los puestos válidos en cada uno.
     * Un mismo puesto puede aparecer en varios departamentos: el catálogo
     * de puestos es único y la relación se resuelve en la tabla pivote.
     */
    public function run(): void
    {
        $estructura = [
            'Dirección General' => [
                'clave'   => 'DIR',
                'puestos' => ['Director General', 'Asistente de Dirección'],
            ],
            'Adquisiciones' => [
                'clave'   => 'ADQ',
                'puestos' => [
                    'Jefe de Departamento',
                    'Coordinador',
                    'Analista de Compras',
                    'Auxiliar Administrativo',
                ],
            ],
            'Sistemas' => [
                'clave'   => 'SIS',
                'puestos' => [
                    'Jefe de Departamento',
                    'Coordinador de Telecomunicaciones',
                    'Soporte Técnico',
                    'Desarrollador',
                    'Auxiliar Administrativo',
                ],
            ],
            'Recursos Humanos' => [
                'clave'   => 'RRHH',
                'puestos' => [
                    'Jefe de Departamento',
                    'Coordinador',
                    'Analista de Nóminas',
                    'Auxiliar Administrativo',
                ],
            ],
            'Contabilidad y Finanzas' => [
                'clave'   => 'CON',
                'puestos' => [
                    'Jefe de Departamento',
                    'Contador',
                    'Auxiliar Contable',
                    'Auxiliar Administrativo',
                ],
            ],
            'Proyectos y Obra' => [
                'clave'   => 'PRO',
                'puestos' => [
                    'Jefe de Departamento',
                    'Coordinador de Proyectos',
                    'Ingeniero Residente',
                    'Supervisor de Obra',
                    'Técnico Electricista',
                ],
            ],
            'Almacén' => [
                'clave'   => 'ALM',
                'puestos' => ['Jefe de Almacén', 'Almacenista', 'Auxiliar Administrativo'],
            ],
        ];

        foreach ($estructura as $nombre => $config) {

            $departamento = Departamento::firstOrCreate(
                ['nombre' => $nombre],
                [
                    'clave'  => $config['clave'],
                    'activo' => true,
                ]
            );

            $puestoIds = [];

            foreach ($config['puestos'] as $nombrePuesto) {
                $puesto = Puesto::firstOrCreate(
                    ['nombre' => $nombrePuesto],
                    ['activo' => true]
                );

                $puestoIds[] = $puesto->id;
            }

            // syncWithoutDetaching preserva vinculaciones hechas a mano
            // desde la interfaz si vuelves a correr el seeder
            $departamento->puestos()->syncWithoutDetaching($puestoIds);

            $this->command->info("✔ {$nombre} — " . count($puestoIds) . ' puestos vinculados');
        }
    }
}