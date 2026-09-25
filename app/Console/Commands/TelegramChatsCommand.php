<?php
// app/Console/Commands/TelegramChatsCommand.php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Lista los chats donde el bot ha recibido actividad, para obtener
 * el chat_id sin exponer el token en el navegador.
 *
 * Antes de ejecutarlo: agrega el bot al grupo y escribe en el grupo
 * /start@NombreDeTuBot (con el modo privacidad activo, el bot solo
 * "ve" comandos que lo mencionan).
 */
class TelegramChatsCommand extends Command
{
    protected $signature = 'telegram:chats';

    protected $description = 'Valida el token del bot y lista los chat IDs recientes (grupos y privados)';

    public function handle(TelegramService $telegram): int
    {
        try {
            $bot = $telegram->obtenerBot();
        } catch (Throwable $e) {
            $this->error('No se pudo validar el token: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info("Bot conectado: @{$bot['username']} ({$bot['first_name']})");

        try {
            $actualizaciones = $telegram->obtenerActualizaciones();
        } catch (Throwable $e) {
            $this->error('No se pudieron leer las actualizaciones: ' . $e->getMessage());
            $this->line('Si el error menciona "webhook", el bot tiene un webhook activo y getUpdates no está disponible.');

            return self::FAILURE;
        }

        $chats = collect($actualizaciones)
            ->map(fn (array $u) => $u['message']['chat']
                ?? $u['channel_post']['chat']
                ?? $u['my_chat_member']['chat']
                ?? null)
            ->filter()
            ->unique('id')
            ->values();

        if ($chats->isEmpty()) {
            $this->warn('No hay actividad reciente.');
            $this->line("Escribe /start@{$bot['username']} en el grupo y vuelve a ejecutar este comando.");

            return self::SUCCESS;
        }

        $this->table(
            ['chat_id', 'Tipo', 'Nombre'],
            $chats->map(fn (array $c) => [
                $c['id'],
                $c['type'],
                $c['title'] ?? trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')),
            ])->all()
        );

        $this->line('Copia el chat_id del grupo en HELPDESK_TELEGRAM_CHAT_ID (incluye el signo menos).');

        return self::SUCCESS;
    }
}
