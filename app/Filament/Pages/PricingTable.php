<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\PricingDllSetting;
use App\Models\PricingPercentageSetting;
use App\Support\OutletContext;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use BackedEnum;

class PricingTable extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Tabel Harga';

    protected string $view = 'filament.pages.pricing-table';

    public array $rows = [];

    public function mount(): void
    {
        $this->loadData();
    }

    private function resolveOutletId(): ?int
    {
        $outletId = OutletContext::id();
        if ($outletId) {
            return $outletId;
        }

        $user = Auth::user();
        if (! $user) {
            return null;
        }

        $defaultOutlet = $user->defaultOutlet();
        if ($defaultOutlet) {
            return $defaultOutlet->id;
        }

        return $user->outlets()->pluck('outlets.id')->first();
    }

    private function loadData(): void
    {
        $outletId = $this->resolveOutletId();

        $sizes = [100, 200, 500, 1000];
        $dllSetting = $outletId ? PricingDllSetting::where('outlet_id', $outletId)->first() : null;

        $percentageSetting = $outletId ? PricingPercentageSetting::where('outlet_id', $outletId)->first() : null;

        $percentByGrams = [
            100 => $percentageSetting?->pct_100,
            200 => $percentageSetting?->pct_200,
            500 => $percentageSetting?->pct_500,
            1000 => $percentageSetting?->pct_1000,
        ];

        $products = Product::forOutlet($outletId)
            ->where('is_active', true)
            ->with(['pricingExtra.pricings'])
            ->orderBy('name')
            ->get();

        $rows = $products->map(function ($product) use ($sizes, $dllSetting, $percentByGrams) {
            $extra = $product->pricingExtra;
            $dll = $dllSetting;
            $pricingsByGrams = $extra ? $extra->pricings->keyBy('grams') : collect();

            $modalByGrams = [
                100 => $extra?->modal_100,
                200 => $extra?->modal_200,
                500 => $extra?->modal_500,
                1000 => $extra?->modal_1kg,
            ];

            $dllByGrams = [
                100 => $dll?->dll_100,
                200 => $dll?->dll_200,
                500 => $dll?->dll_500,
                1000 => $dll?->dll_1000,
            ];

            $sizeRows = [];
            foreach ($sizes as $grams) {
                $pricing = $pricingsByGrams->get($grams);
                $modal = $modalByGrams[$grams] ?? null;
                $dllCost = $dllByGrams[$grams] ?? null;
                $computedCost = ($modal !== null && $dllCost !== null)
                    ? (float) $modal + (float) $dllCost
                    : null;
                $pct = $percentByGrams[$grams] ?? null;
                $pctNormalized = $pct !== null ? ((float) $pct / 100) : null;
                $computedPrice = ($computedCost !== null && $pctNormalized !== null)
                    ? round($computedCost * (1 + $pctNormalized), 0)
                    : null;
                $computedMargin = ($computedPrice !== null && $computedCost !== null)
                    ? $computedPrice - $computedCost
                    : null;

                $sizeRows[$grams] = [
                    'price' => $computedPrice,
                    'cost' => $computedCost,
                    'margin' => $computedMargin,
                ];
            }

            return [
                'name' => $product->name,
                'sizes' => $sizeRows,
                'base' => [
                    'kg' => $extra?->modal_1kg,
                    'gr' => $extra?->modal_1gr,
                    'g100' => $extra?->modal_100,
                    'g200' => $extra?->modal_200,
                    'g500' => $extra?->modal_500,
                ],
                'dll' => [
                    'g100' => $dll?->dll_100,
                    'g200' => $dll?->dll_200,
                    'g500' => $dll?->dll_500,
                    'g1000' => $dll?->dll_1000,
                ],
            ];
        })->values()->all();

        $this->rows = $rows;
    }
}
