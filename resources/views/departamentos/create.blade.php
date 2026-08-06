@extends('adminlte::page')

@section('title', 'Nuevo departamento')

@section('content_header')
    <h1>Nuevo departamento</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del departamento</h3>
        </div>
        <form action="{{ route('departamentos.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('departamentos._form')
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('departamentos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar departamento
                </button>
            </div>
        </form>
    </div>
@stop