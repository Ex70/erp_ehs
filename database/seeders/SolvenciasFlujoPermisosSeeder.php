<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permisos del módulo de solvencias con trazabilidad.
 *
 * El acceso básico (bandeja, expedientes de su flujo) NO requiere permiso,
 * igual que la Mesa de Ayuda. Estos permisos solo amplían el alcance:
 *   solvencias_flujo.ver.todas   tablero con todas las solvencias
 *   solvencias_flujo.configurar  Personas y flujos, folios y Contabilidad
 *   solvencias_flujo.cancelar    cancelar cualquier solvencia activa
 *
 * Solo el administrador los recibe aquí; al resto se asignan desde Roles.
 */
class SolvenciasFlujoPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = [
            'solvencias_flujo.ver.todas',
            'solvencias_flujo.configurar',
            'solvencias_flujo.cancelar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        Role::where('name', 'administrador')
            ->where('guard_name', 'web')
            ->first()
            ?->givePermissionTo($permisos);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('✔ Permisos solvencias_flujo.* creados y asignados a administrador');
    }
}
