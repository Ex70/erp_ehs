<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DepartamentoPermisosSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            'departamentos.ver',
            'departamentos.crear',
            'departamentos.editar',
            'departamentos.eliminar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name'       => $permiso,
                'guard_name' => 'web',
            ]);
        }

        if ($admin = Role::where('name', 'administrador')->first()) {
            $admin->givePermissionTo($permisos);
        }

        if ($jefe = Role::where('name', 'jefe_area')->first()) {
            $jefe->givePermissionTo('departamentos.ver');
        }

        if ($coord = Role::where('name', 'coordinador')->first()) {
            $coord->givePermissionTo('departamentos.ver');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('✔ Permisos de departamentos asignados');
    }
}