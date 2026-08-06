<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermisosFaltantesSeeder extends Seeder
{
    public function run(): void
    {
        $nuevos = [
            'solvencias.ver',
            'solvencias.crear',
            'solvencias.editar',
            'solvencias.eliminar',
            'cat_helpdesk.ver',
            'cat_helpdesk.crear',
            'cat_helpdesk.editar',
        ];

        foreach ($nuevos as $permiso) {
            Permission::firstOrCreate([
                'name'       => $permiso,
                'guard_name' => 'web',
            ]);
        }

        // El administrador recibe absolutamente todos los permisos existentes
        if ($admin = Role::where('name', 'administrador')->first()) {
            $admin->syncPermissions(Permission::all());
            $this->command->info('✔ administrador -> '.$admin->permissions()->count().' permisos');
        }

        // Coordinador: opera solvencias y administra catálogos de helpdesk
        if ($coord = Role::where('name', 'coordinador')->first()) {
            $coord->givePermissionTo([
                'solvencias.ver', 'solvencias.crear', 'solvencias.editar',
                'cat_helpdesk.ver', 'cat_helpdesk.crear', 'cat_helpdesk.editar',
            ]);
            $this->command->info('✔ coordinador -> permisos de solvencias y catálogos helpdesk');
        }

        // Auxiliar: solo consulta y captura de solvencias
        if ($aux = Role::where('name', 'auxiliar')->first()) {
            $aux->givePermissionTo(['solvencias.ver', 'solvencias.crear']);
            $this->command->info('✔ auxiliar -> solvencias.ver, solvencias.crear');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}