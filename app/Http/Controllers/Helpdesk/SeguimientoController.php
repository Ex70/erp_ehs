<?php
namespace App\Http\Controllers\Helpdesk;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketSeguimiento;
use App\Notifications\TicketCerradoNotificacion;
use App\Traits\NotificaTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;   // ← para Str::limit (mejor que el alias global \Str)

class SeguimientoController extends Controller
{
    use NotificaTicket;

    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'estado'     => 'required|in:pendiente,en_atencion,en_desarrollo,en_pruebas,finalizado,escalado',
            'comentario' => 'nullable|string|max:1000',
            'resolucion' => 'nullable|string',
            'archivos'    => 'nullable|array|max:5',
            'archivos.*'  => 'file|max:5120|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt',
        ], [
            'archivos.max'     => 'Máximo 5 archivos por seguimiento.',
            'archivos.*.max'   => 'Cada archivo no debe superar 5 MB.',
            'archivos.*.mimes' => 'Formato no permitido. Usa imágenes, PDF, Word, Excel o texto.',
        ]);

        // Registrar en historial  ← AHORA capturado en $seguimiento
        $seguimiento = TicketSeguimiento::create([
            'ticket_id'  => $ticket->id,
            'user_id'    => Auth::id(),
            'estado'     => $request->estado,
            'comentario' => $request->comentario,
        ]);

        // Guardar archivos adjuntos
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $ruta = $archivo->store("tickets/seguimientos/{$seguimiento->id}", 'public');

                $seguimiento->archivos()->create([
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'ruta'            => $ruta,
                    'mime'            => $archivo->getMimeType(),
                    'tamano'          => $archivo->getSize(),
                    'subido_por'      => Auth::id(),
                ]);
            }
        }

        $update = ['seguimiento' => $request->estado];

        if ($request->estado === 'finalizado') {
            $update['fecha_cierre'] = now();
            $update['resolucion']   = $request->resolucion;

            // Notificar al solicitante
            try {
                $ticket->solicitante->notify(new TicketCerradoNotificacion($ticket));
            } catch (\Exception $e) {
                logger()->error('Error notificando cierre: ' . $e->getMessage());
            }
        } else {
            // Nuevo seguimiento/comentario (sin cerrar): avisar solo al solicitante
            $this->notificarActualizacionTicket(
                $ticket->fresh(),
                'seguimiento',
                $request->filled('comentario')
                    ? "Nuevo comentario en el ticket {$ticket->folio}: " . Str::limit($request->comentario, 100)
                    : "Hay una actualización de seguimiento en el ticket {$ticket->folio}.",
                incluirJefes: false
            );
        }

        $ticket->update($update);

        return back()->with('success', 'Seguimiento actualizado correctamente.');
    }
}