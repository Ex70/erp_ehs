<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HelpdeskPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        $permisos = [
            'tickets.crear',
            'tickets.ver.propio',
            'tickets.ver.todos',
            'tickets.editar.propio',
            'tickets.editar.todos',
            'tickets.eliminar',
            'tickets.asignar',
            'tickets.dashboard',
            'tickets.calificar',
        ];

        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // Administrador — acceso total
        $admin = Role::findByName('administrador');
        $admin->givePermissionTo($permisos);

        // Coordinador (jefe sistemas) — gestión completa sin eliminar
        $coord = Role::findByName('coordinador');
        $coord->givePermissionTo([
            'tickets.crear', 'tickets.ver.propio', 'tickets.ver.todos',
            'tickets.editar.propio', 'tickets.editar.todos',
            'tickets.asignar', 'tickets.dashboard', 'tickets.calificar',
        ]);

        // Auxiliar (técnico) — ver asignados y actualizar seguimiento
        $aux = Role::findByName('auxiliar');
        $aux->givePermissionTo([
            'tickets.crear', 'tickets.ver.propio',
            'tickets.editar.propio', 'tickets.dashboard',
        ]);

        // Jefe_area — solo crear y ver propios
        $jefe = Role::findByName('jefe_area');
        $jefe->givePermissionTo([
            'tickets.crear', 'tickets.ver.propio', 'tickets.calificar',
        ]);
    }
}