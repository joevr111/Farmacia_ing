@extends('template.administrador')

@section('title', 'Dashboard')
@section('name_section', 'Dashboard')
@section('description', 'Gráficos y KPIs')
@section('item_section', 'Inicio')
@section('route_section', route('dashboard.index'))
@section('section', 'Dashboard')
@section('module', 'Principal')

@section('content')
<style>
    .dashboard-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-top: 4px solid #343a40;
        border-radius: 10px;
        padding: 20px;
        height: 100%;
        box-shadow: 0 3px 8px rgba(0,0,0,.05);
    }

    .stat-label {
        font-size: 14px;
        color: #6c757d;
    }

    .stat-value {
        font-size: 27px;
        font-weight: 700;
        color: #343a40;
        margin: 12px 0 5px;
    }

    .chart-title {
        font-size: 16px;
        font-weight: 700;
        color: #343a40;
        margin-bottom: 20px;
    }

    .chart-container {
        position: relative;
        height: 300px;
    }
</style>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="dashboard-card">
            <div class="stat-label">Vendido hoy</div>
            <div class="stat-value">S/ {{ number_format($ventasHoy, 2) }}</div>
            <small class="text-secondary">Ingresos del día</small>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="dashboard-card">
            <div class="stat-label">Vendido este mes</div>
            <div class="stat-value">S/ {{ number_format($ventasMes, 2) }}</div>
            <small class="text-secondary">Acumulado mensual</small>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="dashboard-card">
            <div class="stat-label">Clientes registrados</div>
            <div class="stat-value">{{ $totalClientes }}</div>
            <small class="text-secondary">Total de clientes</small>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="dashboard-card">
            <div class="stat-label">Stock bajo</div>
            <div class="stat-value text-danger">{{ $stockBajo }}</div>
            <small class="text-secondary">10 unidades o menos</small>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="dashboard-card">
            <h5 class="chart-title">Ventas de los últimos 7 días</h5>
            <div class="chart-container">
                <canvas id="ventasChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-5">
        <div class="dashboard-card">
            <h5 class="chart-title">Ventas del mes por categoría</h5>
            <div class="chart-container">
                <canvas id="categoriasChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-7">
        <div class="dashboard-card">
            <h5 class="chart-title">Top 5 medicamentos más vendidos del mes</h5>
            <div class="chart-container">
                <canvas id="medicamentosChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rosa = '#e57389';

    new Chart(document.getElementById('ventasChart'), {
        type: 'line',
        data: {
            labels: @json($ventasLabels),
            datasets: [{
                label: 'Ventas',
                data: @json($ventasDatos),
                borderColor: rosa,
                backgroundColor: 'rgba(229,115,137,0.18)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: rosa
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: context => 'S/ ' + Number(context.raw).toFixed(2)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: value => 'S/ ' + value }
                },
                x: { grid: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('categoriasChart'), {
        type: 'doughnut',
        data: {
            labels: @json($categoriasLabels),
            datasets: [{
                data: @json($categoriasDatos),
                backgroundColor: [
                    '#e57389',
                    '#6c757d',
                    '#adb5bd',
                    '#f0a5b4',
                    '#c94b64',
                    '#929aa4',
                    '#f5c5ce'
                ],
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '55%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 15 }
                },
                tooltip: {
                    callbacks: {
                        label: context => context.label + ': S/ ' + Number(context.raw).toFixed(2)
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('medicamentosChart'), {
        type: 'bar',
        data: {
            labels: @json($medicamentosLabels),
            datasets: [{
                label: 'Unidades vendidas',
                data: @json($medicamentosDatos),
                backgroundColor: [
                    '#e57389',
                    '#6c757d',
                    '#adb5bd',
                    '#d95c75',
                    '#929aa4'
                ],
                borderRadius: 5,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 } },
                y: { grid: { display: false } }
            }
        }
    });
});
</script>
@endpush
