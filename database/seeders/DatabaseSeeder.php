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

        $outletA = Outlet::updateOrCreate(['code' => 'JKT01'], [
            'name' => 'Outlet Jakarta',
            'phone' => '021-555-0001',
            'address' => 'Jl. Sudirman No. 1, Jakarta',
            'tax_rate' => 11.00,
            'service_charge_rate' => 0,
            'rounding_unit' => 1,
        ]);

        $outletB = Outlet::updateOrCreate(['code' => 'BDG01'], [
            'name' => 'Outlet Bandung',
            'phone' => '022-555-0002',
            'address' => 'Jl. Asia Afrika No. 2, Bandung',
            'tax_rate' => 11.00,
            'service_charge_rate' => 0,
            'rounding_unit' => 1,
        ]);

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

        $owner->outlets()->sync([
            $outletA->id => ['is_default' => true],
            $outletB->id => ['is_default' => true],
        ]);
        $admin->outlets()->sync([
            $outletA->id => ['is_default' => true],
            $outletB->id => ['is_default' => true],
        ]);
        $manager->outlets()->sync([$outletA->id => ['is_default' => true]]);
        $cashier->outlets()->sync([$outletA->id => ['is_default' => true]]);

        $category = Category::updateOrCreate([
            'outlet_id' => $outletA->id,
            'slug' => 'biji-kopi',
        ], [
            'name' => 'Biji Kopi',
            'is_active' => true,
        ]);

        $tag = Tag::updateOrCreate([
            'outlet_id' => $outletA->id,
            'slug' => 'single-origin',
        ], [
            'name' => 'Single Origin',
        ]);

        $robustaGayo = Product::updateOrCreate([
            'outlet_id' => $outletA->id,
            'sku' => 'RG',
        ], [
            'outlet_id' => $outletA->id,
            'category_id' => $category->id,
            'name' => 'Robusta Gayo',
            'base_price' => 21000,
            'cost_price' => null,
            'has_variants' => true,
            'is_active' => true,
        ]);
        $robustaGayo->tags()->sync([$tag->id]);

        $robustaGayoPremium = Product::updateOrCreate([
            'outlet_id' => $outletA->id,
            'sku' => 'RGP',
        ], [
            'outlet_id' => $outletA->id,
            'category_id' => $category->id,
            'name' => 'Robusta Gayo Premium',
            'base_price' => 25000,
            'cost_price' => null,
            'has_variants' => true,
            'is_active' => true,
        ]);
        $robustaGayoPremium->tags()->sync([$tag->id]);

        $rg100 = ProductVariant::updateOrCreate([
            'product_id' => $robustaGayo->id,
            'sku' => 'RG-100',
        ], [
            'name' => '100 Gr',
            'price_override' => 21000,
            'cost_price' => 9380,
            'grams_per_unit' => 100,
        ]);

        $rg200 = ProductVariant::updateOrCreate([
            'product_id' => $robustaGayo->id,
            'sku' => 'RG-200',
        ], [
            'name' => '200 Gr',
            'price_override' => 37000,
            'cost_price' => 17679,
            'grams_per_unit' => 200,
        ]);

        $rg500 = ProductVariant::updateOrCreate([
            'product_id' => $robustaGayo->id,
            'sku' => 'RG-500',
        ], [
            'name' => '500 Gr',
            'price_override' => 88000,
            'cost_price' => 42940,
            'grams_per_unit' => 500,
        ]);

        $rg1000 = ProductVariant::updateOrCreate([
            'product_id' => $robustaGayo->id,
            'sku' => 'RG-1000',
        ], [
            'name' => '1 Kg',
            'price_override' => 167000,
            'cost_price' => 84530,
            'grams_per_unit' => 1000,
        ]);

        $rgp100 = ProductVariant::updateOrCreate([
            'product_id' => $robustaGayoPremium->id,
            'sku' => 'RGP-100',
        ], [
            'name' => '100 Gr',
            'price_override' => 25000,
            'cost_price' => 14980,
            'grams_per_unit' => 100,
        ]);

        $rgp200 = ProductVariant::updateOrCreate([
            'product_id' => $robustaGayoPremium->id,
            'sku' => 'RGP-200',
        ], [
            'name' => '200 Gr',
            'price_override' => 46000,
            'cost_price' => 28879,
            'grams_per_unit' => 200,
        ]);

        $rgp500 = ProductVariant::updateOrCreate([
            'product_id' => $robustaGayoPremium->id,
            'sku' => 'RGP-500',
        ], [
            'name' => '500 Gr',
            'price_override' => 109000,
            'cost_price' => 70940,
            'grams_per_unit' => 500,
        ]);

        $rgp1000 = ProductVariant::updateOrCreate([
            'product_id' => $robustaGayoPremium->id,
            'sku' => 'RGP-1000',
        ], [
            'name' => '1 Kg',
            'price_override' => 206000,
            'cost_price' => 140530,
            'grams_per_unit' => 1000,
        ]);

        InventoryStock::updateOrCreate([
            'outlet_id' => $outletA->id,
            'product_id' => $robustaGayo->id,
            'product_variant_id' => null,
        ], [
            'qty_grams' => 900,
            'min_qty_grams' => 0,
        ]);

        InventoryStock::updateOrCreate([
            'outlet_id' => $outletA->id,
            'product_id' => $robustaGayoPremium->id,
            'product_variant_id' => null,
        ], [
            'qty_grams' => 300,
            'min_qty_grams' => 0,
        ]);

        LoyaltyRule::updateOrCreate(['outlet_id' => $outletA->id], [
            'calculation_mode' => 'per_amount',
            'earn_rate_amount' => 10000,
            'earn_rate_points' => 1,
        ]);

        Coupon::updateOrCreate([
            'outlet_id' => $outletA->id,
            'code' => 'PROMO10',
        ], [
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
        ]);

        $customer = Customer::updateOrCreate([
            'outlet_id' => $outletA->id,
            'phone' => '08123456789',
        ], [
            'name' => 'Budi',
            'points_balance' => 10,
        ]);

        $shift = Shift::updateOrCreate([
            'outlet_id' => $outletA->id,
            'opened_by' => $cashier->id,
            'status' => 'open',
        ], [
            'opened_at' => now()->subHours(2),
            'opening_balance' => 200000,
        ]);

        $sale = Sale::updateOrCreate([
            'outlet_id' => $outletA->id,
            'receipt_number' => $outletA->code.'-'.now()->format('Ymd').'-0001',
        ], [
            'status' => 'paid',
            'subtotal' => 21000,
            'discount_total' => 0,
            'tax_total' => 2310,
            'service_total' => 0,
            'grand_total' => 23310,
            'tax_rate' => 11,
            'service_charge_rate' => 0,
            'customer_id' => $customer->id,
            'cashier_id' => $cashier->id,
            'shift_id' => $shift->id,
            'paid_at' => now()->subHour(),
            'public_token' => Str::uuid()->toString(),
        ]);

        SaleItem::updateOrCreate([
            'sale_id' => $sale->id,
            'product_variant_id' => $rg100->id,
        ], [
            'product_id' => $robustaGayo->id,
            'name_snapshot' => 'Robusta Gayo - 100 Gr',
            'sku_snapshot' => 'RG-100',
            'qty' => 1,
            'grams_per_unit' => 100,
            'grams_total' => 100,
            'unit_price' => 21000,
            'discount_amount' => 0,
            'tax_amount' => 2310,
            'line_total' => 21000,
        ]);

        Payment::updateOrCreate([
            'sale_id' => $sale->id,
            'method' => 'cash',
        ], [
            'outlet_id' => $outletA->id,
            'amount' => 23310,
            'change_amount' => 0,
            'paid_at' => now()->subHour(),
        ]);
    }
}
