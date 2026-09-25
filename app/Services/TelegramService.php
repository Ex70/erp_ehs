<?php
// app/Services/TelegramService.php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Cliente mínimo de la Bot API de Telegram.
 *
 * - Nunca registra el token en logs.
 * - Lanza RuntimeException ante cualquier fallo para que la cola reintente
 *   (el controlador que despacha ya envuelve la llamada en try/catch).
 */
class TelegramService
{
    /**
     * Interruptor general + token presente.
     */
    public function habilitado(): bool
    {
        return (bool) config('services.telegram.enabled')
            && filled(config('services.telegram.token'));
    }

    /**
     * Envía un mensaje de texto con formato HTML.
     *
     * @param  array  $opciones  Parámetros extra de sendMessage (reply_markup, disable_notification, etc.)
     */
    public function enviarMensaje(string|int $chatId, string $html, array $opciones = []): array
    {
        $payload = array_merge([
            'chat_id'              => $chatId,
            'text'                 => $html,
            'parse_mode'           => 'HTML',
            'link_preview_options' => ['is_disabled' => true],
        ], $opciones);

        return $this->llamar('sendMessage', $payload);
    }

    /**
     * Datos del bot (sirve para validar que el token es correcto).
     */
    public function obtenerBot(): array
    {
        return $this->llamar('getMe')['result'] ?? [];
    }

    /**
     * Últimas actualizaciones recibidas por el bot (para descubrir chat IDs).
     * No funciona si el bot tiene un webhook configurado.
     */
    public function obtenerActualizaciones(): array
    {
        return $this->llamar('getUpdates', [
            'limit'           => 100,
            'allowed_updates' => ['message', 'channel_post', 'my_chat_member'],
        ])['result'] ?? [];
    }

    /**
     * Escapa texto libre para parse_mode=HTML.
     * Obligatorio para todo dato capturado por usuarios (descripciones, nombres).
     */
    public static function escapar(?string $texto): string
    {
        return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Telegram rechaza botones con URL a localhost o IPs locales
     * ("Wrong HTTP URL"). En local el enlace se manda como texto.
     */
    public static function urlValidaParaBoton(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! $host || ! str_contains($host, '.')) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return false;
        }

        return str_starts_with($url, 'https://') || str_starts_with($url, 'http://');
    }

    // ─── Interno ────────────────────────────────────────────────────────────

    protected function llamar(string $metodo, array $payload = []): array
    {
        $token = config('services.telegram.token');

        if (blank($token)) {
            throw new RuntimeException('Telegram: TELEGRAM_BOT_TOKEN no está configurado.');
        }

        $url = rtrim(config('services.telegram.api_url'), '/') . "/bot{$token}/{$metodo}";

        try {
            $cliente = Http::timeout(config('services.telegram.timeout', 10))->acceptJson();

            // Métodos sin parámetros (getMe) van por GET; el resto como JSON
            $respuesta = empty($payload)
                ? $cliente->get($url)
                : $cliente->asJson()->post($url, $payload);
        } catch (ConnectionException $e) {
            Log::error("Telegram: sin conexión al llamar {$metodo}", [
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException("Telegram: sin conexión ({$metodo}).", 0, $e);
        }

        $json = $respuesta->json() ?? [];

        if ($respuesta->successful() && ($json['ok'] ?? false)) {
            return $json;
        }

        $descripcion = $json['description'] ?? 'Respuesta no válida de Telegram';

        $contexto = [
            'metodo'  => $metodo,
            'status'  => $respuesta->status(),
            'codigo'  => $json['error_code'] ?? null,
            'detalle' => $descripcion,
            'chat_id' => $payload['chat_id'] ?? null,
        ];

        // Cuando un grupo se convierte en supergrupo, su ID cambia.
        // Telegram informa el nuevo ID: hay que actualizar el .env.
        if (isset($json['parameters']['migrate_to_chat_id'])) {
            $contexto['nuevo_chat_id'] = $json['parameters']['migrate_to_chat_id'];
            $contexto['accion'] = 'Actualiza HELPDESK_TELEGRAM_CHAT_ID con nuevo_chat_id';
        }

        Log::error("Telegram: error en {$metodo}", $contexto);

        throw new RuntimeException("Telegram ({$metodo}): {$descripcion}", (int) ($json['error_code'] ?? $respuesta->status()));
    }
}
