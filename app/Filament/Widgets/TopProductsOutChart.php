<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use App\Support\OutletContext;
use Filament\Widgets\ChartWidget;

class TopProductsOutChart extends ChartWidget
{
    protected ?string $heading = 'Top 5 Stok Keluar (Bulan Berjalan)';
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $outletId = OutletContext::id();
        if (! $outletId) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        $start = now()->startOfMonth();
        $end = now()->endOfDay();

        $rows = StockMovement::query()
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->where('stock_movements.outlet_id', $outletId)
            ->where('stock_movements.type', 'out')
            ->whereBetween('stock_movements.created_at', [$start, $end])
            ->groupBy('stock_movements.product_id', 'products.name')
            ->selectRaw('products.name as product_name')
            ->selectRaw('SUM(ABS(stock_movements.qty_grams)) as qty_grams')
            ->orderByDesc('qty_grams')
            ->limit(5)
            ->get();

        return [
            'labels' => $rows->pluck('product_name')->all(),
            'datasets' => [
                [
                    'label' => 'Qty Keluar (g)',
                    'data' => $rows->pluck('qty_grams')->map(fn ($value) => (float) $value)->all(),
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
