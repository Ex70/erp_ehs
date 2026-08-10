<?php

namespace App\Notifications;

use App\Models\Comunicado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ComunicadoPublicadoNotificacion extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Comunicado $comunicado)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('rrhh/comunicados') . '?ver=' . $this->comunicado->id;

        $resumen = $this->comunicado->extracto
            ?: Str::limit(strip_tags($this->comunicado->contenido_completo ?? ''), 180);

        return (new MailMessage)
            ->subject('Nuevo comunicado: ' . $this->comunicado->titulo)
            ->greeting('Hola ' . ($notifiable->name ?? '') . ',')
            ->line('Capital Humano publicó un nuevo comunicado.')
            ->line('**' . $this->comunicado->titulo . '**')
            ->lineIf((bool) $resumen, $resumen)
            ->action('Ver comunicado', $url)
            ->line('Publicado por ' . $this->comunicado->autor . '.')
            ->salutation('EHS Tecnologías — ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo'          => 'comunicado',
            'comunicado_id' => $this->comunicado->id,
            'titulo'        => $this->comunicado->titulo,
            'categoria'     => $this->comunicado->categoria,
            'mensaje'       => 'Nuevo comunicado publicado por ' . $this->comunicado->autor,
            'icono'         => $this->comunicado->icono_emoji ?: '📢',
            'color'         => $this->comunicado->color_fondo ?: '#E43022',
            'url'           => url('rrhh/comunicados') . '?ver=' . $this->comunicado->id,
        ];
    }
}