<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

#[Fillable(['type', 'message', 'detail', 'user_id', 'is_read'])]
class Activity extends Model
{
    // Registrar un movimiento: Activity::log('salida', 'Venta #000001', 'Total: S/ 10.00')
    public static function log(string $type, string $message, ?string $detail = null): void
    {
        static::create([
            'type' => $type,
            'message' => $message,
            'detail' => $detail,
            'user_id' => Auth::id(),
        ]);
    }
}