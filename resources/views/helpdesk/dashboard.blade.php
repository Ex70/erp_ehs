@php
    use App\Models\Ticket;
@endphp
@extends('adminlte::page')
@section('title', 'Dashboard Helpdesk')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">Dashboard general</h1>
            <small class="text-muted">
                Departamento de Sistemas — {{ config('app.name') }}
            </small>
        </div>
        <div>
            {{ auth()->user()->name }} | {{ now()->format('d/M/Y') }}
        </div>
    </div>
@stop

@section('content')

    {{-- KPIs --}}
    <div class="row">
        <div class="col-lg col-6">
            <div class="info-box">
                <span class="info-box-icon bg-primary elevation-1">
                    <i class="fas fa-ticket-alt"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Total tickets</span>
                    <span class="info-box-number">{{ $stats['total'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg col-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning elevation-1">
                    <i class="fas fa-clock"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendientes</span>
                    <span class="info-box-number">{{ $stats['pendientes'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg col-6">
            <div class="info-box">
                <span class="info-box-icon bg-info elevation-1">
                    <i class="fas fa-spinner"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">En proceso</span>
                    <span class="info-box-number">{{ $stats['en_proceso'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg col-6">
            <div class="info-box">
                <span class="info-box-icon bg-success elevation-1">
                    <i class="fas fa-check-circle"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Finalizados</span>
                    <span class="info-box-number">{{ $stats['finalizados'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg col-6">
            <div class="info-box">
                <span class="info-box-icon bg-danger elevation-1">
                    <i class="fas fa-exclamation-triangle"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Escalados</span>
                    <span class="info-box-number">{{ $stats['escalados'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficas --}}
    <div class="row">

        {{-- Dona: por estado --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tickets por estado</h3>
                </div>
                <div class="card-body">
                    <canvas id="chart-estado" height="260"></canvas>
                    {{-- Leyenda --}}
                    <div class="d-flex flex-wrap gap-2 mt-2 justify-content-center">
                        @php
                            $coloresEstado = [
                                'pendiente'     => '#EF9F27',
                                'en_atencion'   => '#378ADD',
                                'en_desarrollo' => '#7F77DD',
                                'en_pruebas'    => '#1D9E75',
                                'finalizado'    => '#639922',
                                'escalado'      => '#E24B4A',
                            ];
                            $etiquetas = Ticket::etiquetasSeguimiento();
                        @endphp
                        @foreach($coloresEstado as $key => $color)
                            <span class="d-flex align-items-center gap-1 small mr-2">
                                <span style="width:12px;height:12px;border-radius:50%;background:{{ $color }};display:inline-block"></span>
                                {{ $etiquetas[$key] ?? $key }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Barras: por tipo de falla --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tickets por tipo de falla</h3>
                </div>
                <div class="card-body">
                    <canvas id="chart-tipo" height="260"></canvas>
                </div>
            </div>
        </div>

        {{-- Barras: por departamento --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tickets por departamento</h3>
                </div>
                <div class="card-body">
                    <canvas id="chart-depto" height="260"></canvas>
                </div>
            </div>
        </div>

        {{-- Línea: tendencia mensual --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tendencia mensual</h3>
                </div>
                <div class="card-body">
                    <canvas id="chart-tendencia" height="260"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- Acceso rápido --}}
    <div class="row">
        <div class="col-12">
            <a href="{{ route('helpdesk.tickets.index') }}"
               class="btn btn-primary mr-2">
                <i class="fas fa-list"></i> Ver todos los tickets
            </a>
            <a href="{{ route('helpdesk.tickets.create') }}"
               class="btn btn-success">
                <i class="fas fa-plus"></i> Nueva incidencia
            </a>
        </div>
    </div>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    const coloresPaleta = [
        '#EF9F27','#378ADD','#7F77DD','#1D9E75','#639922','#E24B4A',
        '#4e73df','#1cc88a','#36b9cc','#f6c23e'
    ];

    // Dona — por estado
    const estadoData  = @json($porEstado);
    const etiquetas   = @json(Ticket::etiquetasSeguimiento());
    const estadoLabels = Object.keys(estadoData).map(k => etiquetas[k] || k);
    const estadoCounts = Object.values(estadoData);
    const estadoColores = {
        pendiente:'#EF9F27', en_atencion:'#378ADD', en_desarrollo:'#7F77DD',
        en_pruebas:'#1D9E75', finalizado:'#639922', escalado:'#E24B4A'
    };

    new Chart(document.getElementById('chart-estado'), {
        type: 'doughnut',
        data: {
            labels: estadoLabels,
            datasets: [{
                data: estadoCounts,
                backgroundColor: Object.keys(estadoData).map(k => estadoColores[k] || '#ccc'),
                borderWidth: 2,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: { legend: { display: false } }
        }
    });

    // Barras — por tipo de falla
    const tipoData = @json($porTipo);
    new Chart(document.getElementById('chart-tipo'), {
        type: 'bar',
        data: {
            labels: tipoData.map(t => t.nombre),
            datasets: [{
                label: 'Tickets',
                data: tipoData.map(t => t.total),
                backgroundColor: coloresPaleta.slice(0, tipoData.length),
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });

    // Barras — por departamento
    const deptoData = @json($porDepartamento);
    new Chart(document.getElementById('chart-depto'), {
        type: 'bar',
        data: {
            labels: deptoData.map(d => d.depto),
            datasets: [{
                label: 'Tickets',
                data: deptoData.map(d => d.total),
                backgroundColor: coloresPaleta.slice(2, deptoData.length + 2),
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });

    // Línea — tendencia mensual
    const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const tendData = @json($tendencia);
    new Chart(document.getElementById('chart-tendencia'), {
        type: 'line',
        data: {
            labels: tendData.map(t => meses[t.mes - 1]),
            datasets: [{
                label: 'Tickets',
                data: tendData.map(t => t.total),
                borderColor: '#378ADD',
                backgroundColor: 'rgba(55,138,221,0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#378ADD',
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });

});
</script>
@stop