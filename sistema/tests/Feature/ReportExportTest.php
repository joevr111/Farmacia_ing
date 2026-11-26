<?php

namespace Tests\Feature;

use App\Exports\ReportsExport;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DetailSale;
use App\Models\Medicine;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_screen_and_excel_apply_date_seller_and_category_filters(): void
    {
        $this->travelTo('2026-11-26 12:00:00');
        $fixture = $this->createReportFixture();
        $filters = [
            'from' => '2026-11-01',
            'to' => '2026-11-30',
            'seller' => $fixture['seller']->id,
            'category' => $fixture['category']->id,
        ];

        $this->actingAs($fixture['seller'])
            ->get(route('reports.index', $filters))
            ->assertOk()
            ->assertSee('S/ 20.00')
            ->assertSee('Cliente Demo Uno')
            ->assertDontSee('Cliente Demo Dos')
            ->assertDontSee('Cliente Demo Tres')
            ->assertDontSee('S/ 35.00')
            ->assertDontSee('S/ 50.00');

        Excel::fake();
        $response = $this->actingAs($fixture['seller'])->get(route('reports.export.excel', $filters));

        $response->assertOk();
        Excel::assertDownloaded('reporte_20261101_20261130.xlsx', function (ReportsExport $export): bool {
            $rows = $export->array();

            return $rows[4] === ['Ventas realizadas', 1]
                && in_array(['2026-11-05', 1, 20.0], $rows, true)
                && in_array(['Paracetamol 500 mg x 10 tabletas', 2, 20.0], $rows, true)
                && ! in_array(['2026-11-06', 1, 35.0], $rows, true)
                && ! in_array(['Ibuprofeno 400 mg x 10 tabletas', 1, 35.0], $rows, true);
        });
    }

    public function test_pdf_download_uses_the_selected_filters_and_csv_contains_only_matching_sales(): void
    {
        $this->travelTo('2026-11-26 12:00:00');
        $fixture = $this->createReportFixture();
        $filters = [
            'from' => '2026-11-01',
            'to' => '2026-11-30',
            'seller' => $fixture['seller']->id,
            'category' => $fixture['category']->id,
        ];

        $this->actingAs($fixture['seller'])
            ->get(route('reports.export.pdf', $filters))
            ->assertDownload('reporte_20261101_20261130.pdf');

        $csvResponse = $this->actingAs($fixture['seller'])->get(route('reports.export', $filters));

        $csvResponse->assertDownload('ventas_20261101_20261130.csv');
        $csv = $csvResponse->streamedContent();

        $this->assertStringContainsString('Cliente Demo Uno', $csv);
        $this->assertStringNotContainsString('Cliente Demo Dos', $csv);
        $this->assertStringNotContainsString('Cliente Demo Tres', $csv);
        $this->assertStringNotContainsString('Cliente Demo Cuatro', $csv);
    }

    /**
     * @return array{seller: User, otherSeller: User, category: Category}
     */
    private function createReportFixture(): array
    {
        $role = Role::create(['name' => 'Administrador', 'description' => 'Rol de prueba']);
        $seller = User::factory()->create(['role_id' => $role->id, 'dni' => '12345678']);
        $otherSeller = User::factory()->create(['role_id' => $role->id, 'dni' => '12345679']);
        $category = Category::create(['name' => 'Analgésicos', 'description' => 'Alivio del dolor']);
        $otherCategory = Category::create(['name' => 'Antialérgicos', 'description' => 'Control de alergias']);
        $customers = [
            Customer::create(['name' => 'Cliente Demo Uno', 'dni' => '87654321']),
            Customer::create(['name' => 'Cliente Demo Dos', 'dni' => '87654322']),
            Customer::create(['name' => 'Cliente Demo Tres', 'dni' => '87654323']),
            Customer::create(['name' => 'Cliente Demo Cuatro', 'dni' => '87654324']),
        ];
        $medicines = [
            Medicine::create([
                'category_id' => $category->id,
                'name' => 'Paracetamol 500 mg x 10 tabletas',
                'price' => 10,
                'stock' => 10,
            ]),
            Medicine::create([
                'category_id' => $category->id,
                'name' => 'Ibuprofeno 400 mg x 10 tabletas',
                'price' => 35,
                'stock' => 10,
            ]),
            Medicine::create([
                'category_id' => $otherCategory->id,
                'name' => 'Loratadina 10 mg x 10 tabletas',
                'price' => 10,
                'stock' => 10,
            ]),
        ];

        $this->createSale($customers[0], $seller, $medicines[0], '2026-11-05 10:00:00', 2);
        $this->createSale($customers[1], $otherSeller, $medicines[1], '2026-11-06 10:00:00', 1);
        $this->createSale($customers[2], $seller, $medicines[2], '2026-11-07 10:00:00', 5);
        $this->createSale($customers[3], $seller, $medicines[0], '2026-10-25 10:00:00', 1);

        return ['seller' => $seller, 'otherSeller' => $otherSeller, 'category' => $category];
    }

    private function createSale(Customer $customer, User $seller, Medicine $medicine, string $date, int $amount): void
    {
        $total = (float) $medicine->price * $amount;
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $seller->id,
            'date' => $date,
            'total' => $total,
            'igv' => round($total * 18 / 118, 2),
        ]);

        DetailSale::create([
            'sale_id' => $sale->id,
            'medicine_id' => $medicine->id,
            'amount' => $amount,
            'price_unit' => $medicine->price,
            'subtotal' => $total,
        ]);
    }
}
