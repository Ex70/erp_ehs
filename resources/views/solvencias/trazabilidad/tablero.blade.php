@extends('adminlte::page')
@section('title', 'Tablero de trazabilidad · Solvencias')

@php
    $Exp = \App\Models\SolvenciaExpediente::class;
    $horas = $kpis['mas_antigua'] ? \Illuminate\Support\Carbon::parse($kpis['mas_antigua'])->diffInHours(now()) : 0;
@endphp

@section('content_header')
    <h1 class="mb-0">Tablero de trazabilidad</h1>
    <small class="text-muted">
        Quién tiene cada solvencia en este momento y desde cuándo.
        @cannot('solvencias_flujo.ver.todas') Se muestran las solvencias en las que participas. @endcannot
    </small>
@stop

@section('content')
    @include('solvencias.trazabilidad.partials.nav')
    @include('solvencias.trazabilidad.partials.alertas')

    <div class="sol-kpis">
        <a class="sol-kpi sin" href="{{ route('sol.tablero', ['estado' => $Exp::SIN_ACUSE]) }}">
            <div class="n">{{ $kpis['sin'] }}</div>
            <div class="l">Sin acuse{{ $horas ? ' · la más antigua ' . intdiv((int) $horas, 24) . ' d ' . ((int) $horas % 24) . ' h' : '' }}</div>
        </a>
        <a class="sol-kpi pod" href="{{ route('sol.tablero', ['estado' => $Exp::EN_PODER]) }}">
            <div class="n">{{ $kpis['pod'] }}</div><div class="l">En poder de alguien (acusadas)</div>
        </a>
        <a class="sol-kpi dev" href="{{ route('sol.tablero', ['estado' => $Exp::DEVUELTA]) }}">
            <div class="n">{{ $kpis['dev'] }}</div><div class="l">Devueltas para corrección</div>
        </a>
        <a class="sol-kpi pod" href="{{ route('sol.tablero', ['estado' => $Exp::AUTORIZADA]) }}">
            <div class="n">{{ $kpis['aut'] }}</div><div class="l">Autorizadas por pagar</div>
        </a>
        <a class="sol-kpi pag" href="{{ route('sol.tablero', ['estado' => $Exp::PAGADA]) }}">
            <div class="n">{{ $kpis['pag'] }}</div><div class="l">Pagadas (finalizadas)</div>
        </a>
        <a class="sol-kpi tot" href="{{ route('sol.tablero') }}">
            <div class="n" style="font-size:19px">${{ number_format($kpis['monto'], 2) }}</div><div class="l">Monto en proceso</div>
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('sol.tablero') }}" class="row mb-2">
                <div class="col-md-5 mb-2">
                    <input type="text" name="q" class="form-control form-control-sm" value="{{ request('q') }}"
                           placeholder="Buscar folio, proveedor, descripción o persona…">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="estado" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        @foreach($Exp::ESTADOS as $clave => $etiqueta)
                            <option value="{{ $clave }}" @selected(request('estado') === $clave)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select name="departamento" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Todas las unidades</option>
                        @foreach($departamentos as $d)
                            <option value="{{ $d->id }}" @selected((string) request('departamento') === (string) $d->id)>{{ $d->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 mb-2">
                    <button class="btn btn-sm btn-outline-secondary btn-block" title="Buscar"><i class="fas fa-search"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm sol-tabla mb-0">
                    <thead>
                    <tr class="text-muted small">
                        <th>Folio</th><th>Proveedor y descripción</th><th class="text-right">Monto</th>
                        <th>Pendiente</th><th>Estado</th><th>Quién la tiene</th><th>Desde</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($expedientes as $e)
                        <tr class="clic" data-href="{{ route('sol.expedientes.show', $e) }}">
                            <td class="sol-mono text-nowrap">
                                <a href="{{ route('sol.expedientes.show', $e) }}"><b>{{ $e->folio }}</b></a>
                                <div class="sol-sub" style="font-family:inherit">{{ $e->departamento?->nombre }}</div>
                            </td>
                            <td>
                                {{ $e->proveedor }}
                                @if($e->tipo_proveedor === 'nuevo')<span class="sol-tag">NUEVO</span>@endif
                                @if($n = $e->exentos()->count())<span class="sol-tag" title="Documentos no anexados con observación">⚠ {{ $n }} sin archivo</span>@endif
                                <div class="sol-sub">{{ $e->descripcion }}</div>
                            </td>
                            <td class="text-right text-nowrap"><b>{{ $e->monto_formateado }}</b></td>
                            <td>{{ $e->pendienteTexto() }}</td>
                            <td>@include('solvencias.trazabilidad.partials.estado', ['e' => $e])</td>
                            <td>
                                @switch($e->estado)
                                    @case($Exp::AUTORIZADA) Contabilidad y Finanzas · por pagar @break
                                    @case($Exp::PAGADA) Finalizada · pagada el {{ $Exp::dia($e->pago_fecha) }} @break
                                    @case($Exp::CANCELADA) Archivada · último: {{ $e->holder?->name }} @break
                                    @default
                                        {{ $e->holder?->name }}
                                        @unless($e->acuse)<span class="sol-sub">(sin acuse)</span>@endunless
                                @endswitch
                            </td>
                            <td class="text-nowrap {{ $e->retrasada() ? 'sol-late' : 'sol-sub' }}">{{ $Exp::hace($e->desde) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="sol-vacio text-center">Sin resultados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $expedientes->links() }}</div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/solvencias.css') }}?v={{ @filemtime(public_path('css/solvencias.css')) }}">
@stop

@section('js')
    <script src="{{ asset('js/solvencias.js') }}?v={{ @filemtime(public_path('js/solvencias.js')) }}"></script>
@stop
