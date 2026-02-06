<?php

namespace App\Filament\Widgets;

use App\Models\SaleItem;
use App\Support\OutletContext;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class TopProductsOutChart extends ChartWidget
{
    protected static ?string $heading = 'Top 5 Barang Keluar (30 Hari Terakhir)';

    protected function getData(): array
    {
        $outletId = OutletContext::id();
        if (! $outletId) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        $start = now()->subDays(29)->startOfDay();
        $end = now()->endOfDay();

        $rows = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.outlet_id', $outletId)
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$start, $end])
            ->groupBy('sale_items.product_id', 'products.name')
            ->selectRaw('products.name as product_name')
            ->selectRaw('SUM(sale_items.qty) as qty')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        return [
            'labels' => $rows->pluck('product_name')->all(),
            'datasets' => [
                [
                    'label' => 'Qty Terjual',
                    'data' => $rows->pluck('qty')->map(fn ($value) => (float) $value)->all(),
                    'backgroundColor' => '#6366F1',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
