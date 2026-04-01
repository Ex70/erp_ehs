@extends('adminlte::page')
@section('title', 'Detalle — ' . $asignacion_ip->codigo)

@section('content_header')
    <h1>
        <i class="fas fa-network-wired mr-2"></i>
        {{ $asignacion_ip->codigo }} — {{ $asignacion_ip->nombre }}
    </h1>
@stop

@section('content')
<div class="row">

    {{-- Tarjeta de red --}}
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-wifi mr-1"></i> Datos de red
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered mb-0">
                    <tr>
                        <th>Código</th>
                        <td><span class="badge badge-secondary">{{ $asignacion_ip->codigo }}</span></td>
                    </tr>
                    <tr>
                        <th>Dirección IP</th>
                        <td><span class="badge badge-info">{{ $asignacion_ip->direccion_ip }}</span></td>
                    </tr>
                    <tr>
                        <th>Dirección MAC</th>
                        <td><code>{{ $asignacion_ip->direccion_mac }}</code></td>
                    </tr>
                    <tr>
                        <th>Fecha asignación</th>
                        <td>{{ $asignacion_ip->fecha_asignacion?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Usuario vinculado --}}
        @if($asignacion_ip->usuario)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-1"></i> Usuario del sistema
                    </h3>
                </div>
                <div class="card-body">
                    <p class="mb-1">
                        <strong>{{ $asignacion_ip->usuario->name }}</strong>
                    </p>
                    <p class="text-muted mb-0">
                        {{ '@'.$asignacion_ip->usuario->username }}
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- Dispositivo y ubicación --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-laptop mr-1"></i> Dispositivo
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered mb-0">
                    <tr>
                        <th style="width:35%">Nombre usuario</th>
                        <td>{{ $asignacion_ip->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Tipo</th>
                        <td>{{ $asignacion_ip->dispositivo }}</td>
                    </tr>
                    <tr>
                        <th>Marca</th>
                        <td>{{ $asignacion_ip->marca }}</td>
                    </tr>
                    <tr>
                        <th>Modelo</th>
                        <td>{{ $asignacion_ip->modelo }}</td>
                    </tr>
                    <tr>
                        <th>Número de serie</th>
                        <td><code>{{ $asignacion_ip->numero_serie }}</code></td>
                    </tr>
                    <tr>
                        <th>Área</th>
                        <td>
                            <span class="badge badge-success">
                                {{ $asignacion_ip->area }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Puesto</th>
                        <td>{{ $asignacion_ip->puesto }}</td>
                    </tr>
                </table>
            </div>
            <div class="card-footer">
                <a href="{{ route('sistemas.redes.index') }}"
                   class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Regresar
                </a>
                @can('redes.editar')
                    <a href="{{ route('sistemas.redes.edit', $asignacion_ip) }}"
                       class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                @endcan
            </div>
        </div>
    </div>

</div>
@stop

@section('css')@stop
@section('js')@stop