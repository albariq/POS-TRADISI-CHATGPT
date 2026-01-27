<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OutletSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_with_multiple_active_outlets_is_redirected_to_outlet_selection(): void
    {
        $role = Role::create(['name' => 'CASHIER']);

        $outletA = Outlet::create([
            'code' => 'A01',
            'name' => 'Outlet A',
            'tax_rate' => 11,
            'service_charge_rate' => 0,
            'rounding_unit' => 1,
            'is_active' => true,
        ]);

        $outletB = Outlet::create([
            'code' => 'B01',
            'name' => 'Outlet B',
            'tax_rate' => 11,
            'service_charge_rate' => 0,
            'rounding_unit' => 1,
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Multi Outlet Cashier',
            'email' => 'multi-cashier@test.com',
            'password' => Hash::make('password'),
        ]);

        $user->assignRole($role);
        $user->outlets()->attach([$outletA->id, $outletB->id]);

        $response = $this->actingAs($user)->get('/pos');

        $response->assertRedirect(route('outlets.select'));
    }

    public function test_admin_with_multiple_active_outlets_is_not_forced_to_select_outlet(): void
    {
        $role = Role::create(['name' => 'ADMIN']);

        $defaultOutlet = Outlet::create([
            'code' => 'D01',
            'name' => 'Default Outlet',
            'tax_rate' => 11,
            'service_charge_rate' => 0,
            'rounding_unit' => 1,
            'is_active' => true,
        ]);

        $otherOutlet = Outlet::create([
            'code' => 'O01',
            'name' => 'Other Outlet',
            'tax_rate' => 11,
            'service_charge_rate' => 0,
            'rounding_unit' => 1,
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Admin Multi Outlet',
            'email' => 'admin-multi@test.com',
            'password' => Hash::make('password'),
        ]);

        $user->assignRole($role);
        $user->outlets()->attach($defaultOutlet->id, ['is_default' => true]);
        $user->outlets()->attach($otherOutlet->id, ['is_default' => false]);

        $response = $this->actingAs($user)->get('/pos');

        $response->assertOk();
        $response->assertSessionHas('active_outlet_id', $defaultOutlet->id);
    }
}
