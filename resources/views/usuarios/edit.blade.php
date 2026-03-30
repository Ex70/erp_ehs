@extends('adminlte::page')

@section('title', 'Editar usuario')

@section('content_header')
    <h1>Editar usuario — {{ $usuario->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del usuario</h3>
        </div>
        <form action="{{ route('usuarios.update', $usuario) }}"
              method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="card-body">
                @include('usuarios._form')
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Actualizar usuario
                </button>
            </div>
        </form>
    </div>
@stop