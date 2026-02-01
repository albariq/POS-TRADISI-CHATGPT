<?php

namespace App\Filament\Resources\LabaRugiEntryResource\Pages;

use App\Filament\Resources\LabaRugiEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLabaRugiEntries extends ListRecords
{
    protected static string $resource = LabaRugiEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return LabaRugiEntryResource::getWidgets();
    }
}
