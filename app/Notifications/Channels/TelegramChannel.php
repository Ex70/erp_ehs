<?php
// app/Notifications/Channels/TelegramChannel.php

namespace App\Notifications\Channels;

use App\Notifications\Messages\TelegramMessage;
use App\Services\TelegramService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Canal de notificación "telegram".
 *
 * Se usa devolviendo TelegramChannel::class en via(); Laravel lo resuelve
 * desde el contenedor, así que no requiere registro en un ServiceProvider.
 *
 * Destino:
 *  - Grupo (notificación on-demand):
 *      Notification::route('telegram', $chatId)->notify(...)
 *  - Usuario (a futuro): método routeNotificationForTelegram() en User.
 */
class TelegramChannel
{
    public function __construct(protected TelegramService $telegram)
    {
    }

    public function send(object $notifiable, Notification $notification): void
    {
        if (! $this->telegram->habilitado()) {
            return;
        }

        if (! method_exists($notification, 'toTelegram')) {
            return;
        }

        $chatId = $notifiable->routeNotificationFor('telegram', $notification);

        if (blank($chatId)) {
            return;
        }

        $mensaje = $notification->toTelegram($notifiable);

        if (! $mensaje instanceof TelegramMessage) {
            Log::warning('TelegramChannel: toTelegram() no devolvió un TelegramMessage', [
                'notificacion' => $notification::class,
            ]);

            return;
        }

        $this->telegram->enviarMensaje($chatId, $mensaje->texto(), $mensaje->opciones());
    }
}
