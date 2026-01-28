<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PricingSetting;
use App\Support\OutletContext;

class PricingTableController extends Controller
{
    public function index()
    {
        $outletId = OutletContext::id();

        $sizes = [100, 200, 500, 1000];

        $defaults = [
            'packaging_costs' => config('pricing.packaging_costs'),
            'markups' => config('pricing.markups'),
        ];

        $settings = PricingSetting::where('outlet_id', $outletId)->get()->keyBy('grams');
        $packagingCosts = [];
        $markups = [];

        foreach ($sizes as $grams) {
            $setting = $settings->get($grams);
            $packagingCosts[$grams] = $setting ? (int) $setting->packaging_cost : $defaults['packaging_costs'][$grams];
            $markups[$grams] = $setting ? (float) $setting->markup : $defaults['markups'][$grams];
        }

        $products = Product::forOutlet($outletId)
            ->where('is_active', true)
            ->with('variants')
            ->orderBy('name')
            ->get();

        $rows = $products->map(function ($product) use ($sizes, $packagingCosts, $markups) {
            $variantsByGrams = $product->variants->keyBy('grams_per_unit');

            $sizeRows = [];
            foreach ($sizes as $grams) {
                $variant = $variantsByGrams->get($grams);
                $cost = $variant ? (float) $variant->cost_price : null;
                $price = $cost !== null ? round($cost * (1 + $markups[$grams]), 0) : null;
                $margin = ($price !== null && $cost !== null) ? $price - $cost : null;

                $sizeRows[$grams] = [
                    'price' => $price,
                    'cost' => $cost,
                    'margin' => $margin,
                ];
            }

            $baseKg = null;
            $baseGr = null;
            $base100 = null;
            $base200 = null;
            $base500 = null;

            $cost1kg = $sizeRows[1000]['cost'];
            if ($cost1kg !== null) {
                $baseKg = $cost1kg - $packagingCosts[1000];
                $baseGr = $baseKg / 1000;
                $base100 = $baseKg * 0.1;
                $base200 = $baseKg * 0.2;
                $base500 = $baseKg * 0.5;
            }

            return [
                'name' => $product->name,
                'sizes' => $sizeRows,
                'base' => [
                    'kg' => $baseKg,
                    'gr' => $baseGr,
                    'g100' => $base100,
                    'g200' => $base200,
                    'g500' => $base500,
                ],
            ];
        });

        return view('pricing.index', [
            'rows' => $rows,
            'packagingCosts' => $packagingCosts,
            'markups' => $markups,
        ]);
    }
}
