<?php

namespace App\Filament\Resources\PricingSettingResource\Pages;

use App\Filament\Resources\PricingSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePricingSetting extends CreateRecord
{
    protected static string $resource = PricingSettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $outletId = PricingSettingResource::resolveOutletId();
        if (! $outletId) {
            abort(403);
        }

        $data['outlet_id'] = $outletId;

        return $data;
    }
}
