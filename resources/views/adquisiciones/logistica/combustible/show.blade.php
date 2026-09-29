@extends('adminlte::page')

@section('title', 'Solicitud ' . $solicitud->folio)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="mb-0"><i class="fas fa-gas-pump mr-2"></i>Solicitud {{ $solicitud->folio }}</h1>
            <small class="text-muted">{{ $solicitud->empresa->nombre }} · {{ $solicitud->fecha->format('d/m/Y') }}</small>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('adquisiciones.logistica.combustible.index') }}" class="btn btn-default">
                <i class="fas fa-list mr-1"></i> Listado
            </a>
            <a href="{{ route('adquisiciones.logistica.combustible.pdf', ['solicitud' => $solicitud, 'descargar' => 1]) }}" class="btn btn-success">
                <i class="fas fa-download mr-1"></i> Descargar PDF
            </a>
            @can('logistica.editar')
                <a href="{{ route('adquisiciones.logistica.combustible.edit', $solicitud) }}" class="btn btn-warning">
                    <i class="fas fa-edit mr-1"></i> Editar
                </a>
            @endcan
        </div>
    </div>
@stop

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-5">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Datos</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Folio</dt>
                        <dd class="col-sm-7"><strong>{{ $solicitud->folio }}</strong></dd>

                        <dt class="col-sm-5">Fecha</dt>
                        <dd class="col-sm-7">{{ $solicitud->fecha->format('d/m/Y') }}</dd>

                        <dt class="col-sm-5">Cotización / cliente</dt>
                        <dd class="col-sm-7">{{ $solicitud->cotizacion }} · {{ $solicitud->cliente }}</dd>

                        <dt class="col-sm-5">Monto</dt>
                        <dd class="col-sm-7"><strong>$ {{ number_format((float) $solicitud->monto, 2) }}</strong></dd>

                        <dt class="col-sm-5">Destinatario</dt>
                        <dd class="col-sm-7">{{ $solicitud->destinatario?->name ?? '—' }}</dd>

                        <dt class="col-sm-5">Banco / cuenta</dt>
                        <dd class="col-sm-7">
                            {{ $solicitud->banco ?: '—' }} · {{ $solicitud->cuenta ?: '—' }}
                            @if ($solicitud->clabe)
                                <small class="d-block text-muted">CLABE {{ $solicitud->clabe }}</small>
                            @endif
                        </dd>

                        <dt class="col-sm-5">Forma de pago</dt>
                        <dd class="col-sm-7">{{ $solicitud->forma_pago }}</dd>

                        @if ($solicitud->observaciones)
                            <dt class="col-sm-5">Observaciones</dt>
                            <dd class="col-sm-7">{{ $solicitud->observaciones }}</dd>
                        @endif

                        <dt class="col-sm-5">Elaboró</dt>
                        <dd class="col-sm-7">
                            {{ $solicitud->elaboro?->name ?? '—' }}
                            <small class="d-block text-muted">{{ $solicitud->elaboro_cargo }}</small>
                        </dd>

                        <dt class="col-sm-5">Capturó</dt>
                        <dd class="col-sm-7">
                            {{ $solicitud->creador?->name ?? '—' }}
                            <small class="d-block text-muted">{{ $solicitud->created_at->format('d/m/Y H:i') }}</small>
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-car mr-1"></i> Cargas ({{ $solicitud->conceptos->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:40px">Nº</th>
                                <th>Unidad</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($solicitud->conceptos as $c)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if ($c->vehiculo)
                                            <span class="d-inline-block rounded-circle mr-1 align-middle"
                                                  style="width:10px;height:10px;background:{{ $c->vehiculo->color_etiqueta }}"></span>
                                        @endif
                                        {{ $c->vehiculo?->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="text-right">$ {{ number_format((float) $c->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-pdf mr-1"></i> Vista previa</h3>
                    <div class="card-tools">
                        <a href="{{ route('adquisiciones.logistica.combustible.pdf', $solicitud) }}" target="_blank" class="btn btn-tool" title="Abrir en pestaña nueva">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <iframe src="{{ route('adquisiciones.logistica.combustible.pdf', $solicitud) }}"
                            style="width:100%;height:70vh;border:0" title="PDF de la solicitud"></iframe>
                </div>
            </div>
        </div>
    </div>
@stop