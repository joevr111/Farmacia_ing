<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $startToday = now()->startOfDay();
        $startMonth = now()->startOfMonth();

        // Tarjetas de resumen
        $kpis = [
            'today_total' => Sale::where('date', '>=', $startToday)->sum('total'),
            'today_count' => Sale::where('date', '>=', $startToday)->count(),
            'month_total' => Sale::where('date', '>=', $startMonth)->sum('total'),
            'month_count' => Sale::where('date', '>=', $startMonth)->count(),
            'low_stock'   => Medicine::where('stock', '<=', MedicineController::LOW_STOCK)->count(),
            'customers'   => Customer::count(),
        ];

        // Ventas de los últimos 7 días (los días sin ventas salen en 0)
        $byDay = Sale::where('date', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(date) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $labels[] = $day->format('d/m');
            $values[] = (float) ($byDay[$day->toDateString()] ?? 0);
        }

        // Los 5 medicamentos más vendidos
        $topMedicines = DB::table('detail_sales')
            ->join('medicines', 'medicines.id', '=', 'detail_sales.medicine_id')
            ->selectRaw('medicines.name, SUM(detail_sales.amount) as units, SUM(detail_sales.subtotal) as revenue')
            ->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('units')
            ->limit(5)
            ->get();

        // Últimas ventas y medicamentos con stock bajo
        $recentSales = Sale::with(['customer', 'user'])->latest('date')->limit(5)->get();
        $lowStock = Medicine::with('category')
            ->where('stock', '<=', MedicineController::LOW_STOCK)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        return view('principal.dashboard.index', compact(
            'kpis', 'labels', 'values', 'topMedicines', 'recentSales', 'lowStock'
        ));
    }
}