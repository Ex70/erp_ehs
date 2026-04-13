@extends('adminlte::page')
@section('title', $proveedor->nombre)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $proveedor->nombre }}</h1>
        <div>
            <a href="{{ route('solvencias.proveedores.edit', $proveedor) }}"
               class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('solvencias.proveedores.index') }}"
               class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Regresar
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Datos generales</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <tr><th>Nombre</th><td>{{ $proveedor->nombre }}</td></tr>
                        <tr><th>RFC</th><td><code>{{ $proveedor->rfc ?? '—' }}</code></td></tr>
                        <tr><th>Giro</th><td>{{ $proveedor->giro ?? '—' }}</td></tr>
                        <tr><th>Contacto</th><td>{{ $proveedor->contacto ?? '—' }}</td></tr>
                        <tr><th>Teléfono</th><td>{{ $proveedor->telefono ?? '—' }}</td></tr>
                        <tr><th>T. entrega</th><td>{{ $proveedor->tiempo_entrega ?? '—' }}</td></tr>
                        <tr><th>Estado</th>
                            <td>
                                <span class="badge badge-{{ $proveedor->activo ? 'success' : 'secondary' }}">
                                    {{ $proveedor->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-university mr-1"></i>
                        Cuentas bancarias
                    </h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>Banco</th>
                                <th>CLABE</th>
                                <th>Cuenta</th>
                                <th>Referencia</th>
                                <th>T. Entrega</th>
                                <th>Principal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proveedor->cuentasBancarias as $c)
                                <tr>
                                    <td>{{ $c->banco }}</td>
                                    <td><code>{{ $c->clabe ?? '—' }}</code></td>
                                    <td>{{ $c->cuenta ?? '—' }}</td>
                                    <td>{{ $c->referencia ?? '—' }}</td>
                                    <td>{{ $c->tiempo_entrega ?? '—' }}</td>
                                    <td>
                                        @if($c->principal)
                                            <span class="badge badge-success">Sí</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">
                                        Sin cuentas bancarias.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop