@extends('adminlte::page')
@section('title', 'Contabilidad y Finanzas · Solvencias')

@php $Exp = \App\Models\SolvenciaExpediente::class; @endphp

@section('content_header')
    <h1 class="mb-0">Contabilidad y Finanzas</h1>
    <small class="text-muted">Consulta el formato de solvencia original de cada autorizada. Al pagar, adjunta el comprobante y captura la fecha: con eso la solicitud de recurso queda finalizada.</small>
@stop

@section('content')
    @include('solvencias.trazabilidad.partials.nav')
    @include('solvencias.trazabilidad.partials.alertas')

    <form method="GET" action="{{ route('sol.contabilidad') }}" class="mb-3" style="max-width:520px">
        <div class="input-group input-group-sm">
            <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="Buscar folio, proveedor o descripción…">
            <div class="input-group-append"><button class="btn btn-outline-secondary"><i class="fas fa-search"></i></button></div>
        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">💳 Autorizadas por pagar <span class="sol-sub">({{ $porPagar->count() }} · ${{ number_format($porPagar->sum('monto'), 2) }})</span></h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm sol-tabla mb-0">
                    <thead><tr class="text-muted small"><th>Folio</th><th>Proveedor y descripción</th><th class="text-right">Monto</th><th>Autorizada</th><th>Soporte</th><th></th></tr></thead>
                    <tbody>
                    @forelse($porPagar as $e)
                        @php $f = $e->formato(); $ex = $e->exentos()->count(); @endphp
                        <tr class="clic" data-href="{{ route('sol.expedientes.show', $e) }}">
                            <td class="sol-mono text-nowrap"><b>{{ $e->folio }}</b><div class="sol-sub" style="font-family:inherit">{{ $e->departamento?->nombre }}</div></td>
                            <td>{{ $e->proveedor }}<div class="sol-sub">{{ $e->descripcion }}</div></td>
                            <td class="text-right text-nowrap"><b>{{ $e->monto_formateado }}</b></td>
                            <td class="text-nowrap">{{ $Exp::fecha($e->autorizada_en, 'd M Y') }}<div class="sol-sub">{{ $Exp::hace($e->autorizada_en) }}</div></td>
                            <td class="text-nowrap">
                                {{ $e->documentosActivos()->where('exento', false)->where('tipo', '!=', 'comprobante')->count() }} archivo(s)
                                @if($ex)<div><span class="sol-tag">⚠ {{ $ex }} con observación</span></div>@endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap justify-content-end" style="gap:6px">
                                    @if($f?->esExcelLegible())
                                        <button type="button" class="btn btn-xs btn-outline-secondary" data-sol-xlsx
                                                data-url="{{ route('sol.documentos.descargar', $f) }}" data-titulo="{{ $f->nombre }}"
                                                data-sub="{{ $e->folio }} · {{ $e->proveedor }} · {{ $e->monto_formateado }}">
                                            <i class="fas fa-eye mr-1"></i>Ver formato
                                        </button>
                                    @endif
                                    @if($f)
                                        <a class="btn btn-xs btn-outline-secondary" href="{{ route('sol.documentos.descargar', $f) }}" title="Descargar original"><i class="fas fa-download"></i></a>
                                    @endif
                                    <button type="button" class="btn btn-xs btn-sol" data-sol-pagar
                                            data-url="{{ route('sol.expedientes.pagar', $e) }}" data-folio="{{ $e->folio }}"
                                            data-resumen="{{ $e->monto_formateado }} a {{ $e->proveedor }} · {{ $e->descripcion }}">
                                        <i class="fas fa-money-check-alt mr-1"></i>Registrar pago
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="sol-vacio text-center">No hay solvencias pendientes de pago.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">✅ Pagadas · finalizadas <span class="sol-sub">({{ $pagadas->total() }})</span></h3></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm sol-tabla mb-0">
                    <thead><tr class="text-muted small"><th>Folio</th><th>Proveedor y descripción</th><th class="text-right">Monto</th><th>Fecha de pago</th><th>Comprobante</th><th></th></tr></thead>
                    <tbody>
                    @forelse($pagadas as $e)
                        @php $f = $e->formato(); $c = $e->comprobante(); @endphp
                        <tr class="clic" data-href="{{ route('sol.expedientes.show', $e) }}">
                            <td class="sol-mono text-nowrap"><b>{{ $e->folio }}</b><div class="sol-sub" style="font-family:inherit">{{ $e->departamento?->nombre }}</div></td>
                            <td>{{ $e->proveedor }}<div class="sol-sub">{{ $e->descripcion }}</div></td>
                            <td class="text-right text-nowrap"><b>{{ $e->monto_formateado }}</b></td>
                            <td class="text-nowrap"><b>{{ $Exp::dia($e->pago_fecha) }}</b><div class="sol-sub">{{ $e->pagoPor?->name }}</div></td>
                            <td class="text-nowrap">
                                @if($c?->tieneArchivo())
                                    @if($c->seMuestraEnLinea())
                                        <a class="btn btn-xs btn-outline-secondary" target="_blank" rel="noopener" href="{{ route('sol.documentos.ver', $c) }}"><i class="fas fa-eye mr-1"></i>Ver</a>
                                    @endif
                                    <a class="btn btn-xs btn-outline-secondary" href="{{ route('sol.documentos.descargar', $c) }}"><i class="fas fa-download"></i></a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap justify-content-end" style="gap:6px">
                                    @if($f?->esExcelLegible())
                                        <button type="button" class="btn btn-xs btn-outline-secondary" data-sol-xlsx
                                                data-url="{{ route('sol.documentos.descargar', $f) }}" data-titulo="{{ $f->nombre }}"
                                                data-sub="{{ $e->folio }} · {{ $e->proveedor }} · {{ $e->monto_formateado }}">
                                            <i class="fas fa-eye mr-1"></i>Ver formato
                                        </button>
                                    @endif
                                    @if($f)
                                        <a class="btn btn-xs btn-outline-secondary" href="{{ route('sol.documentos.descargar', $f) }}" title="Descargar original"><i class="fas fa-download"></i></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="sol-vacio text-center">Aún no hay solicitudes pagadas.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pagadas->hasPages())
            <div class="card-footer">{{ $pagadas->links() }}</div>
        @endif
    </div>
    <div class="sol-hint">Abre cualquier renglón para ver todo el expediente: datos, documentos, observaciones y bitácora.</div>

    @include('solvencias.trazabilidad.partials.modal-pago')
    @include('solvencias.trazabilidad.partials.visor-excel')
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/solvencias.css') }}?v={{ @filemtime(public_path('css/solvencias.css')) }}">
@stop

@section('js')
    <script src="{{ asset('js/solvencias.js') }}?v={{ @filemtime(public_path('js/solvencias.js')) }}"></script>
@stop
