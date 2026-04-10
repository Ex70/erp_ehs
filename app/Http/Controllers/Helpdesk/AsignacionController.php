<?php
namespace App\Http\Controllers\Helpdesk;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketAsignacion;
use App\Models\User;
use App\Notifications\TicketAsignadoNotificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AsignacionController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'tecnicos' => 'required|array|min:1',
            'tecnicos.*' => 'exists:users,id',
        ], [
            'tecnicos.required' => 'Debes seleccionar al menos un técnico.',
        ]);

        // Desactivar asignaciones anteriores
        $ticket->asignaciones()->update(['activo' => false]);

        // Crear nuevas asignaciones y notificar
        foreach ($request->tecnicos as $userId) {
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