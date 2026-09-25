<?php
// app/Notifications/NuevoTicketTelegramNotificacion.php

namespace App\Notifications;

use App\Models\Ticket;
use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Messages\TelegramMessage;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Aviso al grupo de Telegram de Sistemas cuando se registra un ticket.
 *
 * Complementa (no reemplaza) a NuevoTicketNotificacion, que sigue
 * enviando el correo a los administradores.
 *
 * Uso (on-demand, sin usuario destinatario):
 *   Notification::route('telegram', config('helpdesk.telegram.chat_id'))
 *       ->notify(new NuevoTicketTelegramNotificacion($ticket));
 */
class NuevoTicketTelegramNotificacion extends Notification implements ShouldQueue
{
    use Queueable;

    /** Reintentos si Telegram no responde. */
    public int $tries = 3;

    /** Segundos de espera entre reintentos. */
    public array $backoff = [15, 60];

    public function __construct(public Ticket $ticket)
    {
    }

    public function via(object $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $ticket = $this->ticket->loadMissing([
            'solicitante.departamento',
            'tipoFalla',
            'categoriaServicio',
        ]);

        $e = fn (?string $texto) => TelegramService::escapar($texto);

        $iconoPrioridad = match ($ticket->prioridad) {
            'urgente' => '🔴',
            'alta'    => '🟠',
            'media'   => '🔵',
            default   => '⚪',
        };

        $maxDescripcion = (int) config('helpdesk.telegram.max_descripcion', 300);
        $descripcion    = Str::limit((string) $ticket->descripcion, $maxDescripcion);

        $mensaje = TelegramMessage::crear()
            ->linea('🎫 <b>Nuevo ticket · ' . $e($ticket->folio) . '</b>')
            ->espacio()
            ->linea('👤 <b>Solicitante:</b> ' . $e($ticket->solicitante?->name ?? '—'))
            ->linea('🏢 <b>Departamento:</b> ' . $e($ticket->solicitante?->departamento?->nombre ?? '—'))
            ->linea('🛠 <b>Tipo de falla:</b> ' . $e($ticket->tipoFalla?->nombre ?? '—'))
            ->linea('📂 <b>Categoría:</b> ' . $e($ticket->categoriaServicio?->nombre ?? '—'))
            ->linea($iconoPrioridad . ' <b>Prioridad:</b> ' . $e($ticket->prioridad_label));

        if ($ticket->evidencia) {
            $mensaje->linea('📎 Incluye evidencia adjunta');
        }

        $mensaje->espacio()
            ->linea('<i>' . $e($descripcion) . '</i>')
            ->espacio()
            ->linea('🕒 ' . ($ticket->created_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i')))
            ->boton('Ver ticket en el ERP', route('helpdesk.tickets.show', $ticket));

        // Prioridad baja llega sin sonido para no saturar al grupo
        return $mensaje->silencioso($ticket->prioridad === 'baja');
    }
}
