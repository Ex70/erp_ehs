@extends('adminlte::page')

@section('title', 'Editar departamento')

@section('content_header')
    <h1>Editar departamento — {{ $departamento->nombre }}</h1>
@stop

@section('content')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del departamento</h3>
        </div>
        <form action="{{ route('departamentos.update', $departamento) }}" method="POST">
            @csrf @method('PUT')
            <div class="card-body">
                @include('departamentos._form')
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('departamentos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Actualizar departamento
                </button>
            </div>
        </form>
    </div>
@stop