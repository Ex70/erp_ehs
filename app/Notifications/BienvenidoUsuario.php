<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BienvenidoUsuario extends Notification
{
    public function __construct(
        protected string $passwordTemporal,
        protected string $urlRegistro
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenido a ' . config('app.name') . ' — Completa tu registro')
            ->greeting('Hola, ' . $notifiable->name . '.')
            ->line('Tu cuenta en **' . config('app.name') . '** ha sido creada correctamente.')
            ->line('---')
            ->line('**Tus datos de acceso:**')
            ->line('**Usuario:** ' . $notifiable->username)
            ->line('**Correo:** ' . $notifiable->email)
            ->line('**Contraseña temporal:** ' . $this->passwordTemporal)
            ->line('---')
            ->line('Para activar tu cuenta y establecer tu contraseña definitiva, haz clic en el botón:')
            ->action('Completar mi registro', $this->urlRegistro)
            ->line('Este enlace es válido por **72 horas**.')
            ->line('Si no solicitaste esta cuenta, puedes ignorar este mensaje.')
            ->salutation('Saludos, ' . config('app.name'));
    }
}