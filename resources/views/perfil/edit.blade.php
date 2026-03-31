@extends('adminlte::page')
@section('title', 'Editar mi perfil')

@section('content_header')
    <h1>Editar datos personales</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información personal</h3>
        </div>
        <form action="{{ route('perfil.update') }}" method="POST">
            @csrf @method('PUT')
            <div class="card-body">

                <div class="form-group">
                    <label>Nombre completo <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $usuario->name) }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre de usuario <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">@</span>
                                </div>
                                <input type="text" name="username"
                                       class="form-control @error('username') is-invalid @enderror"
                                       value="{{ old('username', $usuario->username) }}">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Correo electrónico <span class="text-danger">*</span></label>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $usuario->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Info de solo lectura --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Puesto</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $usuario->puesto?->nombre ?? 'Sin puesto' }}"
                                   readonly>
                            <small class="text-muted">
                                El puesto lo asigna el administrador.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Rol</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $usuario->getRoleNames()->implode(', ') }}"
                                   readonly>
                            <small class="text-muted">
                                El rol lo asigna el administrador.
                            </small>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('perfil.show') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
@stop

@section('css')@stop
@section('js')@stop