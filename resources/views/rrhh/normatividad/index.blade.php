@extends('adminlte::page')

@section('title', 'Normatividad')

@php
    $categoriaActiva = request('cat', array_key_first($categorias));
    if (! array_key_exists($categoriaActiva, $categorias)) {
        $categoriaActiva = array_key_first($categorias);
    }
@endphp

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap">
        <div>
            <h1 class="m-0">📜 Normatividad y Políticas</h1>
            <small class="text-muted">Políticas, protocolos, reglamentos y normas oficiales del Grupo</small>
        </div>
        @can('normatividad.crear')
            <button type="button" class="btn btn-primary btn-sm" id="btnNuevoDocumento">
                + Agregar Documento
            </button>
        @endcan
    </div>
@stop

@section('content')
<div class="norm-wrap">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>Revisa los siguientes datos:</strong>
            <ul class="mb-0 mt-1 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @can('normatividad.editar')
        @if ($porVencer > 0)
            <div class="callout callout-warning">
                Hay <strong>{{ $porVencer }}</strong>
                {{ $porVencer === 1 ? 'documento vencido o por vencer' : 'documentos vencidos o por vencer' }}
                en los próximos {{ config('normatividad.dias_aviso_vencimiento') }} días.
            </div>
        @endif
    @endcan

    {{-- ══ BÚSQUEDA ══ --}}
    <form method="GET" action="{{ route('rrhh.normatividad.index') }}" class="mb-3">
        <input type="hidden" name="cat" value="{{ $categoriaActiva }}">
        <div class="input-group norm-buscador">
            <input type="text" name="q" class="form-control" value="{{ $termino }}"
                   placeholder="Buscar por título, responsable o versión…">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
                @if (filled($termino))
                    <a href="{{ route('rrhh.normatividad.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                @endif
            </div>
        </div>
        @if (filled($termino))
            <small class="text-muted">Mostrando resultados para «{{ $termino }}» en todas las categorías.</small>
        @endif
    </form>

    {{-- ══ PESTAÑAS ══ --}}
    <div class="norm-tabs" role="tablist">
        @foreach ($categorias as $clave => $cfg)
            <button type="button"
                    class="norm-tab {{ $clave === $categoriaActiva ? 'active' : '' }}"
                    data-pane="{{ $clave }}">
                {{ $cfg['icono'] }} {{ $cfg['nombre'] }}
                <span class="conteo">{{ $conteos[$clave] ?? 0 }}</span>
            </button>
        @endforeach
    </div>

    {{-- ══ LISTAS ══ --}}
    @foreach ($categorias as $clave => $cfg)
        <div class="norm-pane {{ $clave === $categoriaActiva ? 'active' : '' }}" id="pane-{{ $clave }}">
            @include('rrhh.normatividad.partials._lista', [
                'lista' => $documentos[$clave] ?? collect(),
                'cfg'   => $cfg,
            ])
        </div>
    @endforeach

</div>

@can('normatividad.crear')
    @include('rrhh.normatividad.partials._modal_documento')
@endcan
@stop

@section('css')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/normatividad.css') }}">
@stop

@section('js')
<script>
(function () {
    'use strict';

    // Pestañas: cambia el panel visible y refleja la categoría en la URL
    // para que al guardar se regrese a la pestaña correcta.
    document.querySelectorAll('.norm-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.norm-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.norm-pane').forEach(p => p.classList.remove('active'));
            tab.classList.add('active');

            const pane = document.getElementById('pane-' + tab.dataset.pane);
            if (pane) pane.classList.add('active');

            const url = new URL(window.location.href);
            url.searchParams.set('cat', tab.dataset.pane);
            window.history.replaceState({}, '', url);

            const campoCat = document.querySelector('form [name="cat"]');
            if (campoCat) campoCat.value = tab.dataset.pane;
        });
    });
})();
</script>

@can('normatividad.crear')
    @include('rrhh.normatividad.partials._scripts_admin')
@endcan
@stop
