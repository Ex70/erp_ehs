<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ComunicadosPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            'comunicados.ver',
            'comunicados.crear',
            'comunicados.editar',
            'comunicados.eliminar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // Administrador y jefe_area tienen todos los permisos
        $rolesCompletos = ['administrador', 'jefe_area'];
        foreach ($rolesCompletos as $rolNombre) {
            $rol = Role::findByName($rolNombre);
            $rol->givePermissionTo($permisos);
        }

        // Coordinador y auxiliar solo pueden ver
        $rolesSolo = ['coordinador', 'auxiliar'];
        foreach ($rolesSolo as $rolNombre) {
            $rol = Role::findByName($rolNombre);
            $rol->givePermissionTo(['comunicados.ver']);
        }
    }
}