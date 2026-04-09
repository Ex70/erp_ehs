<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdquisicionesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        $permisos = [
            'adquisiciones.ver',
            'adquisiciones.crear',
            'adquisiciones.editar',
            'adquisiciones.eliminar',
            'adquisiciones.adjudicar',
            'adquisiciones.exportar',
            // Catálogos
            'cat_adquisiciones.ver',
            'cat_adquisiciones.crear',
            'cat_adquisiciones.editar',
            'cat_adquisiciones.eliminar',
        ];

        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // Administrador: todo
        $admin = Role::findByName('administrador');
        $admin->givePermissionTo($permisos);

        // Coordinador: ver, crear, editar y adjudicar
        $coord = Role::findByName('coordinador');
        $coord->givePermissionTo([
            'adquisiciones.ver',
            'adquisiciones.crear',
            'adquisiciones.editar',
            'adquisiciones.adjudicar',
            'cat_adquisiciones.ver',
        ]);

        // Auxiliar: solo ver y crear
        $aux = Role::findByName('auxiliar');
        $aux->givePermissionTo([
            'adquisiciones.ver',
            'adquisiciones.crear',
            'adquisiciones.editar',
            'cat_adquisiciones.ver',
        ]);
    }
}