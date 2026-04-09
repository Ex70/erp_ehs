@extends('adminlte::page')
@section('title', 'Dependencias / Empresas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-building mr-2"></i>Dependencias / Empresas</h1>
        <button class="btn btn-primary btn-sm"
                data-toggle="modal" data-target="#modal-dep">
            <i class="fas fa-plus"></i> Nueva dependencia
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
                        <th>Destinatarios</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dependencias as $i => $d)
                        <tr>
                            <td>{{ $dependencias->firstItem() + $i }}</td>
                            <td>{{ $d->nombre }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $d->destinatarios_count }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $d->activo ? 'success' : 'secondary' }}">
                                    {{ $d->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-xs"
                                        onclick="editarDep({{ $d->id }}, '{{ addslashes($d->nombre) }}', {{ $d->activo ? 1 : 0 }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('adquisiciones.dependencias.destroy', $d) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-xs"
                                            {{ $d->destinatarios_count > 0 ? 'disabled title=Tiene destinatarios asociados' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No hay dependencias registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $dependencias->links() }}
        </div>
    </div>

    {{-- Modal nuevo --}}
    <div class="modal fade" id="modal-dep" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <form id="form-dep" method="POST"
                  action="{{ route('adquisiciones.dependencias.store') }}">
                @csrf
                <input type="hidden" name="_method" id="method-dep" value="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="titulo-dep">Nueva dependencia</h5>
                        <button type="button" class="close"
                                data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="dep-nombre"
                                   class="form-control" required
                                   placeholder="Nombre de la dependencia o empresa">
                        </div>
                        <div class="form-group" id="dep-activo-group" style="display:none">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" class="custom-control-input"
                                       id="dep-activo" name="activo" value="1">
                                <label class="custom-control-label" for="dep-activo">
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
function editarDep(id, nombre, activo) {
    const f = document.getElementById('form-dep');
    f.action = '{{ url('adquisiciones/dependencias') }}/' + id;
    document.getElementById('method-dep').value = 'PUT';
    document.getElementById('dep-nombre').value  = nombre;
    document.getElementById('dep-activo').checked = activo === 1;
    document.getElementById('dep-activo-group').style.display = 'block';
    document.getElementById('titulo-dep').textContent = 'Editar dependencia';
    $('#modal-dep').modal('show');
}

document.getElementById('modal-dep').addEventListener('hidden.bs.modal', function () {
    const f = document.getElementById('form-dep');
    f.action = '{{ route('adquisiciones.dependencias.store') }}';
    document.getElementById('method-dep').value = 'POST';
    document.getElementById('dep-activo-group').style.display = 'none';
    document.getElementById('titulo-dep').textContent = 'Nueva dependencia';
    f.reset();
});
</script>
@stop