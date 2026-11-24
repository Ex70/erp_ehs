@php
    $Exp   = \App\Models\SolvenciaExpediente::class;
    $final = $e->esFinal();

    // Última aprobación registrada por etapa (✓ Revisó, ✓ Validó…)
    $aprobado = [];
    foreach ($e->movimientos as $m) {
        if ($m->tipo === 'ok' && $m->etapa !== null) {
            $aprobado[$m->etapa] = $m;
        }
    }
@endphp
<div class="sol-steps">
    @foreach($e->recorrido as $i => $st)
        @php
            $p    = e($e->nombresDe($st['personas']));
            $hecho = in_array($e->estado, [$Exp::AUTORIZADA, $Exp::PAGADA], true)
                  || (! $e->esDevuelta() && $i < $e->etapa)
                  || ($e->esDevuelta() && $i > 0 && $i < $e->devuelta_desde);
            $clase = '';

            if (! $final && $i === $e->etapa) {
                $p = e($e->nombreDe($e->holder_id));
            }
            if ($hecho) {
                $clase = 'done';
                if (isset($aprobado[$i])) {
                    $p = e($aprobado[$i]->nombre) . '<br>' . e($Exp::fecha($aprobado[$i]->created_at, 'd M Y'));
                }
            }
            if (! $final && $i === $e->etapa) {
                $clase = 'cur';
            }
            if ($e->esDevuelta() && $i === $e->devuelta_desde) {
                $clase .= ' cur';
            }
        @endphp
        <div class="sol-step {{ $clase }}">
            <div class="c"></div>{{ $st['verbo'] }}
            <div class="p">{!! $p !!}</div>
        </div>
    @endforeach

    @php
        $pagada = $e->estado === $Exp::PAGADA;
        $porPagar = $e->estado === $Exp::AUTORIZADA;
    @endphp
    <div class="sol-step {{ $pagada ? 'done' : '' }} {{ $porPagar ? 'cur' : '' }}">
        <div class="c"></div>Pago
        <div class="p">
            @if($pagada)
                {{ $e->nombreDe($e->pago_por_id) }}<br>{{ $Exp::dia($e->pago_fecha) }}
            @else
                {{ $pagadores }}
            @endif
        </div>
    </div>
</div>
