<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\DetailSale;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    // Listado de ventas (con cliente y vendedor cargados)
    public function index()
    {
        $sales = Sale::withTrashed()->with(['customer', 'user'])->latest('date')->get();

        return view('management.sales.index', compact('sales'));
    }

    // Formulario para registrar una venta
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $medicines = Medicine::where('stock', '>', 0)->orderBy('name')->get();

        return view('management.sales.create', compact('customers', 'users', 'medicines'));
    }

    public function edit(Sale $sale)
    {
        $sale->load('details.medicine');

        $customers = Customer::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $stockAvailable = $sale->details->mapWithKeys(fn (DetailSale $detail) => [
            $detail->medicine_id => (int) $detail->medicine->stock + (int) $detail->amount,
        ])->all();
        $historicalPrices = $sale->details->mapWithKeys(fn (DetailSale $detail) => [
            $detail->medicine_id => $detail->price_unit,
        ])->all();
        $medicines = Medicine::query()
            ->where('stock', '>', 0)
            ->orWhereIn('id', $sale->details->pluck('medicine_id'))
            ->orderBy('name')
            ->get();
        $items = $sale->details->map(fn (DetailSale $detail) => [
            'medicine_id' => $detail->medicine_id,
            'amount' => $detail->amount,
            'price_unit' => $detail->price_unit,
        ])->all();

        return view('management.sales.edit', compact(
            'sale',
            'customers',
            'users',
            'medicines',
            'stockAvailable',
            'historicalPrices',
            'items',
        ));
    }

    // Guardar la venta con sus detalles
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|distinct|exists:medicines,id',
            'items.*.amount' => 'required|integer|min:1',
        ], [
            'items.required' => 'Debe agregar al menos un medicamento.',
            'items.*.medicine_id.distinct' => 'No repita el mismo medicamento, aumente la cantidad.',
        ]);

        // Transacción: si algo falla, no se guarda nada
        $sale = DB::transaction(function () use ($request) {
            // 1. Crear la venta (el total se calcula después)
            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'user_id' => $request->user_id,
                'date' => now(),
                'total' => 0,
                'igv' => 0,
            ]);

            $total = 0;

            // 2. Registrar cada medicamento vendido
            foreach ($request->items as $item) {
                // lockForUpdate(): bloquea la fila hasta terminar, para que dos ventas
                // a la vez no vendan el mismo stock
                $medicine = Medicine::lockForUpdate()->find($item['medicine_id']);

                if ($medicine->stock < $item['amount']) {
                    // Lanzar la excepción deshace todo lo guardado en la transacción (rollback)
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$medicine->name} (disponible: {$medicine->stock}).",
                    ]);
                }

                // El precio se toma de la base de datos, no del formulario
                $subtotal = $medicine->price * $item['amount'];

                $sale->details()->create([
                    'medicine_id' => $medicine->id,
                    'amount' => $item['amount'],
                    'price_unit' => $medicine->price,
                    'subtotal' => $subtotal,
                ]);

                // 3. Descontar el stock
                $medicine->decrement('stock', $item['amount']);

                $total += $subtotal;
            }

            // 4. Guardar total e IGV (el precio ya incluye el 18% de IGV)
            $sale->update([
                'total' => $total,
                'igv' => round($total * 18 / 118, 2),
            ]);

            return $sale;
        });
        Activity::log('salida', 'Venta #'.str_pad($sale->id, 6, '0', STR_PAD_LEFT).' registrada', 'Total: S/ '.number_format($sale->total, 2));

        return to_route('sales.show', $sale)->with('ok', 'Venta registrada');
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|distinct|exists:medicines,id',
            'items.*.amount' => 'required|integer|min:1',
        ], [
            'items.required' => 'Debe agregar al menos un medicamento.',
            'items.*.medicine_id.distinct' => 'No repita el mismo medicamento, aumente la cantidad.',
        ]);

        $sale = DB::transaction(function () use ($validated, $sale) {
            $sale = Sale::query()->lockForUpdate()->findOrFail($sale->id);
            $existingDetails = $sale->details()->lockForUpdate()->get()->keyBy('medicine_id');
            $items = collect($validated['items'])->keyBy('medicine_id');
            $medicineIds = $items->keys()
                ->merge($existingDetails->keys())
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->sort()
                ->values();
            $medicines = Medicine::query()
                ->whereIn('id', $medicineIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;

            foreach ($medicineIds as $medicineId) {
                $medicine = $medicines->get($medicineId);
                $existingDetail = $existingDetails->get($medicineId);
                $item = $items->get($medicineId);

                if (! $medicine) {
                    throw ValidationException::withMessages([
                        'items' => 'Uno de los medicamentos ya no está disponible.',
                    ]);
                }

                $availableStock = (int) $medicine->stock + (int) ($existingDetail?->amount ?? 0);
                $amount = (int) ($item['amount'] ?? 0);

                if ($amount > $availableStock) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$medicine->name} (disponible: {$availableStock}).",
                    ]);
                }

                $priceUnit = $existingDetail
                    ? (float) $existingDetail->price_unit
                    : (float) $medicine->price;
                $subtotal = $priceUnit * $amount;

                $medicine->stock = $availableStock - $amount;
                $medicine->save();

                if ($item) {
                    $detailAttributes = [
                        'medicine_id' => $medicine->id,
                        'amount' => $amount,
                        'price_unit' => $priceUnit,
                        'subtotal' => $subtotal,
                    ];

                    if ($existingDetail) {
                        $existingDetail->update($detailAttributes);
                    } else {
                        $sale->details()->create($detailAttributes);
                    }

                    $total += $subtotal;
                } elseif ($existingDetail) {
                    $existingDetail->delete();
                }
            }

            $sale->update([
                'customer_id' => $validated['customer_id'],
                'user_id' => $validated['user_id'],
                'total' => $total,
                'igv' => round($total * 18 / 118, 2),
            ]);

            return $sale;
        });

        Activity::log(
            'salida',
            'Venta #'.str_pad($sale->id, 6, '0', STR_PAD_LEFT).' actualizada',
            'Total: S/ '.number_format($sale->total, 2),
        );

        return to_route('sales.show', $sale)->with('ok', 'Venta actualizada');
    }

    // Ver el detalle de una venta
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'details.medicine']);

        return view('management.sales.show', compact('sale'));
    }

    // Anular venta: devuelve el stock y la elimina
    // Anular venta: devuelve el stock y la marca como eliminada (se puede recuperar)
    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            foreach ($sale->details as $detail) {
                Medicine::where('id', $detail->medicine_id)->increment('stock', $detail->amount);
            }

            $sale->delete(); // soft delete: los detalles se conservan
        });
        Activity::log('anulacion', 'Venta #'.str_pad($sale->id, 6, '0', STR_PAD_LEFT).' anulada', 'Stock devuelto · S/ '.number_format($sale->total, 2));

        return to_route('sales.index')->with('ok', 'Venta anulada y stock devuelto');
    }

    // Recuperar una venta anulada: vuelve a descontar el stock
    public function restore(Sale $sale)
    {
        try {
            DB::transaction(function () use ($sale) {
                foreach ($sale->details as $detail) {
                    $medicine = Medicine::lockForUpdate()->find($detail->medicine_id);

                    if ($medicine->stock < $detail->amount) {
                        throw new \RuntimeException(
                            "No se puede recuperar: stock insuficiente de {$medicine->name} (disponible: {$medicine->stock})."
                        );
                    }

                    $medicine->decrement('stock', $detail->amount);
                }

                $sale->restore();
            });
        } catch (\RuntimeException $e) {
            return to_route('sales.index')->with('error', $e->getMessage());
        }
        Activity::log('recuperacion', 'Venta #'.str_pad($sale->id, 6, '0', STR_PAD_LEFT).' recuperada', 'Stock descontado de nuevo · S/ '.number_format($sale->total, 2));

        return to_route('sales.index')->with('ok', 'Venta recuperada y stock descontado nuevamente');
    }

    // Eliminar definitivamente una venta ya anulada (no se puede recuperar)
    public function forceDelete(Sale $sale)
    {
        // Solo se puede eliminar por completo una venta que ya está anulada
        if (! $sale->trashed()) {
            return to_route('sales.index')->with('error', 'Primero debe anular la venta.');
        }

        DB::transaction(function () use ($sale) {
            // El stock ya se devolvió al anular, por eso aquí NO se toca.
            // Primero los detalles (la clave foránea es restrictOnDelete)...
            $sale->details()->delete();

            // ...y después la venta, de forma permanente
            $sale->forceDelete();
        });

        return to_route('sales.index')->with('ok', 'Venta eliminada definitivamente');
    }
}
