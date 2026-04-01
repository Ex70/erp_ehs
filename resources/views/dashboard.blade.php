@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')

    {{-- Tarjetas de resumen --}}
    <div class="row">

        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalUsuarios }}</h3>
                    <p>Usuarios totales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('usuarios.index') }}" class="small-box-footer">
                    Ver usuarios <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $usuariosActivos }}</h3>
                    <p>Usuarios activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <a href="{{ route('usuarios.index') }}" class="small-box-footer">
                    Ver detalle <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalRoles }}</h3>
                    <p>Roles definidos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-tag"></i>
                </div>
                <a href="{{ route('roles.index') }}" class="small-box-footer">
                    Ver roles <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $totalPermisos }}</h3>
                    <p>Permisos del sistema</p>
                </div>
                <div class="icon">
                    <i class="fas fa-key"></i>
                </div>
                <a href="{{ route('permisos.index') }}" class="small-box-footer">
                    Ver permisos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

    </div>

    {{-- Segunda fila: puestos --}}
    <div class="row">

        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-primary elevation-1">
                    <i class="fas fa-briefcase"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Puestos totales</span>
                    <span class="info-box-number">{{ $totalPuestos }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-success elevation-1">
                    <i class="fas fa-briefcase"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Puestos activos</span>
                    <span class="info-box-number">{{ $puestosActivos }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning elevation-1">
                    <i class="fas fa-user-slash"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Usuarios inactivos</span>
                    <span class="info-box-number">
                        {{ $totalUsuarios - $usuariosActivos }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-danger elevation-1">
                    <i class="fas fa-shield-alt"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Módulos con permisos</span>
                    <span class="info-box-number">
                        {{ collect(app(\Spatie\Permission\Models\Permission::class)::all())
                            ->map(fn($p) => explode('.', $p->name)[0])
                            ->unique()->count() }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- Gráficas --}}
    <div class="row">

        {{-- Usuarios por rol --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-tag mr-2"></i>Usuarios por rol
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="graficaRoles" height="200"></canvas>
                </div>
            </div>
        </div>

        {{-- Usuarios por puesto --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-briefcase mr-2"></i>Usuarios por puesto
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="graficaPuestos" height="200"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- Últimos usuarios registrados --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-clock mr-2"></i>Últimos usuarios registrados
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('usuarios.create') }}"
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nuevo usuario
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Puesto</th>
                                <th>Estado</th>
                                <th>Fecha de alta</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimosUsuarios as $usuario)
                                <tr>
                                    <td>
                                        @if($usuario->avatar)
                                            <img src="{{ asset('storage/'.$usuario->avatar) }}"
                                                 class="img-circle elevation-1 mr-1"
                                                 style="width:26px;height:26px;object-fit:cover;">
                                        @endif
                                        {{ $usuario->name }}
                                    </td>
                                    <td>{{ $usuario->username }}</td>
                                    <td>
                                        @foreach($usuario->getRoleNames() as $rol)
                                            <span class="badge badge-primary">{{ $rol }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $usuario->puesto?->nombre ?? '—' }}</td>
                                    <td>
                                        @if($usuario->activo)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>{{ $usuario->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('usuarios.show', $usuario) }}"
                                           class="btn btn-info btn-xs">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No hay usuarios registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('usuarios.index') }}" class="btn btn-default btn-sm">
                        Ver todos los usuarios
                        <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Paleta de colores
    const colores = [
        '#4e73df','#1cc88a','#36b9cc','#f6c23e',
        '#e74a3b','#858796','#5a5c69','#2e59d9',
    ];

    // Gráfica de roles — barras horizontales
    const rolesData = @json($usuariosPorRol);

    new Chart(document.getElementById('graficaRoles'), {
        type: 'bar',
        data: {
            labels: rolesData.map(r => r.label),
            datasets: [{
                label: 'Usuarios',
                data: rolesData.map(r => r.total),
                backgroundColor: colores.slice(0, rolesData.length),
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.raw} usuario${ctx.raw !== 1 ? 's' : ''}`
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { display: false },
                },
                y: {
                    grid: { display: false },
                }
            }
        }
    });

    // Gráfica de puestos — dona
    const puestosData = @json($usuariosPorPuesto);

    new Chart(document.getElementById('graficaPuestos'), {
        type: 'doughnut',
        data: {
            labels: puestosData.map(p => p.label),
            datasets: [{
                data: puestosData.map(p => p.total),
                backgroundColor: colores.slice(0, puestosData.length),
                hoverOffset: 8,
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { boxWidth: 14, padding: 12 }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.raw} usuario${ctx.raw !== 1 ? 's' : ''}`
                    }
                }
            },
            cutout: '65%',
        }
    });

});
</script>
@stop