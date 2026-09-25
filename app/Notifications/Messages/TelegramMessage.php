<?php
// app/Notifications/Messages/TelegramMessage.php

namespace App\Notifications\Messages;

use App\Services\TelegramService;

/**
 * Mensaje de Telegram construido por el método toTelegram() de una notificación.
 *
 * Las líneas se escriben en HTML de Telegram (<b>, <i>, <code>, <a>).
 * Todo texto capturado por usuarios debe pasar por TelegramService::escapar().
 */
class TelegramMessage
{
    protected array $lineas = [];

    protected array $botones = [];

    protected bool $silencioso = false;

    public static function crear(): static
    {
        return new static();
    }

    /** Agrega una línea ya formateada en HTML. */
    public function linea(string $html): static
    {
        $this->lineas[] = $html;

        return $this;
    }

    /** Agrega una línea en blanco (separador visual). */
    public function espacio(): static
    {
        $this->lineas[] = '';

        return $this;
    }

    /**
     * Botón con enlace. Si la URL no es aceptada por Telegram
     * (localhost / IP), se agrega como línea de texto.
     */
    public function boton(string $texto, string $url): static
    {
        if (TelegramService::urlValidaParaBoton($url)) {
            $this->botones[] = ['text' => $texto, 'url' => $url];
        } else {
            $this->espacio();
            $this->linea('🔗 ' . TelegramService::escapar($url));
        }

        return $this;
    }

    /** Entrega sin sonido de notificación. */
    public function silencioso(bool $silencioso = true): static
    {
        $this->silencioso = $silencioso;

        return $this;
    }

    public function texto(): string
    {
        return implode("\n", $this->lineas);
    }

    /** Parámetros extra para sendMessage. */
    public function opciones(): array
    {
        $opciones = [];

        if (! empty($this->botones)) {
            // Un botón por fila
            $opciones['reply_markup'] = [
                'inline_keyboard' => array_map(fn ($b) => [$b], $this->botones),
            ];
        }

        if ($this->silencioso) {
            $opciones['disable_notification'] = true;
        }

        return $opciones;
    }
}
