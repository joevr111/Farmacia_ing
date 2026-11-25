<?php

namespace App\Providers;

use App\Http\Controllers\MedicineController;
use App\Models\Activity;
use App\Models\Medicine;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Datos de la campanita: se envían a la vista del header en cada página
        View::composer('partials.header', function ($view) {
            $lowQuery = Medicine::where('stock', '<=', MedicineController::LOW_STOCK);

            $view->with([
                'notifLowStockCount' => (clone $lowQuery)->count(),
                'notifLowStock' => (clone $lowQuery)->orderBy('stock')->limit(5)->get(),
                'notifActivities' => Activity::latest()->limit(8)->get(),
                'notifUnread' => Activity::where('is_read', false)->count(),
            ]);
        });
    }
}