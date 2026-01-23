<?php

namespace App\Exports;

use App\Models\InventoryStock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventoryReportExport implements FromCollection, WithHeadings
{
    public function __construct(protected int $outletId)
    {
    }

    public function collection()
    {
        return InventoryStock::where('outlet_id', $this->outletId)
            ->with('product', 'variant')
            ->get()
            ->map(function (InventoryStock $stock) {
                return [
                    'product' => $stock->product?->name,
                    'variant' => $stock->variant?->name,
                    'sku' => $stock->variant?->sku ?? $stock->product?->sku,
                    'qty_grams' => $stock->qty_grams,
                    'min_qty_grams' => $stock->min_qty_grams,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Product',
            'Variant',
            'SKU',
            'Qty (g)',
            'Min (g)',
        ];
    }
}
