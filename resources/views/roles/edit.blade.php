@extends('adminlte::page')
@section('title', 'Editar rol')

@section('content_header')
    <h1>Editar rol — {{ $rol->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del rol</h3>
        </div>
        <form action="{{ route('roles.update', $rol) }}" method="POST">
            @csrf @method('PUT')
            <div class="card-body">
                @include('roles._form')
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Actualizar rol
                </button>
            </div>
        </form>
    </div>
@stop

@section('js')
{{-- Mismo JS que create --}}
<script>
document.querySelectorAll('.check-modulo').forEach(function(chkModulo) {
    chkModulo.addEventListener('change', function() {
        const modulo = this.dataset.modulo;
        document.querySelectorAll('.check-' + modulo).forEach(function(chk) {
            chk.checked = chkModulo.checked;
        });
    });
});

document.querySelectorAll('.check-permiso').forEach(function(chk) {
    chk.addEventListener('change', function() {
        const modulo    = this.classList[2].replace('check-', '');
        const hijos     = document.querySelectorAll('.check-' + modulo);
        const marcados  = document.querySelectorAll('.check-' + modulo + ':checked');
        const chkModulo = document.getElementById('modulo_' + modulo);
        chkModulo.checked       = marcados.length === hijos.length;
        chkModulo.indeterminate = marcados.length > 0 && marcados.length < hijos.length;
    });
});

// Al cargar, sincronizar estado inicial de checkboxes de módulo
document.querySelectorAll('.check-modulo').forEach(function(chkModulo) {
    const modulo   = chkModulo.dataset.modulo;
    const hijos    = document.querySelectorAll('.check-' + modulo);
    const marcados = document.querySelectorAll('.check-' + modulo + ':checked');
    chkModulo.checked       = marcados.length === hijos.length;
    chkModulo.indeterminate = marcados.length > 0 && marcados.length < hijos.length;
});
</script>
@stop