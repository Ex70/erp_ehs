@extends('adminlte::page')

@section('title', 'Editar puesto')

@section('content_header')
    <h1>Editar puesto — {{ $puesto->nombre }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del puesto</h3>
        </div>
        <form action="{{ route('puestos.update', $puesto) }}" method="POST">
            @csrf @method('PUT')
            <div class="card-body">
                @include('puestos._form')
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('puestos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Actualizar puesto
                </button>
            </div>
        </form>
    </div>
@stop