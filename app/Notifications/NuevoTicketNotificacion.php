<?php
namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NuevoTicketNotificacion extends Notification
{
    public function __construct(protected Ticket $ticket) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('helpdesk.tickets.show', $this->ticket);

        return (new MailMessage)
            ->subject("Nuevo ticket de soporte — {$this->ticket->folio}")
            ->greeting("Hola, {$notifiable->name}.")
            ->line("Se ha registrado un nuevo ticket de incidencia que requiere asignación.")
            ->line("**Folio:** {$this->ticket->folio}")
            ->line("**Solicitante:** {$this->ticket->solicitante->name}")
            ->line("**Departamento:** {$this->ticket->solicitante->puesto?->nombre ?? '—'}")
            ->line("**Tipo de falla:** {$this->ticket->tipoFalla?->nombre ?? '—'}")
            ->line("**Prioridad:** " . ucfirst($this->ticket->prioridad))
            ->line("**Descripción:** {$this->ticket->descripcion}")
            ->action('Ver ticket', $url)
            ->line('Por favor asigna el ticket a un técnico para su atención.')
            ->salutation('Sistema de Mesa de Ayuda — ' . config('app.name'));
    }
}