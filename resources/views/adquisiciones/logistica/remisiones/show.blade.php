@extends('adminlte::page')

@section('title', 'Remisión ' . $remision->folio)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="mb-0"><i class="fas fa-file-signature mr-2"></i>Remisión {{ $remision->folio }}</h1>
            <small class="text-muted">{{ $remision->empresa->nombre }} · {{ $remision->fecha->format('d/m/Y') }}</small>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('adquisiciones.logistica.remisiones.index') }}" class="btn btn-default">
                <i class="fas fa-list mr-1"></i> Listado
            </a>
            <a href="{{ route('adquisiciones.logistica.remisiones.pdf', ['remision' => $remision, 'descargar' => 1]) }}" class="btn btn-success">
                <i class="fas fa-download mr-1"></i> Descargar PDF
            </a>
            @can('logistica.editar')
                <a href="{{ route('adquisiciones.logistica.remisiones.edit', $remision) }}" class="btn btn-warning">
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
                        <dt class="col-sm-5">Empresa</dt>
                        <dd class="col-sm-7">
                            <span class="badge" style="background: {{ $formato['color'] ?? '#6c757d' }}; color: #fff;">{{ $remision->empresa->clave }}</span>
                            {{ $remision->empresa->nombre }}
                        </dd>

                        <dt class="col-sm-5">Folio</dt>
                        <dd class="col-sm-7"><strong>{{ $remision->folio }}</strong></dd>

                        @if (! empty($formato['num_remision']))
                            <dt class="col-sm-5">Núm. de remisión</dt>
                            <dd class="col-sm-7">{{ $remision->num_remision ?: '—' }}</dd>
                        @endif

                        <dt class="col-sm-5">Fecha</dt>
                        <dd class="col-sm-7">{{ $remision->fecha->format('d/m/Y') }}</dd>

                        <dt class="col-sm-5">Cliente</dt>
                        <dd class="col-sm-7">{{ $remision->dependencia->nombre }}</dd>

                        <dt class="col-sm-5">Referencia</dt>
                        <dd class="col-sm-7">{{ $remision->referencia ?: '—' }}</dd>

                        @if (! empty($formato['responsables']))
                            <dt class="col-sm-5">Entrega</dt>
                            <dd class="col-sm-7">{{ $remision->entrega?->name ?? '—' }}</dd>

                            <dt class="col-sm-5">Recibe</dt>
                            <dd class="col-sm-7">
                                {{ $remision->recibe_nombre ?: '—' }}
                                @if ($remision->recibe_cargo)
                                    <small class="d-block text-muted">{{ $remision->recibe_cargo }}</small>
                                @endif
                            </dd>
                        @endif

                        @if ($remision->observaciones)
                            <dt class="col-sm-5">Observaciones</dt>
                            <dd class="col-sm-7">{!! nl2br(e($remision->observaciones)) !!}</dd>
                        @endif

                        <dt class="col-sm-5">Capturó</dt>
                        <dd class="col-sm-7">
                            {{ $remision->creador?->name ?? '—' }}
                            <small class="d-block text-muted">{{ $remision->created_at->format('d/m/Y H:i') }}</small>
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-boxes mr-1"></i> Partidas ({{ $remision->partidas->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:50px">No.</th>
                                <th>Descripción</th>
                                <th class="text-right">Cant.</th>
                                <th>U/M</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($remision->partidas as $p)
                                <tr>
                                    <td>{{ $p->numero }}</td>
                                    <td>
                                        @if ($p->imagen_url)
                                            <img src="{{ $p->imagen_url }}" class="img-thumbnail float-right ml-2" style="max-width:48px;max-height:48px" alt="">
                                        @endif
                                        {!! nl2br(e($p->descripcion)) !!}
                                    </td>
                                    <td class="text-right">{{ \App\Services\Logistica\PdfLogistica::cantidad($p->cantidad) }}</td>
                                    <td>{{ $p->unidad }}</td>
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
                        <a href="{{ route('adquisiciones.logistica.remisiones.pdf', $remision) }}" target="_blank" class="btn btn-tool" title="Abrir en pestaña nueva">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <iframe src="{{ route('adquisiciones.logistica.remisiones.pdf', $remision) }}"
                            style="width:100%;height:80vh;border:0" title="PDF de la remisión"></iframe>
                </div>
            </div>
        </div>
    </div>
@stop