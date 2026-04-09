@extends('adminlte::page')
@section('title', 'Base de datos de clientes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-users mr-2"></i>
                Base de datos de clientes
            </h1>
            <small class="text-muted">
                Directorio completo de clientes —
                {{ $stats['total'] }} registrado{{ $stats['total'] != 1 ? 's' : '' }}
            </small>
        </div>
        <div>
            <button class="btn btn-success btn-sm mr-1" onclick="exportarExcel()">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
            <button class="btn btn-danger btn-sm"
                    data-toggle="modal" data-target="#modal-destinatario">
                <i class="fas fa-plus"></i> Nuevo cliente
            </button>
        </div>
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

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET"
                  action="{{ route('adquisiciones.destinatarios.index') }}"
                  class="d-flex align-items-center flex-wrap gap-2">
                <div class="flex-grow-1" style="min-width:260px">
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="NOMBRE, DEPENDENCIA, CARGO, LUGAR..."
                           value="{{ request('q') }}">
                </div>
                <div style="min-width:200px">
                    <select name="q_dependencia" class="form-control form-control-sm">
                        <option value="">TODAS</option>
                        @foreach($dependencias as $d)
                            <option value="{{ $d->id }}"
                                {{ request('q_dependencia') == $d->id ? 'selected' : '' }}>
                                {{ $d->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:150px">
                    <input type="text" name="q_lugar"
                           class="form-control form-control-sm"
                           placeholder="LUGAR..."
                           value="{{ request('q_lugar') }}">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i>
                </button>
                <a href="{{ route('adquisiciones.destinatarios.index') }}"
                   class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </form>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0"
                   style="min-width:900px" id="tabla-destinatarios">
                <thead class="thead-dark">
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Dirigido a</th>
                        <th>Cargo</th>
                        <th>Dependencia</th>
                        <th>Con atención a</th>
                        <th>Lugar</th>
                        <th>Correo electrónico</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($destinatarios as $i => $d)
                        <tr>
                            <td>{{ $destinatarios->firstItem() + $i }}</td>
                            <td><strong>{{ $d->dirigido_a }}</strong></td>
                            <td>{{ $d->cargo ?? '—' }}</td>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $d->dependencia?->nombre ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $d->atencion_a ?? '—' }}</td>
                            <td>{{ $d->lugar ?? '—' }}</td>
                            <td>
                                @if($d->correo)
                                    <a href="mailto:{{ $d->correo }}">
                                        {{ $d->correo }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                {{ $d->telefono ?? '—' }}
                                @if($d->telefono_secundario)
                                    <br>
                                    <small class="text-muted">
                                        {{ $d->telefono_secundario }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-warning btn-xs"
                                        onclick="editarDestinatario({{ $d->id }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('adquisiciones.destinatarios.destroy', $d) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar a {{ addslashes($d->dirigido_a) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-xs">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                SIN CLIENTES REGISTRADOS —
                                AGREGA EL PRIMERO CON "+ NUEVO CLIENTE"
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">
                {{ $destinatarios->total() }} registro(s) encontrado(s)
            </small>
            {{ $destinatarios->withQueryString()->links() }}
        </div>
    </div>

    {{-- Modal Nuevo / Editar --}}
    <div class="modal fade" id="modal-destinatario" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="form-destinatario" method="POST"
                  action="{{ route('adquisiciones.destinatarios.store') }}">
                @csrf
                <input type="hidden" name="_method" id="method-destinatario" value="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="titulo-modal-destinatario">
                            <i class="fas fa-user-plus mr-1"></i> Nuevo cliente
                        </h5>
                        <button type="button" class="close"
                                data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">

                        {{-- Dirigido a --}}
                        <div class="form-group">
                            <label>
                                Dirigido a (nombre del cliente / representante)
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="dirigido_a" id="d-dirigido"
                                   class="form-control"
                                   placeholder="NOMBRE COMPLETO DE LA PERSONA"
                                   required>
                        </div>

                        {{-- Cargo y Dependencia --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cargo de la persona</label>
                                    <input type="text" name="cargo" id="d-cargo"
                                           class="form-control"
                                           placeholder="EJ: DIRECTOR GENERAL, JEFE DE COMPRAS...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>
                                        Dependencia / Empresa
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <select name="dependencia_id" id="d-dependencia"
                                                class="form-control" required>
                                            <option value="">
                                                NOMBRE DE LA DEPENDENCIA O EMPRESA
                                            </option>
                                            @foreach($dependencias as $dep)
                                                <option value="{{ $dep->id }}">
                                                    {{ $dep->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button type="button"
                                                    class="btn btn-outline-secondary btn-sm"
                                                    onclick="nuevaDependencia()"
                                                    title="Agregar dependencia">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Atención y Lugar --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Con atención a</label>
                                    <input type="text" name="atencion_a" id="d-atencion"
                                           class="form-control"
                                           placeholder="PERSONA QUE DA SEGUIMIENTO">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Lugar / Ubicación</label>
                                    <input type="text" name="lugar" id="d-lugar"
                                           class="form-control"
                                           placeholder="CIUDAD, ESTADO O DIRECCIÓN">
                                </div>
                            </div>
                        </div>

                        {{-- Correo y Teléfono --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Correo electrónico</label>
                                    <input type="email" name="correo" id="d-correo"
                                           class="form-control"
                                           placeholder="CORREO@DEPENDENCIA.GOB.MX">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Teléfono principal</label>
                                    <input type="text" name="telefono" id="d-telefono"
                                           class="form-control"
                                           placeholder="10 DÍGITOS">
                                </div>
                            </div>
                        </div>

                        {{-- Teléfono secundario --}}
                        <div class="form-group">
                            <label>Teléfono secundario / Extensión</label>
                            <input type="text" name="telefono_secundario" id="d-telefono2"
                                   class="form-control"
                                   placeholder="OPCIONAL">
                        </div>

                        {{-- Dirección --}}
                        <div class="form-group">
                            <label>Dirección completa</label>
                            <input type="text" name="direccion" id="d-direccion"
                                   class="form-control"
                                   placeholder="CALLE, NÚMERO, COLONIA, CP, MUNICIPIO">
                        </div>

                        {{-- Observaciones --}}
                        <div class="form-group">
                            <label>Observaciones / Notas</label>
                            <textarea name="observaciones" id="d-observaciones"
                                      class="form-control" rows="3"
                                      placeholder="NOTAS IMPORTANTES, HORARIOS DE ATENCIÓN, REQUISITOS ESPECIALES..."></textarea>
                        </div>

                        {{-- Estado (solo edición) --}}
                        <div id="d-activo-group" style="display:none">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" class="custom-control-input"
                                       id="d-activo" name="activo" value="1">
                                <label class="custom-control-label" for="d-activo">
                                    Destinatario activo
                                </label>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"
                                id="btn-guardar-dest">
                            <i class="fas fa-save"></i> Guardar cliente
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal rápido para nueva dependencia --}}
    <div class="modal fade" id="modal-dependencia-rapida" tabindex="-1"
         style="z-index:1060">
        <div class="modal-dialog modal-sm">
            <form id="form-dep-rapida" method="POST"
                  action="{{ route('adquisiciones.dependencias.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nueva dependencia</h5>
                        <button type="button" class="close"
                                data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="dep-rapida-nombre"
                                   class="form-control" required
                                   placeholder="Nombre de la dependencia">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm"
                                data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
const destinatariosData = @json($destinatarios->items());
const BASE = '{{ url('adquisiciones/destinatarios') }}';

// ── Editar destinatario ─────────────────────────────────────────
function editarDestinatario(id) {
    const d = destinatariosData.find(x => x.id === id);
    if (!d) return;

    const f = document.getElementById('form-destinatario');
    f.action = BASE + '/' + id;
    document.getElementById('method-destinatario').value = 'PUT';

    document.getElementById('d-dirigido').value    = d.dirigido_a || '';
    document.getElementById('d-cargo').value       = d.cargo || '';
    document.getElementById('d-dependencia').value = d.dependencia_id || '';
    document.getElementById('d-atencion').value    = d.atencion_a || '';
    document.getElementById('d-lugar').value       = d.lugar || '';
    document.getElementById('d-correo').value      = d.correo || '';
    document.getElementById('d-telefono').value    = d.telefono || '';
    document.getElementById('d-telefono2').value   = d.telefono_secundario || '';
    document.getElementById('d-direccion').value   = d.direccion || '';
    document.getElementById('d-observaciones').value = d.observaciones || '';
    document.getElementById('d-activo').checked    = d.activo;
    document.getElementById('d-activo-group').style.display = 'block';

    document.getElementById('titulo-modal-destinatario').innerHTML =
        '<i class="fas fa-edit mr-1"></i> Editar cliente';
    document.getElementById('btn-guardar-dest').textContent = 'Actualizar cliente';

    $('#modal-destinatario').modal('show');
}

// Resetear modal al cerrar
document.getElementById('modal-destinatario')
    .addEventListener('hidden.bs.modal', function () {
        const f = document.getElementById('form-destinatario');
        f.action = '{{ route('adquisiciones.destinatarios.store') }}';
        document.getElementById('method-destinatario').value = 'POST';
        document.getElementById('d-activo-group').style.display = 'none';
        document.getElementById('titulo-modal-destinatario').innerHTML =
            '<i class="fas fa-user-plus mr-1"></i> Nuevo cliente';
        document.getElementById('btn-guardar-dest').textContent = 'Guardar cliente';
        f.reset();
    });

// ── Nueva dependencia rápida ────────────────────────────────────
function nuevaDependencia() {
    $('#modal-dependencia-rapida').modal('show');
}

// Al guardar dependencia rápida, recargar el select del modal principal
document.getElementById('form-dep-rapida').addEventListener('submit', function(e) {
    e.preventDefault();
    const nombre = document.getElementById('dep-rapida-nombre').value.trim();
    if (!nombre) return;

    fetch('{{ route('adquisiciones.dependencias.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ nombre }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.id) {
            const sel = document.getElementById('d-dependencia');
            const opt = new Option(data.nombre, data.id, true, true);
            sel.appendChild(opt);
            $('#modal-dependencia-rapida').modal('hide');
            document.getElementById('dep-rapida-nombre').value = '';
        }
    })
    .catch(() => alert('Error al guardar la dependencia.'));
});

// ── Exportar Excel ──────────────────────────────────────────────
function exportarExcel() {
    const rows = [[
        '#', 'Dirigido a', 'Cargo', 'Dependencia',
        'Con atención a', 'Lugar', 'Correo', 'Teléfono'
    ]];

    document.querySelectorAll('#tabla-destinatarios tbody tr').forEach((tr, i) => {
        const tds = tr.querySelectorAll('td');
        if (tds.length < 2) return;
        rows.push([
            i + 1,
            tds[1]?.innerText?.trim() || '',
            tds[2]?.innerText?.trim() || '',
            tds[3]?.innerText?.trim() || '',
            tds[4]?.innerText?.trim() || '',
            tds[5]?.innerText?.trim() || '',
            tds[6]?.innerText?.trim() || '',
            tds[7]?.innerText?.trim() || '',
        ]);
    });

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(rows);
    XLSX.utils.book_append_sheet(wb, ws, 'Clientes');
    XLSX.writeFile(wb, 'Clientes_' + new Date().toISOString().slice(0,10) + '.xlsx');
}
</script>
@stop