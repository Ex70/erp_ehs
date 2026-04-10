<?php
namespace App\Http\Controllers\Helpdesk;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardHelpdeskController extends Controller
{
    public function index()
    {
        // KPIs
        $stats = [
            'total'         => Ticket::count(),
            'pendientes'    => Ticket::where('seguimiento', 'pendiente')->count(),
            'en_proceso'    => Ticket::whereIn('seguimiento', ['en_atencion','en_desarrollo','en_pruebas'])->count(),
            'finalizados'   => Ticket::where('seguimiento', 'finalizado')->count(),
            'escalados'     => Ticket::where('seguimiento', 'escalado')->count(),
        ];

        // Tickets por estado (dona)
        $porEstado = Ticket::select('seguimiento', DB::raw('count(*) as total'))
                           ->groupBy('seguimiento')
                           ->get()
                           ->mapWithKeys(fn($r) => [$r->seguimiento => $r->total]);

        // Tickets por tipo de falla (barras)
        $porTipo = Ticket::select('tipos_falla.nombre', DB::raw('count(*) as total'))
                         ->join('tipos_falla', 'tickets.tipo_falla_id', '=', 'tipos_falla.id')
                         ->groupBy('tipos_falla.nombre')
                         ->get();

        // Tickets por departamento (barras)
        $porDepartamento = Ticket::select('puestos.nombre as depto', DB::raw('count(*) as total'))
                                 ->join('users', 'tickets.user_id', '=', 'users.id')
                                 ->join('puestos', 'users.puesto_id', '=', 'puestos.id')
                                 ->groupBy('puestos.nombre')
                                 ->get();

        // Tendencia mensual (línea) — últimos 6 meses
        $tendencia = Ticket::select(
                            DB::raw('MONTH(created_at) as mes'),
                            DB::raw('YEAR(created_at) as anio'),
                            DB::raw('count(*) as total')
                        )
                        ->where('created_at', '>=', now()->subMonths(6))
                        ->groupBy('anio', 'mes')
                        ->orderBy('anio')
                        ->orderBy('mes')
                        ->get();

        return view('helpdesk.dashboard', compact(
            'stats', 'porEstado', 'porTipo', 'porDepartamento', 'tendencia'
        ));
    }
}