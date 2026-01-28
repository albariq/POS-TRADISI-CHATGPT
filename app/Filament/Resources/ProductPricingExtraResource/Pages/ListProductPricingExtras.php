<?php

namespace App\Filament\Resources\ProductPricingExtraResource\Pages;

use App\Filament\Resources\ProductPricingExtraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductPricingExtras extends ListRecords
{
    protected static string $resource = ProductPricingExtraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
