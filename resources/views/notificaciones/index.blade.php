@extends('adminlte::page')

@section('title', 'Notificaciones')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="m-0">
            <i class="far fa-bell text-primary mr-2"></i>Notificaciones
        </h1>
        <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Notificaciones</li>
        </ol>
    </div>
@stop

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                Bandeja de entrada
                @if ($totalNoLeidas > 0)
                    <span class="badge badge-danger ml-1">{{ $totalNoLeidas }} sin leer</span>
                @endif
            </h3>

            <div class="card-tools">
                @if ($totalNoLeidas > 0)
                    <form action="{{ route('notificaciones.leerTodas') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-check-double mr-1"></i>Marcar todas como leídas
                        </button>
                    </form>
                @endif

                <form action="{{ route('notificaciones.limpiarLeidas') }}" method="POST" class="d-inline"
                      onsubmit="return confirm('¿Eliminar todas las notificaciones ya leídas?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-broom mr-1"></i>Limpiar leídas
                    </button>
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            <ul class="nav nav-tabs px-3 pt-2 border-bottom-0">
                <li class="nav-item">
                    <a class="nav-link {{ $estado === 'todas' ? 'active' : '' }}"
                       href="{{ route('notificaciones.index') }}">
                        Todas <span class="badge badge-secondary ml-1">{{ $totalGeneral }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $estado === 'no_leidas' ? 'active' : '' }}"
                       href="{{ route('notificaciones.index', ['estado' => 'no_leidas']) }}">
                        No leídas <span class="badge badge-danger ml-1">{{ $totalNoLeidas }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $estado === 'leidas' ? 'active' : '' }}"
                       href="{{ route('notificaciones.index', ['estado' => 'leidas']) }}">
                        Leídas
                    </a>
                </li>
            </ul>

            <div class="list-group list-group-flush">
                @forelse ($notificaciones as $n)
                    <div class="list-group-item {{ $n['leida'] ? '' : 'bg-light' }}">
                        <div class="d-flex align-items-start">
                            <div class="mr-3 mt-1">
                                <span class="btn btn-{{ $n['color'] }} btn-sm disabled"
                                      style="width: 36px; height: 36px; line-height: 22px;">
                                    <i class="{{ $n['icono'] }}"></i>
                                </span>
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <h6 class="mb-1">
                                        @if (! $n['leida'])
                                            <span class="badge badge-danger mr-1">Nuevo</span>
                                        @endif
                                        @if ($n['url'])
                                            <a href="{{ route('notificaciones.ver', $n['id']) }}"
                                               class="text-dark font-weight-bold">
                                                {{ $n['titulo'] }}
                                            </a>
                                        @else
                                            <span class="font-weight-bold">{{ $n['titulo'] }}</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted" title="{{ $n['fecha_corta'] }}">
                                        <i class="far fa-clock mr-1"></i>{{ $n['fecha_humana'] }}
                                    </small>
                                </div>

                                @if ($n['mensaje'])
                                    <p class="mb-1 text-muted">{{ $n['mensaje'] }}</p>
                                @endif

                                <div class="mt-2">
                                    @if ($n['url'])
                                        <a href="{{ route('notificaciones.ver', $n['id']) }}"
                                           class="btn btn-xs btn-primary">
                                            <i class="fas fa-external-link-alt mr-1"></i>Abrir
                                        </a>
                                    @endif

                                    @if ($n['leida'])
                                        <form action="{{ route('notificaciones.noLeer', $n['id']) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-secondary">
                                                <i class="far fa-envelope mr-1"></i>Marcar no leída
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('notificaciones.leer', $n['id']) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-success">
                                                <i class="fas fa-check mr-1"></i>Marcar leída
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('notificaciones.destroy', $n['id']) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar esta notificación?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger">
                                            <i class="fas fa-trash mr-1"></i>Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center text-muted py-5">
                        <i class="far fa-bell-slash fa-3x mb-3 d-block"></i>
                        No hay notificaciones para mostrar.
                    </div>
                @endforelse
            </div>
        </div>

        @if ($notificaciones->hasPages())
            <div class="card-footer">
                {{ $notificaciones->links() }}
            </div>
        @endif
    </div>

@stop

@section('css')
<style>
    .list-group-item .btn-xs { padding: .15rem .4rem; font-size: .75rem; }
    .dropdown-item-title { white-space: normal; }
</style>
@stop