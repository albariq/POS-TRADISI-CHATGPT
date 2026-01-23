<?php

namespace App\Services;

use App\Models\Outlet;

class SalesCalculator
{
    public function calculate(array $items, Outlet $outlet, float $transactionDiscount = 0, float $couponDiscount = 0): array
    {
        $subtotal = 0;
        $itemDiscountTotal = 0;

        foreach ($items as $item) {
            $lineSubtotal = $item['unit_price'] * $item['qty'];
            $lineDiscount = $item['discount_amount'] ?? 0;
            $subtotal += $lineSubtotal;
            $itemDiscountTotal += $lineDiscount;
        }

        $discountTotal = $itemDiscountTotal + $transactionDiscount + $couponDiscount;
        $netBeforeTax = max(0, $subtotal - $discountTotal);
        $taxTotal = round($netBeforeTax * ($outlet->tax_rate / 100), 2);
        $serviceTotal = round($netBeforeTax * ($outlet->service_charge_rate / 100), 2);
        $grandTotalRaw = $netBeforeTax + $taxTotal + $serviceTotal;

        $roundingUnit = max(1, (int) $outlet->rounding_unit);
        $roundedGrandTotal = round($grandTotalRaw / $roundingUnit) * $roundingUnit;
        $roundingAdjustment = round($roundedGrandTotal - $grandTotalRaw, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($discountTotal, 2),
            'tax_total' => round($taxTotal, 2),
            'service_total' => round($serviceTotal, 2),
            'rounding_adjustment' => $roundingAdjustment,
            'grand_total' => round($roundedGrandTotal, 2),
        ];
    }
}
