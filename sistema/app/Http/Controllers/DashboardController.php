<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $startMonth = now()->startOfMonth();
        $endMonth = now()->endOfMonth();

        $ventasHoy = Sale::whereDate('date', today())->sum('total');
        $ventasMes = Sale::whereBetween('date', [$startMonth, $endMonth])->sum('total');
        $totalClientes = Customer::count();
        $stockBajo = Medicine::where('stock', '<=', MedicineController::LOW_STOCK)->count();

        $dailySales = Sale::query()
            ->whereBetween('date', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(date) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $ventasLabels = [];
        $ventasDatos = [];

        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $date = Carbon::today()->subDays($daysAgo);
            $dayKey = $date->toDateString();

            $ventasLabels[] = $date->format('d/m');
            $ventasDatos[] = (float) ($dailySales[$dayKey] ?? 0);
        }

        $categorySales = DB::table('detail_sales')
            ->join('sales', 'sales.id', '=', 'detail_sales.sale_id')
            ->join('medicines', 'medicines.id', '=', 'detail_sales.medicine_id')
            ->join('categories', 'categories.id', '=', 'medicines.category_id')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.date', [$startMonth, $endMonth])
            ->selectRaw('categories.name, SUM(detail_sales.subtotal) as total')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get();

        $topMedicines = DB::table('detail_sales')
            ->join('sales', 'sales.id', '=', 'detail_sales.sale_id')
            ->join('medicines', 'medicines.id', '=', 'detail_sales.medicine_id')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.date', [$startMonth, $endMonth])
            ->selectRaw('medicines.name, SUM(detail_sales.amount) as quantity')
            ->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();

        return view('principal.dashboard.index', [
            'ventasHoy' => (float) $ventasHoy,
            'ventasMes' => (float) $ventasMes,
            'totalClientes' => $totalClientes,
            'stockBajo' => $stockBajo,
            'ventasLabels' => $ventasLabels,
            'ventasDatos' => $ventasDatos,
            'categoriasLabels' => $categorySales->pluck('name'),
            'categoriasDatos' => $categorySales->map(fn (object $row): float => (float) $row->total),
            'medicamentosLabels' => $topMedicines->pluck('name'),
            'medicamentosDatos' => $topMedicines->map(fn (object $row): int => (int) $row->quantity),
        ]);
    }
}
