@extends('adminlte::page')
@section('title', 'Nuevo proveedor')

@section('content_header')
    <h1>Nuevo proveedor — Solvencias</h1>
@stop

@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('solvencias.proveedores.store') }}" method="POST">
        @csrf
        @include('solvencias.proveedores._form')
        <div class="d-flex justify-content-between mb-4">
            <a href="{{ route('solvencias.proveedores.index') }}"
               class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar proveedor
            </button>
        </div>
    </form>
@stop