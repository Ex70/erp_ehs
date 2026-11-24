@extends('adminlte::page')
@section('title', $e->folio . ' · Solvencia')

@php
    $Exp   = \App\Models\SolvenciaExpediente::class;
    $verbo = mb_strtolower($e->verboActual());

    switch (true) {
        case $e->estado === $Exp::PAGADA:
            $lbl = 'Pagada · solicitud finalizada · registró';
            $sub = 'Fecha de pago: ' . $Exp::dia($e->pago_fecha) . ' · registrado el ' . $Exp::fecha($e->pago_en) . ' · comprobante adjunto en el expediente';
            break;
        case $e->estado === $Exp::AUTORIZADA:
            $lbl = 'Autorizada · pendiente de pago · autorizó';
            $sub = 'El ' . $Exp::fecha($e->autorizada_en) . ". Contabilidad y Finanzas ({$pagadores}) consulta el formato original y, al pagar, adjunta el comprobante y la fecha de pago.";
            break;
        case $e->estado === $Exp::CANCELADA:
            $lbl = 'Expediente cerrado · Cancelada';
            $sub = 'Último en poder del expediente · cancelada el ' . $Exp::fecha($e->cancelada_en);
            break;
        case ! $e->acuse:
            $lbl = $e->esDevuelta() ? 'Devuelta · pendiente de acuse por' : 'Sin acuse · a nombre de';
            $sub = 'Enviada por ' . $e->nombreDe($e->enviado_por_id) . ' el ' . $Exp::fecha($e->enviado_en) . ' (' . $Exp::hace($e->enviado_en) . ") · le toca: {$verbo}"
                 . ($e->recordatorios ? " · {$e->recordatorios} recordatorio(s)" : '');
            break;
        case $e->estado === $Exp::BORRADOR:
            $lbl = 'Borrador · en poder de';
            $sub = 'Creada el ' . $Exp::fecha($e->created_at);
            break;
        case $e->esDevuelta():
            $lbl = 'Devuelta · en corrección por';
            $sub = 'Acusó la devolución ' . $Exp::hace($e->desde);
            break;
        default:
            $lbl = 'En poder de';
            $sub = 'Acusó de recibido el ' . $Exp::fecha($e->desde) . ' (' . $Exp::hace($e->desde) . ") · le toca: {$verbo}";
    }

    $ultimaDev = $e->movimientos->where('tipo', 'dev')->last();
    $formato   = $e->formato();
@endphp

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:12px">
        <div>
            <h1 class="mb-1">
                <span class="sol-mono" style="font-size:22px">{{ $e->folio }}</span>
                @include('solvencias.trazabilidad.partials.estado', ['e' => $e])
                @if($e->tipo_proveedor === 'nuevo')
                    <span class="sol-tag">PROVEEDOR NUEVO</span>
                @else
                    <span class="sol-tag v">PROVEEDOR VALIDADO</span>
                @endif
            </h1>
            <small class="text-muted">{{ $e->departamento?->nombre }} · elaboró {{ $e->nombreDe($e->creador_id) }} el {{ $Exp::fecha($e->created_at) }}</small>
        </div>
        <button type="button" class="btn btn-default btn-sm sol-noprint" onclick="window.print()"><i class="fas fa-print mr-1"></i>Imprimir expediente</button>
    </div>
@stop

@section('content')
    @include('solvencias.trazabilidad.partials.nav')
    @include('solvencias.trazabilidad.partials.alertas')

    <div class="row">
        <div class="col-lg-7">

            {{-- ── Custodia ── --}}
            <div class="sol-custody {{ $e->claseEstado() }}">
                <div class="lbl">{{ $lbl }}</div>
                <div class="quien">{{ $e->nombreDe($e->holder_id) }}</div>
                <div class="sol-sub">{{ $sub }}</div>

                @if($e->esDevuelta() && $ultimaDev)
                    <div class="sol-warnbox">
                        <b>Motivo de devolución ({{ $ultimaDev->nombre }}):</b><br>
                        {{ \Illuminate\Support\Str::after(\Illuminate\Support\Str::before($ultimaDev->detalle, "\n"), 'Motivo: ') }}
                    </div>
                @endif

                @if(in_array($e->estado, [$Exp::AUTORIZADA, $Exp::PAGADA], true) && $formato)
                    <div class="sol-acciones">
                        @if($formato->esExcelLegible())
                            <button type="button" class="btn btn-sol" data-sol-xlsx
                                    data-url="{{ route('sol.documentos.descargar', $formato) }}"
                                    data-titulo="{{ $formato->nombre }}"
                                    data-sub="{{ $e->folio }} · {{ $e->proveedor }} · {{ $e->monto_formateado }}">
                                <i class="fas fa-eye mr-1"></i>Ver formato (Excel)
                            </button>
                        @endif
                        <a class="btn btn-default" href="{{ route('sol.documentos.descargar', $formato) }}"><i class="fas fa-download mr-1"></i>Descargar original</a>
                        @if($a['pagar'])
                            <button type="button" class="btn btn-sol-amber" data-sol-pagar
                                    data-url="{{ route('sol.expedientes.pagar', $e) }}"
                                    data-folio="{{ $e->folio }}"
                                    data-resumen="{{ $e->monto_formateado }} a {{ $e->proveedor }} · {{ $e->descripcion }}">
                                <i class="fas fa-money-check-alt mr-1"></i>Registrar pago
                            </button>
                        @endif
                    </div>
                @endif
                @if($e->estado === $Exp::AUTORIZADA && ! $esPagador)
                    <div class="sol-hint">El pago lo registra {{ $pagadores }}.</div>
                @endif

                @php
                    $hayBotones = $a['acusar'] || $a['recordar'] || $a['enviar'] || $a['autorizar'] || $a['devolver'] || $a['cancelar'];
                @endphp
                @if($hayBotones)
                    <div class="sol-acciones">
                        @if($a['acusar'])
                            <form method="POST" action="{{ route('sol.expedientes.acuse', $e) }}" data-sol-once>
                                @csrf
                                <button type="submit" class="btn btn-sol-amber">✋ Acusar de recibido</button>
                            </form>
                        @endif
                        @if($a['recordar'])
                            <form method="POST" action="{{ route('sol.expedientes.recordar', $e) }}" data-sol-once>
                                @csrf
                                <button type="submit" class="btn btn-default"><i class="fas fa-bell mr-1"></i>Enviar recordatorio a {{ $e->nombreDe($e->holder_id) }}</button>
                            </form>
                        @endif
                        @if($a['autorizar'])
                            <button type="button" class="btn btn-sol" data-toggle="modal" data-target="#solModalAutorizar" @disabled($a['faltan'])>
                                ✓ Autorizar y cerrar expediente
                            </button>
                        @endif
                        @if($a['enviar'])
                            <button type="button" class="btn btn-sol" data-toggle="modal" data-target="#solModalEnviar" @disabled($a['faltan'] || ! $a['candidatos'])>
                                {{ $a['etiqueta'] }} →
                            </button>
                        @endif
                        @if($a['devolver'])
                            <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#solModalDevolver">↩ Devolver con motivo</button>
                        @endif
                        @if($a['cancelar'])
                            <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#solModalCancelar">✕ Cancelar</button>
                        @endif
                    </div>
                @endif

                @if($a['bloqueo'])<div class="sol-lock">🔒 {{ $a['bloqueo'] }}</div>@endif
                @if($a['aviso'])<div class="sol-hint">{{ $a['aviso'] }}</div>@endif
            </div>

            {{-- ── Recorrido ── --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Recorrido</h3></div>
                <div class="card-body">@include('solvencias.trazabilidad.partials.recorrido')</div>
            </div>

            {{-- ── Qué se paga ── --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Qué se paga</h3>
                    @if($a['editable'])
                        <button type="button" class="btn btn-xs btn-outline-secondary ml-auto sol-noprint" data-toggle="modal" data-target="#solModalCorregir">Corregir datos</button>
                    @else
                        <span class="sol-sub ml-auto"><i class="fas fa-lock"></i> bloqueados</span>
                    @endif
                </div>
                <div class="card-body">
                    <dl class="sol-data">
                        <dt>Monto a pagar</dt><dd style="font-size:18px"><b>{{ $e->monto_formateado }}</b></dd>
                        <dt>Proveedor</dt><dd>{{ $e->proveedor }}</dd>
                        <dt>Descripción</dt><dd>{{ $e->descripcion }}</dd>
                        <dt>Folio</dt><dd class="sol-mono">{{ $e->folio }} <span class="sol-sub">(automático, no editable)</span></dd>
                        @if($e->autorizada_en)<dt>Autorizada</dt><dd>{{ $Exp::fecha($e->autorizada_en) }}</dd>@endif
                        @if($e->pago_fecha)<dt>Fecha de pago</dt><dd><b>{{ $Exp::dia($e->pago_fecha) }}</b></dd>@endif
                    </dl>
                    <div class="sol-hint">Empresa, cotización, cliente, partidas y datos bancarios van en el formato de solvencia adjunto.</div>
                </div>
            </div>

            {{-- ── Documentos ── --}}
            @include('solvencias.trazabilidad.partials.documentos')
        </div>

        <div class="col-lg-5">
            @include('solvencias.trazabilidad.partials.bitacora')
        </div>
    </div>

    @include('solvencias.trazabilidad.partials.modales')
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/solvencias.css') }}?v={{ @filemtime(public_path('css/solvencias.css')) }}">
@stop

@section('js')
    <script src="{{ asset('js/solvencias.js') }}?v={{ @filemtime(public_path('js/solvencias.js')) }}"></script>
@stop
