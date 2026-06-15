<?php
// app/Http/Controllers/NotificacionController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    // Marcar una notificación individual como leída
    public function leer(string $id)
    {
        $notif = Auth::user()
            ->unreadNotifications()
            ->where('id', $id)
            ->first();

        if ($notif) {
            $notif->markAsRead();
        }

        return response()->json(['ok' => true]);
    }

    // Marcar todas como leídas
    public function leerTodas()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }
}