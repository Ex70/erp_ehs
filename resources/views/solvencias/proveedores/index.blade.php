@extends('adminlte::page')
@section('title', 'Proveedores — Solvencias')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-building mr-2"></i>Proveedores para solvencias</h1>
        <a href="{{ route('solvencias.proveedores.create') }}"
           class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nuevo proveedor
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

    {{-- Búsqueda --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET"
                  action="{{ route('solvencias.proveedores.index') }}"
                  class="d-flex gap-2">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Nombre, RFC, giro..."
                       value="{{ request('q') }}" style="max-width:400px">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i>
                </button>
                <a href="{{ route('solvencias.proveedores.index') }}"
                   class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i>
                </a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>RFC</th>
                        <th>Giro</th>
                        <th>Contacto</th>
                        <th>Cuentas bancarias</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proveedores as $p)
                        <tr>
                            <td><strong>{{ $p->nombre }}</strong></td>
                            <td><code>{{ $p->rfc ?? '—' }}</code></td>
                            <td>{{ $p->giro ?? '—' }}</td>
                            <td>{{ $p->contacto ?? '—' }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $p->cuentas_bancarias_count }}
                                    cuenta(s)
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $p->activo ? 'success' : 'secondary' }}">
                                    {{ $p->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('solvencias.proveedores.show', $p) }}"
                                   class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('solvencias.proveedores.edit', $p) }}"
                                   class="btn btn-warning btn-xs">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('solvencias.proveedores.destroy', $p) }}"
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
                            <td colspan="7" class="text-center text-muted py-4">
                                No hay proveedores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $proveedores->withQueryString()->links() }}</div>
    </div>

@stop