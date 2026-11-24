@php
    $Exp       = \App\Models\SolvenciaExpediente::class;
    $req       = $e->requeridos();
    $tipos     = $req;
    $editable  = $a['editable'];
    $completos = $e->documentosActivos()->whereIn('tipo', $req)->count();
    $exentos   = $e->exentos()->count();

    if ($e->comprobante()) {
        $tipos[] = 'comprobante';
    }
@endphp
<div class="card sol-docs">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            Soporte obligatorio · {{ $completos }}/{{ count($req) }}
            @if($exentos)<span class="sol-tag ml-1">{{ $exentos }} con observación</span>@endif
        </h3>
        @unless($editable)<span class="sol-sub ml-auto"><i class="fas fa-lock"></i></span>@endunless
    </div>
    <div class="card-body pt-1">
        @foreach($tipos as $t)
            @php
                $cfg = config("solvencias.documentos.{$t}");
                $d   = $e->documentoActivo($t);
                $old = $e->historialDe($t);
                $ic  = ! $d ? ['no', '!'] : ($d->exento ? ['ex', '⚠'] : ['ok', '✓']);
            @endphp
            <div class="row-doc">
                <div class="ic {{ $ic[0] }}">{{ $ic[1] }}</div>

                <div class="nombre">
                    <div>
                        <b>{{ $cfg['nombre'] }}</b>
                        @if(! $cfg['exceptuable'] && $t !== 'comprobante')<span class="sol-sub">(siempre obligatorio)</span>@endif
                    </div>

                    @if(! $d)
                        <div class="meta" style="color:var(--sol-dev)">Pendiente</div>
                    @elseif($d->exento)
                        <div class="meta"><b style="color:#a15c00">No se anexa</b> · registró {{ $d->user?->name }} · {{ $Exp::fecha($d->created_at) }}</div>
                        <div class="sol-obs">Observación: {{ $d->observacion }}</div>
                    @else
                        <div class="meta">
                            {{ $d->nombre }} · {{ $d->tamano_legible }} · cargó {{ $d->user?->name }} · {{ $Exp::fecha($d->created_at) }}
                        </div>
                        <div class="mt-1 d-flex flex-wrap" style="gap:6px">
                            @if($d->esExcelLegible())
                                <button type="button" class="btn btn-xs btn-outline-secondary" data-sol-xlsx
                                        data-url="{{ route('sol.documentos.descargar', $d) }}"
                                        data-titulo="{{ $d->nombre }}"
                                        data-sub="{{ $e->folio }} · {{ $e->proveedor }} · {{ $e->monto_formateado }} · cargó {{ $d->user?->name }} el {{ $Exp::fecha($d->created_at) }}">
                                    <i class="fas fa-eye mr-1"></i>Vista previa
                                </button>
                            @elseif($d->seMuestraEnLinea())
                                <a class="btn btn-xs btn-outline-secondary" target="_blank" rel="noopener" href="{{ route('sol.documentos.ver', $d) }}">
                                    <i class="fas fa-external-link-alt mr-1"></i>Abrir
                                </a>
                            @endif
                            <a class="btn btn-xs btn-outline-secondary" href="{{ route('sol.documentos.descargar', $d) }}">
                                <i class="fas fa-download mr-1"></i>Descargar
                            </a>
                        </div>
                    @endif

                    @foreach($old as $o)
                        <div class="old">
                            antes: {{ $o->exento ? 'sin archivo — ' . \Illuminate\Support\Str::limit($o->observacion, 60) : $o->nombre }}
                            · {{ $o->user?->name }} · {{ $Exp::fecha($o->created_at) }}
                        </div>
                    @endforeach
                </div>

                @if($editable && $t !== 'comprobante')
                    <div class="botones sol-noprint">
                        <form method="POST" action="{{ route('sol.expedientes.documentos.store', $e) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="tipo" value="{{ $t }}">
                            <label class="btn btn-xs btn-outline-primary mb-0">
                                {{ $d && ! $d->exento ? 'Reemplazar' : 'Cargar' }}
                                <input type="file" name="archivo" hidden data-sol-upload
                                       accept="{{ collect($cfg['extensiones'])->map(fn ($x) => '.' . $x)->implode(',') }}">
                            </label>
                        </form>
                        @if($cfg['exceptuable'] && ! ($d && $d->exento))
                            <button type="button" class="btn btn-xs btn-outline-secondary" data-sol-exceptuar
                                    data-tipo="{{ $t }}" data-nombre="{{ $cfg['nombre'] }}"
                                    title="No anexar este documento, con una observación">No se anexa…</button>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach

        @if($editable)
            <div class="sol-hint">
                Al reemplazar un archivo, la versión anterior se conserva. Un documento solo puede quedar sin anexar con una
                observación que lo justifique (mínimo {{ config('solvencias.min_observacion') }} caracteres); quien revisa puede
                devolverla si el argumento no es válido. Tamaño máximo por archivo: {{ number_format(config('solvencias.archivo.max_kb') / 1024) }} MB.
            </div>
        @endif
    </div>
</div>
