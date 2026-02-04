<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleService
{
    public function __construct(
        protected SalesCalculator $calculator,
        protected StockService $stockService,
        protected TelegramNotificationService $telegramNotificationService
    ) {
    }

    public function nextReceiptNumber(Outlet $outlet): string
    {
        $date = now()->format('Ymd');
        $prefix = $outlet->code.'-'.$date.'-';
        $last = Sale::where('outlet_id', $outlet->id)
            ->where('receipt_number', 'like', $prefix.'%')
            ->orderBy('receipt_number', 'desc')
            ->first();

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last->receipt_number);
            $seq = (int) end($parts) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function createDraft(Outlet $outlet, array $items, ?int $customerId = null, float $transactionDiscount = 0, ?Coupon $coupon = null): Sale
    {
        return $this->storeSale($outlet, $items, $customerId, $transactionDiscount, $coupon, 'draft', []);
    }

    public function checkout(Outlet $outlet, array $items, array $payments, ?int $customerId = null, float $transactionDiscount = 0, ?Coupon $coupon = null): Sale
    {
        return $this->storeSale($outlet, $items, $customerId, $transactionDiscount, $coupon, 'paid', $payments);
    }

    protected function storeSale(Outlet $outlet, array $items, ?int $customerId, float $transactionDiscount, ?Coupon $coupon, string $status, array $payments): Sale
    {
        $sale = DB::transaction(function () use ($outlet, $items, $customerId, $transactionDiscount, $coupon, $status, $payments) {
            $couponDiscount = $this->calculateCouponDiscount($coupon, $items, $transactionDiscount, $outlet);
            $totals = $this->calculator->calculate($items, $outlet, $transactionDiscount, $couponDiscount);

            $productIds = collect($items)->pluck('product_id')->unique()->values();
            $variantIds = collect($items)->pluck('product_variant_id')->filter()->unique()->values();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
            $variants = ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');

            $sale = Sale::create([
                'outlet_id' => $outlet->id,
                'receipt_number' => $this->nextReceiptNumber($outlet),
                'status' => $status,
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'service_total' => $totals['service_total'],
                'rounding_adjustment' => $totals['rounding_adjustment'],
                'grand_total' => $totals['grand_total'],
                'tax_rate' => $outlet->tax_rate,
                'service_charge_rate' => $outlet->service_charge_rate,
                'customer_id' => $customerId,
                'cashier_id' => Auth::id(),
                'shift_id' => $this->currentShiftId($outlet->id),
                'paid_at' => $status === 'paid' ? now() : null,
                'public_token' => Str::uuid()->toString(),
            ]);

            foreach ($items as $item) {
                $gramsPerUnit = (float) ($item['grams_per_unit'] ?? 0);
                $gramsTotal = $gramsPerUnit * $item['qty'];
                $variant = $item['product_variant_id'] ? $variants->get($item['product_variant_id']) : null;
                $product = $products->get($item['product_id']);
                $costPrice = (float) ($variant?->cost_price ?? $product?->cost_price ?? 0);
                $cogsTotal = $costPrice * $item['qty'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'name_snapshot' => $item['name'],
                    'sku_snapshot' => $item['sku'] ?? null,
                    'qty' => $item['qty'],
                    'grams_per_unit' => $gramsPerUnit,
                    'grams_total' => $gramsTotal,
                    'unit_price' => $item['unit_price'],
                    'cost_price_snapshot' => $costPrice,
                    'cogs_total' => $cogsTotal,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'tax_amount' => 0,
                    'line_total' => $item['unit_price'] * $item['qty'] - ($item['discount_amount'] ?? 0),
                    'note' => $item['note'] ?? null,
                ]);

                if ($status === 'paid') {
                    $this->stockService->adjust(
                        $item['product_id'],
                        null,
                        -1 * $gramsTotal,
                        'out',
                        'sale',
                        Sale::class,
                        $sale->id,
                        $outlet->id
                    );
                }
            }

            if ($status === 'paid') {
                foreach ($payments as $payment) {
                    Payment::create([
                        'sale_id' => $sale->id,
                        'outlet_id' => $outlet->id,
                        'method' => $payment['method'],
                        'amount' => $payment['amount'],
                        'change_amount' => $payment['change_amount'] ?? 0,
                        'reference' => $payment['reference'] ?? null,
                        'paid_at' => now(),
                    ]);
                }

                if ($coupon) {
                    CouponRedemption::create([
                        'coupon_id' => $coupon->id,
                        'sale_id' => $sale->id,
                        'customer_id' => $customerId,
                        'outlet_id' => $outlet->id,
                    ]);
                }

                $this->applyLoyalty($outlet->id, $customerId, $sale->grand_total);
            }

            AuditLogger::log('sale_'.$status, Sale::class, $sale->id, null, $sale->toArray(), $outlet->id);

            return $sale;
        });

        if ($sale->status === 'paid') {
            $this->telegramNotificationService->sendSalePaid($sale);
        }

        return $sale;
    }

    public function calculateCouponDiscount(?Coupon $coupon, array $items, float $transactionDiscount, Outlet $outlet): float
    {
        if (! $coupon || ! $coupon->is_active) {
            return 0;
        }

        $subtotal = 0;
        $itemDiscountTotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['unit_price'] * $item['qty'];
            $itemDiscountTotal += $item['discount_amount'] ?? 0;
        }

        $net = max(0, $subtotal - $itemDiscountTotal - $transactionDiscount);
        if ($coupon->min_spend && $net < $coupon->min_spend) {
            return 0;
        }

        $discount = $coupon->type === 'percent'
            ? round($net * ($coupon->value / 100), 2)
            : min($coupon->value, $net);

        if ($coupon->max_discount) {
            $discount = min($discount, $coupon->max_discount);
        }

        return $discount;
    }

    protected function applyLoyalty(int $outletId, ?int $customerId, float $grandTotal): void
    {
        if (! $customerId) {
            return;
        }

        $rule = \App\Models\LoyaltyRule::where('outlet_id', $outletId)->first();
        if (! $rule) {
            return;
        }

        $mode = $rule->calculation_mode ?? 'per_amount';
        $earnRateAmount = max(1, (int) $rule->earn_rate_amount);
        $earnRatePoints = max(0, (int) $rule->earn_rate_points);

        if ($earnRatePoints <= 0) {
            return;
        }

        $points = 0;

        if ($mode === 'per_transaction') {
            $points = $grandTotal >= $earnRateAmount ? $earnRatePoints : 0;
        } else {
            // Default: per nominal belanja (kelipatan earn_rate_amount).
            $points = intdiv((int) floor($grandTotal), $earnRateAmount) * $earnRatePoints;
        }

        if ($points > 0) {
            Customer::where('id', $customerId)->increment('points_balance', $points);
        }
    }

    protected function currentShiftId(int $outletId): ?int
    {
        return \App\Models\Shift::where('outlet_id', $outletId)->where('status', 'open')->latest()->value('id');
    }
}
