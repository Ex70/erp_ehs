<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\Comunicado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ComunicadoController extends Controller
{
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

        $comunicados  = $query->get();
        $categorias   = Comunicado::categorias();

        return view('rrhh.comunicados.index', compact('comunicados', 'categorias'));
    }

    public function store(Request $request)
    {
        $this->authorize('comunicados.crear');

        $data = $request->validate([
            'titulo'            => 'required|string|max:255',
            'categoria'         => 'required|in:' . implode(',', Comunicado::categorias()),
            'icono_emoji'       => 'nullable|string|max:10',
            'color_fondo'       => 'nullable|string|max:7',
            'fecha_publicacion' => 'required|date',
            'autor'             => 'required|string|max:100',
            'extracto'          => 'nullable|string|max:500',
            'contenido_completo'=> 'nullable|string',
            'archivo'           => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120',
        ]);

        if ($request->hasFile('archivo')) {
            $data['archivo'] = $request->file('archivo')
                ->store('comunicados', 'public');
        }

        $data['user_id']      = Auth::id();
        $data['icono_emoji']  = $data['icono_emoji']  ?? Comunicado::emojiPorCategoria($data['categoria']);
        $data['color_fondo']  = $data['color_fondo']  ?? Comunicado::colorPorCategoria($data['categoria']);

        Comunicado::create($data);

        return redirect()->route('rrhh.comunicados.index')->with('success', 'Comunicado publicado correctamente.');
    }

    public function show(Comunicado $comunicado)
    {
        $this->authorize('comunicados.ver');
        return response()->json($comunicado);
    }

    public function update(Request $request, Comunicado $comunicado)
    {
        $this->authorize('comunicados.editar');

        $data = $request->validate([
            'titulo'            => 'required|string|max:255',
            'categoria'         => 'required|in:' . implode(',', Comunicado::categorias()),
            'icono_emoji'       => 'nullable|string|max:10',
            'color_fondo'       => 'nullable|string|max:7',
            'fecha_publicacion' => 'required|date',
            'autor'             => 'required|string|max:100',
            'extracto'          => 'nullable|string|max:500',
            'contenido_completo'=> 'nullable|string',
            'archivo'           => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120',
        ]);

        if ($request->hasFile('archivo')) {
            // Eliminar archivo anterior
            if ($comunicado->archivo) {
                Storage::disk('public')->delete($comunicado->archivo);
            }
            $data['archivo'] = $request->file('archivo')
                ->store('comunicados', 'public');
        }

        $comunicado->update($data);

        return redirect()->route('rrhh.comunicados.index')->with('success', 'Comunicado actualizado correctamente.');
    }

    public function destroy(Comunicado $comunicado)
    {
        $this->authorize('comunicados.eliminar');

        if ($comunicado->archivo) {
            Storage::disk('public')->delete($comunicado->archivo);
        }

        $comunicado->delete();

        return redirect()->route('rrhh.comunicados.index')->with('success', 'Comunicado eliminado correctamente.');
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
}