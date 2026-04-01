@extends('adminlte::page')
@section('title', 'Editar asignación — ' . $asignacion_ip->codigo)

@section('content_header')
    <h1>
        <i class="fas fa-network-wired mr-2"></i>
        Editar — {{ $asignacion_ip->codigo }}
    </h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del registro</h3>
        </div>
        <form action="{{ route('sistemas.redes.update', $asignacion_ip) }}" method="POST">
            @csrf @method('PUT')
            <div class="card-body">
                @include('sistemas.redes._form')
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('sistemas.redes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Actualizar registro
                </button>
            </div>
        </form>
    </div>
@stop

@section('js')
@include('sistemas.redes._scripts')
@stop