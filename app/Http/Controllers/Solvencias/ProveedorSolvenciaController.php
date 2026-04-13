<?php
namespace App\Http\Controllers\Solvencias;

use App\Http\Controllers\Controller;
use App\Models\ProveedorSolvencia;
use App\Models\ProveedorCuentaBancaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorSolvenciaController extends Controller
{
    public function index(Request $request)
    {
        $query = ProveedorSolvencia::withCount('cuentasBancarias')
                                   ->orderBy('nombre');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%'.$request->q.'%')
                  ->orWhere('rfc', 'like', '%'.$request->q.'%')
                  ->orWhere('giro', 'like', '%'.$request->q.'%');
            });
        }

        $proveedores = $query->paginate(20);

        return view('solvencias.proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        $proveedor = new ProveedorSolvencia();
        return view('solvencias.proveedores.create', compact('proveedor'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        DB::transaction(function () use ($request) {
            $proveedor = ProveedorSolvencia::create([
                'nombre'         => $request->nombre,
                'rfc'            => $request->rfc,
                'giro'           => $request->giro,
                'contacto'       => $request->contacto,
                'telefono'       => $request->telefono,
                'facturacion'    => $request->facturacion,
                'tiempo_entrega' => $request->tiempo_entrega,
                'activo'         => true,
            ]);

            foreach ($request->cuentas ?? [] as $i => $c) {
                if (empty($c['banco'])) continue;

                ProveedorCuentaBancaria::create([
                    'proveedor_solvencia_id' => $proveedor->id,
                    'banco'                  => $c['banco'],
                    'clabe'                  => $c['clabe'] ?? null,
                    'cuenta'                 => $c['cuenta'] ?? null,
                    'referencia'             => $c['referencia'] ?? null,
                    'tiempo_entrega'         => $c['tiempo_entrega'] ?? null,
                    'principal'              => $i === 0,
                ]);
            }
        });

        return redirect()
            ->route('solvencias.proveedores.index')
            ->with('success', 'Proveedor agregado correctamente.');
    }

    public function show(ProveedorSolvencia $proveedoresSolvencia)
    {
        $proveedoresSolvencia->load('cuentasBancarias');
        return view('solvencias.proveedores.show',
            ['proveedor' => $proveedoresSolvencia]);
    }

    public function edit(ProveedorSolvencia $proveedoresSolvencia)
    {
        $proveedoresSolvencia->load('cuentasBancarias');
        return view('solvencias.proveedores.edit',
            ['proveedor' => $proveedoresSolvencia]);
    }

    public function update(Request $request, ProveedorSolvencia $proveedoresSolvencia)
    {
        $request->validate($this->rules($proveedoresSolvencia->id));

        DB::transaction(function () use ($request, $proveedoresSolvencia) {
            $proveedoresSolvencia->update([
                'nombre'         => $request->nombre,
                'rfc'            => $request->rfc,
                'giro'           => $request->giro,
                'contacto'       => $request->contacto,
                'telefono'       => $request->telefono,
                'facturacion'    => $request->facturacion,
                'tiempo_entrega' => $request->tiempo_entrega,
                'activo'         => $request->boolean('activo', true),
            ]);

            // Reemplazar cuentas bancarias
            $proveedoresSolvencia->cuentasBancarias()->delete();

            foreach ($request->cuentas ?? [] as $i => $c) {
                if (empty($c['banco'])) continue;

                ProveedorCuentaBancaria::create([
                    'proveedor_solvencia_id' => $proveedoresSolvencia->id,
                    'banco'                  => $c['banco'],
                    'clabe'                  => $c['clabe'] ?? null,
                    'cuenta'                 => $c['cuenta'] ?? null,
                    'referencia'             => $c['referencia'] ?? null,
                    'tiempo_entrega'         => $c['tiempo_entrega'] ?? null,
                    'principal'              => $i === 0,
                ]);
            }
        });

        return redirect()
            ->route('solvencias.proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(ProveedorSolvencia $proveedoresSolvencia)
    {
        $proveedoresSolvencia->delete();
        return redirect()
            ->route('solvencias.proveedores.index')
            ->with('success', 'Proveedor eliminado.');
    }

    private function rules(?int $excludeId = null): array
    {
        $unique = $excludeId
            ? "required|string|max:150|unique:proveedores_solvencia,nombre,{$excludeId}"
            : 'required|string|max:150|unique:proveedores_solvencia,nombre';

        return [
            'nombre'         => $unique,
            'rfc'            => 'nullable|string|max:30',
            'giro'           => 'nullable|string|max:100',
            'contacto'       => 'nullable|string|max:100',
            'telefono'       => 'nullable|string|max:30',
            'facturacion'    => 'nullable|string|max:100',
            'tiempo_entrega' => 'nullable|string|max:60',
            'cuentas'        => 'nullable|array',
            'cuentas.*.banco'=> 'required_with:cuentas|string|max:60',
            'cuentas.*.clabe'=> 'nullable|string|max:20',
        ];
    }
}