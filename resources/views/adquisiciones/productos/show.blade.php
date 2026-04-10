@extends('adminlte::page')
@section('title', $producto->nombre)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-box mr-2"></i>{{ $producto->nombre }}</h1>
        <div>
            <a href="{{ route('adquisiciones.productos.edit', $producto) }}"
               class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('adquisiciones.productos.index') }}"
               class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Regresar
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">

    {{-- Imagen y archivos --}}
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                @if($producto->imagen)
                    <img src="{{ asset('storage/'.$producto->imagen) }}"
                         class="img-fluid rounded mb-3"
                         style="max-height:200px;object-fit:contain"
                         alt="{{ $producto->nombre }}">
                @else
                    <div class="py-4 text-muted">
                        <i class="fas fa-box fa-4x mb-2"></i>
                        <p class="small">Sin imagen</p>
                    </div>
                @endif

                @if($producto->ficha_tecnica)
                    <a href="{{ asset('storage/'.$producto->ficha_tecnica) }}"
                       target="_blank"
                       class="btn btn-outline-danger btn-sm btn-block">
                        <i class="fas fa-file-pdf"></i> Ver ficha técnica
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Datos --}}
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información del producto</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered mb-3">
                    <tr>
                        <th style="width:35%">Nombre</th>
                        <td>{{ $producto->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Categoría</th>
                        <td>{{ $producto->categoria?->nombre ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Unidad de medida</th>
                        <td>
                            {{ $producto->unidadMedida
                                ? $producto->unidadMedida->clave.' — '.$producto->unidadMedida->nombre
                                : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Precio de referencia</th>
                        <td>
                            {{ $producto->precio_referencia
                                ? '$'.number_format($producto->precio_referencia, 2)
                                : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td>
                            <span class="badge badge-{{ $producto->activo ? 'success' : 'secondary' }}">
                                {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Última actualización</th>
                        <td>{{ $producto->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>

                {{-- Proveedores sugeridos --}}
                <h5 class="mt-3">
                    <i class="fas fa-truck mr-1"></i> Proveedores sugeridos
                </h5>
                @forelse($producto->proveedores as $pv)
                    <span class="badge badge-info mr-1 mb-1 p-2">
                        {{ $pv->nombre }}
                        @if($pv->ciudad)
                            <small>({{ $pv->ciudad }})</small>
                        @endif
                    </span>
                @empty
                    <p class="text-muted">Sin proveedores sugeridos.</p>
                @endforelse

                {{-- Especificaciones --}}
                @if($producto->especificaciones)
                    <h5 class="mt-3">
                        <i class="fas fa-list mr-1"></i> Especificaciones
                    </h5>
                    <div class="bg-light rounded p-3">
                        {{ $producto->especificaciones }}
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@stop
@section('css')@stop
@section('js')@stop