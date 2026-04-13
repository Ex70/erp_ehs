@extends('adminlte::page')
@section('title', 'Base de datos de proveedores')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-database mr-2"></i>
                Base de datos de proveedores
            </h1>
            <small class="text-muted">
                Directorio completo de proveedores —
                {{ $stats['total'] }} registrado{{ $stats['total'] != 1 ? 's' : '' }}
            </small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-warning btn-sm mr-1" onclick="verRanking()">
                <i class="fas fa-trophy"></i> Ranking proveedores
            </button>
            <button class="btn btn-success btn-sm mr-1" onclick="exportarExcel()">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
            <button class="btn btn-danger btn-sm"
                    data-toggle="modal" data-target="#modal-proveedor">
                <i class="fas fa-plus"></i> Nuevo proveedor
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
            <form method="GET" action="{{ route('adquisiciones.proveedores.index') }}"
                  class="d-flex align-items-center gap-2 flex-wrap">
                <div class="flex-grow-1" style="min-width:260px">
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="NOMBRE, CIUDAD, RFC, GIRO..."
                           value="{{ request('q') }}">
                </div>
                <div style="min-width:180px">
                    <select name="q_giro" class="form-control form-control-sm">
                        <option value="">TODOS</option>
                        @foreach($giros as $g)
                            <option value="{{ $g }}"
                                {{ request('q_giro') == $g ? 'selected' : '' }}>
                                {{ $g }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:150px">
                    <input type="text" name="q_ciudad" class="form-control form-control-sm"
                           placeholder="CIUDAD..."
                           value="{{ request('q_ciudad') }}">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <a href="{{ route('adquisiciones.proveedores.index') }}"
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
                   style="min-width:900px" id="tabla-proveedores">
                <thead class="thead-dark">
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Nombre / Razón social</th>
                        <th>Giro / Categoría</th>
                        <th>Ciudad</th>
                        <th>RFC</th>
                        <th>Contacto</th>
                        <th>Teléfono(s)</th>
                        <th>Email</th>
                        <th>Condiciones pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proveedores as $i => $p)
                        <tr>
                            <td>{{ $proveedores->firstItem() + $i }}</td>
                            <td>
                                <strong>{{ $p->nombre }}</strong>
                                @if($p->observaciones)
                                    <br><small class="text-muted">{{ Str::limit($p->observaciones, 40) }}</small>
                                @endif
                            </td>
                            <td>{{ $p->giro ?? '—' }}</td>
                            <td>{{ $p->ciudad ?? '—' }}</td>
                            <td><code>{{ $p->rfc ?? '—' }}</code></td>
                            <td>{{ $p->contacto ?? '—' }}</td>
                            <td>
                                {{ $p->telefono ?? '—' }}
                                @if($p->telefono_secundario)
                                    <br><small class="text-muted">{{ $p->telefono_secundario }}</small>
                                @endif
                            </td>
                            <td>
                                @if($p->correo)
                                    <a href="mailto:{{ $p->correo }}">
                                        {{ $p->correo }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $p->condiciones_pago ?? '—' }}</td>
                            <td>
                                <a href="{{ route('adquisiciones.proveedores.show', $p) }}"
                                class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-warning btn-xs"
                                        onclick="editarProveedor({{ $p->id }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('adquisiciones.proveedores.destroy', $p) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar a {{ addslashes($p->nombre) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-xs"
                                            {{ $p->requerimientoProveedores()->count() > 0 ? 'disabled title=Tiene requerimientos asociados' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                SIN PROVEEDORES REGISTRADOS —
                                AGREGA EL PRIMERO CON "+ NUEVO PROVEEDOR"
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">
                {{ $proveedores->total() }} proveedor(es) encontrado(s)
            </small>
            {{ $proveedores->withQueryString()->links() }}
        </div>
    </div>

    {{-- Modal Nuevo / Editar proveedor --}}
    <div class="modal fade" id="modal-proveedor" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="form-proveedor" method="POST"
                  action="{{ route('adquisiciones.proveedores.store') }}">
                @csrf
                <input type="hidden" name="_method" id="method-proveedor" value="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="titulo-modal-proveedor">
                            <i class="fas fa-plus-circle mr-1"></i> Nuevo proveedor
                        </h5>
                        <button type="button" class="close"
                                data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">

                        {{-- Fila 1 --}}
                        <div class="form-group">
                            <label>Nombre / Razón social <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="p-nombre"
                                   class="form-control"
                                   placeholder="NOMBRE DEL PROVEEDOR O EMPRESA"
                                   required>
                        </div>

                        {{-- Fila 2 --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>RFC</label>
                                    <input type="text" name="rfc" id="p-rfc"
                                           class="form-control text-uppercase"
                                           placeholder="RFC123456XXX">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Giro / Categoría</label>
                                    <input type="text" name="giro" id="p-giro"
                                           class="form-control"
                                           placeholder="EJ: FERRETERÍA, PAPELERÍA, TI..."
                                           list="dl-giros">
                                    <datalist id="dl-giros">
                                        @foreach($giros as $g)
                                            <option value="{{ $g }}">
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>
                        </div>

                        {{-- Fila 3 --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ciudad</label>
                                    <input type="text" name="ciudad" id="p-ciudad"
                                           class="form-control"
                                           placeholder="CIUDAD, ESTADO">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombre del contacto</label>
                                    <input type="text" name="contacto" id="p-contacto"
                                           class="form-control"
                                           placeholder="NOMBRE DEL CONTACTO">
                                </div>
                            </div>
                        </div>

                        {{-- Fila 4 --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="correo" id="p-correo"
                                           class="form-control"
                                           placeholder="CONTACTO@EMPRESA.COM">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Teléfono principal</label>
                                    <input type="text" name="telefono" id="p-telefono"
                                           class="form-control"
                                           placeholder="10 DÍGITOS">
                                </div>
                            </div>
                        </div>

                        {{-- Fila 5 --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Teléfono secundario</label>
                                    <input type="text" name="telefono_secundario"
                                           id="p-telefono2"
                                           class="form-control"
                                           placeholder="OPCIONAL">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Condiciones de pago</label>
                                    <input type="text" name="condiciones_pago"
                                           id="p-condiciones"
                                           class="form-control"
                                           placeholder="EJ: CRÉDITO 30 DÍAS, CONTADO...">
                                </div>
                            </div>
                        </div>

                        {{-- Fila 6 --}}
                        <div class="form-group">
                            <label>Tiempo de entrega habitual</label>
                            <input type="text" name="tiempo_entrega" id="p-tiempo"
                                   class="form-control"
                                   placeholder="EJ: 3-5 DÍAS HÁBILES">
                        </div>

                        {{-- Fila 7 --}}
                        <div class="form-group">
                            <label>Dirección</label>
                            <input type="text" name="direccion" id="p-direccion"
                                   class="form-control"
                                   placeholder="CALLE, NÚMERO, COLONIA, CP">
                        </div>

                        {{-- Fila 8 --}}
                        <div class="form-group">
                            <label>Observaciones / Notas</label>
                            <textarea name="observaciones" id="p-observaciones"
                                      class="form-control" rows="3"
                                      placeholder="NOTAS IMPORTANTES, ESPECIALIDADES, DESCUENTOS, ETC."></textarea>
                        </div>

                        {{-- Estado (solo en edición) --}}
                        <div id="p-activo-group" style="display:none">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" class="custom-control-input"
                                       id="p-activo" name="activo" value="1">
                                <label class="custom-control-label" for="p-activo">
                                    Proveedor activo
                                </label>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btn-guardar-prov">
                            <i class="fas fa-save"></i> Guardar proveedor
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Ranking --}}
    <div class="modal fade" id="modal-ranking" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-trophy mr-1"></i> Ranking de proveedores
                    </h5>
                    <button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Proveedor</th>
                                <th>Participaciones</th>
                                <th>Adjudicados</th>
                                <th>Efectividad</th>
                            </tr>
                        </thead>
                        <tbody id="ranking-body">
                            <tr>
                                <td colspan="5" class="text-center py-3">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
// ── Datos para el proveedor en edición ──────────────────────────
const proveedoresData = @json($proveedores->items());

function editarProveedor(id) {
    // Buscar en la página actual
    const p = proveedoresData.find(x => x.id === id);
    if (!p) return;

    const f = document.getElementById('form-proveedor');
    f.action = '{{ url('adquisiciones/proveedores') }}/' + id;
    document.getElementById('method-proveedor').value = 'PUT';

    document.getElementById('p-nombre').value      = p.nombre || '';
    document.getElementById('p-rfc').value         = p.rfc || '';
    document.getElementById('p-giro').value        = p.giro || '';
    document.getElementById('p-ciudad').value      = p.ciudad || '';
    document.getElementById('p-contacto').value    = p.contacto || '';
    document.getElementById('p-correo').value      = p.correo || '';
    document.getElementById('p-telefono').value    = p.telefono || '';
    document.getElementById('p-telefono2').value   = p.telefono_secundario || '';
    document.getElementById('p-condiciones').value = p.condiciones_pago || '';
    document.getElementById('p-tiempo').value      = p.tiempo_entrega || '';
    document.getElementById('p-direccion').value   = p.direccion || '';
    document.getElementById('p-observaciones').value = p.observaciones || '';
    document.getElementById('p-activo').checked   = p.activo;
    document.getElementById('p-activo-group').style.display = 'block';

    document.getElementById('titulo-modal-proveedor').innerHTML =
        '<i class="fas fa-edit mr-1"></i> Editar proveedor';
    document.getElementById('btn-guardar-prov').textContent = 'Actualizar proveedor';

    $('#modal-proveedor').modal('show');
}

// Resetear modal al cerrar
document.getElementById('modal-proveedor')
    .addEventListener('hidden.bs.modal', function () {
        const f = document.getElementById('form-proveedor');
        f.action = '{{ route('adquisiciones.proveedores.store') }}';
        document.getElementById('method-proveedor').value = 'POST';
        document.getElementById('p-activo-group').style.display = 'none';
        document.getElementById('titulo-modal-proveedor').innerHTML =
            '<i class="fas fa-plus-circle mr-1"></i> Nuevo proveedor';
        document.getElementById('btn-guardar-prov').textContent = 'Guardar proveedor';
        f.reset();
    });

// ── Ranking ─────────────────────────────────────────────────────
function verRanking() {
    $('#modal-ranking').modal('show');
    fetch('{{ route('adquisiciones.proveedores.ranking') }}')
        .then(r => r.json())
        .then(data => {
            const body = document.getElementById('ranking-body');
            if (!data.length) {
                body.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Sin datos.</td></tr>';
                return;
            }
            body.innerHTML = data.map((p, i) => {
                const efectividad = p.total_participaciones > 0
                    ? Math.round((p.total_ganados / p.total_participaciones) * 100)
                    : 0;
                return `<tr>
                    <td><strong>${i + 1}</strong></td>
                    <td>${p.nombre}</td>
                    <td><span class="badge badge-info">${p.total_participaciones}</span></td>
                    <td><span class="badge badge-success">${p.total_ganados}</span></td>
                    <td>
                        <div class="progress" style="height:18px;min-width:80px">
                            <div class="progress-bar bg-success" style="width:${efectividad}%">
                                ${efectividad}%
                            </div>
                        </div>
                    </td>
                </tr>`;
            }).join('');
        })
        .catch(() => {
            document.getElementById('ranking-body').innerHTML =
                '<tr><td colspan="5" class="text-center text-danger">Error al cargar.</td></tr>';
        });
}

// ── Exportar Excel ───────────────────────────────────────────────
function exportarExcel() {
    const rows = [
        ['#','Nombre','Giro','Ciudad','RFC','Contacto','Teléfono','Email','Condiciones pago','T. Entrega','Dirección']
    ];

    document.querySelectorAll('#tabla-proveedores tbody tr').forEach((tr, i) => {
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
            tds[8]?.innerText?.trim() || '',
            '', '',
        ]);
    });

    const wb  = XLSX.utils.book_new();
    const ws  = XLSX.utils.aoa_to_sheet(rows);
    XLSX.utils.book_append_sheet(wb, ws, 'Proveedores');
    XLSX.writeFile(wb, 'Proveedores_' + new Date().toISOString().slice(0,10) + '.xlsx');
}
</script>
@stop