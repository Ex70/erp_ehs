<?php

namespace App\Providers;

use App\Services\NotificacionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class NotificacionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('partials.notificaciones-dropdown', function ($view) {
            $usuario = Auth::user();

            if (! $usuario) {
                $view->with('notiItems', collect())->with('notiTotal', 0);

                return;
            }

            $limite = (int) config('notificaciones.dropdown_limite', 6);

            $items = $usuario->unreadNotifications()
                ->latest()
                ->limit($limite)
                ->get()
                ->map(fn ($n) => NotificacionService::presentar($n));

            $view->with('notiItems', $items)
                ->with('notiTotal', $usuario->unreadNotifications()->count());
        });
    }
}