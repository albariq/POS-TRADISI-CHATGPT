<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\Outlet;
use App\Models\Product;
use App\Services\StockService;
use App\Support\OutletContext;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $allowedOutlets = $user
            ? $user->outlets()->where('outlets.is_active', true)->pluck('outlets.id')->all()
            : Outlet::where('is_active', true)->pluck('id')->all();

        $requestedOutletId = (int) ($data['outlet_id'] ?? 0);
        $activeOutletId = (int) (OutletContext::id() ?? 0);

        $outletId = null;
        if ($requestedOutletId && in_array($requestedOutletId, $allowedOutlets, true)) {
            $outletId = $requestedOutletId;
        } elseif ($activeOutletId && in_array($activeOutletId, $allowedOutlets, true)) {
            $outletId = $activeOutletId;
        } else {
            $outletId = $allowedOutlets[0] ?? null;
        }

        if (! $outletId) {
            throw ValidationException::withMessages([
                'outlet_id' => 'Cabang tujuan tidak valid.',
            ]);
        }

        $data['outlet_id'] = $outletId;
        $data['created_by'] = Auth::id();
        $data['purchased_at'] = $data['purchased_at'] ?? now();

        $productIds = collect($data['items'] ?? [])
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (! empty($productIds)) {
            $validProductCount = Product::where('outlet_id', $outletId)
                ->whereIn('id', $productIds)
                ->count();

            if ($validProductCount !== count($productIds)) {
                throw ValidationException::withMessages([
                    'items' => 'Semua produk harus berasal dari cabang yang dipilih.',
                ]);
            }
        }

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
                $this->record->id,
                $this->record->outlet_id
            );
            $total += (float) $item->line_total;
        }

        $this->record->update(['total_cost' => $total]);
    }
}
