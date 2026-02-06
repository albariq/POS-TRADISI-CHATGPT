<?php

namespace App\Filament\Pages\InventoryReport\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventorySummary extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    public array $summary = [];

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $summary = $this->summary;
        $rangeLabel = $this->buildRangeLabel($summary['from'] ?? null, $summary['to'] ?? null);
        $topOut = $this->formatTopOut($summary['top_out'] ?? []);

        return [
            Stat::make('Total Keluar', $this->formatGrams($summary['total_out'] ?? 0))
                ->description($rangeLabel),
            Stat::make('Net (Masuk - Keluar)', $this->formatGrams($summary['net'] ?? 0))
                ->description($rangeLabel),
            Stat::make('Top 3 Keluar', $topOut ?: '-')
                ->description($rangeLabel),
        ];
    }

    private function formatGrams(float | int $value): string
    {
        return number_format((float) $value, 0, ',', '.').' g';
    }

    private function buildRangeLabel($from, $to): ?string
    {
        if (! $from || ! $to) {
            return null;
        }

        return $from->format('d M Y').' s/d '.$to->format('d M Y');
    }

    private function formatTopOut(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }

        $parts = [];
        foreach ($rows as $row) {
            $parts[] = $row['name'].' ('.$this->formatGrams($row['total_out']).')';
        }

        return implode(', ', $parts);
    }
}
