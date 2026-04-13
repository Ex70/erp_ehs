@php use App\Models\Solvencia; @endphp
@extends('adminlte::page')
@section('title', 'Editar — ' . $solvencia->folio)

@section('content_header')
    <h1>
        <i class="fas fa-edit mr-2"></i>
        Editar — {{ $solvencia->folio }}
    </h1>
@stop

@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('solvencias.solvencias.update', $solvencia) }}"
          method="POST" id="form-solvencia">
        @csrf @method('PUT')

        {{-- Estatus --}}
        <div class="form-group">
            <label>Estatus</label>
            <select name="estatus" class="form-control" style="max-width:200px">
                @foreach(Solvencia::estatuses() as $key => $label)
                    <option value="{{ $key }}"
                        {{ $solvencia->estatus == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        @include('solvencias._form')

        <div class="d-flex justify-content-between mb-4">
            <a href="{{ route('solvencias.solvencias.show', $solvencia) }}"
               class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Actualizar solvencia
            </button>
        </div>
    </form>
@stop

@section('js')
@include('solvencias._scripts')
@stop