<?php
// app/Console/Commands/TelegramTestCommand.php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Notifications\NuevoTicketTelegramNotificacion;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Pruebas de envío:
 *
 *   php artisan telegram:test
 *       → mensaje simple al grupo de Mesa de Ayuda
 *
 *   php artisan telegram:test --ticket=GQZSIS0042
 *       → notificación real de "nuevo ticket" (por folio o ID), enviada
 *         de inmediato sin pasar por la cola
 *
 *   php artisan telegram:test --chat=-100123456789
 *       → usa otro chat en lugar del configurado
 */
class TelegramTestCommand extends Command
{
    protected $signature = 'telegram:test
                            {--chat= : chat_id destino (por defecto HELPDESK_TELEGRAM_CHAT_ID)}
                            {--ticket= : Folio o ID de un ticket existente para enviar la notificación real}';

    protected $description = 'Envía un mensaje de prueba a Telegram';

    public function handle(TelegramService $telegram): int
    {
        if (! $telegram->habilitado()) {
            $this->error('Telegram está deshabilitado. Revisa TELEGRAM_ENABLED=true y TELEGRAM_BOT_TOKEN en .env (y ejecuta config:clear).');

            return self::FAILURE;
        }

        $chatId = $this->option('chat') ?: config('helpdesk.telegram.chat_id');

        if (blank($chatId)) {
            $this->error('No hay chat destino. Define HELPDESK_TELEGRAM_CHAT_ID o usa --chat=');

            return self::FAILURE;
        }

        try {
            if ($referencia = $this->option('ticket')) {
                $ticket = Ticket::where('folio', $referencia)->first()
                    ?? Ticket::find($referencia);

                if (! $ticket) {
                    $this->error("No existe el ticket {$referencia}.");

                    return self::FAILURE;
                }

                // notifyNow: envío inmediato, ignora ShouldQueue
                Notification::route('telegram', $chatId)
                    ->notifyNow(new NuevoTicketTelegramNotificacion($ticket));

                $this->info("Notificación del ticket {$ticket->folio} enviada al chat {$chatId}.");

                return self::SUCCESS;
            }

            $telegram->enviarMensaje(
                $chatId,
                '✅ <b>Prueba de conexión</b>' . "\n" .
                TelegramService::escapar(config('app.name')) . ' · ' . now()->format('d/m/Y H:i')
            );

            $this->info("Mensaje de prueba enviado al chat {$chatId}.");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Falló el envío: ' . $e->getMessage());
            $this->line('Detalle en storage/logs/laravel.log');

            return self::FAILURE;
        }
    }
}
