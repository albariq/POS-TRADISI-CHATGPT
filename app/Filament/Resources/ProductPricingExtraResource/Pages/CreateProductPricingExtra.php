<?php

namespace App\Filament\Resources\ProductPricingExtraResource\Pages;

use App\Filament\Resources\ProductPricingExtraResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductPricingExtra extends CreateRecord
{
    protected static string $resource = ProductPricingExtraResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->hydrateModalFromKg($data);
    }

    private function hydrateModalFromKg(array $data): array
    {
        $value = is_numeric($data['modal_1kg'] ?? null) ? (float) $data['modal_1kg'] : null;
        if ($value === null) {
            $data['modal_1gr'] = null;
            $data['modal_100'] = null;
            $data['modal_200'] = null;
            $data['modal_500'] = null;
            return $data;
        }

        $data['modal_1gr'] = round($value / 1000, 2);
        $data['modal_100'] = round($value * 0.1, 2);
        $data['modal_200'] = round($value * 0.2, 2);
        $data['modal_500'] = round($value * 0.5, 2);

        return $data;
    }
}
