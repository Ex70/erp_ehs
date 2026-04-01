<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Puesto;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{
    public function index()
    {
        // Tarjetas de resumen
        $totalUsuarios  = User::count();
        $usuariosActivos = User::where('activo', true)->count();
        $totalRoles     = Role::count();
        $totalPuestos   = Puesto::count();
        $puestosActivos = Puesto::where('activo', true)->count();
        $totalPermisos  = Permission::count();

        // Usuarios por rol para la gráfica
        $usuariosPorRol = Role::withCount('users')
            ->orderByDesc('users_count')
            ->get()
            ->map(fn($r) => [
                'label' => ucfirst(str_replace('_', ' ', $r->name)),
                'total' => $r->users_count,
            ]);

        // Usuarios por puesto para la gráfica
        $usuariosPorPuesto = Puesto::withCount('users')
            ->having('users_count', '>', 0)
            ->orderByDesc('users_count')
            ->get()
            ->map(fn($p) => [
                'label' => $p->nombre,
                'total' => $p->users_count,
            ]);

        // Últimos 5 usuarios registrados
        $ultimosUsuarios = User::with(['puesto', 'roles'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalUsuarios',
            'usuariosActivos',
            'totalRoles',
            'totalPuestos',
            'puestosActivos',
            'totalPermisos',
            'usuariosPorRol',
            'usuariosPorPuesto',
            'ultimosUsuarios',
        ));
    }
}