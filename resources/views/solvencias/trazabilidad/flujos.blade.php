@extends('adminlte::page')
@section('title', 'Personas y flujos · Solvencias')

@php $FS = \App\Services\Solvencias\FlujoService::class; @endphp

@section('content_header')
    <h1 class="mb-0">Personas y flujos</h1>
    <small class="text-muted">
        Cada etapa se asigna a personas del ERP (si hay dos o más, quien envía elige a cuál). Los cambios aplican a solvencias nuevas;
        cada expediente conserva el recorrido con el que nació.
    </small>
@stop

@section('content')
    @include('solvencias.trazabilidad.partials.nav')
    @include('solvencias.trazabilidad.partials.alertas')

    <div class="row">
        {{-- ═══════════ Flujos ═══════════ --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Flujo por unidad de negocio (departamento)</h3></div>
                <div class="card-body">

                    @forelse($flujos as $flujo)
                        @php
                            $total  = $flujo->etapas->count();
                            $vacias = $flujo->etapasVacias();
                        @endphp
                        <div class="sol-flujo">
                            <div class="d-flex justify-content-between align-items-start flex-wrap mb-2" style="gap:8px">
                                <div>
                                    <h5 class="mb-0">
                                        {{ $flujo->nombre }}
                                        @if($flujo->por_confirmar)<span class="sol-tag">POR CONFIRMAR</span>@endif
                                        @unless($flujo->activo)<span class="sol-tag p">INACTIVO</span>@endunless
                                    </h5>
                                    <small class="text-muted">{{ $flujo->expedientes_count }} expediente(s) · folios {{ $flujo->prefijo_folio }}-{{ $anio }}-0000</small>
                                </div>
                                <form method="POST" action="{{ route('sol.flujos.update', $flujo) }}" class="form-inline" style="gap:6px">
                                    @csrf @method('PUT')
                                    <label class="small text-muted mr-1">Prefijo</label>
                                    <input type="text" name="prefijo_folio" value="{{ $flujo->prefijo_folio }}" maxlength="10"
                                           class="form-control form-control-sm sol-mono" style="width:90px;text-transform:uppercase">
                                    <div class="custom-control custom-switch ml-2">
                                        <input type="checkbox" class="custom-control-input" id="act{{ $flujo->id }}" name="activo" value="1" @checked($flujo->activo)>
                                        <label class="custom-control-label small" for="act{{ $flujo->id }}">Activo</label>
                                    </div>
                                    <button class="btn btn-xs btn-outline-secondary ml-1">Guardar</button>
                                </form>
                            </div>

                            @foreach($flujo->etapas as $etapa)
                                @php $fija = $etapa->esFija($total); @endphp
                                <div class="sol-etapa">
                                    <span class="n">{{ $etapa->orden + 1 }}</span>

                                    @if($fija)
                                        <b class="verbo">{{ $etapa->verbo }}</b>
                                    @else
                                        <form method="POST" action="{{ route('sol.flujos.etapas.update', $etapa) }}" class="verbo">
                                            @csrf @method('PUT')
                                            <select name="verbo" class="form-control form-control-sm" onchange="this.form.submit()">
                                                @foreach(config('solvencias.verbos_intermedios') as $v)
                                                    <option @selected($etapa->verbo === $v)>{{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @endif

                                    <span class="personas">
                                        @forelse($etapa->personas as $p)
                                            @if(! $loop->first)<span class="sol-sub">o</span>@endif
                                            <span class="sol-persona" @unless($p->activo) style="opacity:.55" title="Usuario inactivo: no recibe solvencias" @endunless>
                                                {{ $p->name }}
                                                <form method="POST" action="{{ route('sol.flujos.personas.destroy', [$etapa, $p]) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" title="Quitar">✕</button>
                                                </form>
                                            </span>
                                        @empty
                                            <span class="text-danger small"><i class="fas fa-exclamation-circle"></i> Sin persona</span>
                                        @endforelse

                                        <form method="POST" action="{{ route('sol.flujos.personas.store', $etapa) }}">
                                            @csrf
                                            <select name="user_id" class="form-control form-control-sm" style="max-width:190px" onchange="if(this.value) this.form.submit()">
                                                <option value="">+ persona…</option>
                                                @foreach($usuarios as $depto => $lista)
                                                    @php $lista = $lista->whereNotIn('id', $etapa->personas->pluck('id')); @endphp
                                                    @if($lista->isNotEmpty())
                                                        <optgroup label="{{ $depto }}">
                                                            @foreach($lista as $u)
                                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </form>
                                    </span>

                                    @unless($fija)
                                        <form method="POST" action="{{ route('sol.flujos.etapas.destroy', $etapa) }}">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-outline-danger" title="Quitar etapa">✕</button>
                                        </form>
                                    @endunless
                                </div>
                            @endforeach

                            @if($vacias)
                                <div class="sol-warnbox">Etapas sin persona: <b>{{ implode(', ', $vacias) }}</b>. Nadie puede elaborar en este flujo hasta completarlo.</div>
                            @endif

                            <div class="d-flex flex-wrap mt-2" style="gap:6px">
                                <form method="POST" action="{{ route('sol.flujos.etapas.store', $flujo) }}">
                                    @csrf
                                    <button class="btn btn-xs btn-outline-secondary">+ Agregar etapa antes de autorizar</button>
                                </form>
                                @if($flujo->por_confirmar)
                                    <form method="POST" action="{{ route('sol.flujos.confirmar', $flujo) }}">
                                        @csrf
                                        <button class="btn btn-xs btn-outline-success">Marcar como confirmado</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="sol-vacio">Aún no hay flujos. Habilita el primero abajo o corre el seeder SolvenciaFlujosSeeder.</div>
                    @endforelse

                    {{-- Habilitar flujo --}}
                    <div class="sol-note mt-3">
                        <form method="POST" action="{{ route('sol.flujos.store') }}" class="form-row align-items-end">
                            @csrf
                            <div class="col-md-6 mb-2">
                                <label class="small mb-1">Habilitar flujo para el departamento</label>
                                <select name="departamento_id" class="form-control form-control-sm" required>
                                    <option value="">Selecciona…</option>
                                    @foreach($sinFlujo as $d)
                                        <option value="{{ $d->id }}" data-clave="{{ $d->clave }}">{{ $d->nombre }}{{ $d->clave ? " ({$d->clave})" : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small mb-1">Prefijo de folio</label>
                                <input type="text" name="prefijo_folio" maxlength="10" required class="form-control form-control-sm sol-mono"
                                       style="text-transform:uppercase" placeholder="Ej. TAL" value="{{ old('prefijo_folio') }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <button class="btn btn-sm btn-sol btn-block" @disabled($sinFlujo->isEmpty())>Habilitar</button>
                            </div>
                        </form>
                        <div>Se crea con la cadena Elabora → Revisa → Valida → Autoriza, vacía y marcada «por confirmar».</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════ Personas, Contabilidad y folios ═══════════ --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Personas en los flujos</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr class="text-muted small"><th>Nombre</th><th>Responsabilidad en solvencias</th></tr></thead>
                        <tbody>
                        @forelse($participantes as $p)
                            <tr>
                                <td style="min-width:170px">{{ $p->name }}@unless($p->activo)<span class="sol-tag p ml-1">INACTIVO</span>@endunless</td>
                                <td class="small text-muted">{{ $FS::responsabilidadTexto($p->id, $flujos) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="sol-vacio px-3">Nadie asignado todavía.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Contabilidad y Finanzas</h3></div>
                <div class="card-body">
                    <div class="sol-sub mb-2">Consultan las solvencias autorizadas (expediente completo y formato original) y registran el pago: comprobante y fecha. Con eso la solicitud queda finalizada.</div>
                    <div class="d-flex flex-wrap align-items-center" style="gap:6px">
                        @forelse($pagadores as $p)
                            <span class="sol-persona">
                                {{ $p->name }}
                                @if($pagadores->count() > 1)
                                    <form method="POST" action="{{ route('sol.flujos.pagadores.destroy', $p) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Quitar">✕</button>
                                    </form>
                                @endif
                            </span>
                        @empty
                            <span class="text-danger small"><i class="fas fa-exclamation-circle"></i> Nadie registra pagos todavía</span>
                        @endforelse
                        <form method="POST" action="{{ route('sol.flujos.pagadores.store') }}">
                            @csrf
                            <select name="user_id" class="form-control form-control-sm" style="max-width:200px" onchange="if(this.value) this.form.submit()">
                                <option value="">+ persona…</option>
                                @foreach($usuarios as $depto => $lista)
                                    @php $lista = $lista->whereNotIn('id', $pagadores->pluck('id')); @endphp
                                    @if($lista->isNotEmpty())
                                        <optgroup label="{{ $depto }}">
                                            @foreach($lista as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Folios</h3></div>
                <div class="card-body">
                    <div class="sol-note">
                        Formato <span class="sol-mono">PREFIJO-AÑO-0000</span>, consecutivo por prefijo y año, asignado al crear el expediente
                        y no editable. Un borrador cancelado conserva su folio, así no quedan huecos sin explicación.
                        Sistemas usa <span class="sol-mono">SIST</span> y continúa la numeración del formato anterior.
                    </div>
                    <table class="table table-sm mt-2 mb-3">
                        <thead><tr class="text-muted small"><th>Serie</th><th class="text-right">Último folio</th></tr></thead>
                        <tbody>
                        @forelse($folios as $f)
                            <tr><td class="sol-mono">{{ $f->serie }}</td><td class="text-right sol-mono">{{ str_pad($f->ultimo, 4, '0', STR_PAD_LEFT) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="sol-vacio">Aún no se ha emitido ningún folio.</td></tr>
                        @endforelse
                        </tbody>
                    </table>

                    <form method="POST" action="{{ route('sol.flujos.folios.store') }}" class="form-row align-items-end">
                        @csrf
                        <div class="col-4 mb-2">
                            <label class="small mb-1">Prefijo</label>
                            <select name="prefijo" class="form-control form-control-sm" required>
                                @foreach($flujos as $fl)<option>{{ $fl->prefijo_folio }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-3 mb-2">
                            <label class="small mb-1">Año</label>
                            <input type="number" name="anio" value="{{ $anio }}" min="2020" max="2100" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-3 mb-2">
                            <label class="small mb-1">Último usado</label>
                            <input type="number" name="ultimo" min="0" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-2 mb-2">
                            <button class="btn btn-sm btn-outline-secondary btn-block" @disabled($flujos->isEmpty())>Fijar</button>
                        </div>
                    </form>
                    <small class="text-muted">Sirve para continuar una numeración en papel o anterior. Solo puede subir, nunca bajar.</small>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/solvencias.css') }}?v={{ @filemtime(public_path('css/solvencias.css')) }}">
@stop

@section('js')
    <script src="{{ asset('js/solvencias.js') }}?v={{ @filemtime(public_path('js/solvencias.js')) }}"></script>
    <script>
        // Sugerir el prefijo con la clave del departamento al habilitar un flujo
        document.querySelector('select[name=departamento_id]')?.addEventListener('change', function () {
            const clave = this.selectedOptions[0]?.dataset.clave || '';
            const input = this.form.querySelector('input[name=prefijo_folio]');
            if (input && !input.value) input.value = clave;
        });
    </script>
@stop
