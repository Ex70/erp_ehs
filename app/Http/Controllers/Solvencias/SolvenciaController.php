<?php
namespace App\Http\Controllers\Solvencias;

use App\Http\Controllers\Controller;
use App\Models\Solvencia;
use App\Models\SolvenciaPartida;
use App\Models\EmpresaSolvencia;
use App\Models\ProveedorSolvencia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolvenciaController extends Controller
{
    public function index(Request $request)
    {
        $query = Solvencia::with(['empresa', 'elaborador'])
                          ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('folio', 'like', '%'.$request->q.'%')
                  ->orWhere('cliente', 'like', '%'.$request->q.'%')
                  ->orWhere('numero_cotizacion', 'like', '%'.$request->q.'%');
            });
        }

        if ($request->filled('q_estatus')) {
            $query->where('estatus', $request->q_estatus);
        }

        if ($request->filled('q_empresa')) {
            $query->where('empresa_solvencia_id', $request->q_empresa);
        }

        $solvencias = $query->paginate(15);
        $empresas   = EmpresaSolvencia::where('activo', true)->orderBy('nombre')->get();
        $estatuses  = Solvencia::estatuses();

        return view('solvencias.index', compact(
            'solvencias', 'empresas', 'estatuses'
        ));
    }

    public function create()
    {
        $empresas    = EmpresaSolvencia::where('activo', true)->orderBy('nombre')->get();
        $proveedores = \App\Models\Proveedor::where('activo', true)
                                    ->with('cuentasBancarias')
                                    ->orderBy('nombre')
                                    ->get();
        $usuarios    = User::where('activo', true)->orderBy('name')->get();
        $usuario     = Auth::user()->load('puesto');
        $solvencia   = new Solvencia();

        return view('solvencias.create', compact(
            'empresas', 'proveedores', 'usuarios', 'usuario', 'solvencia'
        ));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        DB::transaction(function () use ($request) {
            $solvencia = Solvencia::create([
                'folio'               => Solvencia::generarFolio(),
                'empresa_solvencia_id'=> $request->empresa_solvencia_id,
                'user_id'             => Auth::id(),
                'fecha'               => $request->fecha,
                'numero_cotizacion'   => $request->numero_cotizacion,
                'cliente'             => $request->cliente,
                'departamento'        => $request->departamento,
                'elaboro_nombre'      => $request->elaboro_nombre,
                'elaboro_cargo'       => $request->elaboro_cargo,
                'valido_nombre'       => $request->valido_nombre,
                'valido_cargo'        => $request->valido_cargo,
                'autorizo_nombre'     => $request->autorizo_nombre,
                'autorizo_cargo'      => $request->autorizo_cargo,
                'estatus'             => 'borrador',
                'observaciones'       => $request->observaciones,
                'subtotal'            => 0,
                'iva'                 => 0,
                'total'               => 0,
                'monto_solicitado'    => 0,
                'monto_autorizado'    => 0,
            ]);

            foreach ($request->partidas ?? [] as $i => $p) {
                if (empty($p['descripcion'])) continue;

                SolvenciaPartida::create([
                    'solvencia_id'          => $solvencia->id,
                    'proveedor_id' => $p['proveedor_id'] ?? null,
                    'cuenta_bancaria_id'    => $p['cuenta_bancaria_id'] ?? null,
                    'numero'                => $i + 1,
                    'descripcion'           => $p['descripcion'],
                    'cantidad'              => $p['cantidad'] ?? 1,
                    'importe'               => $p['importe'] ?? 0,
                    'concepto'              => $p['concepto'] ?? null,
                ]);
            }

            $solvencia->recalcularTotales();
        });

        return redirect()
            ->route('solvencias.index')
            ->with('success', 'Solvencia creada correctamente.');
    }

    public function show(Solvencia $solvencia)
    {
        $solvencia->load([
            'empresa',
            'elaborador.puesto',
            'partidas.proveedor',
            'partidas.cuentaBancaria',
        ]);

        return view('solvencias.show', compact('solvencia'));
    }

    public function edit(Solvencia $solvencia)
    {
        $solvencia->load(['partidas.proveedor', 'partidas.cuentaBancaria']);

        $empresas    = EmpresaSolvencia::where('activo', true)->orderBy('nombre')->get();
        $proveedores = \App\Models\Proveedor::where('activo', true)
                                    ->with('cuentasBancarias')
                                    ->orderBy('nombre')
                                    ->get();
        $usuarios    = User::where('activo', true)->orderBy('name')->get();
        $usuario     = Auth::user()->load('puesto');

        return view('solvencias.edit', compact(
            'solvencia', 'empresas', 'proveedores', 'usuarios', 'usuario'
        ));
    }

    public function update(Request $request, Solvencia $solvencia)
    {
        $request->validate($this->rules());

        DB::transaction(function () use ($request, $solvencia) {
            $solvencia->update([
                'empresa_solvencia_id'=> $request->empresa_solvencia_id,
                'fecha'               => $request->fecha,
                'numero_cotizacion'   => $request->numero_cotizacion,
                'cliente'             => $request->cliente,
                'departamento'        => $request->departamento,
                'elaboro_nombre'      => $request->elaboro_nombre,
                'elaboro_cargo'       => $request->elaboro_cargo,
                'valido_nombre'       => $request->valido_nombre,
                'valido_cargo'        => $request->valido_cargo,
                'autorizo_nombre'     => $request->autorizo_nombre,
                'autorizo_cargo'      => $request->autorizo_cargo,
                'estatus'             => $request->estatus ?? $solvencia->estatus,
                'observaciones'       => $request->observaciones,
            ]);

            $solvencia->partidas()->delete();

            foreach ($request->partidas ?? [] as $i => $p) {
                if (empty($p['descripcion'])) continue;

                SolvenciaPartida::create([
                    'solvencia_id'          => $solvencia->id,
                    'proveedor_id' => $p['proveedor_id'] ?? null,
                    'cuenta_bancaria_id'    => $p['cuenta_bancaria_id'] ?? null,
                    'numero'                => $i + 1,
                    'descripcion'           => $p['descripcion'],
                    'cantidad'              => $p['cantidad'] ?? 1,
                    'importe'               => $p['importe'] ?? 0,
                    'concepto'              => $p['concepto'] ?? null,
                ]);
            }

            $solvencia->recalcularTotales();
        });

        return redirect()
            ->route('solvencias.show', $solvencia)
            ->with('success', 'Solvencia actualizada correctamente.');
    }

    public function destroy(Solvencia $solvencia)
    {
        $solvencia->delete();

        return redirect()
            ->route('solvencias.index')
            ->with('success', "Solvencia {$solvencia->folio} eliminada.");
    }

    private function rules(): array
    {
        return [
            'empresa_solvencia_id' => 'required|exists:empresas_solvencia,id',
            'fecha'                => 'required|date',
            'numero_cotizacion'    => 'nullable|string|max:200',
            'cliente'              => 'nullable|string|max:120',
            'departamento'         => 'nullable|string|max:120',
            'elaboro_nombre'       => 'nullable|string|max:100',
            'elaboro_cargo'        => 'nullable|string|max:100',
            'valido_nombre'        => 'nullable|string|max:100',
            'valido_cargo'         => 'nullable|string|max:100',
            'autorizo_nombre'      => 'nullable|string|max:100',
            'autorizo_cargo'       => 'nullable|string|max:100',
            'partidas'             => 'required|array|min:1',
            'partidas.*.descripcion'           => 'required|string|max:200',
            'partidas.*.cantidad'              => 'nullable|numeric|min:0',
            'partidas.*.importe'               => 'required|numeric|min:0',
            'partidas.*.proveedor_solvencia_id'=> 'nullable|exists:proveedores_solvencia,id',
            'partidas.*.cuenta_bancaria_id'    => 'nullable|exists:proveedor_cuentas_bancarias,id',
            'partidas.*.concepto'              => 'nullable|string|max:200',
        ];
    }
}