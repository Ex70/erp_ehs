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
     * Notifica al solicitante + todos los admin/coordinador activos,
     * excluyendo al usuario que realiza la acción.
     */
    protected function notificarActualizacionTicket(
        Ticket $ticket,
        string $evento,
        string $detalle
    ): void {
        $destinatarios = collect();

        // Solicitante
        if ($ticket->solicitante && $ticket->solicitante->id !== Auth::id()) {
            $destinatarios->push($ticket->solicitante);
        }

        // Admin y coordinadores activos (excepto quien hace la acción)
        $jefes = User::role(['administrador', 'coordinador'])
            ->where('activo', true)
            ->where('id', '!=', Auth::id())
            ->get();

        $destinatarios = $destinatarios
            ->merge($jefes)
            ->unique('id');

        foreach ($destinatarios as $usuario) {
            try {
                $usuario->notify(new TicketActualizadoNotificacion($ticket, $evento, $detalle));
            } catch (\Exception $e) {
                logger()->error("Error notificando usuario {$usuario->id} en ticket {$ticket->folio}: " . $e->getMessage());
            }
        }
    }
}