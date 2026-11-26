<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SalesUpdateTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_edit_form_shows_historical_price_and_restored_available_stock(): void
    {
        [$user, $sale, $medicine] = $this->createSaleScenario(stock: 1, amount: 2, currentPrice: 25, historicalPrice: 10);

        $response = $this->actingAs($user)->get(route('sales.edit', $sale));

        $response->assertOk()
            ->assertSee('data-price="10.00"', false)
            ->assertSee('stock: 3');
    }

    public function test_update_preserves_historical_prices_and_adjusts_stock_atomically(): void
    {
        [$user, $sale, $medicine] = $this->createSaleScenario(stock: 6, amount: 2, currentPrice: 25, historicalPrice: 10);
        $category = Category::query()->firstOrFail();
        $newMedicine = Medicine::create([
            'category_id' => $category->id,
            'name' => 'Nuevo medicamento',
            'price' => 7,
            'stock' => 3,
        ]);
        $originalDate = $sale->date->copy();

        $response = $this->actingAs($user)->put(route('sales.update', $sale), [
            'customer_id' => $sale->customer_id,
            'user_id' => $sale->user_id,
            'items' => [
                ['medicine_id' => $medicine->id, 'amount' => 3],
                ['medicine_id' => $newMedicine->id, 'amount' => 2],
            ],
        ]);

        $response->assertRedirect(route('sales.show', $sale));
        $sale->refresh();
        $details = $sale->details()->get()->keyBy('medicine_id');

        $this->assertSame(5, (int) $medicine->fresh()->stock);
        $this->assertSame(1, (int) $newMedicine->fresh()->stock);
        $this->assertSame(10.0, (float) $details->get($medicine->id)->price_unit);
        $this->assertSame(30.0, (float) $details->get($medicine->id)->subtotal);
        $this->assertSame(7.0, (float) $details->get($newMedicine->id)->price_unit);
        $this->assertSame(14.0, (float) $details->get($newMedicine->id)->subtotal);
        $this->assertSame(44.0, (float) $sale->total);
        $this->assertSame(6.71, (float) $sale->igv);
        $this->assertSame($originalDate->toDateTimeString(), $sale->date->toDateTimeString());
    }

    public function test_update_rejects_insufficient_stock_without_changing_sale_or_inventory(): void
    {
        [$user, $sale, $medicine] = $this->createSaleScenario(stock: 1, amount: 2, currentPrice: 25, historicalPrice: 10);

        $response = $this->actingAs($user)
            ->from(route('sales.edit', $sale))
            ->put(route('sales.update', $sale), [
                'customer_id' => $sale->customer_id,
                'user_id' => $sale->user_id,
                'items' => [
                    ['medicine_id' => $medicine->id, 'amount' => 4],
                ],
            ]);

        $response->assertRedirect(route('sales.edit', $sale))
            ->assertSessionHasErrors('items');

        $this->assertSame(1, (int) $medicine->fresh()->stock);
        $this->assertSame(2, (int) $sale->fresh()->details()->firstOrFail()->amount);
        $this->assertSame(20.0, (float) $sale->fresh()->total);
    }

    public function test_update_restores_stock_when_a_medicine_is_removed_from_the_sale(): void
    {
        [$user, $sale, $medicine] = $this->createSaleScenario(stock: 1, amount: 2, currentPrice: 25, historicalPrice: 10);
        $category = Category::query()->firstOrFail();
        $replacement = Medicine::create([
            'category_id' => $category->id,
            'name' => 'Medicamento de reemplazo',
            'price' => 5,
            'stock' => 4,
        ]);

        $response = $this->actingAs($user)->put(route('sales.update', $sale), [
            'customer_id' => $sale->customer_id,
            'user_id' => $sale->user_id,
            'items' => [
                ['medicine_id' => $replacement->id, 'amount' => 1],
            ],
        ]);

        $response->assertRedirect(route('sales.show', $sale));
        $this->assertSame(3, (int) $medicine->fresh()->stock);
        $this->assertDatabaseMissing('detail_sales', [
            'sale_id' => $sale->id,
            'medicine_id' => $medicine->id,
        ]);
        $this->assertSame(3, (int) $replacement->fresh()->stock);
        $this->assertSame(5.0, (float) $sale->fresh()->total);
    }

    public function test_anulled_sales_cannot_be_edited(): void
    {
        [$user, $sale] = $this->createSaleScenario(stock: 1, amount: 2, currentPrice: 25, historicalPrice: 10);
        $sale->delete();

        $this->actingAs($user)
            ->get(route('sales.edit', $sale))
            ->assertNotFound();
    }

    /**
     * @return array{0: User, 1: Sale, 2: Medicine}
     */
    private function createSaleScenario(int $stock, int $amount, float $currentPrice, float $historicalPrice): array
    {
        $role = Role::create(['name' => 'Administrador', 'description' => 'Rol de prueba']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'dni' => '12345678',
        ]);
        $customer = Customer::create([
            'name' => 'Cliente de prueba',
            'dni' => '87654321',
        ]);
        $category = Category::create([
            'name' => 'Categoría de prueba',
            'description' => 'Descripción de prueba',
        ]);
        $medicine = Medicine::create([
            'category_id' => $category->id,
            'name' => 'Medicamento existente',
            'price' => $currentPrice,
            'stock' => $stock,
        ]);
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'date' => now()->subDay(),
            'total' => $historicalPrice * $amount,
            'igv' => round($historicalPrice * $amount * 18 / 118, 2),
        ]);
        $sale->details()->create([
            'medicine_id' => $medicine->id,
            'amount' => $amount,
            'price_unit' => $historicalPrice,
            'subtotal' => $historicalPrice * $amount,
        ]);

        return [$user, $sale, $medicine];
    }
}
