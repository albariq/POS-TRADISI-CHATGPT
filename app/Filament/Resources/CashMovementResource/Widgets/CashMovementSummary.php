<?php

namespace App\Filament\Resources\CashMovementResource\Widgets;

use App\Models\CashMovement;
use App\Support\OutletContext;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashMovementSummary extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $outletId = OutletContext::id();
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        if (! $outletId) {
            return [
                Stat::make('Kas Masuk (Bulan Ini)', $this->formatRp(0)),
                Stat::make('Kas Keluar (Bulan Ini)', $this->formatRp(0)),
                Stat::make('Net (Bulan Ini)', $this->formatRp(0)),
                Stat::make('Total Keuangan', $this->formatRp(0)),
            ];
        }

        $baseQuery = CashMovement::query()->where('outlet_id', $outletId);

        $monthIn = (float) (clone $baseQuery)
            ->where('type', 'in')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
        $monthOut = (float) (clone $baseQuery)
            ->where('type', 'out')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $totalIn = (float) (clone $baseQuery)
            ->where('type', 'in')
            ->sum('amount');
        $totalOut = (float) (clone $baseQuery)
            ->where('type', 'out')
            ->sum('amount');

        return [
            Stat::make('Kas Masuk (Bulan Ini)', $this->formatRp($monthIn))
                ->icon('heroicon-o-arrow-up-circle'),
            Stat::make('Kas Keluar (Bulan Ini)', $this->formatRp($monthOut))
                ->icon('heroicon-o-arrow-down-circle'),
            Stat::make('Net (Bulan Ini)', $this->formatRp($monthIn - $monthOut))
                ->icon('heroicon-o-scale'),
            Stat::make('Total Keuangan', $this->formatRp($totalIn - $totalOut))
                ->icon('heroicon-o-banknotes'),
        ];
    }

    private function formatRp(float | int $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }
}
