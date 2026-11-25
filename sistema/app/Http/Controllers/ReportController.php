<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // Lee el rango de fechas del formulario (por defecto: desde el 1.º del mes hasta hoy)
    private function range(Request $request): array
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $from = Carbon::parse($request->input('from') ?: now()->startOfMonth()->toDateString())->startOfDay();
        $to = Carbon::parse($request->input('to') ?: now()->toDateString())->endOfDay();

        return [$from, $to];
    }

    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        // Resumen del período
        $summary = Sale::whereBetween('date', [$from, $to])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total, COALESCE(SUM(igv), 0) as igv')
            ->first();

        // Ventas por día
        $byDay = Sale::whereBetween('date', [$from, $to])
            ->selectRaw('DATE(date) as day, COUNT(*) as sales, SUM(total) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Ventas por vendedor
        $bySeller = Sale::whereBetween('date', [$from, $to])
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->selectRaw('users.name as seller, COUNT(*) as sales, SUM(sales.total) as total')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->get();

        // Medicamentos más vendidos del período
        $topMedicines = DB::table('detail_sales')
            ->join('sales', 'sales.id', '=', 'detail_sales.sale_id')
            ->join('medicines', 'medicines.id', '=', 'detail_sales.medicine_id')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.date', [$from, $to])
            ->selectRaw('medicines.name, SUM(detail_sales.amount) as units, SUM(detail_sales.subtotal) as revenue')
            ->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('units')
            ->limit(10)
            ->get();

        // Ventas por categoría
        $byCategory = DB::table('detail_sales')
            ->join('sales', 'sales.id', '=', 'detail_sales.sale_id')
            ->join('medicines', 'medicines.id', '=', 'detail_sales.medicine_id')
            ->join('categories', 'categories.id', '=', 'medicines.category_id')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.date', [$from, $to])
            ->selectRaw('categories.name as category, SUM(detail_sales.amount) as units, SUM(detail_sales.subtotal) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        // Inventario con stock bajo (no depende del rango de fechas)
        $lowStock = Medicine::with('category')
            ->where('stock', '<=', MedicineController::LOW_STOCK)
            ->orderBy('stock')
            ->get();

        return view('principal.reports.index', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'summary' => $summary,
            'byDay' => $byDay,
            'bySeller' => $bySeller,
            'topMedicines' => $topMedicines,
            'byCategory' => $byCategory,
            'lowStock' => $lowStock,
        ]);
    }

    // Descarga las ventas del rango en CSV (se abre con Excel)
    public function export(Request $request)
    {
        [$from, $to] = $this->range($request);

        $sales = Sale::with(['customer', 'user'])
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get();

        $name = 'ventas_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($sales) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM: para que Excel muestre bien las tildes

            fputcsv($out, ['N° venta', 'Fecha', 'Cliente', 'DNI', 'Vendedor', 'Op. gravada', 'IGV', 'Total'], ';');

            foreach ($sales as $sale) {
                fputcsv($out, [
                    str_pad($sale->id, 6, '0', STR_PAD_LEFT),
                    $sale->date->format('d/m/Y H:i'),
                    $sale->customer->name,
                    $sale->customer->dni,
                    $sale->user->name,
                    number_format($sale->total - $sale->igv, 2, '.', ''),
                    number_format($sale->igv, 2, '.', ''),
                    number_format($sale->total, 2, '.', ''),
                ], ';');
            }

            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}