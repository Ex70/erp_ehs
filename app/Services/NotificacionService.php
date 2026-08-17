<?php

namespace App\Services;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class NotificacionService
{
    /**
     * Normaliza una notificación de base de datos a un arreglo listo para la vista.
     * Soporta distintos formatos de payload (español / inglés) para no romper
     * las notificaciones ya existentes en el sistema.
     */
    public static function presentar(DatabaseNotification $notificacion): array
    {
        $data = self::datos($notificacion);
        $estilo = self::estilo($notificacion->type);

        $titulo = $data['titulo']
            ?? $data['title']
            ?? $data['asunto']
            ?? self::tituloPorTipo($notificacion->type);

        $mensaje = $data['mensaje']
            ?? $data['message']
            ?? $data['cuerpo']
            ?? $data['body']
            ?? $data['descripcion']
            ?? null;

        $url = $data['url']
            ?? $data['action_url']
            ?? $data['enlace']
            ?? $data['link']
            ?? null;

        return [
            'id'            => $notificacion->id,
            'titulo'        => Str::limit(trim(strip_tags((string) $titulo)), 90),
            'mensaje'       => $mensaje ? Str::limit(trim(strip_tags((string) $mensaje)), 180) : null,
            'url'           => self::urlSegura($url),
            'icono'         => $data['icono'] ?? $data['icon'] ?? $estilo['icono'],
            'color'         => $data['color'] ?? $estilo['color'],
            'leida'         => ! is_null($notificacion->read_at),
            'fecha'         => $notificacion->created_at,
            'fecha_corta'   => optional($notificacion->created_at)->format('d/m/Y H:i'),
            'fecha_humana'  => optional($notificacion->created_at)->diffForHumans(),
            'tipo'          => class_basename($notificacion->type),
        ];
    }

    public static function datos(DatabaseNotification $notificacion): array
    {
        $data = $notificacion->data;

        if (is_string($data)) {
            $data = json_decode($data, true) ?: [];
        }

        return is_array($data) ? $data : [];
    }

    protected static function estilo(string $tipo): array
    {
        foreach ((array) config('notificaciones.estilos', []) as $clave => $estilo) {
            if (Str::contains($tipo, $clave)) {
                return array_merge(config('notificaciones.estilo_por_defecto'), $estilo);
            }
        }

        return config('notificaciones.estilo_por_defecto', ['icono' => 'far fa-bell', 'color' => 'primary']);
    }

    protected static function tituloPorTipo(string $tipo): string
    {
        return Str::of(class_basename($tipo))
            ->replace(['Notificacion', 'Notification'], '')
            ->snake(' ')
            ->replace('_', ' ')
            ->title()
            ->trim()
            ->whenEmpty(fn () => Str::of('Notificación'))
            ->toString();
    }

    /**
     * Evita redirecciones a dominios externos inyectados en el payload.
     */
    protected static function urlSegura(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        if (Str::startsWith($url, '/')) {
            return $url;
        }

        if (Str::startsWith($url, url('/'))) {
            return $url;
        }

        return null;
    }
}