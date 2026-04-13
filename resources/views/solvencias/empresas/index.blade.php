@extends('adminlte::page')
@section('title', 'Empresas — Solvencias')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-building mr-2"></i>Empresas internas</h1>
        <button class="btn btn-primary btn-sm"
                data-toggle="modal" data-target="#modal-empresa">
            <i class="fas fa-plus"></i> Nueva empresa
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
                        <th>Nombre</th>
                        <th>RFC</th>
                        <th>Solvencias</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($empresas as $e)
                        <tr>
                            <td>{{ $e->nombre }}</td>
                            <td><code>{{ $e->rfc ?? '—' }}</code></td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $e->solvencias_count }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $e->activo ? 'success' : 'secondary' }}">
                                    {{ $e->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-xs"
                                        onclick="editarEmpresa({{ $e->id }}, '{{ addslashes($e->nombre) }}', '{{ $e->rfc }}', {{ $e->activo ? 1 : 0 }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('solvencias.empresas.destroy', $e) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-xs"
                                            {{ $e->solvencias_count > 0 ? 'disabled title=Tiene solvencias' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No hay empresas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $empresas->links() }}</div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="modal-empresa" tabindex="-1">
        <div class="modal-dialog">
            <form id="form-empresa" method="POST"
                  action="{{ route('solvencias.empresas.store') }}">
                @csrf
                <input type="hidden" name="_method" id="method-empresa" value="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="titulo-empresa">Nueva empresa</h5>
                        <button type="button" class="close"
                                data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="emp-nombre"
                                   class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>RFC</label>
                            <input type="text" name="rfc" id="emp-rfc"
                                   class="form-control text-uppercase">
                        </div>
                        <div class="form-group mb-0" id="emp-activo-group"
                             style="display:none">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" class="custom-control-input"
                                       id="emp-activo" name="activo" value="1">
                                <label class="custom-control-label" for="emp-activo">
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
function editarEmpresa(id, nombre, rfc, activo) {
    document.getElementById('form-empresa').action =
        '{{ url('solvencias/empresas') }}/' + id;
    document.getElementById('method-empresa').value = 'PUT';
    document.getElementById('emp-nombre').value  = nombre;
    document.getElementById('emp-rfc').value     = rfc || '';
    document.getElementById('emp-activo').checked = activo === 1;
    document.getElementById('emp-activo-group').style.display = 'block';
    document.getElementById('titulo-empresa').textContent = 'Editar empresa';
    $('#modal-empresa').modal('show');
}

document.getElementById('modal-empresa').addEventListener('hidden.bs.modal', function() {
    document.getElementById('form-empresa').action =
        '{{ route('solvencias.empresas.store') }}';
    document.getElementById('method-empresa').value = 'POST';
    document.getElementById('emp-activo-group').style.display = 'none';
    document.getElementById('titulo-empresa').textContent = 'Nueva empresa';
    document.getElementById('form-empresa').reset();
});
</script>
@stop