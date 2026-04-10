<?php
namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketCerradoNotificacion extends Notification
{
    public function __construct(protected Ticket $ticket) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('helpdesk.tickets.show', $this->ticket);

        return (new MailMessage)
            ->subject("Tu ticket ha sido resuelto — {$this->ticket->folio}")
            ->greeting("Hola, {$notifiable->name}.")
            ->line("Tu ticket de soporte ha sido marcado como **Finalizado**.")
            ->line("**Folio:** {$this->ticket->folio}")
            ->line("**Resolución:** " . ($this->ticket->resolucion ?? 'Ver detalle en el sistema'))
            ->action('Ver detalle y calificar', $url)
            ->line('Te invitamos a calificar la atención recibida desde el enlace anterior.')
            ->salutation('Sistema de Mesa de Ayuda — ' . config('app.name'));
    }
}