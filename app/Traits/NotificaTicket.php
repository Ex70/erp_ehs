<?php
// app/Traits/NotificaTicket.php

namespace App\Traits;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketActualizadoNotificacion;
use Illuminate\Support\Facades\Auth;

trait NotificaTicket
{
    /**
     * Notifica al solicitante (siempre, si no es quien hace la acción) y,
     * opcionalmente, a todos los admin/coordinador activos (excluyendo
     * al usuario que realiza la acción).
     *
     * @param bool $incluirJefes  Si es false, solo se notifica al solicitante.
     */
    protected function notificarActualizacionTicket(
        Ticket $ticket,
        string $evento,
        string $detalle,
        bool $incluirJefes = true
    ): void {
        $destinatarios = collect();

        // Solicitante
        if ($ticket->solicitante && $ticket->solicitante->id !== Auth::id()) {
            $destinatarios->push($ticket->solicitante);
        }

        if ($incluirJefes) {
            // Admin y coordinadores activos (excepto quien hace la acción)
            $jefes = User::role(['administrador', 'coordinador'])
                ->where('activo', true)
                ->where('id', '!=', Auth::id())
                ->get();

            $destinatarios = $destinatarios->merge($jefes);
        }

        $destinatarios = $destinatarios->unique('id');

        foreach ($destinatarios as $usuario) {
            try {
                $usuario->notify(new TicketActualizadoNotificacion($ticket, $evento, $detalle));
            } catch (\Exception $e) {
                logger()->error("Error notificando usuario {$usuario->id} en ticket {$ticket->folio}: " . $e->getMessage());
            }
        }
    }
}