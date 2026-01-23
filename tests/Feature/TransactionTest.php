<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_paid_sale(): void
    {
        $role = Role::create(['name' => 'CASHIER']);
        $outlet = Outlet::create([
            'code' => 'TST',
            'name' => 'Test Outlet',
            'tax_rate' => 11,
            'service_charge_rate' => 0,
            'rounding_unit' => 1,
        ]);

        $user = User::create([
            'name' => 'Cashier',
            'email' => 'cashier@test.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole($role);
        $user->outlets()->attach($outlet->id, ['is_default' => true]);

        $product = Product::create([
            'outlet_id' => $outlet->id,
            'sku' => 'SKU-1',
            'name' => 'Test Product',
            'base_price' => 10000,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $service = app(SaleService::class);
        $sale = $service->checkout(
            $outlet,
            [[
                'product_id' => $product->id,
                'product_variant_id' => null,
                'name' => $product->name,
                'sku' => $product->sku,
                'qty' => 1,
                'unit_price' => 10000,
                'discount_amount' => 0,
            ]],
            [[
                'method' => 'cash',
                'amount' => 11100,
                'change_amount' => 0,
            ]]
        );

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'paid',
        ]);
    }
}
