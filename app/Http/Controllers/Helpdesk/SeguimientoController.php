<?php
namespace App\Http\Controllers\Helpdesk;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketSeguimiento;
use App\Notifications\TicketCerradoNotificacion;
use App\Traits\NotificaTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeguimientoController extends Controller
{
    use NotificaTicket;

    public function store(Request $request, Ticket $ticket)
    {
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

        $estadoAnterior = $ticket->seguimiento;

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

        // Determinar evento y detalle para la notificación general
        if ($estadoAnterior !== $request->estado) {
            $etiquetas     = Ticket::etiquetasSeguimiento();
            $etiquetaNueva = $etiquetas[$request->estado] ?? $request->estado;
            $evento        = 'estado';
            $detalle       = "El estado del ticket {$ticket->folio} cambió a: {$etiquetaNueva}.";

            if ($request->filled('comentario')) {
                $detalle .= " Comentario: \"{$request->comentario}\"";
            }
        } else {
            $evento  = 'seguimiento';
            $detalle = "{$user->name} agregó un comentario en el ticket {$ticket->folio}.";

            if ($request->filled('comentario')) {
                $detalle .= " \"{$request->comentario}\"";
            }
        }

        $this->notificarActualizacionTicket($ticket->fresh(), $evento, $detalle);

        return back()->with('success', 'Seguimiento actualizado correctamente.');
    }
}