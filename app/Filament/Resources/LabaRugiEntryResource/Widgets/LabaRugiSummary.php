<?php

namespace App\Filament\Resources\LabaRugiEntryResource\Widgets;

use App\Models\LabaRugiEntry;
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

        if (! $outletId) {
            return [
                Stat::make('Pendapatan (Bulan Ini)', 'Rp 0'),
                Stat::make('Biaya (Bulan Ini)', 'Rp 0'),
                Stat::make('Laba Bersih (Bulan Ini)', 'Rp 0'),
            ];
        }

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
            Stat::make('Pendapatan (Bulan Ini)', 'Rp '.number_format($pendapatan, 0, ',', '.'))
                ->icon('heroicon-o-arrow-up-circle'),
            Stat::make('Biaya (Bulan Ini)', 'Rp '.number_format($biaya, 0, ',', '.'))
                ->icon('heroicon-o-arrow-down-circle'),
            Stat::make('Laba Bersih (Bulan Ini)', 'Rp '.number_format($labaBersih, 0, ',', '.'))
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
