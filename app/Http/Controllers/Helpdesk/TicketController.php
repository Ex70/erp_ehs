<?php
namespace App\Http\Controllers\Helpdesk;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TipoFalla;
use App\Models\CategoriaServicio;
use App\Models\User;
use App\Notifications\NuevoTicketNotificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        // Administrador/coordinador ve todos — el resto solo los suyos
        $query = Auth::user()->can('tickets.ver.todos')
            ? Ticket::with(['solicitante.puesto', 'tipoFalla', 'categoriaServicio', 'tecnicos'])
            : Ticket::with(['solicitante.puesto', 'tipoFalla', 'categoriaServicio', 'tecnicos'])
                    ->where('user_id', Auth::id());

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('folio', 'like', '%'.$request->q.'%')
                  ->orWhereHas('solicitante', fn($u) =>
                      $u->where('name', 'like', '%'.$request->q.'%')
                  );
            });
        }

        if ($request->filled('q_departamento')) {
            $query->whereHas('solicitante.puesto', fn($q) =>
                $q->where('nombre', 'like', '%'.$request->q_departamento.'%')
            );
        }

        if ($request->filled('q_seguimiento')) {
            $query->where('seguimiento', $request->q_seguimiento);
        }

        if ($request->filled('q_tipo')) {
            $query->where('tipo_falla_id', $request->q_tipo);
        }

        if ($request->filled('q_prioridad')) {
            $query->where('prioridad', $request->q_prioridad);
        }

        $tickets    = $query->orderByDesc('created_at')->paginate(15);
        $tiposFalla = TipoFalla::where('activo', true)->get();
        $estatuses  = Ticket::etiquetasSeguimiento();

        return view('helpdesk.tickets.index', compact(
            'tickets', 'tiposFalla', 'estatuses'
        ));
    }

    public function create()
    {
        $usuario    = Auth::user()->load('puesto');
        $tiposFalla = TipoFalla::where('activo', true)->orderBy('nombre')->get();
        $categorias = CategoriaServicio::where('activo', true)->orderBy('nombre')->get();

        return view('helpdesk.tickets.create', compact(
            'usuario', 'tiposFalla', 'categorias'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_falla_id'        => 'required|exists:tipos_falla,id',
            'categoria_servicio_id'=> 'nullable|exists:categorias_servicio,id',
            'prioridad'            => 'required|in:baja,media,alta,urgente',
            'descripcion'          => 'required|string|min:10',
            'evidencia'            => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ], [
            'tipo_falla_id.required'   => 'El tipo de falla es obligatorio.',
            'prioridad.required'       => 'La prioridad es obligatoria.',
            'descripcion.required'     => 'La descripción es obligatoria.',
            'descripcion.min'          => 'La descripción debe tener al menos 10 caracteres.',
            'evidencia.max'            => 'El archivo no debe superar 5MB.',
        ]);

        $data = [
            'folio'                 => Ticket::generarFolio(),
            'user_id'               => Auth::id(),
            'tipo_falla_id'         => $request->tipo_falla_id,
            'categoria_servicio_id' => $request->categoria_servicio_id,
            'prioridad'             => $request->prioridad,
            'descripcion'           => $request->descripcion,
            'seguimiento'           => 'pendiente',
        ];

        if ($request->hasFile('evidencia')) {
            $data['evidencia'] = $request->file('evidencia')
                                         ->store('helpdesk/evidencias', 'public');
        }

        $ticket = Ticket::create($data);

        // Notificar al jefe de sistemas (rol coordinador + administrador)
        $jefes = User::role(['administrador', 'coordinador'])
                     ->where('activo', true)
                     ->where('id', '!=', Auth::id())
                     ->get();

        foreach ($jefes as $jefe) {
            try {
                $jefe->notify(new NuevoTicketNotificacion($ticket));
            } catch (\Exception $e) {
                logger()->error("Error notificando jefe {$jefe->id}: " . $e->getMessage());
            }
        }

        return redirect()
            ->route('helpdesk.tickets.show', $ticket)
            ->with('success', "Incidencia registrada correctamente. Folio: {$ticket->folio}");
    }

    public function show(Ticket $ticket)
    {
        // Usuario solo puede ver sus propios tickets
        if (!Auth::user()->can('tickets.ver.todos') && $ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $ticket->load([
            'solicitante.puesto',
            'tipoFalla',
            'categoriaServicio',
            'tecnicos',
            'seguimientos.usuario',
        ]);

        $tecnicos = User::role(['administrador', 'coordinador', 'auxiliar'])
                        ->where('activo', true)
                        ->orderBy('name')
                        ->get();

        $estatuses = Ticket::etiquetasSeguimiento();

        return view('helpdesk.tickets.show', compact(
            'ticket', 'tecnicos', 'estatuses'
        ));
    }

    public function edit(Ticket $ticket)
    {
        // Solo admin puede editar cualquier ticket; usuario solo el suyo
        if (!Auth::user()->can('tickets.editar.todos') && $ticket->user_id !== Auth::id()) {
            abort(403);
        }

        // No se puede editar un ticket ya finalizado (solo admin)
        if ($ticket->seguimiento === 'finalizado' && !Auth::user()->can('tickets.editar.todos')) {
            return back()->with('error', 'No puedes editar un ticket ya finalizado.');
        }

        $tiposFalla = TipoFalla::where('activo', true)->orderBy('nombre')->get();
        $categorias = CategoriaServicio::where('activo', true)->orderBy('nombre')->get();

        return view('helpdesk.tickets.edit', compact('ticket', 'tiposFalla', 'categorias'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        if (!Auth::user()->can('tickets.editar.todos') && $ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'tipo_falla_id'         => 'required|exists:tipos_falla,id',
            'categoria_servicio_id' => 'nullable|exists:categorias_servicio,id',
            'prioridad'             => 'required|in:baja,media,alta,urgente',
            'descripcion'           => 'required|string|min:10',
            'evidencia'             => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $data = [
            'tipo_falla_id'         => $request->tipo_falla_id,
            'categoria_servicio_id' => $request->categoria_servicio_id,
            'prioridad'             => $request->prioridad,
            'descripcion'           => $request->descripcion,
        ];

        // Solo admin puede cambiar el seguimiento
        if (Auth::user()->can('tickets.editar.todos') && $request->filled('seguimiento')) {
            $data['seguimiento'] = $request->seguimiento;

            if ($request->seguimiento === 'finalizado') {
                $data['fecha_cierre'] = now();
                $data['resolucion']   = $request->resolucion;

                // Notificar al solicitante
                try {
                    $ticket->solicitante->notify(new \App\Notifications\TicketCerradoNotificacion($ticket));
                } catch (\Exception $e) {
                    logger()->error('Error notificando cierre: ' . $e->getMessage());
                }
            }
        }

        if ($request->hasFile('evidencia')) {
            if ($ticket->evidencia) {
                Storage::disk('public')->delete($ticket->evidencia);
            }
            $data['evidencia'] = $request->file('evidencia')
                                         ->store('helpdesk/evidencias', 'public');
        }

        $ticket->update($data);

        return redirect()
            ->route('helpdesk.tickets.show', $ticket)
            ->with('success', 'Ticket actualizado correctamente.');
    }

    public function destroy(Ticket $ticket)
    {
        if ($ticket->evidencia) {
            Storage::disk('public')->delete($ticket->evidencia);
        }

        $ticket->delete();

        return redirect()
            ->route('helpdesk.tickets.index')
            ->with('success', "Ticket {$ticket->folio} eliminado correctamente.");
    }
}