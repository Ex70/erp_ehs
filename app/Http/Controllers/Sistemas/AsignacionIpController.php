<?php

namespace App\Http\Controllers\Sistemas;

use App\Http\Controllers\Controller;
use App\Models\AsignacionIp;
use App\Models\User;
use App\Http\Requests\StoreAsignacionIpRequest;
use App\Http\Requests\UpdateAsignacionIpRequest;
use Illuminate\Http\Request;

class AsignacionIpController extends Controller
{
    public function index(Request $request)
    {
        $query = AsignacionIp::with('usuario');

        if ($request->filled('q_ip')) {
            $query->where('direccion_ip', 'like', '%'.$request->q_ip.'%');
        }
        if ($request->filled('q_usuario')) {
            $query->where('nombre', 'like', '%'.$request->q_usuario.'%');
        }
        if ($request->filled('q_area')) {
            $query->where('area', $request->q_area);
        }
        if ($request->filled('q_dispositivo')) {
            $query->where('dispositivo', $request->q_dispositivo);
        }
        if ($request->filled('q_mac')) {
            $query->where('direccion_mac', 'like', '%'.$request->q_mac.'%');
        }

        $asignaciones = $query->orderByDesc('created_at')->paginate(15);

        $stats = [
            'total'  => AsignacionIp::count(),
            'areas'  => AsignacionIp::distinct('area')->count('area'),
            'tipos'  => AsignacionIp::distinct('dispositivo')->count('dispositivo'),
            'ultimo' => AsignacionIp::latest()->value('fecha_asignacion'),
        ];

        $tiposDispositivo = AsignacionIp::tiposDispositivo();
        $areas            = AsignacionIp::distinct()->orderBy('area')->pluck('area');

        return view('sistemas.redes.index', compact(
            'asignaciones', 'stats', 'tiposDispositivo', 'areas'
        ));
    }

    public function create()
    {
        $usuarios         = User::where('activo', true)->orderBy('name')->get();
        $tiposDispositivo = AsignacionIp::tiposDispositivo();
        $areas            = AsignacionIp::distinct()->orderBy('area')->pluck('area');
        $asignacion       = new AsignacionIp();

        return view('sistemas.redes.create', compact(
            'usuarios', 'tiposDispositivo', 'areas', 'asignacion'
        ));
    }

    public function store(StoreAsignacionIpRequest $request)
    {
        $data           = $request->validated();
        $data['codigo'] = AsignacionIp::generarCodigo();

        if (empty($data['fecha_asignacion'])) {
            $data['fecha_asignacion'] = now()->toDateString();
        }

        AsignacionIp::create($data);

        return redirect()
            ->route('sistemas.redes.index')
            ->with('success', 'Registro de IP creado correctamente.');
    }

    public function show(AsignacionIp $asignacion_ip)
    {
        $asignacion_ip->load('usuario');

        return view('sistemas.redes.show', compact('asignacion_ip'));
    }

    public function edit(AsignacionIp $asignacion_ip)
    {
        $usuarios         = User::where('activo', true)->orderBy('name')->get();
        $tiposDispositivo = AsignacionIp::tiposDispositivo();
        $areas            = AsignacionIp::distinct()->orderBy('area')->pluck('area');

        return view('sistemas.redes.edit', compact(
            'asignacion_ip', 'usuarios', 'tiposDispositivo', 'areas'
        ));
    }

    public function update(UpdateAsignacionIpRequest $request, AsignacionIp $asignacion_ip)
    {
        $asignacion_ip->update($request->validated());

        return redirect()
            ->route('sistemas.redes.index')
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(AsignacionIp $asignacion_ip)
    {
        $asignacion_ip->delete(); // SoftDelete

        return redirect()
            ->route('sistemas.redes.index')
            ->with('success', 'Registro eliminado correctamente.');
    }
}