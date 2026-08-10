<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\CulturaItem;
use App\Models\CulturaSeccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CulturaController extends Controller
{
    /**
     * Vista pública del módulo (todos los colaboradores con permiso de lectura).
     */
    public function index()
    {
        $secciones = CulturaSeccion::orderBy('orden')->get()->keyBy('clave');

        // Evita accesos a índices inexistentes en la vista si aún no se sembró todo.
        $titulosPorDefecto = [
            CulturaSeccion::CLAVE_PRESENTACION => 'Quiénes Somos',
            CulturaSeccion::CLAVE_MISION       => 'Misión',
            CulturaSeccion::CLAVE_VISION       => 'Visión',
            CulturaSeccion::CLAVE_HISTORIA     => 'Nuestra Historia',
            CulturaSeccion::CLAVE_OBJETIVOS    => 'Objetivos Estratégicos',
        ];

        foreach ($titulosPorDefecto as $clave => $titulo) {
            if (! $secciones->has($clave)) {
                $secciones->put($clave, new CulturaSeccion(['clave' => $clave, 'titulo' => $titulo]));
            }
        }

        $slides    = CulturaItem::deTipo(CulturaItem::TIPO_SLIDE)->activos()->ordenados()->get();
        $valores   = CulturaItem::deTipo(CulturaItem::TIPO_VALOR)->activos()->ordenados()->get();
        $objetivos = CulturaItem::deTipo(CulturaItem::TIPO_OBJETIVO)->activos()->ordenados()->get();
        $empresas  = CulturaItem::deTipo(CulturaItem::TIPO_EMPRESA)->activos()->ordenados()->get();

        // Para el modal de edición se cargan también los inactivos.
        $slidesAdmin = CulturaItem::deTipo(CulturaItem::TIPO_SLIDE)->ordenados()->get();

        return view('rrhh.cultura.index', compact(
            'secciones', 'slides', 'valores', 'objetivos', 'empresas', 'slidesAdmin'
        ));
    }

    /* ═════════════ SECCIONES DE TEXTO ═════════════ */

    public function actualizarSeccion(Request $request, string $clave): JsonResponse
    {
        abort_unless(in_array($clave, CulturaSeccion::CLAVES, true), 404);

        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'texto'  => ['nullable', 'string', 'max:5000'],
            'icono'  => ['nullable', 'string', 'max:16'],
        ], [], [
            'titulo' => 'título de sección',
            'texto'  => 'texto',
        ]);

        $seccion = CulturaSeccion::updateOrCreate(
            ['clave' => $clave],
            $datos + ['actualizado_por' => $request->user()->id]
        );

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Sección actualizada correctamente.',
            'seccion' => $seccion->only(['clave', 'titulo', 'texto', 'icono']),
        ]);
    }

    /* ═════════════ LISTAS: VALORES / OBJETIVOS / EMPRESAS ═════════════ */

    /**
     * Reemplaza por completo la colección de un tipo (semántica del prototipo:
     * el modal envía el arreglo completo tal como quedó en pantalla).
     */
    public function sincronizarItems(Request $request, string $tipo): JsonResponse
    {
        abort_unless(in_array($tipo, CulturaItem::TIPOS_LISTA, true), 404);

        $request->validate([
            'items'                 => ['present', 'array', 'max:60'],
            'items.*.id'            => ['nullable', 'integer', Rule::exists('cultura_items', 'id')->where('tipo', $tipo)],
            'items.*.titulo'        => ['required', 'string', 'max:200'],
            'items.*.descripcion'   => ['nullable', 'string', 'max:1000'],
            'items.*.icono'         => ['nullable', 'string', 'max:16'],
            'items.*.color'         => ['nullable', 'string', 'max:20'],
        ], [
            'items.*.titulo.required' => 'Cada elemento necesita un nombre o título.',
        ]);

        $items = collect($request->input('items'))
            ->filter(fn ($i) => filled($i['titulo'] ?? null))
            ->values();

        DB::transaction(function () use ($items, $tipo) {
            $conservados = [];

            foreach ($items as $indice => $item) {
                $atributos = [
                    'tipo'        => $tipo,
                    'titulo'      => trim($item['titulo']),
                    'descripcion' => $item['descripcion'] ?? null,
                    'icono'       => $item['icono'] ?? null,
                    'color'       => $item['color'] ?? null,
                    'orden'       => $indice + 1,
                    'activo'      => true,
                ];

                $registro = ! empty($item['id'])
                    ? tap(CulturaItem::deTipo($tipo)->findOrFail($item['id']))->update($atributos)
                    : CulturaItem::create($atributos);

                $conservados[] = $registro->id;
            }

            CulturaItem::deTipo($tipo)->whereNotIn('id', $conservados ?: [0])->delete();
        });

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Contenido guardado correctamente.',
            'total'   => $items->count(),
        ]);
    }

    /* ═════════════ CARRUSEL (SLIDES) ═════════════ */

    public function guardarSlide(Request $request, ?CulturaItem $item = null): JsonResponse
    {
        // Si la ruta no trae {item}, el contenedor podría inyectar un modelo vacío.
        $item = ($item && $item->exists) ? $item : null;

        if ($item && $item->tipo !== CulturaItem::TIPO_SLIDE) {
            abort(404);
        }

        $datos = $request->validate([
            'titulo'      => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'icono'       => ['nullable', 'string', 'max:16'],
            'color'       => ['nullable', 'string', 'max:20'],
            'orden'       => ['nullable', 'integer', 'min:1', 'max:999'],
            'duracion'    => ['nullable', 'integer', 'min:2', 'max:30'],
            'activo'      => ['nullable', 'boolean'],
            'imagen'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
        ], [], [
            'titulo' => 'título de la diapositiva',
            'imagen' => 'imagen',
        ]);

        $atributos = [
            'tipo'        => CulturaItem::TIPO_SLIDE,
            'titulo'      => $datos['titulo'],
            'descripcion' => $datos['descripcion'] ?? null,
            'icono'       => $datos['icono'] ?? '🏢',
            'color'       => $datos['color'] ?? '#1a0a0a',
            'orden'       => $datos['orden'] ?? (CulturaItem::deTipo(CulturaItem::TIPO_SLIDE)->max('orden') + 1),
            'duracion'    => $datos['duracion'] ?? 5,
            'activo'      => $request->boolean('activo', true),
        ];

        if ($request->hasFile('imagen')) {
            $item?->borrarImagen();
            $atributos['imagen'] = $request->file('imagen')->store('cultura/slides', 'public');
        }

        $slide = $item
            ? tap($item)->update($atributos)
            : CulturaItem::create($atributos);

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Diapositiva guardada correctamente.',
            'slide'   => $slide->only(['id', 'titulo', 'descripcion', 'icono', 'color', 'orden', 'duracion']),
        ]);
    }

    public function eliminarSlide(CulturaItem $item): JsonResponse
    {
        abort_unless($item->tipo === CulturaItem::TIPO_SLIDE, 404);

        $item->borrarImagen();
        $item->delete();

        return response()->json(['ok' => true, 'mensaje' => 'Diapositiva eliminada.']);
    }
}
