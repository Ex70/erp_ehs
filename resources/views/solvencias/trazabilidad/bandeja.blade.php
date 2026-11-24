@extends('adminlte::page')
@section('title', 'Mi bandeja · Solvencias')

@section('content_header')
    <div class="d-flex justify-content-between align-items-end flex-wrap" style="gap:12px">
        <div>
            <h1 class="mb-0">{{ auth()->user()->name }}</h1>
            <small class="text-muted">{{ $responsabilidad }}</small>
        </div>
        @if($puedeElaborar)
            <a href="{{ route('sol.expedientes.create') }}" class="btn btn-sol btn-sm">
                <i class="fas fa-plus"></i> Nueva solvencia
            </a>
        @endif
    </div>
@stop

@section('content')
    @include('solvencias.trazabilidad.partials.nav')
    @include('solvencias.trazabilidad.partials.alertas')

    <div class="card">
        <div class="card-body">

            <div class="sol-sec">
                <h2>📥 Por acusar de recibido<span class="count">{{ $porAcusar->count() }}</span></h2>
                @forelse($porAcusar as $e)
                    @include('solvencias.trazabilidad.partials.tarjeta', ['e' => $e, 'modo' => 'acusar'])
                @empty
                    <div class="sol-vacio">No tienes solvencias pendientes de acuse.</div>
                @endforelse
            </div>

            <div class="sol-sec">
                <h2>📂 En mi poder<span class="count">{{ $enPoder->count() }}</span></h2>
                @forelse($enPoder as $e)
                    @include('solvencias.trazabilidad.partials.tarjeta', ['e' => $e, 'modo' => 'abrir'])
                @empty
                    <div class="sol-vacio">No tienes solvencias en tu poder.</div>
                @endforelse
            </div>

            @if($esPagador)
                <div class="sol-sec">
                    <h2>💳 Autorizadas por pagar<span class="count">{{ $porPagar->count() }}</span></h2>
                    @forelse($porPagar as $e)
                        @include('solvencias.trazabilidad.partials.tarjeta', ['e' => $e, 'modo' => 'pagar'])
                    @empty
                        <div class="sol-vacio">No hay solvencias autorizadas pendientes de pago.</div>
                    @endforelse
                </div>
            @endif

            <div class="sol-sec mb-0">
                <h2>📤 Enviadas por mí sin acuse<span class="count">{{ $enviadas->count() }}</span></h2>
                @forelse($enviadas as $e)
                    @include('solvencias.trazabilidad.partials.tarjeta', ['e' => $e, 'modo' => 'recordar'])
                @empty
                    <div class="sol-vacio">Todo lo que enviaste ya fue acusado de recibido.</div>
                @endforelse
            </div>

        </div>
    </div>

    @if($esPagador)
        @include('solvencias.trazabilidad.partials.modal-pago')
        @include('solvencias.trazabilidad.partials.visor-excel')
    @endif
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/solvencias.css') }}?v={{ @filemtime(public_path('css/solvencias.css')) }}">
@stop

@section('js')
    <script src="{{ asset('js/solvencias.js') }}?v={{ @filemtime(public_path('js/solvencias.js')) }}"></script>
@stop
