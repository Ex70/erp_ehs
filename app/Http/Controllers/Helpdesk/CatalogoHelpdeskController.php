<?php
namespace App\Http\Controllers\Helpdesk;

use App\Http\Controllers\Controller;
use App\Models\TipoFalla;
use App\Models\CategoriaServicio;
use Illuminate\Http\Request;

class CatalogoHelpdeskController extends Controller
{
    public function index()
    {
        $tiposFalla = TipoFalla::withCount('tickets')->orderBy('nombre')->get();
        $categorias = CategoriaServicio::withCount('tickets')->orderBy('nombre')->get();

        return view('helpdesk.catalogos.index', compact('tiposFalla', 'categorias'));
    }

    public function storeTipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:60|unique:tipos_falla,nombre',
            'color'  => 'required|string|max:20',
        ]);

        TipoFalla::create(['nombre' => $request->nombre, 'color' => $request->color, 'activo' => true]);

        return redirect()->route('helpdesk.catalogos.index')->with('success', 'Tipo de falla agregado.');
    }

    public function updateTipo(Request $request, TipoFalla $tipoFalla)
    {
        $request->validate([
            'nombre' => "required|string|max:60|unique:tipos_falla,nombre,{$tipoFalla->id}",
            'color'  => 'required|string|max:20',
            'activo' => 'boolean',
        ]);

        $tipoFalla->update(['nombre' => $request->nombre, 'color' => $request->color, 'activo' => $request->boolean('activo')]);

        return redirect()->route('helpdesk.catalogos.index')->with('success', 'Tipo de falla actualizado.');
    }

    public function storeCategoria(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:60|unique:categorias_servicio,nombre',
        ]);

        CategoriaServicio::create(['nombre' => $request->nombre, 'activo' => true]);

        return redirect()->route('helpdesk.catalogos.index')->with('success', 'Categoría agregada.');
    }

    public function updateCategoria(Request $request, CategoriaServicio $categoriaServicio)
    {
        $request->validate([
            'nombre' => "required|string|max:60|unique:categorias_servicio,nombre,{$categoriaServicio->id}",
            'activo' => 'boolean',
        ]);

        $categoriaServicio->update(['nombre' => $request->nombre, 'activo' => $request->boolean('activo')]);

        return redirect()->route('helpdesk.catalogos.index')->with('success', 'Categoría actualizada.');
    }
}