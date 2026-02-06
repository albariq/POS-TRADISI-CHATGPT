<?php

namespace App\Filament\Pages;

use App\Models\CashMovement;
use App\Support\OutletContext;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use UnitEnum;

class DashboardKas extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'KAS';

    protected static ?string $navigationLabel = 'Dashboard Kas';

    protected string $view = 'filament.pages.dashboard-kas';

    public array $summary = [];
    public array $series = [];
    public array $recentMovements = [];

    public function mount(): void
    {
        $this->loadData();
    }

    private function loadData(): void
    {
        $outletId = OutletContext::id();
        if (! $outletId) {
            $this->summary = [];
            $this->series = [];
            $this->recentMovements = [];
            return;
        }

        $todayStart = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        $cashInQuery = CashMovement::query()
            ->where('outlet_id', $outletId)
            ->where('type', 'in');

        $cashOutQuery = CashMovement::query()
            ->where('outlet_id', $outletId)
            ->where('type', 'out');

        $sumIn = fn (Carbon $from) => (float) $cashInQuery->clone()
            ->where('created_at', '>=', $from)
            ->sum('amount');
        $sumOut = fn (Carbon $from) => (float) $cashOutQuery->clone()
            ->where('created_at', '>=', $from)
            ->sum('amount');

        $todayIn = $sumIn($todayStart);
        $todayOut = $sumOut($todayStart);
        $weekIn = $sumIn($weekStart);
        $weekOut = $sumOut($weekStart);
        $monthIn = $sumIn($monthStart);
        $monthOut = $sumOut($monthStart);

        $this->summary = [
            'today_in' => $todayIn,
            'today_out' => $todayOut,
            'today_net' => $todayIn - $todayOut,
            'week_in' => $weekIn,
            'week_out' => $weekOut,
            'week_net' => $weekIn - $weekOut,
            'month_in' => $monthIn,
            'month_out' => $monthOut,
            'month_net' => $monthIn - $monthOut,
        ];

        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $next = $date->copy()->addDay();
            $cashIn = (float) $cashInQuery->clone()
                ->whereBetween('created_at', [$date, $next])
                ->sum('amount');
            $cashOut = (float) $cashOutQuery->clone()
                ->whereBetween('created_at', [$date, $next])
                ->sum('amount');

            $days[] = [
                'label' => $date->format('d M'),
                'net' => $cashIn - $cashOut,
            ];
        }
        $this->series = $days;

        $this->recentMovements = CashMovement::with('creator')
            ->where('outlet_id', $outletId)
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (CashMovement $movement) {
                return [
                    'type' => $movement->type,
                    'amount' => (float) $movement->amount,
                    'reason' => $movement->reason,
                    'created_at' => $movement->created_at,
                    'creator' => $movement->creator?->name,
                ];
            })
            ->all();

    }
}
