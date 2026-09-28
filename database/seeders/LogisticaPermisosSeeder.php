<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LogisticaPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = [
            'logistica.ver',
            'logistica.crear',
            'logistica.editar',
            'logistica.eliminar',
            'cat_logistica.ver',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // Solo el administrador los recibe automáticamente. Los roles del ERP son
        // por nivel, no por departamento: si se asignaran aquí a "auxiliar", un
        // auxiliar de RRHH también vería Logística. Asígnalos desde Roles.
        Role::where('name', 'administrador')
            ->where('guard_name', 'web')
            ->first()
            ?->givePermissionTo($permisos);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}