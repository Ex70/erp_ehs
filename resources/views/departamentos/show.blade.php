@extends('adminlte::page')

@section('title', $departamento->nombre)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            {{ $departamento->nombre }}
            @if($departamento->clave)
                <small class="text-muted">({{ $departamento->clave }})</small>
            @endif
        </h1>
        @can('departamentos.editar')
            <a href="{{ route('departamentos.edit', $departamento) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="row">
        {{-- Ficha del departamento --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información general</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Responsable</dt>
                        <dd>{{ $departamento->responsable->name ?? 'Sin asignar' }}</dd>

                        <dt>Estatus</dt>
                        <dd>
                            @if($departamento->activo)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-secondary">Inactivo</span>
                            @endif
                        </dd>

                        <dt>Descripción</dt>
                        <dd>{{ $departamento->descripcion ?: '—' }}</dd>

                        <dt>Total de colaboradores</dt>
                        <dd>{{ $plantilla->flatten()->count() }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Puestos habilitados</h3>
                </div>
                <div class="card-body">
                    @forelse($departamento->puestos as $p)
                        <span class="badge badge-info mb-1">{{ $p->nombre }}</span>
                    @empty
                        <p class="text-muted mb-0">
                            No hay puestos habilitados. Edita el departamento para asignarlos.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Plantilla agrupada por puesto --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Plantilla por puesto</h3>
                </div>
                <div class="card-body p-0">
                    @forelse($plantilla as $nombrePuesto => $usuarios)
                        <div class="p-3 border-bottom bg-light">
                            <strong>{{ $nombrePuesto }}</strong>
                            <span class="badge badge-primary ml-1">{{ $usuarios->count() }}</span>
                        </div>
                        <table class="table table-sm mb-0">
                            <tbody>
                                @foreach($usuarios as $u)
                                    <tr>
                                        <td style="width:50px;">
                                            @if($u->avatar)
                                                <img src="{{ asset('storage/'.$u->avatar) }}"
                                                     style="width:32px;height:32px;object-fit:cover;border-radius:50%;">
                                            @else
                                                <span class="badge badge-secondary rounded-circle p-2">
                                                    {{ Str::upper(Str::substr($u->name, 0, 1)) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $u->name }}
                                            <br><small class="text-muted">{{ $u->email }}</small>
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('usuarios.show', $u) }}"
                                               class="btn btn-xs btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @empty
                        <p class="text-muted text-center py-4 mb-0">
                            No hay colaboradores activos asignados a este departamento.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('departamentos.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
@stop