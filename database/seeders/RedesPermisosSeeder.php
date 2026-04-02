<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RedesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        $permisos = [
            'redes.ver',
            'redes.crear',
            'redes.editar',
            'redes.eliminar',
        ];

        $permisosCatalogos = [
            'catalogos_sistemas.ver',
            'catalogos_sistemas.crear',
            'catalogos_sistemas.editar',
            'catalogos_sistemas.eliminar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        foreach ($permisosCatalogos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // Asignar todos al administrador
        $admin = Role::findByName('administrador');
        $admin->givePermissionTo($permisos);
        $admin->givePermissionTo($permisosCatalogos);
    }
}