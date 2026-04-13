@extends('adminlte::page')
@section('title', $proveedor->nombre)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $proveedor->nombre }}</h1>
        <a href="{{ route('adquisiciones.proveedores.index') }}"
           class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Regresar
        </a>
    </div>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        {{-- Datos generales --}}
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
                        <tr><th>Ciudad</th><td>{{ $proveedor->ciudad ?? '—' }}</td></tr>
                        <tr><th>Correo</th><td>{{ $proveedor->correo ?? '—' }}</td></tr>
                        <tr><th>Teléfono</th><td>{{ $proveedor->telefono ?? '—' }}</td></tr>
                        <tr><th>Condiciones pago</th><td>{{ $proveedor->condiciones_pago ?? '—' }}</td></tr>
                        <tr><th>T. entrega</th><td>{{ $proveedor->tiempo_entrega ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Cuentas bancarias --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-university mr-1"></i>
                        Cuentas bancarias
                    </h3>
                    <button class="btn btn-primary btn-sm"
                            data-toggle="modal" data-target="#modal-cuenta">
                        <i class="fas fa-plus"></i> Agregar cuenta
                    </button>
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
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proveedor->cuentasBancarias as $c)
                                <tr>
                                    <td><strong>{{ $c->banco }}</strong></td>
                                    <td><code>{{ $c->clabe ?? '—' }}</code></td>
                                    <td>{{ $c->cuenta ?? '—' }}</td>
                                    <td>{{ $c->referencia ?? '—' }}</td>
                                    <td>{{ $c->tiempo_entrega ?? '—' }}</td>
                                    <td>
                                        @if($c->principal)
                                            <span class="badge badge-success">Principal</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('adquisiciones.proveedores.cuentas.destroy', $c) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar esta cuenta?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-xs">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">
                                        Sin cuentas bancarias. Agrega la primera.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal agregar cuenta --}}
    <div class="modal fade" id="modal-cuenta" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('adquisiciones.proveedores.cuentas.store', $proveedor) }}"
                  method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nueva cuenta bancaria</h5>
                        <button type="button" class="close"
                                data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Banco <span class="text-danger">*</span></label>
                            <input type="text" name="banco"
                                   class="form-control" required
                                   placeholder="Ej: BBVA">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>CLABE</label>
                                    <input type="text" name="clabe"
                                           class="form-control"
                                           placeholder="18 dígitos"
                                           maxlength="25">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Número de cuenta</label>
                                    <input type="text" name="cuenta"
                                           class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Referencia</label>
                                    <input type="text" name="referencia"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tiempo de entrega</label>
                                    <input type="text" name="tiempo_entrega"
                                           class="form-control"
                                           placeholder="Ej: 15 HORAS">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar cuenta
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@stop