<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\DemoFarmaciaSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use LogicException;
use Tests\TestCase;

class DemoFarmaciaSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_demo_seeder_creates_complete_related_records_once_and_keeps_totals_consistent(): void
    {
        $role = Role::create(['name' => 'Administrador', 'description' => 'Rol de prueba']);
        User::factory()->create(['role_id' => $role->id, 'dni' => '12345678']);

        $seeder = new DemoFarmaciaSeeder;
        $seeder->run();
        $seeder->run();

        $this->assertDatabaseCount('categories', 8);
        $this->assertDatabaseCount('customers', 10);
        $this->assertDatabaseCount('medicines', 10);
        $this->assertDatabaseCount('sales', 10);
        $this->assertDatabaseCount('detail_sales', 20);

        foreach (Customer::all() as $customer) {
            $this->assertMatchesRegularExpression('/^\d{8}$/', $customer->dni);
            $this->assertMatchesRegularExpression('/^\d{9}$/', $customer->phone);
        }

        $expectedStock = [
            'Paracetamol 500 mg x 10 tabletas' => 23,
            'Ibuprofeno 400 mg x 10 tabletas' => 30,
            'Loratadina 10 mg x 10 tabletas' => 15,
            'Omeprazol 20 mg x 10 cápsulas' => 7,
            'Amoxicilina 500 mg x 10 cápsulas' => 39,
            'Azitromicina 500 mg x 3 tabletas' => 11,
            'Suero oral 500 ml' => 33,
            'Vitamina C 1 g x 10 tabletas efervescentes' => 19,
            'Clotrimazol crema 1% 20 g' => 6,
            'Hidrocortisona crema 1% 15 g' => 13,
        ];

        foreach ($expectedStock as $name => $stock) {
            $this->assertDatabaseHas('medicines', ['name' => $name, 'stock' => $stock]);
        }

        foreach (Sale::with('details')->get() as $sale) {
            $subtotal = $sale->details->sum(fn ($detail): float => (float) $detail->subtotal);

            $this->assertSame((float) $sale->total, $subtotal);
            $this->assertSame(round((float) $sale->total * 18 / 118, 2), (float) $sale->igv);
        }
    }

    public function test_demo_seeder_refuses_production_environment(): void
    {
        $this->app->instance('env', 'production');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('DemoFarmaciaSeeder solo se permite');

        (new DemoFarmaciaSeeder)->run();
    }
}
