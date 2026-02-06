<?php

namespace App\Filament\Resources\LabaRugiEntryResource\Widgets;

use App\Models\LabaRugiEntry;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\OutletContext;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LabaRugiSummary extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $outletId = OutletContext::id();
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        if (! $outletId) {
            return [
                Stat::make('Pendapatan (Hari Ini)', 'Rp 0'),
                Stat::make('Pendapatan (Bulan Ini)', 'Rp 0'),
                Stat::make('Biaya (Bulan Ini)', 'Rp 0'),
                Stat::make('Laba Bersih (Bulan Ini)', 'Rp 0'),
            ];
        }

        $harianRow = Sale::where('outlet_id', $outletId)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->selectRaw('SUM(subtotal) as subtotal_sum')
            ->selectRaw('SUM(discount_total) as discount_sum')
            ->first();

        $cogsHarian = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.outlet_id', $outletId)
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$todayStart, $todayEnd])
            ->sum('sale_items.cogs_total');

        $netSalesHarian = max(0, (float) ($harianRow->subtotal_sum ?? 0) - (float) ($harianRow->discount_sum ?? 0));
        $pendapatanHarian = $netSalesHarian - (float) $cogsHarian;

        $pendapatan = LabaRugiEntry::where('outlet_id', $outletId)
            ->where('jenis', 'pendapatan')
            ->whereBetween('tanggal', [$start, $end])
            ->sum('nominal');

        $biaya = LabaRugiEntry::where('outlet_id', $outletId)
            ->where('jenis', 'biaya')
            ->whereBetween('tanggal', [$start, $end])
            ->sum('nominal');

        $labaBersih = $pendapatan - $biaya;

        return [
            Stat::make('Pendapatan (Hari Ini)', 'Rp '.number_format($pendapatanHarian, 0, ',', '.'))
                ->icon('heroicon-o-sparkles'),
            Stat::make('Pendapatan (Bulan Ini)', 'Rp '.number_format($pendapatan, 0, ',', '.'))
                ->icon('heroicon-o-arrow-up-circle'),
            Stat::make('Biaya (Bulan Ini)', 'Rp '.number_format($biaya, 0, ',', '.'))
                ->icon('heroicon-o-arrow-down-circle'),
            Stat::make('Laba Bersih (Bulan Ini)', 'Rp '.number_format($labaBersih, 0, ',', '.'))
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
