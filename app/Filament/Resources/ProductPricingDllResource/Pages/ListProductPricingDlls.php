<?php

namespace App\Filament\Resources\ProductPricingDllResource\Pages;

use App\Filament\Resources\ProductPricingDllResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductPricingDlls extends ListRecords
{
    protected static string $resource = ProductPricingDllResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
