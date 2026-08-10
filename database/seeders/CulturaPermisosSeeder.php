<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CulturaPermisosSeeder extends Seeder
{
    /**
     * Permisos del módulo Cultura Organizacional.
     * Notación de tres segmentos: modulo.accion.alcance
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        $permisos = [
            'cultura.ver.todos',     // Consultar el módulo
            'cultura.editar.todos',  // Editar contenido y carrusel
        ];

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, $guard);
        }

        // Lectura: todos los roles existentes
        foreach (['administrador', 'jefe_area', 'coordinador', 'auxiliar'] as $nombreRol) {
            if ($rol = Role::where('name', $nombreRol)->where('guard_name', $guard)->first()) {
                $rol->givePermissionTo('cultura.ver.todos');
            }
        }

        // Edición: solo administrador (agrega aquí el rol de RRHH cuando exista)
        if ($admin = Role::where('name', 'administrador')->where('guard_name', $guard)->first()) {
            $admin->givePermissionTo('cultura.editar.todos');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
