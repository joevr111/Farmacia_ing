<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de ventas e inventario</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 13px; margin: 18px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #bbb; padding: 5px; text-align: left; }
        th { background: #f0f0f0; }
        .number { text-align: right; }
        .period { margin-bottom: 16px; }
    </style>
</head>
<body>
    <h1>Reporte de ventas e inventario</h1>
    <p class="period">Período: {{ \Illuminate\Support\Carbon::parse($from)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($to)->format('d/m/Y') }}</p>

    <h2>Resumen</h2>
    <table>
        <tbody>
            <tr><th>Ventas realizadas</th><td>{{ $summary->count }}</td></tr>
            <tr><th>Total vendido</th><td>S/ {{ number_format($summary->total, 2) }}</td></tr>
            <tr><th>IGV (18%)</th><td>S/ {{ number_format($summary->igv, 2) }}</td></tr>
            <tr><th>Operación gravada</th><td>S/ {{ number_format($summary->total - $summary->igv, 2) }}</td></tr>
            <tr><th>Ticket promedio</th><td>S/ {{ number_format($summary->count ? $summary->total / $summary->count : 0, 2) }}</td></tr>
        </tbody>
    </table>

    <h2>Ventas por día</h2>
    <table>
        <thead><tr><th>Fecha</th><th>Ventas</th><th class="number">Total</th></tr></thead>
        <tbody>
            @forelse ($byDay as $row)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($row->day)->format('d/m/Y') }}</td>
                    <td>{{ $row->sales }}</td>
                    <td class="number">S/ {{ number_format($row->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Sin ventas en este período</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Ventas por vendedor</h2>
    <table>
        <thead><tr><th>Vendedor</th><th>Ventas</th><th class="number">Total</th></tr></thead>
        <tbody>
            @forelse ($bySeller as $row)
                <tr><td>{{ $row->seller }}</td><td>{{ $row->sales }}</td><td class="number">S/ {{ number_format($row->total, 2) }}</td></tr>
            @empty
                <tr><td colspan="3">Sin ventas en este período</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Medicamentos más vendidos (top 10)</h2>
    <table>
        <thead><tr><th>Medicamento</th><th>Unidades</th><th class="number">Monto</th></tr></thead>
        <tbody>
            @forelse ($topMedicines as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->units }}</td><td class="number">S/ {{ number_format($row->revenue, 2) }}</td></tr>
            @empty
                <tr><td colspan="3">Sin ventas en este período</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Ventas por categoría</h2>
    <table>
        <thead><tr><th>Categoría</th><th>Unidades</th><th class="number">Monto</th></tr></thead>
        <tbody>
            @forelse ($byCategory as $row)
                <tr><td>{{ $row->category }}</td><td>{{ $row->units }}</td><td class="number">S/ {{ number_format($row->revenue, 2) }}</td></tr>
            @empty
                <tr><td colspan="3">Sin ventas en este período</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Inventario con stock bajo (10 unidades o menos)</h2>
    <table>
        <thead><tr><th>Medicamento</th><th>Categoría</th><th class="number">Precio</th><th>Stock</th></tr></thead>
        <tbody>
            @forelse ($lowStock as $medicine)
                <tr>
                    <td>{{ $medicine->name }}</td>
                    <td>{{ $medicine->category->name }}</td>
                    <td class="number">S/ {{ number_format($medicine->price, 2) }}</td>
                    <td>{{ $medicine->stock }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Todo el inventario está bien abastecido</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
