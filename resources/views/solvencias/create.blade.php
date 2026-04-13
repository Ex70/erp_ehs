@php use App\Models\Solvencia; @endphp
@extends('adminlte::page')
@section('title', 'Nueva Solvencia')

@section('content_header')
    <h1>
        <i class="fas fa-file-invoice-dollar mr-2"></i>
        Nueva solicitud de solvencia económica
    </h1>
@stop

@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('solvencias.solvencias.store') }}"
          method="POST" id="form-solvencia">
        @csrf
        @include('solvencias._form')
        <div class="d-flex justify-content-between mb-4">
            <a href="{{ route('solvencias.solvencias.index') }}"
               class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar solvencia
            </button>
        </div>
    </form>
@stop

@section('js')
@include('solvencias._scripts')
@stop