<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_cannot_access_products_module(): void
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

        $response = $this->actingAs($user)->get('/products');
        $response->assertStatus(403);
    }
}
