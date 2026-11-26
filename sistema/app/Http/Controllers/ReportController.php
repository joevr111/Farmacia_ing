<?php

namespace App\Http\Controllers;

use App\Exports\ReportsExport;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        return view('principal.reports.index', $this->getReportData($request));
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);

        return Pdf::loadView('principal.reports.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download($this->exportName($data, 'pdf'));
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getReportData($request);

        return Excel::download(
            new ReportsExport($data['from'], $data['to'], $data),
            $this->exportName($data, 'xlsx'),
        );
    }

    public function export(Request $request)
    {
        $data = $this->getReportData($request);
        $sales = $data['recentSales'];
        $name = 'ventas_'.Carbon::parse($data['from'])->format('Ymd').'_'.Carbon::parse($data['to'])->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($sales) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

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

    /**
     * @return array<string, mixed>
     */
    private function getReportData(Request $request): array
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'seller' => 'nullable|exists:users,id',
            'category' => 'nullable|exists:categories,id',
        ]);

        $from = Carbon::parse($validated['from'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $to = Carbon::parse($validated['to'] ?? now()->endOfMonth()->toDateString())->endOfDay();
        $seller = $validated['seller'] ?? null;
        $category = $validated['category'] ?? null;

        $salesQuery = Sale::query()->whereBetween('date', [$from, $to]);

        if ($seller) {
            $salesQuery->where('user_id', $seller);
        }

        if ($category) {
            $salesQuery->whereHas('details.medicine', fn (Builder $query) => $query->where('category_id', $category));
        }

        $summary = (clone $salesQuery)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total, COALESCE(SUM(igv), 0) as igv')
            ->first();

        $byDay = (clone $salesQuery)
            ->selectRaw('DATE(sales.date) as day, COUNT(*) as sales, SUM(sales.total) as total')
            ->groupByRaw('DATE(sales.date)')
            ->orderBy('day')
            ->get();

        $dailyTotals = $byDay->mapWithKeys(fn (object $row): array => [$row->day => (float) $row->total]);
        $labels = [];
        $values = [];
        $lastDate = $to->copy()->startOfDay();

        for ($date = $from->copy()->startOfDay(); $date->lte($lastDate); $date->addDay()) {
            $dateKey = $date->toDateString();
            $labels[] = $date->format('d/m/Y');
            $values[] = $dailyTotals[$dateKey] ?? 0;
        }

        $bySeller = (clone $salesQuery)
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->selectRaw('users.name as seller, COUNT(*) as sales, SUM(sales.total) as total')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->get();

        $detailSales = DB::table('detail_sales')
            ->join('sales', 'sales.id', '=', 'detail_sales.sale_id')
            ->join('medicines', 'medicines.id', '=', 'detail_sales.medicine_id')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.date', [$from, $to])
            ->when($seller, fn ($query) => $query->where('sales.user_id', $seller))
            ->when($category, fn ($query) => $query->where('medicines.category_id', $category));

        $topMedicines = (clone $detailSales)
            ->selectRaw('medicines.name, SUM(detail_sales.amount) as units, SUM(detail_sales.subtotal) as revenue')
            ->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('units')
            ->limit(10)
            ->get();

        $byCategory = (clone $detailSales)
            ->join('categories', 'categories.id', '=', 'medicines.category_id')
            ->selectRaw('categories.name as category, SUM(detail_sales.amount) as units, SUM(detail_sales.subtotal) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        $lowStock = Medicine::with('category')
            ->when($category, fn (Builder $query) => $query->where('category_id', $category))
            ->where('stock', '<=', MedicineController::LOW_STOCK)
            ->orderBy('stock')
            ->get();

        $recentSales = (clone $salesQuery)
            ->with(['customer', 'user'])
            ->orderByDesc('date')
            ->get();

        $totalSales = (float) $summary->total;
        $salesCount = (int) $summary->count;

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'seller' => $seller,
            'category' => $category,
            'vendors' => User::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'totalSales' => $totalSales,
            'salesCount' => $salesCount,
            'averageSale' => $salesCount ? $totalSales / $salesCount : 0,
            'recentSales' => $recentSales,
            'labels' => $labels,
            'values' => $values,
            'summary' => $summary,
            'byDay' => $byDay,
            'bySeller' => $bySeller,
            'topMedicines' => $topMedicines,
            'byCategory' => $byCategory,
            'lowStock' => $lowStock,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function exportName(array $data, string $extension): string
    {
        $from = Carbon::parse($data['from'])->format('Ymd');
        $to = Carbon::parse($data['to'])->format('Ymd');

        return 'reporte_'.$from.'_'.$to.'.'.$extension;
    }
}
