<?php

namespace App\Filament\Pages\SalesReport\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesSummary extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    public array $summary = [];

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $summary = $this->summary;
        $from = $summary['from'] ?? null;
        $to = $summary['to'] ?? null;
        $rangeLabel = $from && $to ? "{$from} s/d {$to}" : null;

        return [
            Stat::make('Net Sales', $this->formatRp($summary['net_sales'] ?? 0))
                ->description($rangeLabel),
            Stat::make('COGS', $this->formatRp($summary['cogs'] ?? 0))
                ->description($rangeLabel),
            Stat::make('Gross Profit', $this->formatRp($summary['gross_profit'] ?? 0))
                ->description($rangeLabel),
            Stat::make('Grand Total', $this->formatRp($summary['grand_total'] ?? 0))
                ->description($rangeLabel),
        ];
    }

    private function formatRp(float | int $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }
}
