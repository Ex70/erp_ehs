<?php
namespace App\Http\Controllers\Adquisiciones;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\CategoriaProducto;
use App\Models\UnidadMedida;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'unidadMedida', 'proveedores'])
                         ->orderBy('nombre');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%'.$request->q.'%')
                  ->orWhereHas('categoria', fn($c) =>
                      $c->where('nombre', 'like', '%'.$request->q.'%')
                  );
            });
        }

        if ($request->filled('q_categoria')) {
            $query->where('categoria_id', $request->q_categoria);
        }

        $productos   = $query->paginate(20);
        $categorias  = CategoriaProducto::where('activo', true)->orderBy('nombre')->get();

        $stats = [
            'total'     => Producto::count(),
            'categorias'=> CategoriaProducto::count(),
        ];

        return view('adquisiciones.productos.index', compact(
            'productos', 'categorias', 'stats'
        ));
    }

    public function create()
    {
        $categorias  = CategoriaProducto::where('activo', true)->orderBy('nombre')->get();
        $unidades    = UnidadMedida::where('activo', true)->orderBy('clave')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
        $producto    = new Producto();

        return view('adquisiciones.productos.create', compact(
            'categorias', 'unidades', 'proveedores', 'producto'
        ));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        $data = [
            'nombre'            => $request->nombre,
            'categoria_id'      => $request->categoria_id,
            'unidad_medida_id'  => $request->unidad_medida_id,
            'precio_referencia' => $request->precio_referencia,
            'especificaciones'  => $request->especificaciones,
            'activo'            => true,
        ];

        // Imagen del producto
        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')
                                      ->store('productos/imagenes', 'public');
        }

        // Ficha técnica PDF
        if ($request->hasFile('ficha_tecnica')) {
            $data['ficha_tecnica'] = $request->file('ficha_tecnica')
                                             ->store('productos/fichas', 'public');
        }

        $producto = Producto::create($data);

        // Proveedores sugeridos (muchos)
        if ($request->filled('proveedor_ids')) {
            $producto->proveedores()->sync($request->proveedor_ids);
        }

        return redirect()
            ->route('adquisiciones.productos.index')
            ->with('success', 'Producto agregado correctamente.');
    }

    public function show(Producto $producto)
    {
        $producto->load(['categoria', 'unidadMedida', 'proveedores']);

        return view('adquisiciones.productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $producto->load(['proveedores']);
        $categorias  = CategoriaProducto::where('activo', true)->orderBy('nombre')->get();
        $unidades    = UnidadMedida::where('activo', true)->orderBy('clave')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();

        return view('adquisiciones.productos.edit', compact(
            'producto', 'categorias', 'unidades', 'proveedores'
        ));
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate($this->rules());

        $data = [
            'nombre'            => $request->nombre,
            'categoria_id'      => $request->categoria_id,
            'unidad_medida_id'  => $request->unidad_medida_id,
            'precio_referencia' => $request->precio_referencia,
            'especificaciones'  => $request->especificaciones,
            'activo'            => $request->boolean('activo', true),
        ];

        // Imagen nueva
        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $data['imagen'] = $request->file('imagen')
                                      ->store('productos/imagenes', 'public');
        }

        // Ficha técnica nueva
        if ($request->hasFile('ficha_tecnica')) {
            if ($producto->ficha_tecnica) {
                Storage::disk('public')->delete($producto->ficha_tecnica);
            }
            $data['ficha_tecnica'] = $request->file('ficha_tecnica')
                                             ->store('productos/fichas', 'public');
        }

        $producto->update($data);

        // Sincronizar proveedores
        $producto->proveedores()->sync($request->proveedor_ids ?? []);

        return redirect()
            ->route('adquisiciones.productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        // Eliminar archivos
        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }
        if ($producto->ficha_tecnica) {
            Storage::disk('public')->delete($producto->ficha_tecnica);
        }

        $producto->delete();

        return redirect()
            ->route('adquisiciones.productos.index')
            ->with('success', 'Producto eliminado.');
    }

    private function rules(): array
    {
        return [
            'nombre'            => 'required|string|max:200',
            'categoria_id'      => 'nullable|exists:categorias_producto,id',
            'unidad_medida_id'  => 'nullable|exists:unidades_medida,id',
            'precio_referencia' => 'nullable|numeric|min:0',
            'especificaciones'  => 'nullable|string',
            'imagen'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'ficha_tecnica'     => 'nullable|mimes:pdf|max:10240',
            'proveedor_ids'     => 'nullable|array',
            'proveedor_ids.*'   => 'exists:proveedores,id',
        ];
    }
}