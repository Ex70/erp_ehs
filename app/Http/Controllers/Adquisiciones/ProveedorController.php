<?php
namespace App\Http\Controllers\Adquisiciones;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Proveedor::orderBy('nombre');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%'.$request->q.'%')
                  ->orWhere('rfc',    'like', '%'.$request->q.'%')
                  ->orWhere('giro',   'like', '%'.$request->q.'%')
                  ->orWhere('ciudad', 'like', '%'.$request->q.'%');
            });
        }

        if ($request->filled('q_giro')) {
            $query->where('giro', $request->q_giro);
        }

        if ($request->filled('q_ciudad')) {
            $query->where('ciudad', 'like', '%'.$request->q_ciudad.'%');
        }

        $proveedores = $query->paginate(20);

        $giros   = Proveedor::whereNotNull('giro')
                            ->distinct()
                            ->orderBy('giro')
                            ->pluck('giro');

        $stats = [
            'total'    => Proveedor::count(),
            'activos'  => Proveedor::where('activo', true)->count(),
            'ciudades' => Proveedor::whereNotNull('ciudad')->distinct('ciudad')->count('ciudad'),
        ];

        return view('adquisiciones.proveedores.index', compact(
            'proveedores', 'giros', 'stats'
        ));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        Proveedor::create([
            'nombre'              => $request->nombre,
            'rfc'                 => $request->rfc,
            'giro'                => $request->giro,
            'ciudad'              => $request->ciudad,
            'correo'              => $request->correo,
            'telefono'            => $request->telefono,
            'telefono_secundario' => $request->telefono_secundario,
            'condiciones_pago'    => $request->condiciones_pago,
            'tiempo_entrega'      => $request->tiempo_entrega,
            'direccion'           => $request->direccion,
            'observaciones'       => $request->observaciones,
            'activo'              => true,
        ]);

        return redirect()
            ->route('adquisiciones.proveedores.index')
            ->with('success', 'Proveedor agregado correctamente.');
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $request->validate($this->rules($proveedor->id));

        $proveedor->update([
            'nombre'              => $request->nombre,
            'rfc'                 => $request->rfc,
            'giro'                => $request->giro,
            'ciudad'              => $request->ciudad,
            'correo'              => $request->correo,
            'telefono'            => $request->telefono,
            'telefono_secundario' => $request->telefono_secundario,
            'condiciones_pago'    => $request->condiciones_pago,
            'tiempo_entrega'      => $request->tiempo_entrega,
            'direccion'           => $request->direccion,
            'observaciones'       => $request->observaciones,
            'activo'              => $request->boolean('activo', true),
        ]);

        return redirect()
            ->route('adquisiciones.proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor)
    {
        if ($proveedor->requerimientoProveedores()->count() > 0) {
            return redirect()
                ->route('adquisiciones.proveedores.index')
                ->with('error', 'No se puede eliminar, tiene requerimientos asociados.');
        }

        $proveedor->delete();

        return redirect()
            ->route('adquisiciones.proveedores.index')
            ->with('success', 'Proveedor eliminado.');
    }

    // Ranking por participación y adjudicaciones
    public function ranking()
    {
        $proveedores = Proveedor::withCount([
            'requerimientoProveedores as total_participaciones',
            'requerimientoProveedores as total_ganados' => fn($q) =>
                $q->where('ganador', true),
        ])
        ->orderByDesc('total_ganados')
        ->orderByDesc('total_participaciones')
        ->get();

        return response()->json($proveedores);
    }

    private function rules(?int $excludeId = null): array
    {
        $unique = $excludeId
            ? "required|string|max:120|unique:proveedores,nombre,{$excludeId}"
            : 'required|string|max:120|unique:proveedores,nombre';

        return [
            'nombre'              => $unique,
            'rfc'                 => 'nullable|string|max:20',
            'giro'                => 'nullable|string|max:100',
            'ciudad'              => 'nullable|string|max:80',
            'correo'              => 'nullable|email|max:100',
            'telefono'            => 'nullable|string|max:30',
            'telefono_secundario' => 'nullable|string|max:30',
            'condiciones_pago'    => 'nullable|string|max:100',
            'tiempo_entrega'      => 'nullable|string|max:60',
            'direccion'           => 'nullable|string|max:200',
            'observaciones'       => 'nullable|string',
        ];
    }
}