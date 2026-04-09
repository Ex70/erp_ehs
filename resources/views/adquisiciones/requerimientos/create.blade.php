@extends('adminlte::page')
@section('title', 'Nuevo requerimiento')

@section('content_header')
    <h1><i class="fas fa-shopping-cart mr-2"></i>Nuevo requerimiento</h1>
@stop

@section('content')
    <form action="{{ route('adquisiciones.requerimientos.store') }}" method="POST">
        @csrf
        @include('adquisiciones.requerimientos._form')
        <div class="d-flex justify-content-between mb-4">
            <a href="{{ route('adquisiciones.requerimientos.index') }}"
               class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar requerimiento
            </button>
        </div>
    </form>
@stop

@section('js')
@include('adquisiciones.requerimientos._scripts')
@stop