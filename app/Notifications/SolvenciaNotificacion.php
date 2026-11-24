<?php

namespace App\Notifications;

use App\Models\SolvenciaExpediente;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso de la cadena de custodia de solvencias (campana + correo encolado).
 * Los canales por evento se definen en config/solvencias.php → notificaciones.
 * El nombre de la clase contiene "Solvencia": la campana toma el estilo
 * config('notificaciones.estilos.Solvencia').
 */
class SolvenciaNotificacion extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [15, 60];

    public function __construct(
        public SolvenciaExpediente $expediente,
        public string $evento,
        public string $mensaje,
        public string $actor
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return config("solvencias.notificaciones.{$this->evento}", ['database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $e = $this->expediente;

        return (new MailMessage)
            ->subject($this->titulo())
            ->greeting("Hola, {$notifiable->name}.")
            ->line($this->mensaje)
            ->line("**Folio:** {$e->folio}")
            ->line('**Monto a pagar:** $' . number_format((float) $e->monto, 2))
            ->line("**Proveedor:** {$e->proveedor}")
            ->line("**Descripción:** {$e->descripcion}")
            ->action(
                in_array($this->evento, ['enviada', 'recordatorio'], true) ? 'Acusar de recibido' : 'Ver expediente',
                route('sol.expedientes.show', $e)   // absoluta para el correo (usa APP_URL)
            )
            ->line('Este es un mensaje automático del sistema EHS ERP.')
            ->salutation('Solvencias económicas — ' . config('app.name'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'tipo'          => 'solvencia',
            'evento'        => $this->evento,
            'expediente_id' => $this->expediente->id,
            'folio'         => $this->expediente->folio,
            'titulo'        => $this->titulo(),
            'mensaje'       => $this->mensaje,
            'icono'         => $this->icono(),
            'color'         => $this->color(),
            'url'           => $this->url(),
        ];
    }

    private function url(): string
    {
        // Relativa para la campana: NotificacionService::urlSegura() la acepta
        // sin depender de APP_URL
        return route('sol.expedientes.show', $this->expediente, false);
    }

    private function titulo(): string
    {
        $f = $this->expediente->folio;

        return match ($this->evento) {
            'enviada'      => "Solvencia {$f} — por acusar de recibido",
            'acuse'        => "Solvencia {$f} — acuse de recibido",
            'recordatorio' => "Solvencia {$f} — recordatorio de acuse",
            'devuelta'     => "Solvencia {$f} — devuelta para corrección",
            'autorizada'   => "Solvencia {$f} — autorizada, pendiente de pago",
            'pagada'       => "Solvencia {$f} — pagada",
            'cancelada'    => "Solvencia {$f} — cancelada",
            default        => "Solvencia {$f}",
        };
    }

    private function icono(): string
    {
        return match ($this->evento) {
            'enviada'      => 'fas fa-inbox',
            'acuse'        => 'fas fa-hand-paper',
            'recordatorio' => 'fas fa-bell',
            'devuelta'     => 'fas fa-undo',
            'autorizada'   => 'fas fa-check-circle',
            'pagada'       => 'fas fa-money-check-alt',
            'cancelada'    => 'fas fa-ban',
            default        => 'fas fa-file-invoice-dollar',
        };
    }

    private function color(): string
    {
        return match ($this->evento) {
            'devuelta', 'cancelada' => 'danger',
            'recordatorio'          => 'warning',
            'autorizada', 'pagada'  => 'success',
            default                 => 'primary',
        };
    }
}
