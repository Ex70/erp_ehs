<?php
namespace App\Http\Controllers\Adquisiciones;

use App\Http\Controllers\Controller;
use App\Models\CategoriaProducto;
use Illuminate\Http\Request;

class CategoriaProductoController extends Controller
{
    public function index()
    {
        $categorias = CategoriaProducto::withCount('productos')
                                       ->orderBy('nombre')
                                       ->paginate(20);

        return view('adquisiciones.categorias_producto.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias_producto,nombre',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique'   => 'Ya existe esa categoría.',
        ]);

        $categoria = CategoriaProducto::create([
            'nombre' => $request->nombre,
            'activo' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json($categoria);
        }

        return redirect()
            ->route('adquisiciones.categorias-producto.index')
            ->with('success', 'Categoría agregada.');
    }

    public function update(Request $request, CategoriaProducto $categorias_producto)
    {
        $request->validate([
            'nombre' => "required|string|max:100|unique:categorias_producto,nombre,{$categorias_producto->id}",
            'activo' => 'boolean',
        ]);

        $categorias_producto->update([
            'nombre' => $request->nombre,
            'activo' => $request->boolean('activo'),
        ]);

        return redirect()
            ->route('adquisiciones.categorias-producto.index')
            ->with('success', 'Categoría actualizada.');
    }

    public function destroy(CategoriaProducto $categorias_producto)
    {
        if ($categorias_producto->productos()->count() > 0) {
            return redirect()
                ->route('adquisiciones.categorias-producto.index')
                ->with('error', 'No se puede eliminar, tiene productos asociados.');
        }

        $categorias_producto->delete();

        return redirect()
            ->route('adquisiciones.categorias-producto.index')
            ->with('success', 'Categoría eliminada.');
    }
}