@extends('template.administrador')

@section('title', 'Reportes')
@section('name_section', 'Reportes')
@section('description', 'Reporte de ventas e inventario')
@section('item_section', 'Inicio')
@section('route_section', route('reports.index'))
@section('section', 'Reportes')
@section('module', 'Principal')

@section('content')
{{-- Filtro por fechas --}}
<div class="card border-0 shadow-sm mb-3 d-print-none">
    <div class="card-body">
        <form action="{{ route('reports.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label for="from" class="form-label small mb-1">Desde</label>
                <input type="date" name="from" id="from" value="{{ $from }}"
                       class="form-control @error('from') is-invalid @enderror">
                @error('from')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-3">
                <label for="to" class="form-label small mb-1">Hasta</label>
                <input type="date" name="to" id="to" value="{{ $to }}"
                       class="form-control @error('to') is-invalid @enderror">
                @error('to')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary text-white">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
                <a href="{{ route('reports.export', ['from' => $from, 'to' => $to]) }}" class="btn btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Exportar CSV
                </a>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Imprimir
                </button>
            </div>
        </form>
    </div>
</div>

<p class="text-muted small">
    Período: {{ \Illuminate\Support\Carbon::parse($from)->format('d/m/Y') }}
    al {{ \Illuminate\Support\Carbon::parse($to)->format('d/m/Y') }}
</p>

{{-- Resumen --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <small class="text-muted text-uppercase d-block">Ventas realizadas</small>
            <span class="fs-4 fw-bold">{{ $summary->count }}</span>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <small class="text-muted text-uppercase d-block">Total vendido</small>
            <span class="fs-4 fw-bold">S/ {{ number_format($summary->total, 2) }}</span>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <small class="text-muted text-uppercase d-block">IGV (18%) / Op. gravada</small>
            <span class="fw-bold">S/ {{ number_format($summary->igv, 2) }}</span>
            <span class="text-muted"> / S/ {{ number_format($summary->total - $summary->igv, 2) }}</span>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <small class="text-muted text-uppercase d-block">Ticket promedio</small>
            <span class="fs-4 fw-bold">
                S/ {{ number_format($summary->count ? $summary->total / $summary->count : 0, 2) }}
            </span>
        </div></div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Ventas por día --}}
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Ventas por día</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th class="small text-uppercase ps-3">Fecha</th>
                            <th class="small text-uppercase text-center">Ventas</th>
                            <th class="small text-uppercase text-end pe-3">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byDay as $row)
                        <tr>
                            <td class="ps-3">{{ \Illuminate\Support\Carbon::parse($row->day)->format('d/m/Y') }}</td>
                            <td class="text-center">{{ $row->sales }}</td>
                            <td class="text-end pe-3">S/ {{ number_format($row->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Sin ventas en este período</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Ventas por vendedor --}}
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Ventas por vendedor</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th class="small text-uppercase ps-3">Vendedor</th>
                            <th class="small text-uppercase text-center">Ventas</th>
                            <th class="small text-uppercase text-end pe-3">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bySeller as $row)
                        <tr>
                            <td class="ps-3">{{ $row->seller }}</td>
                            <td class="text-center">{{ $row->sales }}</td>
                            <td class="text-end pe-3">S/ {{ number_format($row->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Sin ventas en este período</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Más vendidos --}}
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Medicamentos más vendidos (top 10)</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th class="small text-uppercase ps-3">Medicamento</th>
                            <th class="small text-uppercase text-center">Unidades</th>
                            <th class="small text-uppercase text-end pe-3">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topMedicines as $row)
                        <tr>
                            <td class="ps-3">{{ $row->name }}</td>
                            <td class="text-center">{{ $row->units }}</td>
                            <td class="text-end pe-3">S/ {{ number_format($row->revenue, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Sin ventas en este período</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Ventas por categoría --}}
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Ventas por categoría</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th class="small text-uppercase ps-3">Categoría</th>
                            <th class="small text-uppercase text-center">Unidades</th>
                            <th class="small text-uppercase text-end pe-3">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byCategory as $row)
                        <tr>
                            <td class="ps-3">{{ $row->category }}</td>
                            <td class="text-center">{{ $row->units }}</td>
                            <td class="text-end pe-3">S/ {{ number_format($row->revenue, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Sin ventas en este período</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Inventario con stock bajo --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Inventario con stock bajo (10 unidades o menos)</div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th class="small text-uppercase ps-3">Medicamento</th>
                    <th class="small text-uppercase">Categoría</th>
                    <th class="small text-uppercase text-end">Precio</th>
                    <th class="small text-uppercase text-center pe-3">Stock</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lowStock as $medicine)
                <tr>
                    <td class="ps-3">{{ $medicine->name }}</td>
                    <td>{{ $medicine->category->name }}</td>
                    <td class="text-end">S/ {{ number_format($medicine->price, 2) }}</td>
                    <td class="text-center pe-3"><span class="badge bg-danger">{{ $medicine->stock }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-3">Todo el inventario está bien abastecido</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection