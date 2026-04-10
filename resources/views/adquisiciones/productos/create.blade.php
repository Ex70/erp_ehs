@extends('adminlte::page')
@section('title', 'Nuevo producto')

@section('content_header')
    <h1><i class="fas fa-plus mr-2"></i>Nuevo producto / servicio</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del producto</h3>
        </div>
        <form action="{{ route('adquisiciones.productos.store') }}"
              method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                @include('adquisiciones.productos._form')
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('adquisiciones.productos.index') }}"
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
@stop

@section('js')
@include('adquisiciones.productos._scripts')
@stop