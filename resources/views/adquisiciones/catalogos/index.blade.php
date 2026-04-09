@extends('adminlte::page')
@section('title', 'Catálogos — Adquisiciones')

@section('content_header')
    <h1><i class="fas fa-cog mr-2"></i>Catálogos de Adquisiciones</h1>
@stop

@section('content')
<div class="row">

    {{-- Clientes --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-users mr-1"></i> Clientes</h3>
                <button class="btn btn-primary btn-sm"
                        data-toggle="modal" data-target="#modal-cliente">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-dark">
                        <tr><th>Nombre</th><th>Correo</th><th>Estado</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($clientes as $c)
                            <tr>
                                <td>{{ $c->nombre }}</td>
                                <td>{{ $c->correo ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-{{ $c->activo ? 'success' : 'secondary' }}">
                                        {{ $c->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-xs"
                                            onclick="editarCliente({{ $c->id }}, '{{ addslashes($c->nombre) }}', '{{ $c->contacto }}', '{{ $c->correo }}', '{{ $c->telefono }}', {{ $c->activo ? 1 : 0 }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('adquisiciones.clientes.destroy', $c) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Proveedores --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-truck mr-1"></i> Proveedores</h3>
                <button class="btn btn-primary btn-sm"
                        data-toggle="modal" data-target="#modal-proveedor">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-dark">
                        <tr><th>Nombre</th><th>RFC</th><th>Ciudad</th><th>Estado</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($proveedores as $p)
                            <tr>
                                <td>{{ $p->nombre }}</td>
                                <td>{{ $p->rfc ?? '—' }}</td>
                                <td>{{ $p->ciudad ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-{{ $p->activo ? 'success' : 'secondary' }}">
                                        {{ $p->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-xs"
                                            onclick="editarProveedor({{ $p->id }}, '{{ addslashes($p->nombre) }}', '{{ $p->rfc }}', '{{ $p->giro }}', '{{ $p->ciudad }}', '{{ $p->correo }}', '{{ $p->telefono }}', {{ $p->activo ? 1 : 0 }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('adquisiciones.proveedores.destroy', $p) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Empresas --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-building mr-1"></i> Empresas</h3>
                <button class="btn btn-primary btn-sm"
                        data-toggle="modal" data-target="#modal-empresa">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-dark">
                        <tr><th>Clave</th><th>Nombre</th><th>RFC</th><th>Estado</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($empresas as $e)
                            <tr>
                                <td><span class="badge badge-primary">{{ $e->clave }}</span></td>
                                <td>{{ $e->nombre }}</td>
                                <td>{{ $e->rfc ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-{{ $e->activo ? 'success' : 'secondary' }}">
                                        {{ $e->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-xs"
                                            onclick="editarEmpresa({{ $e->id }}, '{{ $e->clave }}', '{{ addslashes($e->nombre) }}', '{{ $e->rfc }}', {{ $e->activo ? 1 : 0 }})">
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

    {{-- Unidades de medida --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-ruler mr-1"></i> Unidades de medida</h3>
                <button class="btn btn-primary btn-sm"
                        data-toggle="modal" data-target="#modal-unidad">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-dark">
                        <tr><th>Clave</th><th>Nombre</th><th>Estado</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($unidades as $u)
                            <tr>
                                <td><code>{{ $u->clave }}</code></td>
                                <td>{{ $u->nombre }}</td>
                                <td>
                                    <span class="badge badge-{{ $u->activo ? 'success' : 'secondary' }}">
                                        {{ $u->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-xs"
                                            onclick="editarUnidad({{ $u->id }}, '{{ $u->clave }}', '{{ addslashes($u->nombre) }}', {{ $u->activo ? 1 : 0 }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('adquisiciones.unidades-medida.destroy', $u) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ── Modales ─────────────────────────────────────────────── --}}

{{-- Modal Cliente --}}
<div class="modal fade" id="modal-cliente" tabindex="-1">
    <div class="modal-dialog">
        <form id="form-cliente" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="titulo-cliente">Nuevo cliente</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="cli-nombre" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contacto</label>
                                <input type="text" name="contacto" id="cli-contacto" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="telefono" id="cli-telefono" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Correo</label>
                        <input type="email" name="correo" id="cli-correo" class="form-control">
                    </div>
                    <div class="form-group" id="cli-activo-group" style="display:none">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" class="custom-control-input"
                                   id="cli-activo" name="activo" value="1">
                            <label class="custom-control-label" for="cli-activo">Activo</label>
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

{{-- Modal Proveedor --}}
<div class="modal fade" id="modal-proveedor" tabindex="-1">
    <div class="modal-dialog">
        <form id="form-proveedor" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="titulo-proveedor">Nuevo proveedor</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="prov-nombre" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>RFC</label>
                                <input type="text" name="rfc" id="prov-rfc" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Giro</label>
                                <input type="text" name="giro" id="prov-giro" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ciudad</label>
                                <input type="text" name="ciudad" id="prov-ciudad" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="telefono" id="prov-telefono" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Correo</label>
                        <input type="email" name="correo" id="prov-correo" class="form-control">
                    </div>
                    <div class="form-group" id="prov-activo-group" style="display:none">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" class="custom-control-input"
                                   id="prov-activo" name="activo" value="1">
                            <label class="custom-control-label" for="prov-activo">Activo</label>
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

{{-- Modal Empresa --}}
<div class="modal fade" id="modal-empresa" tabindex="-1">
    <div class="modal-dialog">
        <form id="form-empresa" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar empresa</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Clave <span class="text-danger">*</span></label>
                                <input type="text" name="clave" id="emp-clave"
                                       class="form-control text-uppercase" required maxlength="10">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="emp-nombre"
                                       class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>RFC</label>
                        <input type="text" name="rfc" id="emp-rfc" class="form-control">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" class="custom-control-input"
                                   id="emp-activo" name="activo" value="1" checked>
                            <label class="custom-control-label" for="emp-activo">Activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Unidad de medida --}}
<div class="modal fade" id="modal-unidad" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form id="form-unidad" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="titulo-unidad">Nueva unidad</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Clave <span class="text-danger">*</span></label>
                        <input type="text" name="clave" id="uni-clave"
                               class="form-control text-uppercase" required maxlength="10"
                               placeholder="Ej: PZA">
                    </div>
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="uni-nombre"
                               class="form-control" required placeholder="Ej: Pieza">
                    </div>
                    <div class="form-group" id="uni-activo-group" style="display:none">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" class="custom-control-input"
                                   id="uni-activo" name="activo" value="1">
                            <label class="custom-control-label" for="uni-activo">Activo</label>
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
const BASE = '{{ url('adquisiciones') }}';

// ── Clientes ──────────────────────────────────────────
function editarCliente(id, nombre, contacto, correo, telefono, activo) {
    const f = document.getElementById('form-cliente');
    f.action = BASE + '/clientes/' + id;
    // Agregar _method PUT
    let m = f.querySelector('input[name="_method"]');
    if (!m) { m = document.createElement('input'); m.type='hidden'; m.name='_method'; f.appendChild(m); }
    m.value = 'PUT';
    document.getElementById('cli-nombre').value   = nombre;
    document.getElementById('cli-contacto').value = contacto || '';
    document.getElementById('cli-correo').value   = correo || '';
    document.getElementById('cli-telefono').value = telefono || '';
    document.getElementById('cli-activo').checked = activo === 1;
    document.getElementById('cli-activo-group').style.display = 'block';
    document.getElementById('titulo-cliente').textContent = 'Editar cliente';
    $('#modal-cliente').modal('show');
}

document.getElementById('modal-cliente').addEventListener('hidden.bs.modal', function() {
    const f = document.getElementById('form-cliente');
    f.action = BASE + '/clientes';
    const m = f.querySelector('input[name="_method"]');
    if (m) m.remove();
    document.getElementById('cli-activo-group').style.display = 'none';
    document.getElementById('titulo-cliente').textContent = 'Nuevo cliente';
    f.reset();
});

// ── Proveedores ───────────────────────────────────────
function editarProveedor(id, nombre, rfc, giro, ciudad, correo, telefono, activo) {
    const f = document.getElementById('form-proveedor');
    f.action = BASE + '/proveedores/' + id;
    let m = f.querySelector('input[name="_method"]');
    if (!m) { m = document.createElement('input'); m.type='hidden'; m.name='_method'; f.appendChild(m); }
    m.value = 'PUT';
    document.getElementById('prov-nombre').value   = nombre;
    document.getElementById('prov-rfc').value      = rfc || '';
    document.getElementById('prov-giro').value     = giro || '';
    document.getElementById('prov-ciudad').value   = ciudad || '';
    document.getElementById('prov-correo').value   = correo || '';
    document.getElementById('prov-telefono').value = telefono || '';
    document.getElementById('prov-activo').checked = activo === 1;
    document.getElementById('prov-activo-group').style.display = 'block';
    document.getElementById('titulo-proveedor').textContent = 'Editar proveedor';
    $('#modal-proveedor').modal('show');
}

document.getElementById('modal-proveedor').addEventListener('hidden.bs.modal', function() {
    const f = document.getElementById('form-proveedor');
    f.action = BASE + '/proveedores';
    const m = f.querySelector('input[name="_method"]');
    if (m) m.remove();
    document.getElementById('prov-activo-group').style.display = 'none';
    document.getElementById('titulo-proveedor').textContent = 'Nuevo proveedor';
    f.reset();
});

// ── Empresas ──────────────────────────────────────────
function editarEmpresa(id, clave, nombre, rfc, activo) {
    const f = document.getElementById('form-empresa');
    f.action = BASE + '/empresas/' + id;
    let m = f.querySelector('input[name="_method"]');
    if (!m) { m = document.createElement('input'); m.type='hidden'; m.name='_method'; f.appendChild(m); }
    m.value = 'PUT';
    document.getElementById('emp-clave').value   = clave;
    document.getElementById('emp-nombre').value  = nombre;
    document.getElementById('emp-rfc').value     = rfc || '';
    document.getElementById('emp-activo').checked = activo === 1;
    $('#modal-empresa').modal('show');
}

// ── Unidades ──────────────────────────────────────────
function editarUnidad(id, clave, nombre, activo) {
    const f = document.getElementById('form-unidad');
    f.action = BASE + '/unidades-medida/' + id;
    let m = f.querySelector('input[name="_method"]');
    if (!m) { m = document.createElement('input'); m.type='hidden'; m.name='_method'; f.appendChild(m); }
    m.value = 'PUT';
    document.getElementById('uni-clave').value   = clave;
    document.getElementById('uni-nombre').value  = nombre;
    document.getElementById('uni-activo').checked = activo === 1;
    document.getElementById('uni-activo-group').style.display = 'block';
    document.getElementById('titulo-unidad').textContent = 'Editar unidad';
    $('#modal-unidad').modal('show');
}

document.getElementById('modal-unidad').addEventListener('hidden.bs.modal', function() {
    const f = document.getElementById('form-unidad');
    f.action = BASE + '/unidades-medida';
    const m = f.querySelector('input[name="_method"]');
    if (m) m.remove();
    document.getElementById('uni-activo-group').style.display = 'none';
    document.getElementById('titulo-unidad').textContent = 'Nueva unidad';
    f.reset();
});
</script>
@stop