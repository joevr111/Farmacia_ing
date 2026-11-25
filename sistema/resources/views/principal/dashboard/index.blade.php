@extends('template.administrador')

@section('title', 'Dashboard')
@section('name_section', 'Dashboard')
@section('description', 'Resumen de la farmacia')
@section('item_section', 'Inicio')
@section('route_section', route('dashboard.index'))
@section('section', 'Dashboard')
@section('module', 'Principal')

@section('content')
{{-- Tarjetas de resumen --}}
<div class="row g-3 mb-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-cash-coin fs-1 text-primary"></i>
                <div>
                    <small class="text-muted text-uppercase d-block">Ventas de hoy</small>
                    <span class="fs-4 fw-bold">S/ {{ number_format($kpis['today_total'], 2) }}</span>
                    <small class="text-muted d-block">{{ $kpis['today_count'] }} venta(s)</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-calendar3 fs-1 text-primary"></i>
                <div>
                    <small class="text-muted text-uppercase d-block">Ventas del mes</small>
                    <span class="fs-4 fw-bold">S/ {{ number_format($kpis['month_total'], 2) }}</span>
                    <small class="text-muted d-block">{{ $kpis['month_count'] }} venta(s)</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <a href="{{ route('medicines.index', ['low_stock' => 1]) }}" class="text-decoration-none text-reset">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                    <div>
                        <small class="text-muted text-uppercase d-block">Stock bajo</small>
                        <span class="fs-4 fw-bold">{{ $kpis['low_stock'] }}</span>
                        <small class="text-muted d-block">medicamento(s)</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-people fs-1 text-primary"></i>
                <div>
                    <small class="text-muted text-uppercase d-block">Clientes</small>
                    <span class="fs-4 fw-bold">{{ $kpis['customers'] }}</span>
                    <small class="text-muted d-block">registrados</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Gráfico y más vendidos --}}
<div class="row g-3 mb-3">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Ventas de los últimos 7 días</div>
            <div class="card-body">
                <div style="height: 280px">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Más vendidos</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th class="small text-uppercase ps-3">Medicamento</th>
                            <th class="small text-uppercase text-center">Unid.</th>
                            <th class="small text-uppercase text-end pe-3">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topMedicines as $item)
                        <tr>
                            <td class="ps-3">{{ $item->name }}</td>
                            <td class="text-center">{{ $item->units }}</td>
                            <td class="text-end pe-3">S/ {{ number_format($item->revenue, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Aún no hay ventas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Últimas ventas y stock bajo --}}
<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span>Últimas ventas</span>
                <a href="{{ route('sales.index') }}" class="small text-decoration-none">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse ($recentSales as $sale)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('sales.show', $sale) }}" class="text-decoration-none">
                                    {{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>
                            <td>{{ $sale->customer->name }}</td>
                            <td class="text-muted small">{{ $sale->date->format('d/m H:i') }}</td>
                            <td class="text-end pe-3">S/ {{ number_format($sale->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td class="text-center text-muted py-3">Aún no hay ventas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span>Stock bajo</span>
                <a href="{{ route('medicines.index', ['low_stock' => 1]) }}" class="small text-decoration-none">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse ($lowStock as $medicine)
                        <tr>
                            <td class="ps-3">{{ $medicine->name }}</td>
                            <td class="text-muted small">{{ $medicine->category->name }}</td>
                            <td class="text-end pe-3"><span class="badge bg-danger">{{ $medicine->stock }}</span></td>
                        </tr>
                        @empty
                        <tr><td class="text-center text-muted py-3">Todo el inventario está bien abastecido</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    const primary = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary').trim() || '#ff0844';

    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'Ventas (S/)',
                data: @json($values),
                backgroundColor: primary,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => 'S/ ' + v } } }
        }
    });
</script>
@endpush