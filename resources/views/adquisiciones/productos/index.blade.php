@extends('adminlte::page')
@section('title', 'Productos y Servicios Frecuentes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-box-open mr-2"></i>
                Catálogo de productos y servicios frecuentes
            </h1>
            <small class="text-muted">
                {{ $stats['total'] }} producto{{ $stats['total'] != 1 ? 's' : '' }} registrado{{ $stats['total'] != 1 ? 's' : '' }}
            </small>
        </div>
        <a href="{{ route('adquisiciones.productos.create') }}"
           class="btn btn-danger btn-sm">
            <i class="fas fa-plus"></i> Nuevo producto
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

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET"
                  action="{{ route('adquisiciones.productos.index') }}"
                  class="d-flex align-items-center flex-wrap gap-2">
                <div class="flex-grow-1" style="min-width:280px">
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="NOMBRE, CATEGORÍA..."
                           value="{{ request('q') }}">
                </div>
                <div style="min-width:200px">
                    <select name="q_categoria" class="form-control form-control-sm">
                        <option value="">TODAS</option>
                        @foreach($categorias as $c)
                            <option value="{{ $c->id }}"
                                {{ request('q_categoria') == $c->id ? 'selected' : '' }}>
                                {{ $c->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i>
                </button>
                <a href="{{ route('adquisiciones.productos.index') }}"
                   class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </form>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0"
                   style="min-width:800px">
                <thead class="thead-dark">
                    <tr>
                        <th style="width:40px">#</th>
                        <th style="width:60px">Imagen</th>
                        <th>Producto / Servicio</th>
                        <th>Categoría</th>
                        <th>Unidad</th>
                        <th>Precio ref.</th>
                        <th>Proveedor sugerido</th>
                        <th>Última actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $i => $p)
                        <tr>
                            <td>{{ $productos->firstItem() + $i }}</td>
                            <td>
                                @if($p->imagen)
                                    <img src="{{ asset('storage/'.$p->imagen) }}"
                                         style="width:42px;height:42px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6"
                                         alt="{{ $p->nombre }}">
                                @else
                                    <div style="width:42px;height:42px;border-radius:6px;
                                                background:#f4f4f4;border:1px solid #dee2e6;
                                                display:flex;align-items:center;justify-content:center">
                                        <i class="fas fa-box text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $p->nombre }}</strong>
                                @if($p->ficha_tecnica)
                                    <br>
                                    <a href="{{ asset('storage/'.$p->ficha_tecnica) }}"
                                       target="_blank"
                                       class="badge badge-secondary mt-1">
                                        <i class="fas fa-file-pdf"></i> Ficha técnica
                                    </a>
                                @endif
                            </td>
                            <td>{{ $p->categoria?->nombre ?? '—' }}</td>
                            <td>{{ $p->unidadMedida?->clave ?? '—' }}</td>
                            <td>
                                {{ $p->precio_referencia
                                    ? '$'.number_format($p->precio_referencia, 2)
                                    : '—' }}
                            </td>
                            <td>
                                @forelse($p->proveedores as $pv)
                                    <span class="badge badge-info mr-1">
                                        {{ $pv->nombre }}
                                    </span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $p->updated_at->format('d/m/Y') }}
                                </small>
                            </td>
                            <td>
                                <a href="{{ route('adquisiciones.productos.show', $p) }}"
                                   class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('adquisiciones.productos.edit', $p) }}"
                                   class="btn btn-warning btn-xs">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('adquisiciones.productos.destroy', $p) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar {{ addslashes($p->nombre) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-xs">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                                CATÁLOGO VACÍO — AGREGA EL PRIMER PRODUCTO
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">
                {{ $productos->total() }} producto(s) encontrado(s)
            </small>
            {{ $productos->withQueryString()->links() }}
        </div>
    </div>

@stop
@section('css')@stop
@section('js')@stop