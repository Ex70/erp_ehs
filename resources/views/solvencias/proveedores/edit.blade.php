@extends('adminlte::page')
@section('title', 'Editar — ' . $proveedor->nombre)

@section('content_header')
    <h1>Editar proveedor — {{ $proveedor->nombre }}</h1>
@stop

@section('content')
    <form action="{{ route('solvencias.proveedores.update', $proveedor) }}"
          method="POST">
        @csrf @method('PUT')
        @include('solvencias.proveedores._form')
        <div class="d-flex justify-content-between mb-4">
            <a href="{{ route('solvencias.proveedores.index') }}"
               class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Actualizar
            </button>
        </div>
    </form>
@stop