<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\LoyaltyRule;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shift;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\Payment;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = Role::firstOrCreate(['name' => 'OWNER']);
        $adminRole = Role::firstOrCreate(['name' => 'ADMIN']);
        $managerRole = Role::firstOrCreate(['name' => 'MANAGER']);
        $cashierRole = Role::firstOrCreate(['name' => 'CASHIER']);

        $outletA = Outlet::create([
            'code' => 'JKT01',
            'name' => 'Outlet Jakarta',
            'phone' => '021-555-0001',
            'address' => 'Jl. Sudirman No. 1, Jakarta',
            'tax_rate' => 11.00,
            'service_charge_rate' => 0,
            'rounding_unit' => 1,
        ]);

        $outletB = Outlet::create([
            'code' => 'BDG01',
            'name' => 'Outlet Bandung',
            'phone' => '022-555-0002',
            'address' => 'Jl. Asia Afrika No. 2, Bandung',
            'tax_rate' => 11.00,
            'service_charge_rate' => 0,
            'rounding_unit' => 1,
        ]);

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@demo.test',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'is_active' => true,
        ]);
        $owner->assignRole($ownerRole);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@demo.test',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'is_active' => true,
        ]);
        $admin->assignRole($adminRole);

        $manager = User::create([
            'name' => 'Manager',
            'email' => 'manager@demo.test',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'is_active' => true,
        ]);
        $manager->assignRole($managerRole);

        $cashier = User::create([
            'name' => 'Cashier',
            'email' => 'cashier@demo.test',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'is_active' => true,
        ]);
        $cashier->assignRole($cashierRole);

        $owner->outlets()->attach([$outletA->id, $outletB->id], ['is_default' => true]);
        $admin->outlets()->attach([$outletA->id, $outletB->id], ['is_default' => true]);
        $manager->outlets()->attach($outletA->id, ['is_default' => true]);
        $cashier->outlets()->attach($outletA->id, ['is_default' => true]);

        $category = Category::create([
            'outlet_id' => $outletA->id,
            'name' => 'Minuman',
            'slug' => 'minuman',
            'is_active' => true,
        ]);

        $tag = Tag::create([
            'outlet_id' => $outletA->id,
            'name' => 'Favorit',
            'slug' => 'favorit',
        ]);

        $product = Product::create([
            'outlet_id' => $outletA->id,
            'category_id' => $category->id,
            'sku' => 'PROD-001',
            'name' => 'Kopi Susu',
            'base_price' => 20000,
            'cost_price' => 10000,
            'is_active' => true,
        ]);
        $product->tags()->sync([$tag->id]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Large',
            'sku' => 'PROD-001-L',
            'price_override' => 25000,
            'grams_per_unit' => 300,
        ]);

        InventoryStock::create([
            'outlet_id' => $outletA->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'qty_grams' => 15000,
            'min_qty_grams' => 1500,
        ]);

        LoyaltyRule::create([
            'outlet_id' => $outletA->id,
            'earn_rate_amount' => 10000,
            'earn_rate_points' => 1,
        ]);

        Coupon::create([
            'outlet_id' => $outletA->id,
            'code' => 'PROMO10',
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'outlet_id' => $outletA->id,
            'name' => 'Budi',
            'phone' => '08123456789',
            'points_balance' => 10,
        ]);

        $shift = Shift::create([
            'outlet_id' => $outletA->id,
            'opened_by' => $cashier->id,
            'opened_at' => now()->subHours(2),
            'opening_balance' => 200000,
            'status' => 'open',
        ]);

        $sale = Sale::create([
            'outlet_id' => $outletA->id,
            'receipt_number' => $outletA->code.'-'.now()->format('Ymd').'-0001',
            'status' => 'paid',
            'subtotal' => 20000,
            'discount_total' => 0,
            'tax_total' => 2200,
            'service_total' => 0,
            'grand_total' => 22200,
            'tax_rate' => 11,
            'service_charge_rate' => 0,
            'customer_id' => $customer->id,
            'cashier_id' => $cashier->id,
            'shift_id' => $shift->id,
            'paid_at' => now()->subHour(),
            'public_token' => Str::uuid()->toString(),
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'name_snapshot' => 'Kopi Susu - Large',
            'sku_snapshot' => 'PROD-001-L',
            'qty' => 1,
            'grams_per_unit' => 300,
            'grams_total' => 300,
            'unit_price' => 20000,
            'discount_amount' => 0,
            'tax_amount' => 2200,
            'line_total' => 20000,
        ]);

        Payment::create([
            'sale_id' => $sale->id,
            'outlet_id' => $outletA->id,
            'method' => 'cash',
            'amount' => 22200,
            'change_amount' => 0,
            'paid_at' => now()->subHour(),
        ]);
    }
}
