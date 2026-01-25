<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Services\StockService;
use App\Support\OutletContext;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['outlet_id'] = OutletContext::id();
        $data['created_by'] = Auth::id();
        $data['purchased_at'] = $data['purchased_at'] ?? now();

        $total = 0;
        foreach (($data['items'] ?? []) as $item) {
            $qty = (float) ($item['qty_grams'] ?? 0);
            $unitCost = (float) ($item['unit_cost'] ?? 0);
            $total += ($qty / 1000) * $unitCost;
        }
        $data['total_cost'] = $total;

        return $data;
    }

    protected function afterCreate(): void
    {
        $total = 0;
        foreach ($this->record->items as $item) {
            app(StockService::class)->adjust(
                $item->product_id,
                null,
                (float) $item->qty_grams,
                'in',
                'purchase',
                $this->record::class,
                $this->record->id
            );
            $total += (float) $item->line_total;
        }

        $this->record->update(['total_cost' => $total]);
    }
}
