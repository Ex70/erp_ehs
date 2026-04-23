<?php
namespace App\Http\Controllers\Helpdesk;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketSeguimiento;
use App\Notifications\TicketCerradoNotificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeguimientoController extends Controller
{
    public function store(Request $request, Ticket $ticket){
        $ticket->load('tecnicos');
        $user = Auth::user();

        // Solo admin/coordinador o técnico asignado puede actualizar seguimiento
        if (!$user->can('tickets.editar.todos')
            && $ticket->user_id !== $user->id
            && !$ticket->tecnicos->contains('id', $user->id)) {
            abort(403);
        }

        $request->validate([
            'estado'     => 'required|in:pendiente,en_atencion,en_desarrollo,en_pruebas,finalizado,escalado',
            'comentario' => 'nullable|string|max:1000',
            'resolucion' => 'nullable|string',
        ]);

        TicketSeguimiento::create([
            'ticket_id'  => $ticket->id,
            'user_id'    => Auth::id(),
            'estado'     => $request->estado,
            'comentario' => $request->comentario,
        ]);

        $update = ['seguimiento' => $request->estado];

        if ($request->estado === 'finalizado') {
            $update['fecha_cierre'] = now();
            $update['resolucion']   = $request->resolucion;

            try {
                $ticket->solicitante->notify(new TicketCerradoNotificacion($ticket));
            } catch (\Exception $e) {
                logger()->error('Error notificando cierre: ' . $e->getMessage());
            }
        }

        $ticket->update($update);

        return back()->with('success', 'Seguimiento actualizado correctamente.');
    }
}