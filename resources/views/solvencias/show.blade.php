@php use App\Models\Solvencia; @endphp
@extends('adminlte::page')
@section('title', $solvencia->folio)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-file-invoice-dollar mr-2"></i>
            {{ $solvencia->folio }}
        </h1>
        <div>
            <a href="{{ route('solvencias.pdf', $solvencia) }}"
               class="btn btn-danger btn-sm mr-1" target="_blank">
                <i class="fas fa-file-pdf"></i> Descargar PDF
            </a>
            <a href="{{ route('solvencias.solvencias.edit', $solvencia) }}"
               class="btn btn-warning btn-sm mr-1">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('solvencias.solvencias.index') }}"
               class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Regresar
            </a>
        </div>
    </div>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @php
        $cls = [
            'borrador'  => 'secondary',
            'pendiente' => 'warning',
            'aprobada'  => 'success',
            'rechazada' => 'danger',
            'pagada'    => 'info',
        ][$solvencia->estatus] ?? 'secondary';
    @endphp

    {{-- Encabezado datos --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered mb-0">
                        <tr>
                            <th style="width:35%">Empresa</th>
                            <td>{{ $solvencia->empresa?->nombre }}</td>
                        </tr>
                        <tr>
                            <th>Fecha</th>
                            <td>{{ $solvencia->fecha?->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>N° Cotización</th>
                            <td>{{ $solvencia->numero_cotizacion ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Cliente</th>
                            <td>{{ $solvencia->cliente ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Departamento</th>
                            <td>{{ $solvencia->departamento ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Estatus</th>
                            <td>
                                <span class="badge badge-{{ $cls }}">
                                    {{ Solvencia::estatuses()[$solvencia->estatus] }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-right">
                    <p class="mb-1">
                        <span class="text-muted">Subtotal:</span>
                        <strong>${{ number_format($solvencia->subtotal, 2) }}</strong>
                    </p>
                    <p class="mb-1">
                        <span class="text-muted">IVA (16%):</span>
                        <strong>${{ number_format($solvencia->iva, 2) }}</strong>
                    </p>
                    <hr>
                    <p class="mb-1 h5">
                        <span class="text-muted">Total:</span>
                        <strong class="text-success">
                            ${{ number_format($solvencia->total, 2) }}
                        </strong>
                    </p>
                    <p class="mb-1">
                        <small class="text-muted">Monto solicitado:</small>
                        ${{ number_format($solvencia->monto_solicitado, 2) }}
                    </p>
                    <p class="mb-0">
                        <small class="text-muted">Monto autorizado:</small>
                        ${{ number_format($solvencia->monto_autorizado, 2) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Partidas --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Partidas</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-striped mb-0" style="min-width:900px">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Importe</th>
                        <th>Proveedor</th>
                        <th>RFC</th>
                        <th>Banco</th>
                        <th>CLABE</th>
                        <th>Cuenta</th>
                        <th>Referencia</th>
                        <th>Concepto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($solvencia->partidas as $p)
                        <tr>
                            <td>{{ $p->numero }}</td>
                            <td>{{ $p->descripcion }}</td>
                            <td>{{ $p->cantidad }}</td>
                            <td>${{ number_format($p->importe, 2) }}</td>
                            <td>{{ $p->proveedor?->nombre ?? '—' }}</td>
                            <td><code>{{ $p->proveedor?->rfc ?? '—' }}</code></td>
                            <td>{{ $p->cuentaBancaria?->banco ?? '—' }}</td>
                            <td><code>{{ $p->cuentaBancaria?->clabe ?? '—' }}</code></td>
                            <td>{{ $p->cuentaBancaria?->cuenta ?? '—' }}</td>
                            <td>{{ $p->cuentaBancaria?->referencia ?? '—' }}</td>
                            <td>{{ $p->concepto ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">Sin partidas.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <th colspan="3" class="text-right">Subtotal:</th>
                        <th>${{ number_format($solvencia->subtotal, 2) }}</th>
                        <th colspan="7"></th>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-right">IVA (16%):</th>
                        <th>${{ number_format($solvencia->iva, 2) }}</th>
                        <th colspan="7"></th>
                    </tr>
                    <tr class="table-success">
                        <th colspan="3" class="text-right">Total:</th>
                        <th>${{ number_format($solvencia->total, 2) }}</th>
                        <th colspan="7"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Firmas --}}
    <div class="row">
        @foreach([
            ['elaboro_nombre', 'elaboro_cargo', 'Elaboró'],
            ['valido_nombre', 'valido_cargo', 'Validó'],
            ['autorizo_nombre', 'autorizo_cargo', 'Autorizó'],
        ] as [$nombre, $cargo, $titulo])
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-header py-2 bg-light">
                        <strong class="small text-uppercase">{{ $titulo }}</strong>
                    </div>
                    <div class="card-body py-4">
                        <div style="border-top:1px solid #333;margin:0 20px 8px"></div>
                        <p class="mb-0 font-weight-bold">
                            {{ $solvencia->$nombre ?? '—' }}
                        </p>
                        <p class="text-muted small mb-0">
                            {{ $solvencia->$cargo ?? '' }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

@stop
@section('css')@stop
@section('js')@stop