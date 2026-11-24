@extends('adminlte::page')
@section('title', 'Nueva solvencia')

@section('content_header')
    <h1 class="mb-0">Nueva solvencia</h1>
    <small class="text-muted">Solo tres datos: cuánto se paga, a qué proveedor y de qué se trata. El folio se asigna al crear.</small>
@stop

@section('content')
    @include('solvencias.trazabilidad.partials.nav')
    @include('solvencias.trazabilidad.partials.alertas')

    <div class="card" style="max-width:760px">
        <form method="POST" action="{{ route('sol.expedientes.store') }}" data-sol-once>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="card-body">
                @include('solvencias.trazabilidad.partials.campos', ['datos' => null, 'flujos' => $flujos])
            </div>

            <div class="card-footer d-flex flex-wrap align-items-center" style="gap:8px">
                <button type="submit" class="btn btn-sol"><i class="fas fa-folder-plus mr-1"></i>Crear expediente y asignar folio</button>
                <a href="{{ route('sol.bandeja') }}" class="btn btn-default">Cancelar</a>
                <span class="sol-hint m-0">Después cargas el soporte; el envío se habilita cuando el checklist está completo.</span>
            </div>
        </form>
    </div>

    @if($proveedores->isNotEmpty())
        <datalist id="solProveedores">
            @foreach($proveedores as $p)<option value="{{ $p }}">@endforeach
        </datalist>
    @endif
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/solvencias.css') }}?v={{ @filemtime(public_path('css/solvencias.css')) }}">
@stop

@section('js')
    <script src="{{ asset('js/solvencias.js') }}?v={{ @filemtime(public_path('js/solvencias.js')) }}"></script>
@stop
