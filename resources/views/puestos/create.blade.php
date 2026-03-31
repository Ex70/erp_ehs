@extends('adminlte::page')

@section('title', 'Nuevo puesto')

@section('content_header')
    <h1>Nuevo puesto</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del puesto</h3>
        </div>
        <form action="{{ route('puestos.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('puestos._form')
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('puestos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar puesto
                </button>
            </div>
        </form>
    </div>
@stop