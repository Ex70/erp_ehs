@php
    $Exp     = \App\Models\SolvenciaExpediente::class;
    $exentos = $e->exentos()->count();

    if ($e->estado === $Exp::AUTORIZADA) {
        $edad = 'Autorizada ' . $Exp::hace($e->autorizada_en) . ' · pendiente de pago';
    } elseif ($modo === 'recordar') {
        $edad = 'Enviada a ' . ($e->holder?->name ?? '—') . ' ' . $Exp::hace($e->enviado_en) . ' · sin acuse'
              . ($e->recordatorios ? " · {$e->recordatorios} recordatorio(s)" : '');
    } elseif (! $e->acuse) {
        $edad = 'Enviada por ' . ($e->enviadoPor?->name ?? '—') . ' ' . $Exp::hace($e->enviado_en)
              . ($e->recordatorios ? " · {$e->recordatorios} recordatorio(s)" : '');
    } else {
        $edad = 'En poder de ' . ((int) $e->holder_id === (int) auth()->id() ? 'ti' : ($e->holder?->name ?? '—'))
              . ' ' . $Exp::hace($e->desde) . ' · le toca: ' . mb_strtolower($e->verboActual());
    }
@endphp
<div class="sol-tarjeta">
    <div class="main">
        <a href="{{ route('sol.expedientes.show', $e) }}">
            <div class="top">
                <span class="folio sol-mono">{{ $e->folio }}</span>
                @include('solvencias.trazabilidad.partials.estado', ['e' => $e])
                @if($e->tipo_proveedor === 'nuevo')<span class="sol-tag">PROVEEDOR NUEVO</span>@endif
                @if($exentos)<span class="sol-tag" title="Documentos no anexados con observación">⚠ {{ $exentos }} sin archivo</span>@endif
                <span class="sol-sub">{{ $e->departamento?->nombre }}</span>
            </div>
            <div class="desc"><b>{{ $e->monto_formateado }}</b> · {{ $e->proveedor }} · {{ $e->descripcion }}</div>
            <div class="age {{ $e->retrasada() ? 'sol-late' : '' }}">{{ $edad }}</div>
        </a>
    </div>

    <div class="d-flex flex-wrap justify-content-end" style="gap:6px">
        @switch($modo)
            @case('acusar')
                <form method="POST" action="{{ route('sol.expedientes.acuse', $e) }}" data-sol-once>
                    @csrf
                    <button type="submit" class="btn btn-sol-amber btn-sm"><i class="fas fa-hand-paper mr-1"></i>Acusar recibido</button>
                </form>
                @break

            @case('recordar')
                <form method="POST" action="{{ route('sol.expedientes.recordar', $e) }}" data-sol-once>
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-bell mr-1"></i>Recordar</button>
                </form>
                @break

            @case('pagar')
                @if($f = $e->formato())
                    @if($f->esExcelLegible())
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-sol-xlsx
                                data-url="{{ route('sol.documentos.descargar', $f) }}"
                                data-titulo="{{ $f->nombre }}" data-sub="{{ $e->folio }} · {{ $e->proveedor }} · {{ $e->monto_formateado }}">
                            <i class="fas fa-eye mr-1"></i>Ver formato
                        </button>
                    @endif
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('sol.documentos.descargar', $f) }}"><i class="fas fa-download"></i></a>
                @endif
                <button type="button" class="btn btn-sol btn-sm" data-sol-pagar
                        data-url="{{ route('sol.expedientes.pagar', $e) }}"
                        data-folio="{{ $e->folio }}"
                        data-resumen="{{ $e->monto_formateado }} a {{ $e->proveedor }} · {{ $e->descripcion }}">
                    <i class="fas fa-money-check-alt mr-1"></i>Registrar pago
                </button>
                @break

            @default
                <a href="{{ route('sol.expedientes.show', $e) }}" class="btn btn-outline-secondary btn-sm">Abrir expediente</a>
        @endswitch
    </div>
</div>
