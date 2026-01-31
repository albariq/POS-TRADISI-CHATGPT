<?php

namespace App\Filament\Pages;

use App\Models\CashMovement;
use App\Models\Payment;
use App\Models\Shift;
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
    public array $recentCashSales = [];
    public array $shiftSnapshot = [];

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
            $this->recentCashSales = [];
            $this->shiftSnapshot = [];
            return;
        }

        $todayStart = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        $cashPaymentsQuery = Payment::query()
            ->where('outlet_id', $outletId)
            ->where('method', 'cash');

        $cashInQuery = CashMovement::query()
            ->where('outlet_id', $outletId)
            ->where('type', 'in');

        $cashOutQuery = CashMovement::query()
            ->where('outlet_id', $outletId)
            ->where('type', 'out');

        $sumPayments = fn (Carbon $from) => (float) $cashPaymentsQuery->clone()
            ->where('created_at', '>=', $from)
            ->sum('amount');
        $sumIn = fn (Carbon $from) => (float) $cashInQuery->clone()
            ->where('created_at', '>=', $from)
            ->sum('amount');
        $sumOut = fn (Carbon $from) => (float) $cashOutQuery->clone()
            ->where('created_at', '>=', $from)
            ->sum('amount');

        $todayIn = $sumPayments($todayStart) + $sumIn($todayStart);
        $todayOut = $sumOut($todayStart);
        $weekIn = $sumPayments($weekStart) + $sumIn($weekStart);
        $weekOut = $sumOut($weekStart);
        $monthIn = $sumPayments($monthStart) + $sumIn($monthStart);
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
            $payments = (float) $cashPaymentsQuery->clone()
                ->whereBetween('created_at', [$date, $next])
                ->sum('amount');
            $cashIn = (float) $cashInQuery->clone()
                ->whereBetween('created_at', [$date, $next])
                ->sum('amount');
            $cashOut = (float) $cashOutQuery->clone()
                ->whereBetween('created_at', [$date, $next])
                ->sum('amount');

            $days[] = [
                'label' => $date->format('d M'),
                'net' => $payments + $cashIn - $cashOut,
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

        $this->recentCashSales = Payment::with('sale')
            ->where('outlet_id', $outletId)
            ->where('method', 'cash')
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (Payment $payment) {
                return [
                    'amount' => (float) $payment->amount,
                    'paid_at' => $payment->paid_at ?? $payment->created_at,
                    'receipt' => $payment->sale?->receipt_number,
                ];
            })
            ->all();

        $shift = Shift::where('outlet_id', $outletId)
            ->where('status', 'open')
            ->latest()
            ->first();

        if ($shift) {
            $expectedCash = (float) $cashPaymentsQuery->clone()
                ->whereBetween('created_at', [$shift->opened_at, now()])
                ->sum('amount');
            $this->shiftSnapshot = [
                'opened_at' => $shift->opened_at,
                'opening_balance' => (float) $shift->opening_balance,
                'cash_in' => (float) $shift->cash_in,
                'cash_out' => (float) $shift->cash_out,
                'expected' => (float) ($shift->opening_balance + $expectedCash + $shift->cash_in - $shift->cash_out),
            ];
        } else {
            $this->shiftSnapshot = [];
        }
    }
}
