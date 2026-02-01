<?php

namespace App\Filament\Resources\LabaRugiEntryResource\Widgets;

use App\Models\LabaRugiEntry;
use App\Models\Sale;
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

        $pendapatanHarian = Sale::where('outlet_id', $outletId)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->sum('grand_total');

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
