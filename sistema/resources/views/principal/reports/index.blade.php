@extends('template.administrador')

@section('title', 'Reportes')
@section('name_section', 'Reporte de ventas')
@section('description', 'Consulta y análisis de ventas de la farmacia')
@section('item_section', 'Inicio')
@section('route_section', route('reports.index'))
@section('section', 'Reportes')
@section('module', 'Administración')

@section('content')
<style>
    .report-panel {
        background: #fff;
        border: 1px solid #ddd;
        border-top: 3px solid #343a40;
        border-radius: 4px;
        padding: 16px;
        margin-bottom: 18px;
        color: #343a40;
    }

    .report-filters,
    .report-kpis {
        display: grid;
        gap: 14px;
        align-items: end;
    }

    .report-filters {
        grid-template-columns: repeat(4, minmax(0, 1fr)) auto auto;
    }

    .report-kpis {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .report-kpi {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .report-kpi strong {
        display: block;
        font-size: 24px;
    }

    .report-kpi i {
        font-size: 25px;
        color: #6c757d;
    }

    .report-filters label {
        display: block;
        font-size: 13px;
        margin-bottom: 5px;
    }

    .report-filters input,
    .report-filters select {
        width: 100%;
        min-width: 0;
    }

    .report-red {
        background: #dc3545;
        color: #fff;
        border: 1px solid #dc3545;
    }

    .report-red:hover {
        background: #bb2d3b;
        color: #fff;
    }

    .report-chart {
        height: 330px;
        position: relative;
    }

    .report-panel h6 {
        font-weight: 700;
        margin-bottom: 15px;
    }

    @media (max-width: 900px) {
        .report-filters {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .report-kpis {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        .report-filters,
        #sidebar,
        .app-header {
            display: none !important;
        }
    }
</style>

@php
    $exportFilters = ['from' => $from, 'to' => $to, 'seller' => $seller, 'category' => $category];
@endphp

<div class="report-panel">
    <form class="report-filters" method="GET" action="{{ route('reports.index') }}">
        <div>
            <label for="from">Desde</label>
            <input class="form-control" type="date" id="from" name="from" value="{{ $from }}">
        </div>
        <div>
            <label for="to">Hasta</label>
            <input class="form-control" type="date" id="to" name="to" value="{{ $to }}">
        </div>
        <div>
            <label for="seller">Vendedor</label>
            <select class="form-select" id="seller" name="seller">
                <option value="">Todos</option>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}" @selected($seller == $vendor->id)>{{ $vendor->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="category">Categoría</label>
            <select class="form-select" id="category" name="category">
                <option value="">Todas</option>
                @foreach ($categories as $item)
                    <option value="{{ $item->id }}" @selected($category == $item->id)>{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn report-red" type="submit"><i class="bi bi-funnel"></i> Filtrar</button>
        <a class="btn btn-outline-secondary" title="Limpiar filtros" href="{{ route('reports.index') }}">
            <i class="bi bi-arrow-counterclockwise"></i> Limpiar
        </a>
        <a class="btn btn-outline-secondary" href="{{ route('reports.export.pdf', $exportFilters) }}">
            <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
        </a>
        <a class="btn btn-outline-secondary" href="{{ route('reports.export.excel', $exportFilters) }}">
            <i class="bi bi-file-earmark-excel"></i> Descargar Excel
        </a>
        <a class="btn btn-outline-secondary" href="{{ route('reports.export', $exportFilters) }}">
            <i class="bi bi-file-earmark-spreadsheet"></i> Descargar CSV
        </a>
    </form>

    @foreach ($errors->all() as $error)
        <div class="text-danger mt-2">{{ $error }}</div>
    @endforeach
</div>

<div class="report-kpis">
    <div class="report-panel report-kpi">
        <div><small>Total vendido</small><strong>S/ {{ number_format($totalSales, 2) }}</strong></div>
        <i class="bi bi-cash-stack"></i>
    </div>
    <div class="report-panel report-kpi">
        <div><small>Número de ventas</small><strong>{{ $salesCount }}</strong></div>
        <i class="bi bi-receipt"></i>
    </div>
    <div class="report-panel report-kpi">
        <div><small>Ticket promedio</small><strong>S/ {{ number_format($averageSale, 2) }}</strong></div>
        <i class="bi bi-bar-chart"></i>
    </div>
</div>

<div class="report-panel">
    <h6>Ventas por día</h6>
    <div class="report-chart">
        <canvas id="reportSalesChart"></canvas>
    </div>
</div>

<div class="report-panel">
    <h6>Detalle de ventas</h6>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentSales as $sale)
                    <tr>
                        <td>{{ $sale->date?->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->customer->name ?? 'Sin cliente' }}</td>
                        <td>{{ $sale->user->name ?? 'Sin vendedor' }}</td>
                        <td class="text-end">S/ {{ number_format($sale->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">No hay ventas para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('reportSalesChart');

    if (!window.Chart) {
        canvas.parentElement.textContent = 'No se pudo cargar Chart.js. Comprueba tu conexión a internet.';
        return;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'Ventas por día',
                data: @json($values),
                backgroundColor: '#dc3545',
                borderRadius: 2,
                maxBarThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: context => 'S/ ' + Number(context.raw).toFixed(2) }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: value => 'S/ ' + value } },
                x: { ticks: { maxRotation: 55, minRotation: 45, autoSkip: true } }
            }
        }
    });
});
</script>
@endpush
