@extends('adminlte::page')
@section('title', 'Solvencias Económicas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-file-invoice-dollar mr-2"></i>
                Solvencias económicas
            </h1>
            <small class="text-muted">SIST-108 — Departamento de Sistemas</small>
        </div>
        <a href="{{ route('solvencias.solvencias.create') }}"
           class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nueva solvencia
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
                  action="{{ route('solvencias.solvencias.index') }}"
                  class="d-flex align-items-center flex-wrap gap-2">
                <div class="flex-grow-1" style="min-width:220px">
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="Folio, cliente, cotización..."
                           value="{{ request('q') }}">
                </div>
                <div style="min-width:180px">
                    <select name="q_empresa" class="form-control form-control-sm">
                        <option value="">Todas las empresas</option>
                        @foreach($empresas as $e)
                            <option value="{{ $e->id }}"
                                {{ request('q_empresa') == $e->id ? 'selected' : '' }}>
                                {{ $e->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:150px">
                    <select name="q_estatus" class="form-control form-control-sm">
                        <option value="">Todos los estatus</option>
                        @foreach($estatuses as $key => $label)
                            <option value="{{ $key }}"
                                {{ request('q_estatus') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i>
                </button>
                <a href="{{ route('solvencias.solvencias.index') }}"
                   class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i>
                </a>
            </form>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Folio</th>
                        <th>Empresa</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estatus</th>
                        <th>Elaboró</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($solvencias as $s)
                        @php
                            $cls = [
                                'borrador'  => 'secondary',
                                'pendiente' => 'warning',
                                'aprobada'  => 'success',
                                'rechazada' => 'danger',
                                'pagada'    => 'info',
                            ][$s->estatus] ?? 'secondary';
                        @endphp
                        <tr>
                            <td>
                                <span class="badge badge-dark">
                                    {{ $s->folio }}
                                </span>
                            </td>
                            <td>{{ $s->empresa?->nombre ?? '—' }}</td>
                            <td>{{ $s->cliente ?? '—' }}</td>
                            <td>{{ $s->fecha?->format('d/m/Y') }}</td>
                            <td>
                                <strong>
                                    ${{ number_format($s->total, 2) }}
                                </strong>
                            </td>
                            <td>
                                <span class="badge badge-{{ $cls }}">
                                    {{ Solvencia::estatuses()[$s->estatus] ?? $s->estatus }}
                                </span>
                            </td>
                            <td>{{ $s->elaborador?->name ?? '—' }}</td>
                            <td>
                                <a href="{{ route('solvencias.solvencias.show', $s) }}"
                                   class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('solvencias.solvencias.edit', $s) }}"
                                   class="btn btn-warning btn-xs">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('solvencias.pdf', $s) }}"
                                   class="btn btn-danger btn-xs"
                                   target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <form action="{{ route('solvencias.solvencias.destroy', $s) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar {{ $s->folio }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-xs">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>
                                No hay solvencias registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">{{ $solvencias->total() }} solvencia(s)</small>
            {{ $solvencias->withQueryString()->links() }}
        </div>
    </div>

@stop

@section('js')
<script>
// Necesario para usar Solvencia:: en la vista
</script>
@stop