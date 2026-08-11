<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class NormatividadPermisosSeeder extends Seeder
{
    /**
     * Permisos del módulo de Normatividad.
     *
     * Se usa notación de DOS segmentos (modulo.accion), como departamentos,
     * usuarios y solvencias: este módulo no tiene dimensión de alcance
     * (propio/todos) porque los documentos son institucionales, no personales.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        $permisos = [
            'normatividad.ver',
            'normatividad.crear',
            'normatividad.editar',
            'normatividad.eliminar',
        ];

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, $guard);
        }

        // Lectura: todos los roles. La normatividad es de consulta general.
        foreach (['administrador', 'jefe_area', 'coordinador', 'auxiliar'] as $nombreRol) {
            if ($rol = Role::where('name', $nombreRol)->where('guard_name', $guard)->first()) {
                $rol->givePermissionTo('normatividad.ver');
            }
        }

        // Gestión: solo administrador. Agrega aquí el rol de RRHH cuando exista.
        if ($admin = Role::where('name', 'administrador')->where('guard_name', $guard)->first()) {
            $admin->givePermissionTo($permisos);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
