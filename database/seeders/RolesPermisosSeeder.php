<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Puesto;

class RolesPermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definir permisos por módulo
        $permisos = [
            // Usuarios
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar',
            // Roles
            'roles.ver', 'roles.crear', 'roles.editar', 'roles.eliminar',
            // Puestos
            'puestos.ver', 'puestos.crear', 'puestos.editar', 'puestos.eliminar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // Crear roles
        $admin       = Role::firstOrCreate(['name' => 'administrador']);
        $jefeArea    = Role::firstOrCreate(['name' => 'jefe_area']);
        $coordinador = Role::firstOrCreate(['name' => 'coordinador']);
        $auxiliar    = Role::firstOrCreate(['name' => 'auxiliar']);

        // Administrador tiene todos los permisos
        $admin->syncPermissions(Permission::all());

        // Jefe de área puede ver y editar usuarios
        $jefeArea->syncPermissions([
            'usuarios.ver', 'usuarios.editar',
            'puestos.ver',
        ]);

        // Coordinador solo puede ver
        $coordinador->syncPermissions([
            'usuarios.ver', 'puestos.ver',
        ]);

        // Auxiliar acceso mínimo
        $auxiliar->syncPermissions([
            'usuarios.ver',
        ]);

        // Crear usuario administrador inicial
        $puesto = Puesto::where('nombre', 'Director General')->first();

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@sistema.com'],
            [
                'name'      => 'Administrador',
                'username'  => 'admin',
                'password'  => bcrypt('Admin1234!'),
                'puesto_id' => $puesto?->id,
                'activo'    => true,
            ]
        );

        $adminUser->assignRole('administrador');
    }
}
