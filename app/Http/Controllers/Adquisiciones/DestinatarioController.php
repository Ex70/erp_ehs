<?php
namespace App\Http\Controllers\Adquisiciones;

use App\Http\Controllers\Controller;
use App\Models\Destinatario;
use App\Models\Dependencia;
use Illuminate\Http\Request;

class DestinatarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Destinatario::with('dependencia')->orderBy('dirigido_a');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('dirigido_a', 'like', '%'.$request->q.'%')
                  ->orWhere('cargo',     'like', '%'.$request->q.'%')
                  ->orWhere('lugar',     'like', '%'.$request->q.'%')
                  ->orWhereHas('dependencia', fn($d) =>
                      $d->where('nombre', 'like', '%'.$request->q.'%')
                  );
            });
        }

        if ($request->filled('q_dependencia')) {
            $query->where('dependencia_id', $request->q_dependencia);
        }

        if ($request->filled('q_lugar')) {
            $query->where('lugar', 'like', '%'.$request->q_lugar.'%');
        }

        $destinatarios = $query->paginate(20);
        $dependencias  = Dependencia::where('activo', true)->orderBy('nombre')->get();

        $stats = [
            'total'       => Destinatario::count(),
            'dependencias'=> Dependencia::count(),
        ];

        return view('adquisiciones.destinatarios.index', compact(
            'destinatarios', 'dependencias', 'stats'
        ));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        Destinatario::create([
            'dirigido_a'          => $request->dirigido_a,
            'cargo'               => $request->cargo,
            'dependencia_id'      => $request->dependencia_id,
            'atencion_a'          => $request->atencion_a,
            'lugar'               => $request->lugar,
            'correo'              => $request->correo,
            'telefono'            => $request->telefono,
            'telefono_secundario' => $request->telefono_secundario,
            'direccion'           => $request->direccion,
            'observaciones'       => $request->observaciones,
            'activo'              => true,
        ]);

        return redirect()
            ->route('adquisiciones.destinatarios.index')
            ->with('success', 'Destinatario agregado correctamente.');
    }

    public function update(Request $request, Destinatario $destinatario)
    {
        $request->validate($this->rules($destinatario->id));

        $destinatario->update([
            'dirigido_a'          => $request->dirigido_a,
            'cargo'               => $request->cargo,
            'dependencia_id'      => $request->dependencia_id,
            'atencion_a'          => $request->atencion_a,
            'lugar'               => $request->lugar,
            'correo'              => $request->correo,
            'telefono'            => $request->telefono,
            'telefono_secundario' => $request->telefono_secundario,
            'direccion'           => $request->direccion,
            'observaciones'       => $request->observaciones,
            'activo'              => $request->boolean('activo', true),
        ]);

        return redirect()
            ->route('adquisiciones.destinatarios.index')
            ->with('success', 'Destinatario actualizado correctamente.');
    }

    public function destroy(Destinatario $destinatario)
    {
        $destinatario->delete();

        return redirect()
            ->route('adquisiciones.destinatarios.index')
            ->with('success', 'Destinatario eliminado.');
    }

    private function rules(?int $excludeId = null): array
    {
        return [
            'dirigido_a'          => 'required|string|max:120',
            'cargo'               => 'nullable|string|max:120',
            'dependencia_id'      => 'required|exists:dependencias,id',
            'atencion_a'          => 'nullable|string|max:120',
            'lugar'               => 'nullable|string|max:150',
            'correo'              => 'nullable|email|max:100',
            'telefono'            => 'nullable|string|max:30',
            'telefono_secundario' => 'nullable|string|max:30',
            'direccion'           => 'nullable|string|max:200',
            'observaciones'       => 'nullable|string',
        ];
    }
}