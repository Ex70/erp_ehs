<?php

namespace App\Http\Controllers;

use App\Services\NotificacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    /**
     * Bandeja de notificaciones del usuario autenticado.
     */
    public function index(Request $request): View
    {
        $usuario = $request->user();

        $estado = in_array($request->query('estado'), ['no_leidas', 'leidas'], true)
            ? $request->query('estado')
            : 'todas';

        $consulta = match ($estado) {
            'no_leidas' => $usuario->unreadNotifications(),
            'leidas'    => $usuario->readNotifications(),
            default     => $usuario->notifications(),
        };

        $notificaciones = $consulta->paginate(config('notificaciones.por_pagina', 15))->withQueryString();

        $notificaciones->setCollection(
            $notificaciones->getCollection()->map(fn ($n) => NotificacionService::presentar($n))
        );

        return view('notificaciones.index', [
            'notificaciones' => $notificaciones,
            'estado'         => $estado,
            'totalNoLeidas'  => $usuario->unreadNotifications()->count(),
            'totalGeneral'   => $usuario->notifications()->count(),
        ]);
    }

    /**
     * Marca como leída y redirige al recurso relacionado.
     */
    public function ver(Request $request, string $id): RedirectResponse
    {
        $notificacion = $request->user()->notifications()->findOrFail($id);

        if (is_null($notificacion->read_at)) {
            $notificacion->markAsRead();
        }

        $destino = NotificacionService::presentar($notificacion)['url'];

        return redirect($destino ?: route('notificaciones.index'));
    }

    public function leer(Request $request, string $id): RedirectResponse
    {
        $notificacion = $request->user()->notifications()->findOrFail($id);
        $notificacion->markAsRead();

        return back()->with('success', 'Notificación marcada como leída.');
    }

    public function noLeer(Request $request, string $id): RedirectResponse
    {
        $notificacion = $request->user()->notifications()->findOrFail($id);
        $notificacion->markAsUnread();

        return back()->with('success', 'Notificación marcada como no leída.');
    }

    public function leerTodas(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Todas las notificaciones fueron marcadas como leídas.');
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return back()->with('success', 'Notificación eliminada.');
    }

    public function limpiarLeidas(Request $request): RedirectResponse
    {
        $eliminadas = $request->user()->readNotifications()->delete();

        return back()->with('success', "Se eliminaron {$eliminadas} notificaciones leídas.");
    }

    /**
     * Endpoint GET para refrescar el badge de la campana (sin CSRF).
     */
    public function contador(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $limite = (int) config('notificaciones.dropdown_limite', 6);

        $items = $usuario->unreadNotifications()
            ->latest()
            ->limit($limite)
            ->get()
            ->map(function ($n) {
                $d = NotificacionService::presentar($n);

                return [
                    'id'      => $d['id'],
                    'titulo'  => $d['titulo'],
                    'mensaje' => $d['mensaje'],
                    'icono'   => $d['icono'],
                    'color'   => $d['color'],
                    'fecha'   => $d['fecha_humana'],
                    'url'     => route('notificaciones.ver', $d['id']),
                ];
            })
            ->values();

        return response()->json([
            'total' => $usuario->unreadNotifications()->count(),
            'items' => $items,
        ]);
    }
}