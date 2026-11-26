<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportsExport implements FromArray, WithTitle
{
    public function __construct(
        private readonly string $from,
        private readonly string $to,
        private readonly array $report,
    ) {}

    public function array(): array
    {
        $summary = $this->report['summary'];
        $rows = [
            ['Reporte de ventas e inventario'],
            ['Período', $this->from, 'al', $this->to],
            [],
            ['Resumen'],
            ['Ventas realizadas', (int) $summary->count],
            ['Total vendido (S/)', (float) $summary->total],
            ['IGV (S/)', (float) $summary->igv],
            ['Operación gravada (S/)', (float) $summary->total - (float) $summary->igv],
            ['Ticket promedio (S/)', $summary->count ? (float) $summary->total / (int) $summary->count : 0],
            [],
            ['Ventas por día'],
            ['Fecha', 'Ventas', 'Total (S/)'],
        ];

        foreach ($this->report['byDay'] as $row) {
            $rows[] = [$row->day, (int) $row->sales, (float) $row->total];
        }

        $rows[] = [];
        $rows[] = ['Ventas por vendedor'];
        $rows[] = ['Vendedor', 'Ventas', 'Total (S/)'];

        foreach ($this->report['bySeller'] as $row) {
            $rows[] = [$row->seller, (int) $row->sales, (float) $row->total];
        }

        $rows[] = [];
        $rows[] = ['Medicamentos más vendidos (top 10)'];
        $rows[] = ['Medicamento', 'Unidades', 'Monto (S/)'];

        foreach ($this->report['topMedicines'] as $row) {
            $rows[] = [$row->name, (int) $row->units, (float) $row->revenue];
        }

        $rows[] = [];
        $rows[] = ['Ventas por categoría'];
        $rows[] = ['Categoría', 'Unidades', 'Monto (S/)'];

        foreach ($this->report['byCategory'] as $row) {
            $rows[] = [$row->category, (int) $row->units, (float) $row->revenue];
        }

        $rows[] = [];
        $rows[] = ['Inventario con stock bajo'];
        $rows[] = ['Medicamento', 'Categoría', 'Precio (S/)', 'Stock'];

        foreach ($this->report['lowStock'] as $medicine) {
            $rows[] = [
                $medicine->name,
                $medicine->category->name,
                (float) $medicine->price,
                (int) $medicine->stock,
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Reporte';
    }
}
