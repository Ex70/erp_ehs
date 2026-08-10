<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\Comunicado;
use App\Models\Departamento;
use App\Models\User;
use App\Notifications\ComunicadoPublicadoNotificacion;
use App\Services\ComunicadoDestinatarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class ComunicadoController extends Controller
{
    public function __construct(
        private ComunicadoDestinatarioService $destinatarios
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('comunicados.ver');

        $query = Comunicado::query()->orderByDesc('fecha_publicacion');

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('titulo', 'like', '%' . $request->buscar . '%')
                  ->orWhere('extracto', 'like', '%' . $request->buscar . '%');
            });
        }

        $comunicados = $query->get();
        $categorias  = Comunicado::categorias();

        // Catálogos para el selector de destinatarios
        $departamentos = Departamento::orderBy('nombre')->get(['id', 'nombre']);

        $usuarios = User::where('activo', 1)
            ->with('departamento:id,nombre')
            ->orderBy('name')
            ->get(['id', 'name', 'departamento_id']);

        return view('rrhh.comunicados.index', compact(
            'comunicados', 'categorias', 'departamentos', 'usuarios'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('comunicados.crear');

        $data = $request->validate([
            'titulo'             => 'required|string|max:255',
            'categoria'          => 'required|in:' . implode(',', Comunicado::categorias()),
            'icono_emoji'        => 'nullable|string|max:10',
            'color_fondo'        => 'nullable|string|max:7',
            'fecha_publicacion'  => 'required|date',
            'autor'              => 'required|string|max:100',
            'extracto'           => 'nullable|string|max:500',
            'contenido_completo' => 'nullable|string',
            'archivo'            => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120',
            'alcance'            => 'required|in:todos,segmentado',
            'departamentos'      => 'nullable|array',
            'departamentos.*'    => 'integer|exists:departamentos,id',
            'usuarios'           => 'nullable|array',
            'usuarios.*'         => 'integer|exists:users,id',
            'excluidos'          => 'nullable|array',
            'excluidos.*'        => 'integer|exists:users,id',
        ]);

        if ($request->hasFile('archivo')) {
            $data['archivo'] = $request->file('archivo')->store('comunicados', 'public');
        }

        $data['user_id']     = Auth::id();
        $data['icono_emoji'] = $data['icono_emoji'] ?? Comunicado::emojiPorCategoria($data['categoria']);
        $data['color_fondo'] = $data['color_fondo'] ?? Comunicado::colorPorCategoria($data['categoria']);

        $payload = collect($data)->except(['departamentos', 'usuarios', 'excluidos'])->all();

        $comunicado = DB::transaction(function () use ($payload, $request) {
            $comunicado = Comunicado::create($payload);
            $this->sincronizarDestinatarios($comunicado, $request);
            return $comunicado;
        });

        // Notificación inmediata al publicar (encolada: database + mail)
        $usuarios = $this->destinatarios->resolver($comunicado);

        if ($usuarios->isNotEmpty()) {
            Notification::send($usuarios, new ComunicadoPublicadoNotificacion($comunicado));

            $comunicado->forceFill([
                'notificado_en'     => now(),
                'notificados_count' => $usuarios->count(),
            ])->save();

            $msg = 'Comunicado publicado. Se notificó a ' . $usuarios->count() . ' persona(s).';
        } else {
            $msg = 'Comunicado publicado, pero no se encontraron destinatarios para notificar.';
        }

        return redirect()->route('rrhh.comunicados.index')->with('success', $msg);
    }

    public function show(Comunicado $comunicado)
    {
        $this->authorize('comunicados.ver');

        $payload = $comunicado->toArray();

        $payload['alcance_label']   = $this->destinatarios->etiqueta($comunicado);
        $payload['departamentos_ids'] = $comunicado->departamentos()->pluck('departamentos.id');
        $payload['usuarios_ids']      = $comunicado->usuariosIncluidos()->pluck('users.id');
        $payload['excluidos_ids']     = $comunicado->usuariosExcluidos()->pluck('users.id');

        return response()->json($payload);
    }

    public function update(Request $request, Comunicado $comunicado)
    {
        $this->authorize('comunicados.editar');

        $data = $request->validate([
            'titulo'             => 'required|string|max:255',
            'categoria'          => 'required|in:' . implode(',', Comunicado::categorias()),
            'icono_emoji'        => 'nullable|string|max:10',
            'color_fondo'        => 'nullable|string|max:7',
            'fecha_publicacion'  => 'nullable|date',
            'autor'              => 'required|string|max:100',
            'extracto'           => 'nullable|string|max:500',
            'contenido_completo' => 'nullable|string',
            'archivo'            => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120',
            'alcance'            => 'required|in:todos,segmentado',
            'departamentos'      => 'nullable|array',
            'departamentos.*'    => 'integer|exists:departamentos,id',
            'usuarios'           => 'nullable|array',
            'usuarios.*'         => 'integer|exists:users,id',
            'excluidos'          => 'nullable|array',
            'excluidos.*'        => 'integer|exists:users,id',
        ]);

        // Manejar archivo nuevo
        if ($request->hasFile('archivo')) {
            if ($comunicado->archivo) {
                Storage::disk('public')->delete($comunicado->archivo);
            }
            $data['archivo'] = $request->file('archivo')->store('comunicados', 'public');
        } else {
            unset($data['archivo']);
        }

        // Si no llega fecha, conservar la existente
        if (empty($data['fecha_publicacion'])) {
            unset($data['fecha_publicacion']);
        }

        $payload = collect($data)->except(['departamentos', 'usuarios', 'excluidos'])->all();

        DB::transaction(function () use ($comunicado, $payload, $request) {
            $comunicado->update($payload);
            $this->sincronizarDestinatarios($comunicado, $request);
        });

        // Nota: la edición NO vuelve a notificar (evita spam a quien ya recibió el aviso).

        return redirect()->route('rrhh.comunicados.index')
            ->with('success', 'Comunicado actualizado correctamente.');
    }

    public function destroy(Comunicado $comunicado)
    {
        $this->authorize('comunicados.eliminar');

        if ($comunicado->archivo) {
            Storage::disk('public')->delete($comunicado->archivo);
        }

        $comunicado->delete();

        return redirect()->route('rrhh.comunicados.index')
            ->with('success', 'Comunicado eliminado correctamente.');
    }

    // Retorna los valores por defecto de emoji y color al cambiar categoría (AJAX)
    public function defaults(Request $request)
    {
        $categoria = $request->categoria;

        return response()->json([
            'emoji' => Comunicado::emojiPorCategoria($categoria),
            'color' => Comunicado::colorPorCategoria($categoria),
        ]);
    }

    /**
     * Contador en vivo de destinatarios para el modal (AJAX).
     */
    public function previewDestinatarios(Request $request)
    {
        $this->authorize('comunicados.crear');

        $usuarios = $this->destinatarios->resolverPorCriterios(
            $request->input('alcance') === Comunicado::ALCANCE_SEGMENTADO,
            $request->input('departamentos', []),
            $request->input('usuarios', []),
            $request->input('excluidos', []),
            Auth::id()
        );

        return response()->json([
            'total'   => $usuarios->count(),
            'nombres' => $usuarios->take(8)->pluck('name'),
        ]);
    }

    /**
     * Persiste departamentos, usuarios incluidos y excluidos.
     * La exclusión siempre gana sobre la inclusión.
     */
    private function sincronizarDestinatarios(Comunicado $comunicado, Request $request): void
    {
        $segmentado = $request->input('alcance') === Comunicado::ALCANCE_SEGMENTADO;

        $excluidos = collect($request->input('excluidos', []))
            ->map(fn ($id) => (int) $id)->unique();

        $incluidos = $segmentado
            ? collect($request->input('usuarios', []))
                ->map(fn ($id) => (int) $id)->unique()
                ->reject(fn ($id) => $excluidos->contains($id))
            : collect();

        $comunicado->departamentos()->sync(
            $segmentado ? $request->input('departamentos', []) : []
        );

        DB::table('comunicado_user')->where('comunicado_id', $comunicado->id)->delete();

        $filas = [];
        $now   = now();

        foreach ($incluidos as $id) {
            $filas[] = ['comunicado_id' => $comunicado->id, 'user_id' => $id,
                        'tipo' => 'incluido', 'created_at' => $now, 'updated_at' => $now];
        }
        foreach ($excluidos as $id) {
            $filas[] = ['comunicado_id' => $comunicado->id, 'user_id' => $id,
                        'tipo' => 'excluido', 'created_at' => $now, 'updated_at' => $now];
        }

        if ($filas) {
            DB::table('comunicado_user')->insert($filas);
        }
    }
}