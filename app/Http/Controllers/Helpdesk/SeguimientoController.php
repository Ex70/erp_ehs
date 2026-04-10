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
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'estado'     => 'required|in:pendiente,en_atencion,en_desarrollo,en_pruebas,finalizado,escalado',
            'comentario' => 'nullable|string|max:1000',
            'resolucion' => 'nullable|string',
        ]);

        // Registrar en historial
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

            // Notificar al solicitante
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