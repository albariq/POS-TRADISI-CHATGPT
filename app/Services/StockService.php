<?php

namespace App\Services;

use App\Models\InventoryStock;
use App\Models\StockMovement;
use App\Support\OutletContext;
use Illuminate\Support\Facades\Auth;

class StockService
{
    public function adjust(
        int $productId,
        ?int $variantId,
        float $qtyGramsDelta,
        string $type,
        ?string $reason = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $outletId = null
    ): InventoryStock
    {
        $outletId = $outletId ?? OutletContext::id();

        if (! $outletId) {
            throw new \RuntimeException('Active outlet is not set.');
        }

        $stock = InventoryStock::firstOrCreate([
            'outlet_id' => $outletId,
            'product_id' => $productId,
            'product_variant_id' => $variantId,
        ], [
            'qty_grams' => 0,
            'min_qty_grams' => 0,
        ]);

        $before = (float) $stock->qty_grams;
        $after = $before + $qtyGramsDelta;
        $stock->update(['qty_grams' => $after]);

        $legacyQtyDelta = (int) round($qtyGramsDelta);
        $legacyBefore = (int) round($before);
        $legacyAfter = (int) round($after);

        StockMovement::create([
            'outlet_id' => $outletId,
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'type' => $type,
            'qty' => $legacyQtyDelta,
            'before_qty' => $legacyBefore,
            'after_qty' => $legacyAfter,
            'qty_grams' => $qtyGramsDelta,
            'before_qty_grams' => $before,
            'after_qty_grams' => $after,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'created_by' => Auth::id(),
        ]);

        return $stock;
    }
}
