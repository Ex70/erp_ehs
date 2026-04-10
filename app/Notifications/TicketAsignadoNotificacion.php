<?php
namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketAsignadoNotificacion extends Notification
{
    public function __construct(protected Ticket $ticket) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('helpdesk.tickets.show', $this->ticket);

        return (new MailMessage)
            ->subject("Ticket asignado — {$this->ticket->folio}")
            ->greeting("Hola, {$notifiable->name}.")
            ->line("Se te ha asignado el siguiente ticket de soporte técnico.")
            ->line("**Folio:** {$this->ticket->folio}")
            ->line("**Solicitante:** {$this->ticket->solicitante->name}")
            ->line("**Tipo de falla:** {$this->ticket->tipoFalla?->nombre ?? '—'}")
            ->line("**Prioridad:** " . ucfirst($this->ticket->prioridad))
            ->line("**Descripción:** {$this->ticket->descripcion}")
            ->action('Atender ticket', $url)
            ->line('Por favor atiende este ticket a la brevedad posible.')
            ->salutation('Sistema de Mesa de Ayuda — ' . config('app.name'));
    }
}