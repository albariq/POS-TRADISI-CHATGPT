<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Support\OutletContext;
use Filament\Widgets\ChartWidget;

class PeakHoursChart extends ChartWidget
{
    protected ?string $heading = 'Jam Ramai (7 Hari Terakhir)';

    protected function getData(): array
    {
        $outletId = OutletContext::id();
        if (! $outletId) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        $start = now()->subDays(6)->startOfDay();
        $end = now()->endOfDay();

        $rows = Sale::query()
            ->where('outlet_id', $outletId)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $counts = array_fill(0, 24, 0);
        foreach ($rows as $row) {
            $hour = (int) $row->hour;
            $counts[$hour] = (int) $row->total;
        }

        $labels = [];
        for ($i = 0; $i < 24; $i++) {
            $labels[] = str_pad((string) $i, 2, '0', STR_PAD_LEFT).':00';
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Transaksi',
                    'data' => $counts,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.35)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
