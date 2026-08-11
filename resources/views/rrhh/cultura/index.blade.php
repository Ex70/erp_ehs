@extends('adminlte::page')

@section('title', 'Cultura Organizacional')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap">
        <div>
            <h1 class="m-0">🏛️ Cultura Organizacional</h1>
            <small class="text-muted">Historia, misión, visión, valores y presentación del Grupo</small>
        </div>
        @can('cultura.editar.todos')
            <div class="d-flex flex-wrap" style="gap:7px">
                <button type="button" class="btn btn-primary btn-sm" id="btnNuevaDiapositiva">
                    🖼️ Agregar Imagen
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#modalCulturaSecciones">
                    ✏️ Editar Contenido
                </button>
            </div>
        @endcan
    </div>
@stop

@section('content')
<div class="cultura-wrap">

    @include('rrhh.cultura.partials._hero')

    {{-- ══ PESTAÑAS ══ --}}
    <div class="cultura-tabs" role="tablist">
        <button type="button" class="cultura-tab active" data-pane="presentacion">🏢 Presentación</button>
        <button type="button" class="cultura-tab" data-pane="mision">🎯 Misión &amp; Visión</button>
        <button type="button" class="cultura-tab" data-pane="valores">💎 Valores</button>
        <button type="button" class="cultura-tab" data-pane="historia">📖 Historia</button>
        <button type="button" class="cultura-tab" data-pane="objetivos">🏁 Objetivos</button>
        <button type="button" class="cultura-tab" data-pane="empresas">🏭 Empresas del Grupo</button>
    </div>

    {{-- ── Presentación ── --}}
    <div class="cultura-pane active" id="pane-presentacion">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title cultura-serif">
                    🏢 {{ $secciones['presentacion']->titulo ?? 'Quiénes Somos' }}
                </h3>
            </div>
            <div class="card-body pt-0">
                <div class="cultura-prosa">{!! nl2br(e($secciones['presentacion']->texto ?? '')) !!}</div>
            </div>
        </div>
    </div>

    {{-- ── Misión & Visión ── --}}
    <div class="cultura-pane" id="pane-mision">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="cultura-sec-card h-100">
                    <div class="cultura-sec-icon">{{ $secciones['mision']->icono ?? '🎯' }}</div>
                    <div class="cultura-sec-title">{{ $secciones['mision']->titulo ?? 'Misión' }}</div>
                    <div class="cultura-sec-text">{!! nl2br(e($secciones['mision']->texto ?? '')) !!}</div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="cultura-sec-card h-100">
                    <div class="cultura-sec-icon">{{ $secciones['vision']->icono ?? '🔭' }}</div>
                    <div class="cultura-sec-title">{{ $secciones['vision']->titulo ?? 'Visión' }}</div>
                    <div class="cultura-sec-text">{!! nl2br(e($secciones['vision']->texto ?? '')) !!}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Valores ── --}}
    <div class="cultura-pane" id="pane-valores">
        @if ($valores->isEmpty())
            <div class="callout callout-info mb-0">Aún no se han capturado valores organizacionales.</div>
        @else
            <div class="cultura-sections">
                @foreach ($valores as $valor)
                    <div class="cultura-sec-card">
                        <div class="cultura-sec-icon">{{ $valor->icono ?: '⭐' }}</div>
                        <div class="cultura-sec-title">{{ $valor->titulo }}</div>
                        <div class="cultura-sec-text">{{ $valor->descripcion }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Historia ── --}}
    <div class="cultura-pane" id="pane-historia">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title cultura-serif">
                    📖 {{ $secciones['historia']->titulo ?? 'Nuestra Historia' }}
                </h3>
            </div>
            <div class="card-body pt-0">
                <div class="cultura-prosa">{!! nl2br(e($secciones['historia']->texto ?? '')) !!}</div>
            </div>
        </div>
    </div>

    {{-- ── Objetivos ── --}}
    <div class="cultura-pane" id="pane-objetivos">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title cultura-serif">
                    🏁 {{ $secciones['objetivos']->titulo ?? 'Objetivos Estratégicos' }}
                </h3>
            </div>
            <div class="card-body pt-0">
                @if ($objetivos->isEmpty())
                    <div class="text-muted">Aún no se han capturado objetivos estratégicos.</div>
                @else
                    <ul class="cultura-objetivos">
                        @foreach ($objetivos as $i => $objetivo)
                            <li><span class="num">{{ $i + 1 }}</span><span>{{ $objetivo->titulo }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Empresas del Grupo ── --}}
    <div class="cultura-pane" id="pane-empresas">
        @if ($empresas->isEmpty())
            <div class="callout callout-info mb-0">Aún no se han registrado empresas del grupo.</div>
        @else
            <div class="cultura-empresas">
                @foreach ($empresas as $empresa)
                    <div class="cultura-emp-card">
                        <div class="cultura-emp-cover" style="background: {{ $empresa->color_fondo }}">
                            {{ $empresa->icono ?: '🏢' }}
                        </div>
                        <div class="cultura-emp-body">
                            <div class="cultura-emp-name">{{ $empresa->titulo }}</div>
                            <div class="cultura-emp-desc">{{ $empresa->descripcion }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

@can('cultura.editar.todos')
    @include('rrhh.cultura.partials._modal_secciones')
    @include('rrhh.cultura.partials._modal_slide')
@endcan
@stop

@section('css')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/cultura.css') }}">
@stop

@section('js')
<script>
(function () {
    'use strict';

    /* ═══════ PESTAÑAS ═══════ */
    document.querySelectorAll('.cultura-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.cultura-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.cultura-pane').forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            const pane = document.getElementById('pane-' + tab.dataset.pane);
            if (pane) pane.classList.add('active');
        });
    });

    /* ═══════ CARRUSEL ═══════ */
    const inner = document.getElementById('culturaSlidesInner');
    if (inner) {
        const slides = Array.from(inner.querySelectorAll('.cultura-slide'));
        const dots   = Array.from(document.querySelectorAll('.cultura-dot'));
        let indice = 0, temporizador = null;

        function posicionar() {
            inner.style.transform = 'translateX(-' + (indice * 100) + '%)';
            dots.forEach((d, i) => d.classList.toggle('active', i === indice));
            programar();
        }
        function programar() {
            clearInterval(temporizador);
            if (slides.length < 2) return;
            const seg = parseInt(slides[indice].dataset.duracion || '5', 10);
            temporizador = setInterval(function () {
                indice = (indice + 1) % slides.length;
                posicionar();
            }, Math.max(2, seg) * 1000);
        }
        window.culturaNav = function (dir) {
            if (!slides.length) return;
            indice = (indice + dir + slides.length) % slides.length;
            posicionar();
        };
        dots.forEach((d, i) => d.addEventListener('click', function () { indice = i; posicionar(); }));
        posicionar();
    }
})();
</script>

@can('cultura.editar.todos')
    @include('rrhh.cultura.partials._scripts_admin')
@endcan
@stop
