@extends('adminlte::page')
@section('title', 'Editar — ' . $requerimiento->folio)

@section('content_header')
    <h1>
        <i class="fas fa-edit mr-2"></i>
        Editar — <span class="text-muted">{{ $requerimiento->folio }}</span>
    </h1>
@stop

@section('content')
    <form action="{{ route('adquisiciones.requerimientos.update', $requerimiento) }}"
          method="POST">
        @csrf @method('PUT')
        @include('adquisiciones.requerimientos._form')
        <div class="d-flex justify-content-between mb-4">
            <a href="{{ route('adquisiciones.requerimientos.show', $requerimiento) }}"
               class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Actualizar requerimiento
            </button>
        </div>
    </form>
@stop

@section('js')
@include('adquisiciones.requerimientos._scripts')
@stop