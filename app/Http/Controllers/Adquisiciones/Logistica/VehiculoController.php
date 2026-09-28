<?php

namespace App\Http\Controllers\Adquisiciones\Logistica;

use App\Http\Controllers\Controller;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehiculo::query()
            ->orderByDesc('activo')
            ->orderBy('nombre');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")
                  ->orWhere('placas', 'like', "%{$q}%")
                  ->orWhere('marca', 'like', "%{$q}%")
                  ->orWhere('modelo', 'like', "%{$q}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activos');
        }

        $vehiculos = $query->paginate(20)->withQueryString();

        $stats = [
            'total'   => Vehiculo::count(),
            'activos' => Vehiculo::where('activo', true)->count(),
        ];

        $combustibles = config('logistica.combustibles');

        return view('adquisiciones.logistica.vehiculos.index', compact('vehiculos', 'stats', 'combustibles'));
    }

    public function store(Request $request)
    {
        $data = $this->normalizar($request->validate($this->rules(), $this->messages()), $request);

        $data['km_actualizado_at'] = ($data['km_actual'] ?? null) !== null ? now() : null;

        Vehiculo::create($data);

        return back()->with('success', "Unidad {$data['nombre']} registrada.");
    }

    public function update(Request $request, Vehiculo $vehiculo)
    {
        $data = $this->normalizar($request->validate($this->rules($vehiculo->id), $this->messages()), $request);

        $kmNuevo = $data['km_actual'] ?? null;

        if ($kmNuevo === null) {
            $data['km_actualizado_at'] = null;
        } elseif ((int) $kmNuevo !== (int) $vehiculo->km_actual) {
            $data['km_actualizado_at'] = now();
        }

        $vehiculo->update($data);

        return back()->with('success', "Unidad {$vehiculo->nombre} actualizada.");
    }

    public function destroy(Vehiculo $vehiculo)
    {
        $nombre = $vehiculo->nombre;
        $vehiculo->delete();

        return back()->with('success', "Unidad {$nombre} eliminada.");
    }

    private function rules(?int $id = null): array
    {
        return [
            'nombre'           => ['required', 'string', 'max:60', Rule::unique('vehiculos', 'nombre')->ignore($id)],
            'marca'            => ['nullable', 'string', 'max:60'],
            'modelo'           => ['nullable', 'string', 'max:60'],
            'anio'             => ['nullable', 'integer', 'min:1980', 'max:' . (now()->year + 1)],
            'placas'           => ['nullable', 'string', 'max:20', Rule::unique('vehiculos', 'placas')->ignore($id)],
            'numero_serie'     => ['nullable', 'string', 'max:40'],
            'color'            => ['nullable', 'string', 'max:30'],
            'combustible'      => ['required', Rule::in(array_keys(config('logistica.combustibles')))],
            'rendimiento_km_l' => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            'km_actual'        => ['nullable', 'integer', 'min:0'],
            'color_etiqueta'   => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'observaciones'    => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function messages(): array
    {
        return [
            'nombre.required'      => 'El nombre de la unidad es obligatorio.',
            'nombre.unique'        => 'Ya existe una unidad con ese nombre (incluye unidades eliminadas).',
            'placas.unique'        => 'Esas placas ya están registradas en otra unidad.',
            'anio.min'             => 'El año no es válido.',
            'anio.max'             => 'El año no es válido.',
            'color_etiqueta.regex' => 'El color de etiqueta debe tener formato #RRGGBB.',
        ];
    }

    private function normalizar(array $data, Request $request): array
    {
        foreach (['nombre', 'marca', 'modelo', 'placas', 'numero_serie', 'color'] as $campo) {
            if (isset($data[$campo])) {
                $data[$campo] = mb_strtoupper(trim($data[$campo]));
            }
        }

        $data['color_etiqueta'] = $data['color_etiqueta'] ?? '#1e3a5f';
        $data['activo']         = $request->boolean('activo');

        return $data;
    }
}