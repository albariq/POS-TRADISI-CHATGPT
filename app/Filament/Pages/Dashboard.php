<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;
use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\PeakHoursChart;
use App\Filament\Widgets\TopProductsOutChart;
use App\Filament\Widgets\RecentSales;
use App\Filament\Widgets\RecentPurchases;
use App\Filament\Widgets\LowStock;
use App\Filament\Widgets\RecentStockMovements;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            AccountWidget::class,
            DashboardStats::class,
            PeakHoursChart::class,
            TopProductsOutChart::class,
            RecentSales::class,
            RecentPurchases::class,
            LowStock::class,
            RecentStockMovements::class,
        ];
    }

    protected function getWidgetsColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }
}
