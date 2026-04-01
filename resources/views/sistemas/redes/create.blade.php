@extends('adminlte::page')
@section('title', 'Nueva asignación de IP')

@section('content_header')
    <h1><i class="fas fa-network-wired mr-2"></i>Nueva asignación de IP</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del registro</h3>
        </div>
        <form action="{{ route('sistemas.redes.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('sistemas.redes._form')
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('sistemas.redes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar registro
                </button>
            </div>
        </form>
    </div>
@stop

@section('js')
@include('sistemas.redes._scripts')
@stop