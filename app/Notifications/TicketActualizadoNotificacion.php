<?php
// app/Notifications/TicketActualizadoNotificacion.php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketActualizadoNotificacion extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $evento,   // 'estado' | 'asignacion' | 'seguimiento' | 'edicion'
        public string $detalle   // Texto descriptivo del cambio
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    // ─── Correo ─────────────────────────────────────────────────────────────
    public function toMail(object $notifiable): MailMessage
    {
        $asunto = match ($this->evento) {
            'estado'      => "Ticket {$this->ticket->folio} — Cambio de estado",
            'asignacion'  => "Ticket {$this->ticket->folio} — Técnico asignado",
            'seguimiento' => "Ticket {$this->ticket->folio} — Nuevo comentario",
            'edicion'     => "Ticket {$this->ticket->folio} — Información actualizada",
            default       => "Actualización en ticket {$this->ticket->folio}",
        };

        $etiquetaEstado = Ticket::etiquetasSeguimiento()[$this->ticket->seguimiento] ?? $this->ticket->seguimiento;

        return (new MailMessage)
            ->subject($asunto)
            ->greeting("Hola, {$notifiable->name}")
            ->line($this->detalle)
            ->line("**Folio:** {$this->ticket->folio}")
            ->line("**Estado actual:** {$etiquetaEstado}")
            ->line("**Descripción:** " . \Str::limit($this->ticket->descripcion, 120))
            ->action('Ver ticket', route('helpdesk.tickets.show', $this->ticket))
            ->line('Este es un mensaje automático del sistema EHS ERP.');
    }

    // ─── Notificación en BD (campana ERP) ───────────────────────────────────
    public function toDatabase(object $notifiable): array
    {
        $icono = match ($this->evento) {
            'estado'      => 'fas fa-exchange-alt',
            'asignacion'  => 'fas fa-user-check',
            'seguimiento' => 'fas fa-comment-dots',
            'edicion'     => 'fas fa-edit',
            default       => 'fas fa-bell',
        };

        return [
            'ticket_id'  => $this->ticket->id,
            'folio'      => $this->ticket->folio,
            'evento'     => $this->evento,
            'detalle'    => $this->detalle,
            'icono'      => $icono,
            'url'        => route('helpdesk.tickets.show', $this->ticket),
        ];
    }
}