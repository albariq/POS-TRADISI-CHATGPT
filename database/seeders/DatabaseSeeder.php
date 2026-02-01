<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
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
    }
}
