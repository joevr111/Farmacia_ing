<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;

class DemoFarmaciaSeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, description: string}>
     */
    private const CATEGORIES = [
        ['name' => 'Analgésicos', 'description' => 'Medicamentos para el alivio del dolor'],
        ['name' => 'Antiinflamatorios', 'description' => 'Medicamentos para reducir inflamación'],
        ['name' => 'Antialérgicos', 'description' => 'Medicamentos para síntomas alérgicos'],
        ['name' => 'Gastrointestinales', 'description' => 'Medicamentos y productos digestivos'],
        ['name' => 'Antibióticos', 'description' => 'Medicamentos para infecciones bacterianas'],
        ['name' => 'Vitaminas y suplementos', 'description' => 'Vitaminas y complementos nutricionales'],
        ['name' => 'Dermatológicos', 'description' => 'Productos para el cuidado de la piel'],
        ['name' => 'Rehidratación oral', 'description' => 'Soluciones para hidratación oral'],
    ];

    /**
     * @var array<int, array{name: string, category: string, price: float, stock: int}>
     */
    private const MEDICINES = [
        ['name' => 'Paracetamol 500 mg x 10 tabletas', 'category' => 'Analgésicos', 'price' => 2.50, 'stock' => 28],
        ['name' => 'Ibuprofeno 400 mg x 10 tabletas', 'category' => 'Antiinflamatorios', 'price' => 3.50, 'stock' => 34],
        ['name' => 'Loratadina 10 mg x 10 tabletas', 'category' => 'Antialérgicos', 'price' => 3.20, 'stock' => 18],
        ['name' => 'Omeprazol 20 mg x 10 cápsulas', 'category' => 'Gastrointestinales', 'price' => 4.50, 'stock' => 12],
        ['name' => 'Amoxicilina 500 mg x 10 cápsulas', 'category' => 'Antibióticos', 'price' => 6.50, 'stock' => 42],
        ['name' => 'Azitromicina 500 mg x 3 tabletas', 'category' => 'Antibióticos', 'price' => 9.80, 'stock' => 14],
        ['name' => 'Suero oral 500 ml', 'category' => 'Rehidratación oral', 'price' => 5.50, 'stock' => 36],
        ['name' => 'Vitamina C 1 g x 10 tabletas efervescentes', 'category' => 'Vitaminas y suplementos', 'price' => 12.00, 'stock' => 22],
        ['name' => 'Clotrimazol crema 1% 20 g', 'category' => 'Dermatológicos', 'price' => 7.50, 'stock' => 9],
        ['name' => 'Hidrocortisona crema 1% 15 g', 'category' => 'Dermatológicos', 'price' => 8.90, 'stock' => 16],
    ];

    /**
     * @var array<int, array{name: string, dni: string, phone: string, age: int}>
     */
    private const CUSTOMERS = [
        ['name' => 'Valeria Rojas Cárdenas', 'dni' => '99000001', 'phone' => '900000101', 'age' => 28],
        ['name' => 'Diego Salazar Pineda', 'dni' => '99000002', 'phone' => '900000102', 'age' => 35],
        ['name' => 'Camila Torres Villanueva', 'dni' => '99000003', 'phone' => '900000103', 'age' => 22],
        ['name' => 'Andrés Medina Cabrera', 'dni' => '99000004', 'phone' => '900000104', 'age' => 47],
        ['name' => 'Lucía Herrera Benavides', 'dni' => '99000005', 'phone' => '900000105', 'age' => 31],
        ['name' => 'Mateo Castillo Zamora', 'dni' => '99000006', 'phone' => '900000106', 'age' => 64],
        ['name' => 'Daniela Vargas Paredes', 'dni' => '99000007', 'phone' => '900000107', 'age' => 19],
        ['name' => 'Joaquín Mendoza Salas', 'dni' => '99000008', 'phone' => '900000108', 'age' => 42],
        ['name' => 'Mariana Flores Rivas', 'dni' => '99000009', 'phone' => '900000109', 'age' => 53],
        ['name' => 'Sebastián Navarro León', 'dni' => '99000010', 'phone' => '900000110', 'age' => 26],
    ];

    /**
     * Each sale pairs one customer with two demo medicines and deterministic quantities.
     *
     * @var array<int, array<int, array{medicine: string, amount: int}>>
     */
    private const SALE_ITEMS = [
        [
            ['medicine' => 'Paracetamol 500 mg x 10 tabletas', 'amount' => 2],
            ['medicine' => 'Ibuprofeno 400 mg x 10 tabletas', 'amount' => 1],
        ],
        [
            ['medicine' => 'Ibuprofeno 400 mg x 10 tabletas', 'amount' => 3],
            ['medicine' => 'Loratadina 10 mg x 10 tabletas', 'amount' => 2],
        ],
        [
            ['medicine' => 'Loratadina 10 mg x 10 tabletas', 'amount' => 1],
            ['medicine' => 'Omeprazol 20 mg x 10 cápsulas', 'amount' => 2],
        ],
        [
            ['medicine' => 'Omeprazol 20 mg x 10 cápsulas', 'amount' => 3],
            ['medicine' => 'Amoxicilina 500 mg x 10 cápsulas', 'amount' => 1],
        ],
        [
            ['medicine' => 'Amoxicilina 500 mg x 10 cápsulas', 'amount' => 2],
            ['medicine' => 'Azitromicina 500 mg x 3 tabletas', 'amount' => 1],
        ],
        [
            ['medicine' => 'Azitromicina 500 mg x 3 tabletas', 'amount' => 2],
            ['medicine' => 'Suero oral 500 ml', 'amount' => 2],
        ],
        [
            ['medicine' => 'Suero oral 500 ml', 'amount' => 1],
            ['medicine' => 'Vitamina C 1 g x 10 tabletas efervescentes', 'amount' => 2],
        ],
        [
            ['medicine' => 'Vitamina C 1 g x 10 tabletas efervescentes', 'amount' => 1],
            ['medicine' => 'Clotrimazol crema 1% 20 g', 'amount' => 2],
        ],
        [
            ['medicine' => 'Clotrimazol crema 1% 20 g', 'amount' => 1],
            ['medicine' => 'Hidrocortisona crema 1% 15 g', 'amount' => 2],
        ],
        [
            ['medicine' => 'Hidrocortisona crema 1% 15 g', 'amount' => 1],
            ['medicine' => 'Paracetamol 500 mg x 10 tabletas', 'amount' => 3],
        ],
    ];

    public function run(): void
    {
        $this->guardDemoDatabase();

        DB::transaction(function (): void {
            $user = User::query()->orderBy('id')->first();

            if (! $user) {
                throw new RuntimeException('Primero crea la cuenta demo con DatabaseSeeder en la base aislada.');
            }

            $categories = [];

            foreach (self::CATEGORIES as $attributes) {
                $categories[$attributes['name']] = Category::firstOrCreate(
                    ['name' => $attributes['name']],
                    ['description' => $attributes['description']],
                );
            }

            $customers = [];

            foreach (self::CUSTOMERS as $attributes) {
                $customers[] = Customer::firstOrCreate(
                    ['dni' => $attributes['dni']],
                    [
                        'name' => $attributes['name'],
                        'phone' => $attributes['phone'],
                        'age' => $attributes['age'],
                    ],
                );
            }

            $medicines = [];

            foreach (self::MEDICINES as $attributes) {
                $medicines[$attributes['name']] = Medicine::firstOrCreate(
                    ['name' => $attributes['name']],
                    [
                        'category_id' => $categories[$attributes['category']]->id,
                        'price' => $attributes['price'],
                        'stock' => $attributes['stock'],
                    ],
                );
            }

            foreach (self::SALE_ITEMS as $index => $items) {
                $customer = $customers[$index];
                $date = now()->subDays(9 - $index)->setTime(9 + ($index % 9), 15 + $index);
                $total = 0;

                foreach ($items as $item) {
                    $total += (float) $medicines[$item['medicine']]->price * $item['amount'];
                }

                $sale = Sale::firstOrCreate(
                    [
                        'customer_id' => $customer->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'date' => $date,
                        'total' => round($total, 2),
                        'igv' => round($total * 18 / 118, 2),
                    ],
                );

                if (! $sale->wasRecentlyCreated) {
                    continue;
                }

                foreach ($items as $item) {
                    $medicine = Medicine::query()
                        ->lockForUpdate()
                        ->findOrFail($medicines[$item['medicine']]->id);

                    if ($medicine->stock < $item['amount']) {
                        throw new RuntimeException("Stock insuficiente para {$medicine->name} en los datos demo.");
                    }

                    $price = (float) $medicine->price;
                    $subtotal = round($price * $item['amount'], 2);

                    $sale->details()->create([
                        'medicine_id' => $medicine->id,
                        'amount' => $item['amount'],
                        'price_unit' => $price,
                        'subtotal' => $subtotal,
                    ]);

                    $medicine->decrement('stock', $item['amount']);
                }
            }
        });
    }

    private function guardDemoDatabase(): void
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $isTestingDatabase = app()->environment('testing')
            && $connection === 'sqlite'
            && $database === ':memory:';
        $isDedicatedDemoDatabase = app()->environment('demo')
            && $connection === 'sqlite'
            && $this->normalizePath((string) $database) === $this->normalizePath(database_path('farmacia_demo.sqlite'));

        if (! $isTestingDatabase && ! $isDedicatedDemoDatabase) {
            throw new LogicException('DemoFarmaciaSeeder solo se permite en SQLite de pruebas o database/farmacia_demo.sqlite con APP_ENV=demo.');
        }
    }

    private function normalizePath(string $path): string
    {
        return mb_strtolower(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($path)));
    }
}
