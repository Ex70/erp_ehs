@extends('adminlte::page')
@section('title', 'Categorías de Productos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tags mr-2"></i>Categorías de productos</h1>
        <button class="btn btn-primary btn-sm"
                data-toggle="modal" data-target="#modal-cat">
            <i class="fas fa-plus"></i> Nueva categoría
        </button>
    </div>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categorias as $i => $c)
                        <tr>
                            <td>{{ $categorias->firstItem() + $i }}</td>
                            <td>{{ $c->nombre }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $c->productos_count }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $c->activo ? 'success' : 'secondary' }}">
                                    {{ $c->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-xs"
                                        onclick="editarCat({{ $c->id }}, '{{ addslashes($c->nombre) }}', {{ $c->activo ? 1 : 0 }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('adquisiciones.categorias-producto.destroy', $c) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-xs"
                                            {{ $c->productos_count > 0 ? 'disabled title=Tiene productos asociados' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No hay categorías registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $categorias->links() }}</div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="modal-cat" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <form id="form-cat" method="POST"
                  action="{{ route('adquisiciones.categorias-producto.store') }}">
                @csrf
                <input type="hidden" name="_method" id="method-cat" value="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="titulo-cat">Nueva categoría</h5>
                        <button type="button" class="close"
                                data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="cat-nombre"
                                   class="form-control" required>
                        </div>
                        <div id="cat-activo-group" style="display:none">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" class="custom-control-input"
                                       id="cat-activo" name="activo" value="1">
                                <label class="custom-control-label" for="cat-activo">
                                    Activo
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@stop

@section('js')
<script>
function editarCat(id, nombre, activo) {
    const f = document.getElementById('form-cat');
    f.action = '{{ url('adquisiciones/categorias-producto') }}/' + id;
    document.getElementById('method-cat').value  = 'PUT';
    document.getElementById('cat-nombre').value  = nombre;
    document.getElementById('cat-activo').checked = activo === 1;
    document.getElementById('cat-activo-group').style.display = 'block';
    document.getElementById('titulo-cat').textContent = 'Editar categoría';
    $('#modal-cat').modal('show');
}

document.getElementById('modal-cat').addEventListener('hidden.bs.modal', function () {
    const f = document.getElementById('form-cat');
    f.action = '{{ route('adquisiciones.categorias-producto.store') }}';
    document.getElementById('method-cat').value = 'POST';
    document.getElementById('cat-activo-group').style.display = 'none';
    document.getElementById('titulo-cat').textContent = 'Nueva categoría';
    f.reset();
});
</script>
@stop