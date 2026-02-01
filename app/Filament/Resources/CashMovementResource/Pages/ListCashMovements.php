<?php

namespace App\Filament\Resources\CashMovementResource\Pages;

use App\Filament\Resources\CashMovementResource;
use App\Filament\Resources\CashMovementResource\Widgets\CashMovementSummary;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashMovements extends ListRecords
{
    protected static string $resource = CashMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CashMovementSummary::class,
        ];
    }
}
