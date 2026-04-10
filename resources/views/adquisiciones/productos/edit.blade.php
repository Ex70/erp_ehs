@extends('adminlte::page')
@section('title', 'Editar producto — ' . $producto->nombre)

@section('content_header')
    <h1><i class="fas fa-edit mr-2"></i>Editar — {{ Str::limit($producto->nombre, 50) }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del producto</h3>
        </div>
        <form action="{{ route('adquisiciones.productos.update', $producto) }}"
              method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="card-body">
                @include('adquisiciones.productos._form')
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('adquisiciones.productos.index') }}"
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Actualizar
                </button>
            </div>
        </form>
    </div>
@stop

@section('js')
@include('adquisiciones.productos._scripts')
@stop