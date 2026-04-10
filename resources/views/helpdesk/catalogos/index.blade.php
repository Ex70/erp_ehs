@extends('adminlte::page')
@section('title', 'Catálogos Helpdesk')

@section('content_header')
    <h1><i class="fas fa-cog mr-2"></i>Catálogos del Helpdesk</h1>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">

        {{-- Tipos de falla --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Tipos de falla</h3>
                    <button class="btn btn-primary btn-sm"
                            data-toggle="modal" data-target="#modal-tipo">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-dark">
                            <tr><th>Nombre</th><th>Color</th><th>Tickets</th><th>Estado</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($tiposFalla as $t)
                                <tr>
                                    <td>
                                        <span class="badge badge-{{ $t->color }}">
                                            {{ $t->nombre }}
                                        </span>
                                    </td>
                                    <td>{{ $t->color }}</td>
                                    <td><span class="badge badge-info">{{ $t->tickets_count }}</span></td>
                                    <td>
                                        <span class="badge badge-{{ $t->activo ? 'success' : 'secondary' }}">
                                            {{ $t->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-warning btn-xs"
                                                onclick="editarTipo({{ $t->id }}, '{{ addslashes($t->nombre) }}', '{{ $t->color }}', {{ $t->activo ? 1 : 0 }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Categorías de servicio --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Categorías de servicio</h3>
                    <button class="btn btn-primary btn-sm"
                            data-toggle="modal" data-target="#modal-cat">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-dark">
                            <tr><th>Nombre</th><th>Tickets</th><th>Estado</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($categorias as $c)
                                <tr>
                                    <td>{{ $c->nombre }}</td>
                                    <td><span class="badge badge-info">{{ $c->tickets_count }}</span></td>
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
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Modal tipo de falla --}}
    <div class="modal fade" id="modal-tipo" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <form id="form-tipo" method="POST"
                  action="{{ route('helpdesk.catalogos.tipos.store') }}">
                @csrf
                <input type="hidden" name="_method" id="method-tipo" value="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="titulo-tipo">Nuevo tipo de falla</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="tipo-nombre"
                                   class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Color (badge)</label>
                            <select name="color" id="tipo-color" class="form-control">
                                <option value="secondary">secondary (gris)</option>
                                <option value="primary">primary (azul)</option>
                                <option value="info">info (cian)</option>
                                <option value="warning">warning (amarillo)</option>
                                <option value="danger">danger (rojo)</option>
                                <option value="success">success (verde)</option>
                            </select>
                        </div>
                        <div class="form-group" id="tipo-activo-group" style="display:none">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" class="custom-control-input"
                                       id="tipo-activo" name="activo" value="1">
                                <label class="custom-control-label" for="tipo-activo">Activo</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal categoría --}}
    <div class="modal fade" id="modal-cat" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <form id="form-cat" method="POST"
                  action="{{ route('helpdesk.catalogos.categorias.store') }}">
                @csrf
                <input type="hidden" name="_method" id="method-cat" value="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="titulo-cat">Nueva categoría</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="cat-nombre"
                                   class="form-control" required>
                        </div>
                        <div class="form-group" id="cat-activo-group" style="display:none">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" class="custom-control-input"
                                       id="cat-activo" name="activo" value="1">
                                <label class="custom-control-label" for="cat-activo">Activo</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
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
const TIPOS_URL = '{{ url('helpdesk/catalogos/tipos-falla') }}';
const CATS_URL  = '{{ url('helpdesk/catalogos/categorias') }}';

function editarTipo(id, nombre, color, activo) {
    document.getElementById('form-tipo').action = TIPOS_URL + '/' + id;
    document.getElementById('method-tipo').value = 'PUT';
    document.getElementById('tipo-nombre').value  = nombre;
    document.getElementById('tipo-color').value   = color;
    document.getElementById('tipo-activo').checked = activo === 1;
    document.getElementById('tipo-activo-group').style.display = 'block';
    document.getElementById('titulo-tipo').textContent = 'Editar tipo de falla';
    $('#modal-tipo').modal('show');
}

document.getElementById('modal-tipo').addEventListener('hidden.bs.modal', function() {
    document.getElementById('form-tipo').action = '{{ route('helpdesk.catalogos.tipos.store') }}';
    document.getElementById('method-tipo').value = 'POST';
    document.getElementById('tipo-activo-group').style.display = 'none';
    document.getElementById('titulo-tipo').textContent = 'Nuevo tipo de falla';
    document.getElementById('form-tipo').reset();
});

function editarCat(id, nombre, activo) {
    document.getElementById('form-cat').action = CATS_URL + '/' + id;
    document.getElementById('method-cat').value = 'PUT';
    document.getElementById('cat-nombre').value  = nombre;
    document.getElementById('cat-activo').checked = activo === 1;
    document.getElementById('cat-activo-group').style.display = 'block';
    document.getElementById('titulo-cat').textContent = 'Editar categoría';
    $('#modal-cat').modal('show');
}

document.getElementById('modal-cat').addEventListener('hidden.bs.modal', function() {
    document.getElementById('form-cat').action = '{{ route('helpdesk.catalogos.categorias.store') }}';
    document.getElementById('method-cat').value = 'POST';
    document.getElementById('cat-activo-group').style.display = 'none';
    document.getElementById('titulo-cat').textContent = 'Nueva categoría';
    document.getElementById('form-cat').reset();
});
</script>
@stop