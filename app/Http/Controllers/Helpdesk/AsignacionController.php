<?php

namespace App\Http\Controllers\Helpdesk;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketAsignacion;
use App\Models\User;
use App\Notifications\TicketAsignadoNotificacion;
use App\Services\Helpdesk\TecnicoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AsignacionController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $idsValidos = TecnicoService::idsDisponibles();

        if (empty($idsValidos)) {
            return back()->with(
                'error',
                'No hay usuarios activos registrados en el departamento de soporte. '
                . 'Revisa el catálogo de Departamentos antes de asignar.'
            );
        }

        $request->validate([
            'tecnicos'   => ['required', 'array', 'min:1'],
            'tecnicos.*' => ['integer', Rule::in($idsValidos)],
        ], [
            'tecnicos.required' => 'Debes seleccionar al menos un técnico.',
            'tecnicos.*.in'     => 'Solo puedes asignar usuarios activos del departamento de soporte.',
        ]);

        // Desactivar asignaciones anteriores
        $ticket->asignaciones()->update(['activo' => false]);

        // Crear/reactivar asignaciones y notificar
        foreach ($request->input('tecnicos', []) as $userId) {
            TicketAsignacion::updateOrCreate(
                ['ticket_id' => $ticket->id, 'user_id' => $userId],
                ['activo' => true]
            );

            $tecnico = User::find($userId);

            if ($tecnico && $tecnico->id !== Auth::id()) {
                try {
                    $tecnico->notify(new TicketAsignadoNotificacion($ticket));
                } catch (\Exception $e) {
                    logger()->error("Error notificando técnico {$userId}: " . $e->getMessage());
                }
            }
        }

        // Cambiar estado a en_atencion si está pendiente
        if ($ticket->seguimiento === 'pendiente') {
            $ticket->update(['seguimiento' => 'en_atencion']);
        }

        return back()->with('success', 'Ticket asignado correctamente.');
    }
}