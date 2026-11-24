@php $Exp = \App\Models\SolvenciaExpediente::class; @endphp
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:8px">
        <h3 class="card-title mb-0">Bitácora ({{ $e->movimientos->count() }} movimientos)</h3>
        <span class="sol-integridad {{ $integra ? 'ok' : 'bad' }} ml-auto"
              title="Cada movimiento lleva una huella que depende del anterior; si alguien altera un registro, la cadena se rompe.">
            {{ $integra ? '✓ Cadena íntegra' : '✕ Cadena alterada' }}
        </span>
    </div>
    <div class="card-body">
        <div class="sol-sub mb-3">Solo se agregan registros; nada se edita ni se borra.</div>
        <ul class="sol-timeline">
            @foreach($e->movimientos->sortByDesc('n') as $m)
                <li class="k-{{ $m->tipo }}">
                    <div class="t">{{ $Exp::fecha($m->created_at) }} · #{{ $m->n }}</div>
                    <div class="a">{{ $m->accion }}</div>
                    <div class="d">{{ $m->nombre }}@if($m->rol) <span class="sol-sub">· {{ mb_strtolower($m->rol) }}</span>@endif</div>
                    @if($m->detalle)
                        <div class="d">{{ $m->detalle }}</div>
                    @endif
                    <div class="h sol-mono" title="{{ $m->hash }}">huella {{ substr($m->hash, 0, 12) }}</div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
