<?php

namespace App\Filament\Widgets;

use App\Models\InventoryStock;
use App\Models\Purchase;
use App\Models\Sale;
use App\Support\OutletContext;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $outletId = OutletContext::id();
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        if (! $outletId) {
            return [
                Stat::make('Penjualan Hari Ini', 'Rp 0'),
                Stat::make('Transaksi Hari Ini', '0'),
                Stat::make('Pembelian Hari Ini', 'Rp 0'),
                Stat::make('Stok Menipis', '0'),
            ];
        }

        $salesTotal = Sale::where('outlet_id', $outletId)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->sum('grand_total');

        $salesCount = Sale::where('outlet_id', $outletId)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();

        $purchaseTotal = Purchase::where('outlet_id', $outletId)
            ->whereBetween('purchased_at', [$todayStart, $todayEnd])
            ->sum('total_cost');

        $lowStockCount = InventoryStock::where('outlet_id', $outletId)
            ->where('min_qty_grams', '>', 0)
            ->whereColumn('qty_grams', '<=', 'min_qty_grams')
            ->count();

        return [
            Stat::make('Penjualan Hari Ini', 'Rp '.number_format($salesTotal, 0, ',', '.'))
                ->icon('heroicon-o-banknotes'),
            Stat::make('Transaksi Hari Ini', (string) $salesCount)
                ->icon('heroicon-o-receipt-refund'),
            Stat::make('Pembelian Hari Ini', 'Rp '.number_format($purchaseTotal, 0, ',', '.'))
                ->icon('heroicon-o-arrow-down-circle'),
            Stat::make('Stok Menipis', (string) $lowStockCount)
                ->icon('heroicon-o-exclamation-triangle')
                ->description($lowStockCount > 0 ? 'Perlu restok' : 'Aman'),
        ];
    }
}
