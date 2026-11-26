<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\DetailSale;
use App\Models\Medicine;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardChartTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dashboard_renders_reference_charts_with_live_database_values(): void
    {
        $this->travelTo('2026-11-26 12:00:00');
        $role = Role::create(['name' => 'Administrador', 'description' => 'Rol de prueba']);
        $user = User::factory()->create(['role_id' => $role->id, 'dni' => '12345678']);
        $customer = Customer::create(['name' => 'Cliente de prueba', 'dni' => '87654321']);
        $category = Category::create(['name' => 'Analgésicos', 'description' => 'Alivio del dolor']);
        $medicine = Medicine::create([
            'category_id' => $category->id,
            'name' => 'Paracetamol 500 mg x 10 tabletas',
            'price' => 10,
            'stock' => 5,
        ]);
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'date' => now(),
            'total' => 20,
            'igv' => 3.05,
        ]);
        DetailSale::create([
            'sale_id' => $sale->id,
            'medicine_id' => $medicine->id,
            'amount' => 2,
            'price_unit' => 10,
            'subtotal' => 20,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.index'));

        $response->assertOk()
            ->assertViewHas('ventasHoy', 20.0)
            ->assertViewHas('ventasMes', 20.0)
            ->assertViewHas('totalClientes', 1)
            ->assertViewHas('ventasDatos', function (array $values): bool {
                return $values[6] === 20.0 && count($values) === 7;
            })
            ->assertViewHas('categoriasLabels', fn ($labels): bool => $labels->all() === ['Analgésicos'])
            ->assertViewHas('categoriasDatos', fn ($values): bool => $values->all() === [20.0])
            ->assertViewHas('medicamentosLabels', fn ($labels): bool => $labels->all() === ['Paracetamol 500 mg x 10 tabletas'])
            ->assertViewHas('medicamentosDatos', fn ($values): bool => $values->all() === [2])
            ->assertSee('chart.js@4.4.9')
            ->assertSee('<canvas id="ventasChart"></canvas>', false)
            ->assertSee('<canvas id="categoriasChart"></canvas>', false)
            ->assertSee('<canvas id="medicamentosChart"></canvas>', false)
            ->assertSee('#e57389');
    }
}
