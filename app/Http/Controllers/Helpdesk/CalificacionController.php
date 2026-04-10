<?php
namespace App\Http\Controllers\Helpdesk;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalificacionController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        // Solo el solicitante puede calificar su propio ticket cerrado
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        if ($ticket->seguimiento !== 'finalizado') {
            return back()->with('error', 'Solo puedes calificar tickets finalizados.');
        }

        if ($ticket->calificacion > 0) {
            return back()->with('info', 'Ya calificaste este ticket.');
        }

        $request->validate([
            'calificacion'            => 'required|integer|min:1|max:5',
            'comentario_calificacion' => 'nullable|string|max:500',
        ]);

        $ticket->update([
            'calificacion'            => $request->calificacion,
            'comentario_calificacion' => $request->comentario_calificacion,
        ]);

        return back()->with('success', '¡Gracias por tu calificación!');
    }
}