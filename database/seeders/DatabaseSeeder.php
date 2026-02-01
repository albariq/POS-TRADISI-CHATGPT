<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = Role::firstOrCreate(['name' => 'OWNER']);
        $adminRole = Role::firstOrCreate(['name' => 'ADMIN']);
        $managerRole = Role::firstOrCreate(['name' => 'MANAGER']);
        $cashierRole = Role::firstOrCreate(['name' => 'CASHIER']);

        $owner = User::updateOrCreate(['email' => 'owner@demo.test'], [
            'name' => 'Owner',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'is_active' => true,
        ]);
        $owner->assignRole($ownerRole);

        $admin = User::updateOrCreate(['email' => 'admin@demo.test'], [
            'name' => 'Admin',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'is_active' => true,
        ]);
        $admin->assignRole($adminRole);

        $manager = User::updateOrCreate(['email' => 'manager@demo.test'], [
            'name' => 'Manager',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'is_active' => true,
        ]);
        $manager->assignRole($managerRole);

        $cashier = User::updateOrCreate(['email' => 'cashier@demo.test'], [
            'name' => 'Cashier',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'is_active' => true,
        ]);
        $cashier->assignRole($cashierRole);

        $outlet = Outlet::updateOrCreate(['code' => 'JKT01'], [
            'name' => 'Outlet Maskarebet',
            'phone' => '0823-7166-4523',
            'address' => 'Jl. H Ahmad Dahlan HY',
            'tax_rate' => 0,
            'service_charge_rate' => 0,
            'rounding_unit' => 1,
            'is_active' => true,
        ]);

        $owner->outlets()->sync([
            $outlet->id => ['is_default' => true],
        ]);
        $admin->outlets()->sync([
            $outlet->id => ['is_default' => true],
        ]);
        $manager->outlets()->sync([$outlet->id => ['is_default' => true]]);
        $cashier->outlets()->sync([$outlet->id => ['is_default' => true]]);

        $products = [
            ['RG-100', 'Robusta Gayo 100Gr', 100, 'Rp9.380', 'Rp21.000'],
            ['RG-200', 'Robusta Gayo 200Gr', 200, 'Rp17.679', 'Rp37.000'],
            ['RG-500', 'Robusta Gayo 500Gr', 500, 'Rp42.940', 'Rp88.000'],
            ['RG-1000', 'Robusta Gayo 1Kg', 1000, 'Rp84.530', 'Rp167.000'],
            ['RGP-100', 'Robusta Gayo Premium 100Gr', 100, 'Rp14.980', 'Rp25.000'],
            ['RGP-200', 'Robusta Gayo Premium 200Gr', 200, 'Rp28.879', 'Rp46.000'],
            ['RGP-500', 'Robusta Gayo Premium 500Gr', 500, 'Rp70.940', 'Rp109.000'],
            ['RGP-1000', 'Robusta Gayo Premium 1Kg', 1000, 'Rp140.530', 'Rp206.000'],
            ['RS-100', 'Robusta Sidikalang 100Gr', 100, 'Rp13.280', 'Rp21.000'],
            ['RS-200', 'Robusta Sidikalang 200Gr', 200, 'Rp25.479', 'Rp38.000'],
            ['RS-500', 'Robusta Sidikalang 500Gr', 500, 'Rp62.440', 'Rp90.000'],
            ['RS-1000', 'Robusta Sidikalang 1Kg', 1000, 'Rp123.530', 'Rp170.000'],
            ['RSP-100', 'Robusta Sidikalang Premium 100Gr', 100, 'Rp15.880', 'Rp29.000'],
            ['RSP-200', 'Robusta Sidikalang Premium 200Gr', 200, 'Rp30.679', 'Rp55.000'],
            ['RSP-500', 'Robusta Sidikalang Premium 500Gr', 500, 'Rp75.440', 'Rp129.000'],
            ['RSP-1000', 'Robusta Sidikalang Premium 1Kg', 1000, 'Rp149.530', 'Rp246.000'],
            ['RD-100', 'Robusta Dampit 100Gr', 100, 'Rp12.680', 'Rp20.000'],
            ['RD-200', 'Robusta Dampit 200Gr', 200, 'Rp24.279', 'Rp37.000'],
            ['RD-500', 'Robusta Dampit 500Gr', 500, 'Rp599.440', 'Rp85.000'],
            ['RD-1000', 'Robusta Dampit 1Kg', 1000, 'Rp117.530', 'Rp161.000'],
            ['RDP-100', 'Robusta Dampit Premium 100Gr', 100, 'Rp18.830', 'Rp29.000'],
            ['RDP-200', 'Robusta Dampit Premium 200Gr', 200, 'Rp36.579', 'Rp55.000'],
            ['RDP-500', 'Robusta Dampit Premium 500Gr', 500, 'Rp90.190', 'Rp129.000'],
            ['RDP-1000', 'Robusta Dampit Premium 1Kg', 1000, 'Rp179.030', 'Rp246.000'],
            ['RB-100', 'Robusta Bali 100Gr', 100, 'Rp13.080', 'Rp20.000'],
            ['RB-200', 'Robusta Bali 200Gr', 200, 'Rp25.079', 'Rp38.000'],
            ['RB-500', 'Robusta Bali 500Gr', 500, 'Rp61.440', 'Rp88.000'],
            ['RB-1000', 'Robusta Bali 1Kg', 1000, 'Rp121.530', 'Rp167.000'],
            ['RBP-100', 'Robusta Bali Premium 100Gr', 100, 'Rp14.880', 'Rp29.000'],
            ['RBP-200', 'Robusta Bali Premium 200Gr', 200, 'Rp28.679', 'Rp55.000'],
            ['RBP-500', 'Robusta Bali Premium 500Gr', 500, 'Rp70.440', 'Rp129.000'],
            ['RBP-1000', 'Robusta Bali Premium 1Kg', 1000, 'Rp139.530', 'Rp246.000'],
            ['RF-100', 'Robusta Flores 100Gr', 100, 'Rp13.080', 'Rp20.000'],
            ['RF-200', 'Robusta Flores 200Gr', 200, 'Rp25.079', 'Rp38.000'],
            ['RF-500', 'Robusta Flores 500Gr', 500, 'Rp61.440', 'Rp88.000'],
            ['RF-1000', 'Robusta Flores 1KG', 1000, 'Rp121.530', 'Rp167.000'],
            ['RFP-100', 'Robusta Flores Premium 100Gr', 100, 'Rp18.830', 'Rp29.000'],
            ['RFP-200', 'Robusta Flores Premium 200Gr', 200, 'Rp36.579', 'Rp55.000'],
            ['RFP-500', 'Robusta Flores Premium 500Gr', 500, 'Rp90.190', 'Rp129.000'],
            ['RFP-1000', 'Robusta Flores Premium 1Kg', 1000, 'Rp179.030', 'Rp246.000'],
            ['RT-100', 'Robusta Toraja 100Gr', 100, 'Rp9.780', 'Rp20.000'],
            ['RT-200', 'Robusta Toraja 200Gr', 200, 'Rp18.479', 'Rp38.000'],
            ['RT-500', 'Robusta Toraja 500Gr', 500, 'Rp44.940', 'Rp88.000'],
            ['RT-1000', 'Robusta Toraja 1Kg', 1000, 'Rp88.530', 'Rp167.000'],
            ['RTOP-100', 'Robusta Toraja Premium 100Gr', 100, 'Rp15.880', 'Rp29.000'],
            ['RTOP-200', 'Robusta Toraja Premium 200Gr', 200, 'Rp30.679', 'Rp55.000'],
            ['RTOP-500', 'Robusta Toraja Premium 500Gr', 500, 'Rp75.440', 'Rp129.000'],
            ['RTOP-1000', 'Robusta Toraja Premium 1Kg', 1000, 'Rp149.530', 'Rp246.000'],
            ['RL-100', 'Robusta Lampung 100Gr', 100, 'Rp0', 'Rp0'],
            ['RL-200', 'Robusta Lampung 200Gr', 200, 'Rp0', 'Rp0'],
            ['RL-500', 'Robusta Lampung 500Gr', 500, 'Rp0', 'Rp0'],
            ['RL-1000', 'Robusta Lampung 1Kg', 1000, 'Rp0', 'Rp0'],
            ['RLP-100', 'Robusta Lampung Premium 100Gr', 100, 'Rp0', 'Rp0'],
            ['RLP-200', 'Robusta Lampung Premium 200Gr', 200, 'Rp0', 'Rp0'],
            ['RLP-500', 'Robusta Lampung Premium 500Gr', 500, 'Rp0', 'Rp0'],
            ['RLP-1000', 'Robusta Lampung Premium 1Kg', 1000, 'Rp0', 'Rp0'],
            ['RSE-100', 'Robusta Semendo 100Gr', 100, 'Rp13.680', 'Rp20.000'],
            ['RSE-200', 'Robusta Semendo 200Gr', 200, 'Rp26.279', 'Rp37.000'],
            ['RSE-500', 'Robusta Semendo 500Gr', 500, 'Rp64.440', 'Rp87.000'],
            ['RSE-1000', 'Robusta Semendo 1Kg', 1000, 'Rp127.530', 'Rp166.000'],
            ['RSES100', 'Robusta Semendo Espresso 100Gr', 100, 'Rp13.980', 'Rp22.000'],
            ['RSES200', 'Robusta Semendo Espresso 200Gr', 200, 'Rp26.879', 'Rp40.000'],
            ['RSES500', 'Robusta Semendo Espresso 500Gr', 500, 'Rp65.940', 'Rp95.000'],
            ['RSES1000', 'Robusta Semendo Espresso 1Kg', 1000, 'Rp130.530', 'Rp179.000'],
            ['RP-100', 'Robusta Pagaralam 100Gr', 100, 'Rp13.480', 'Rp20.000'],
            ['RP-200', 'Robusta Pagaralam 200Gr', 200, 'Rp25.879', 'Rp37.000'],
            ['RP-500', 'Robusta Pagaralam 500Gr', 500, 'Rp63.440', 'Rp87.000'],
            ['RP-1000', 'Robusta Pagaralam 1Kg', 1000, 'Rp125.530', 'Rp166.000'],
            ['RPP-100', 'Robusta Pangalengan Premium 100Gr', 100, 'Rp18.180', 'Rp29.000'],
            ['RPP-200', 'Robusta Pangalengan Premium 200Gr', 200, 'Rp35.279', 'Rp55.000'],
            ['RPP-500', 'Robusta Pangalengan Premium 500Gr', 500, 'Rp86.940', 'Rp129.000'],
            ['RPP-1000', 'Robusta Pangalengan Premium 1kg', 1000, 'Rp172.530', 'Rp246.000'],
            ['RTP-100', 'Robusta Temanggung Premium 100Gr', 100, 'Rp18.180', 'Rp29.000'],
            ['RTP-200', 'Robusta Temanggung Premium 200Gr', 200, 'Rp35.279', 'Rp55.000'],
            ['RTP-500', 'Robusta Temanggung Premium 500Gr', 500, 'Rp86.940', 'Rp129.000'],
            ['RTP-1000', 'Robusta Temanggung Premium 1Kg', 1000, 'Rp172.530', 'Rp246.000'],
            ['TBRB-100', 'Tradisi Bold Robusta Blend 100Gr', 100, 'Rp13.580', 'Rp21.000'],
            ['TBRB-200', 'Tradisi Bold Robusta Blend 200Gr', 200, 'Rp26.079', 'Rp39.000'],
            ['TBRB-500', 'Tradisi Bold Robusta Blend 500Gr', 500, 'Rp63.940', 'Rp92.000'],
            ['TBRB-1000', 'Tradisi Bold Robusta Blend 1Kg', 1000, 'Rp126.530', 'Rp174.000'],
            ['AG-100', 'Arabica Gayo 100Gr', 100, 'Rp29.480', 'Rp45.000'],
            ['AG-200', 'Arabica Gayo 200Gr', 200, 'Rp57.879', 'Rp87.000'],
            ['AG-500', 'Arabica Gayo 500Gr', 500, 'Rp143.440', 'Rp205.000'],
            ['AG-1000', 'Arabica Gayo 1Kg', 1000, 'Rp285.530', 'Rp391.000'],
            ['AP-100', 'Arabica Pagaralam 100Gr', 100, 'Rp29.180', 'Rp41.000'],
            ['AP-200', 'Arabica Pagaralam 200Gr', 200, 'Rp57.279', 'Rp77.000'],
            ['AP-500', 'Arabica Pagaralam 500Gr', 500, 'Rp141.940', 'Rp182.000'],
            ['AP-1000', 'Arabica Pagaralam 1Kg', 1000, 'Rp282.530', 'Rp350.000'],
            ['AFB-100', 'Arabica Flores Bajawa 100Gr', 100, 'Rp15.380', 'Rp41.000'],
            ['AFB-200', 'Arabica Flores Bajawa 200Gr', 200, 'Rp29.679', 'Rp77.000'],
            ['AFB-500', 'Arabica Flores Bajawa 500Gr', 500, 'Rp72.940', 'Rp182.000'],
            ['AFB-1000', 'Arabica Flores Bajawa 1Kg', 1000, 'Rp144.530', 'Rp350.000'],
            ['KS', 'Kopi Seduh', null, 'Rp2.500', 'Rp5.000'],
        ];

        $categories = [
            'robusta' => 'Robusta',
            'arabica' => 'Arabica',
            'blend' => 'Blend',
            'kopi-seduh' => 'Kopi Seduh',
        ];

        $categoryIds = [];
        $hasCategoryOutlet = Schema::hasColumn('categories', 'outlet_id');
        foreach ($categories as $slug => $name) {
            $categoryMatch = [
                'slug' => $slug,
            ];
            if ($hasCategoryOutlet) {
                $categoryMatch['outlet_id'] = $outlet->id;
            }

            $categoryValues = [
                'name' => $name,
                'is_active' => true,
            ];
            if ($hasCategoryOutlet) {
                $categoryValues['outlet_id'] = $outlet->id;
            }

            $categoryIds[$slug] = Category::updateOrCreate($categoryMatch, $categoryValues)->id;
        }

        $hasProductOutlet = Schema::hasColumn('products', 'outlet_id');
        $hasProductOutlets = Schema::hasTable('product_outlets');

        $groups = [];
        foreach ($products as [$sku, $name, $grams, $hpp, $price]) {
            $baseSku = $this->extractBaseSku($sku);
            $baseName = $this->stripWeightSuffix($name);
            $groups[$baseSku]['name'] = $baseName;
            $groups[$baseSku]['category_id'] = $this->resolveCategoryId($baseSku, $categoryIds);
            $groups[$baseSku]['variants'][] = [
                'sku' => $sku,
                'name' => $this->formatVariantName($grams),
                'grams' => $grams,
                'price' => $this->toNumber($price),
                'cost' => $this->toNumber($hpp),
            ];
        }

        foreach ($groups as $baseSku => $group) {
            $productMatch = [
                'sku' => $baseSku,
            ];
            if ($hasProductOutlet) {
                $productMatch['outlet_id'] = $outlet->id;
            }

            $productValues = [
                'category_id' => $group['category_id'],
                'name' => $group['name'],
                'description' => null,
                'base_price' => 0,
                'cost_price' => null,
                'has_variants' => count($group['variants']) > 0,
                'is_active' => true,
            ];
            if ($hasProductOutlet) {
                $productValues['outlet_id'] = $outlet->id;
            }

            $product = Product::updateOrCreate($productMatch, $productValues);

            if ($hasProductOutlets) {
                $product->outlets()->syncWithoutDetaching([$outlet->id]);
            }

            foreach ($group['variants'] as $variant) {
                if (! $variant['grams']) {
                    continue;
                }

                $product->variants()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'sku' => $variant['sku'],
                    ],
                    [
                        'name' => $variant['name'],
                        'price_override' => $variant['price'],
                        'cost_price' => $variant['cost'],
                        'grams_per_unit' => $variant['grams'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function resolveCategoryId(string $sku, array $categoryIds): ?int
    {
        $skuUpper = strtoupper($sku);

        if ($skuUpper === 'KS') {
            return $categoryIds['kopi-seduh'] ?? null;
        }

        if (str_starts_with($skuUpper, 'A')) {
            return $categoryIds['arabica'] ?? null;
        }

        if (str_starts_with($skuUpper, 'TB')) {
            return $categoryIds['blend'] ?? null;
        }

        if (str_starts_with($skuUpper, 'R')) {
            return $categoryIds['robusta'] ?? null;
        }

        return null;
    }

    private function extractBaseSku(string $sku): string
    {
        if (str_contains($sku, '-')) {
            return explode('-', $sku)[0];
        }

        if (preg_match('/^([A-Z]+)[0-9]+$/i', $sku, $matches)) {
            return strtoupper($matches[1]);
        }

        return strtoupper($sku);
    }

    private function stripWeightSuffix(string $name): string
    {
        $cleaned = preg_replace('/\s*(100|200|500|1000)\s*Gr\b/i', '', $name);
        $cleaned = preg_replace('/\s*1\s*Kg\b/i', '', $cleaned);

        return trim($cleaned ?? $name);
    }

    private function formatVariantName(?int $grams): string
    {
        if (! $grams) {
            return 'Varian';
        }

        return $grams.' Gr';
    }

    private function toNumber(string $value): float
    {
        return (float) str_replace(['Rp', '.', ' '], '', $value);
    }
}
