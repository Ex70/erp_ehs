<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BienvenidoUsuario extends Notification
{
    public function __construct(
        protected string $passwordTemporal
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenido a ' . config('app.name'))
            ->greeting('Hola, ' . $notifiable->name . '.')
            ->line('Tu cuenta en **' . config('app.name') . '** ha sido creada exitosamente.')
            ->line('Estos son tus datos de acceso:')
            ->line('**Usuario:** ' . $notifiable->username)
            ->line('**Contraseña:** ' . $this->passwordTemporal)
            ->line('**Correo:** ' . $notifiable->email)
            ->action('Acceder al sistema', url('/'))
            ->line('Por seguridad, te recomendamos cambiar tu contraseña al iniciar sesión por primera vez.')
            ->salutation('Saludos, ' . config('app.name'));
    }
}