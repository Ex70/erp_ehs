@extends('adminlte::page')
@section('title', 'Requerimiento ' . $requerimiento->folio)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-file-alt mr-2"></i>
            {{ $requerimiento->folio }}
        </h1>
        <div>
            @can('adquisiciones.editar')
                <a href="{{ route('adquisiciones.requerimientos.edit', $requerimiento) }}"
                   class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Editar
                </a>
            @endcan
            @can('adquisiciones.adjudicar')
                @if(!$requerimiento->autorizado)
                    <button class="btn btn-success btn-sm"
                            data-toggle="modal" data-target="#modal-adjudicar">
                        <i class="fas fa-check"></i> Adjudicar
                    </button>
                @endif
            @endcan
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

    <div class="row">

        {{-- Columna izquierda: datos generales --}}
        <div class="col-md-4">

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Datos generales</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <tr><th>Folio</th>
                            <td><strong>{{ $requerimiento->folio }}</strong></td></tr>
                        <tr><th>Cliente</th>
                            <td>{{ $requerimiento->cliente?->nombre ?? '—' }}</td></tr>
                        <tr><th>Empresa emisora</th>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $requerimiento->empresaEmisora?->clave ?? '—' }}
                                </span>
                            </td></tr>
                        <tr><th>Empresa realiza</th>
                            <td>{{ $requerimiento->empresa_realiza ?? '—' }}</td></tr>
                        <tr><th>Analista</th>
                            <td>{{ $requerimiento->analista?->name ?? '—' }}</td></tr>
                        <tr><th>Tipo</th>
                            <td>
                                @php $tipoCls = ['normal'=>'secondary','urgente'=>'warning','critico'=>'danger']; @endphp
                                <span class="badge badge-{{ $tipoCls[$requerimiento->tipo] ?? 'secondary' }}">
                                    {{ ucfirst($requerimiento->tipo) }}
                                </span>
                            </td></tr>
                        <tr><th>Línea negocio</th>
                            <td>{{ $requerimiento->linea_negocio ?? '—' }}</td></tr>
                        <tr><th>Fecha solicitud</th>
                            <td>{{ $requerimiento->fecha_solicitud?->format('d/m/Y') }}</td></tr>
                        <tr><th>Fecha entrega</th>
                            <td>{{ $requerimiento->fecha_entrega?->format('d/m/Y') ?? '—' }}</td></tr>
                        <tr><th>Estatus</th>
                            <td>
                                @php
                                    $cls = ['pendiente'=>'secondary','cotizando'=>'info',
                                            'enviado'=>'warning','autorizado'=>'success','cancelado'=>'danger'];
                                @endphp
                                <span class="badge badge-{{ $cls[$requerimiento->status] ?? 'secondary' }}">
                                    {{ ucfirst($requerimiento->status) }}
                                </span>
                            </td></tr>
                    </table>
                </div>
            </div>

            {{-- Montos --}}
            @if($requerimiento->autorizado)
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Adjudicación</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <tr><th>Monto autorizado</th>
                                <td class="text-success font-weight-bold">
                                    ${{ number_format($requerimiento->monto_autorizado, 2) }}
                                </td></tr>
                            <tr><th>Costo proveedor</th>
                                <td>${{ number_format($requerimiento->costo_proveedor, 2) }}</td></tr>
                            <tr><th>Ganancia</th>
                                <td class="text-success font-weight-bold">
                                    ${{ number_format($requerimiento->ganancia, 2) }}
                                </td></tr>
                            <tr><th>Margen real</th>
                                <td>{{ $requerimiento->margen_real }}%</td></tr>
                            <tr><th>F. máx. entrega</th>
                                <td>{{ $requerimiento->fecha_max_entrega_aut?->format('d/m/Y') ?? '—' }}</td></tr>
                        </table>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered mb-0">
                            <tr><th>Monto estimado</th>
                                <td>${{ $requerimiento->monto_estimado ? number_format($requerimiento->monto_estimado, 2) : '—' }}</td></tr>
                            <tr><th>Margen</th>
                                <td>{{ $requerimiento->margen }}%</td></tr>
                            <tr><th>Indirectos</th>
                                <td>{{ $requerimiento->indirectos }}%</td></tr>
                        </table>
                    </div>
                </div>
            @endif

        </div>

        {{-- Columna derecha --}}
        <div class="col-md-8">

            {{-- Partidas --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-1"></i> Partidas
                    </h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Descripción</th>
                                <th>Cant.</th>
                                <th>Unidad</th>
                                <th>P. Proveedor</th>
                                <th>P. Cliente</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalCliente = 0; @endphp
                            @forelse($requerimiento->partidas as $i => $p)
                                @php $totalCliente += $p->subtotal_cliente; @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $p->descripcion }}</td>
                                    <td>{{ $p->cantidad }}</td>
                                    <td>{{ $p->unidadMedida?->clave ?? '—' }}</td>
                                    <td>{{ $p->precio_proveedor ? '$'.number_format($p->precio_proveedor, 2) : '—' }}</td>
                                    <td>{{ $p->precio_cliente ? '$'.number_format($p->precio_cliente, 2) : '—' }}</td>
                                    <td>{{ $p->precio_cliente ? '$'.number_format($p->subtotal_cliente, 2) : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">Sin partidas.</td></tr>
                            @endforelse
                        </tbody>
                        @if($requerimiento->partidas->count())
                            <tfoot>
                                <tr class="table-active">
                                    <th colspan="6" class="text-right">Total:</th>
                                    <th>${{ number_format($totalCliente, 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Proveedores --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-truck mr-1"></i> Proveedores cotizantes
                    </h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>Proveedor</th>
                                <th>Monto</th>
                                <th>T. Entrega</th>
                                <th>C. Envío</th>
                                <th>Disponib.</th>
                                <th>Ganador</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requerimiento->proveedores as $pv)
                                <tr class="{{ $pv->ganador ? 'table-success' : '' }}">
                                    <td>{{ $pv->proveedor?->nombre ?? '—' }}</td>
                                    <td>{{ $pv->monto ? '$'.number_format($pv->monto, 2) : '—' }}</td>
                                    <td>{{ $pv->tiempo_entrega ?? '—' }}</td>
                                    <td>{{ $pv->costo_envio ? '$'.number_format($pv->costo_envio, 2) : '—' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $pv->disponibilidad == 'SI' ? 'success' : ($pv->disponibilidad == 'NO' ? 'danger' : 'warning') }}">
                                            {{ $pv->disponibilidad }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($pv->ganador)
                                            <span class="badge badge-success">
                                                <i class="fas fa-trophy"></i> Ganador
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">Sin proveedores.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Observaciones --}}
            @if($requerimiento->observaciones)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Observaciones</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $requerimiento->observaciones }}</p>
                    </div>
                </div>
            @endif

            {{-- Notas --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-sticky-note mr-1"></i> Notas del expediente
                    </h3>
                </div>
                <div class="card-body">
                    @forelse($requerimiento->notas as $nota)
                        <div class="d-flex justify-content-between align-items-start mb-2
                                    p-2 bg-light rounded">
                            <div>
                                <strong class="small">{{ $nota->usuario?->name ?? '—' }}</strong>
                                <span class="text-muted small ml-2">
                                    {{ $nota->created_at->format('d/m/Y H:i') }}
                                </span>
                                <p class="mb-0 mt-1">{{ $nota->texto }}</p>
                            </div>
                            @can('adquisiciones.editar')
                                <form action="{{ route('adquisiciones.notas.destroy', $nota) }}"
                                      method="POST"
                                      onsubmit="return confirm('¿Eliminar nota?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @empty
                        <p class="text-muted mb-2">Sin notas.</p>
                    @endforelse

                    {{-- Agregar nota --}}
                    <form action="{{ route('adquisiciones.requerimientos.notas.store', $requerimiento) }}"
                          method="POST" class="mt-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="texto" class="form-control"
                                   placeholder="Escribir una nota..." required maxlength="1000">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal adjudicación --}}
    @can('adquisiciones.adjudicar')
        @if(!$requerimiento->autorizado)
            <div class="modal fade" id="modal-adjudicar" tabindex="-1">
                <div class="modal-dialog">
                    <form action="{{ route('adquisiciones.requerimientos.adjudicar', $requerimiento) }}"
                          method="POST">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-check-circle mr-1"></i> Adjudicar
                                </h5>
                                <button type="button" class="close text-white"
                                        data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Monto autorizado <span class="text-danger">*</span></label>
                                            <input type="number" name="monto_autorizado"
                                                   class="form-control" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Costo proveedor <span class="text-danger">*</span></label>
                                            <input type="number" name="costo_proveedor"
                                                   class="form-control" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Proveedor ganador</label>
                                    <select name="proveedor_ganador_id" class="form-control">
                                        <option value="">— Ninguno —</option>
                                        @foreach($requerimiento->proveedores as $pv)
                                            <option value="{{ $pv->proveedor_id }}">
                                                {{ $pv->proveedor?->nombre ?? '—' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Fecha máxima de entrega</label>
                                    <input type="date" name="fecha_max_entrega_aut"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Adjudicar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endcan

@stop
@section('css')@stop
@section('js')@stop