<?php
namespace App\Http\Controllers\Adquisiciones;

use App\Http\Controllers\Controller;
use App\Models\Requerimiento;
use App\Models\RequerimientoPartida;
use App\Models\RequerimientoProveedor;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Proveedor;
use App\Models\UnidadMedida;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequerimientoController extends Controller
{
    public function index(Request $request)
    {
        $query = Requerimiento::with(['cliente', 'empresaEmisora', 'analista'])
            ->orderByDesc('created_at');

        if ($request->filled('q_folio')) {
            $query->where('folio', 'like', '%'.$request->q_folio.'%');
        }
        if ($request->filled('q_cliente')) {
            $query->whereHas('cliente', fn($q) =>
                $q->where('nombre', 'like', '%'.$request->q_cliente.'%')
            );
        }
        if ($request->filled('q_status')) {
            $query->where('status', $request->q_status);
        }
        if ($request->filled('q_empresa')) {
            $query->where('empresa_emisora_id', $request->q_empresa);
        }
        if ($request->filled('q_analista')) {
            $query->where('analista_id', $request->q_analista);
        }
        if ($request->filled('q_tipo')) {
            $query->where('tipo', $request->q_tipo);
        }

        $requerimientos = $query->paginate(15);

        $stats = [
            'total'      => Requerimiento::count(),
            'pendientes' => Requerimiento::where('status', 'pendiente')->count(),
            'cotizando'  => Requerimiento::where('status', 'cotizando')->count(),
            'autorizados'=> Requerimiento::where('status', 'autorizado')->count(),
        ];

        $empresas  = Empresa::where('activo', true)->get();
        $analistas = User::where('activo', true)->orderBy('name')->get();
        $estatuses = Requerimiento::estatuses();
        $tipos     = Requerimiento::tipos();

        return view('adquisiciones.requerimientos.index', compact(
            'requerimientos', 'stats', 'empresas', 'analistas', 'estatuses', 'tipos'
        ));
    }

    public function create()
    {
        $clientes   = Cliente::where('activo', true)->orderBy('nombre')->get();
        $empresas   = Empresa::where('activo', true)->orderBy('clave')->get();
        $analistas  = User::where('activo', true)->orderBy('name')->get();
        $unidades   = UnidadMedida::where('activo', true)->orderBy('clave')->get();
        $proveedores= Proveedor::where('activo', true)->orderBy('nombre')->get();
        $tipos      = Requerimiento::tipos();
        $requerimiento = new Requerimiento();

        return view('adquisiciones.requerimientos.create', compact(
            'clientes', 'empresas', 'analistas', 'unidades',
            'proveedores', 'tipos', 'requerimiento'
        ));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        DB::transaction(function () use ($request) {
            $empresa = Empresa::findOrFail($request->empresa_emisora_id);

            $requerimiento = Requerimiento::create([
                'folio'              => Requerimiento::generarFolio($empresa->clave),
                'cliente_id'         => $request->cliente_id,
                'empresa_emisora_id' => $request->empresa_emisora_id,
                'empresa_realiza'    => $request->empresa_realiza,
                'analista_id'        => $request->analista_id ?? Auth::id(),
                'tipo'               => $request->tipo,
                'linea_negocio'      => $request->linea_negocio,
                'fecha_solicitud'    => $request->fecha_solicitud,
                'fecha_entrega'      => $request->fecha_entrega,
                'margen'             => $request->margen ?? 0,
                'indirectos'         => $request->indirectos ?? 0,
                'monto_estimado'     => $request->monto_estimado,
                'status'             => 'pendiente',
                'observaciones'      => $request->observaciones,
            ]);

            // Guardar partidas
            foreach ($request->partidas ?? [] as $p) {
                if (empty($p['descripcion'])) continue;
                RequerimientoPartida::create([
                    'requerimiento_id' => $requerimiento->id,
                    'descripcion'      => $p['descripcion'],
                    'cantidad'         => $p['cantidad'] ?? 1,
                    'unidad_medida_id' => $p['unidad_medida_id'] ?? null,
                    'precio_proveedor' => $p['precio_proveedor'] ?? null,
                    'precio_cliente'   => $p['precio_cliente'] ?? null,
                    'notas'            => $p['notas'] ?? null,
                ]);
            }

            // Guardar proveedores
            foreach ($request->proveedores ?? [] as $pv) {
                if (empty($pv['proveedor_id'])) continue;
                RequerimientoProveedor::create([
                    'requerimiento_id' => $requerimiento->id,
                    'proveedor_id'     => $pv['proveedor_id'],
                    'monto'            => $pv['monto'] ?? null,
                    'tiempo_entrega'   => $pv['tiempo_entrega'] ?? null,
                    'costo_envio'      => $pv['costo_envio'] ?? 0,
                    'disponibilidad'   => $pv['disponibilidad'] ?? 'SI',
                    'url'              => $pv['url'] ?? null,
                    'notas'            => $pv['notas'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('adquisiciones.requerimientos.index')
            ->with('success', 'Requerimiento creado correctamente.');
    }

    public function show(Requerimiento $requerimiento)
    {
        $requerimiento->load([
            'cliente', 'empresaEmisora', 'analista',
            'partidas.unidadMedida',
            'proveedores.proveedor',
            'notas.usuario',
        ]);

        return view('adquisiciones.requerimientos.show', compact('requerimiento'));
    }

    public function edit(Requerimiento $requerimiento)
    {
        $requerimiento->load(['partidas.unidadMedida', 'proveedores.proveedor']);

        $clientes    = Cliente::where('activo', true)->orderBy('nombre')->get();
        $empresas    = Empresa::where('activo', true)->orderBy('clave')->get();
        $analistas   = User::where('activo', true)->orderBy('name')->get();
        $unidades    = UnidadMedida::where('activo', true)->orderBy('clave')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
        $tipos       = Requerimiento::tipos();

        return view('adquisiciones.requerimientos.edit', compact(
            'requerimiento', 'clientes', 'empresas', 'analistas',
            'unidades', 'proveedores', 'tipos'
        ));
    }

    public function update(Request $request, Requerimiento $requerimiento)
    {
        $request->validate($this->rules());

        DB::transaction(function () use ($request, $requerimiento) {
            $requerimiento->update([
                'cliente_id'         => $request->cliente_id,
                'empresa_emisora_id' => $request->empresa_emisora_id,
                'empresa_realiza'    => $request->empresa_realiza,
                'analista_id'        => $request->analista_id,
                'tipo'               => $request->tipo,
                'linea_negocio'      => $request->linea_negocio,
                'fecha_solicitud'    => $request->fecha_solicitud,
                'fecha_entrega'      => $request->fecha_entrega,
                'margen'             => $request->margen ?? 0,
                'indirectos'         => $request->indirectos ?? 0,
                'monto_estimado'     => $request->monto_estimado,
                'status'             => $request->status,
                'observaciones'      => $request->observaciones,
            ]);

            // Reemplazar partidas
            $requerimiento->partidas()->delete();
            foreach ($request->partidas ?? [] as $p) {
                if (empty($p['descripcion'])) continue;
                RequerimientoPartida::create([
                    'requerimiento_id' => $requerimiento->id,
                    'descripcion'      => $p['descripcion'],
                    'cantidad'         => $p['cantidad'] ?? 1,
                    'unidad_medida_id' => $p['unidad_medida_id'] ?? null,
                    'precio_proveedor' => $p['precio_proveedor'] ?? null,
                    'precio_cliente'   => $p['precio_cliente'] ?? null,
                    'notas'            => $p['notas'] ?? null,
                ]);
            }

            // Reemplazar proveedores
            $requerimiento->proveedores()->delete();
            foreach ($request->proveedores ?? [] as $pv) {
                if (empty($pv['proveedor_id'])) continue;
                RequerimientoProveedor::create([
                    'requerimiento_id' => $requerimiento->id,
                    'proveedor_id'     => $pv['proveedor_id'],
                    'monto'            => $pv['monto'] ?? null,
                    'tiempo_entrega'   => $pv['tiempo_entrega'] ?? null,
                    'costo_envio'      => $pv['costo_envio'] ?? 0,
                    'disponibilidad'   => $pv['disponibilidad'] ?? 'SI',
                    'url'              => $pv['url'] ?? null,
                    'notas'            => $pv['notas'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('adquisiciones.requerimientos.show', $requerimiento)
            ->with('success', 'Requerimiento actualizado correctamente.');
    }

    public function destroy(Requerimiento $requerimiento)
    {
        $requerimiento->delete();

        return redirect()
            ->route('adquisiciones.requerimientos.index')
            ->with('success', 'Requerimiento enviado a papelera.');
    }

    private function rules(): array
    {
        return [
            'cliente_id'         => 'required|exists:clientes,id',
            'empresa_emisora_id' => 'required|exists:empresas,id',
            'empresa_realiza'    => 'nullable|string|max:60',
            'analista_id'        => 'nullable|exists:users,id',
            'tipo'               => 'required|in:normal,urgente,critico',
            'linea_negocio'      => 'nullable|string|max:60',
            'fecha_solicitud'    => 'required|date',
            'fecha_entrega'      => 'nullable|date|after_or_equal:fecha_solicitud',
            'margen'             => 'nullable|numeric|min:0|max:100',
            'indirectos'         => 'nullable|numeric|min:0|max:100',
            'monto_estimado'     => 'nullable|numeric|min:0',
            'status'             => 'nullable|in:pendiente,cotizando,enviado,autorizado,cancelado',
            'observaciones'      => 'nullable|string',
            'partidas'           => 'nullable|array',
            'partidas.*.descripcion'    => 'required_with:partidas|string|max:200',
            'partidas.*.cantidad'       => 'nullable|numeric|min:0.01',
            'partidas.*.unidad_medida_id' => 'nullable|exists:unidades_medida,id',
            'partidas.*.precio_proveedor' => 'nullable|numeric|min:0',
            'partidas.*.precio_cliente'   => 'nullable|numeric|min:0',
            'proveedores'        => 'nullable|array',
            'proveedores.*.proveedor_id' => 'nullable|exists:proveedores,id',
            'proveedores.*.monto'        => 'nullable|numeric|min:0',
        ];
    }
}