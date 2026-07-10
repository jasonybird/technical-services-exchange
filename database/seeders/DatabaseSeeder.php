<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = ['admin', 'provider', 'buyer'];

        foreach ($roles as $role) {
            Role::findOrCreate($role);
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => Hash::make('password')]
        );
        $admin->syncRoles(['admin']);

        $provider = User::updateOrCreate(
            ['email' => 'provider@example.com'],
            ['name' => 'Provider User', 'password' => Hash::make('password')]
        );
        $provider->syncRoles(['provider']);

        $buyer = User::updateOrCreate(
            ['email' => 'buyer@example.com'],
            ['name' => 'Buyer User', 'password' => Hash::make('password')]
        );
        $buyer->syncRoles(['buyer']);

        $hybrid = User::updateOrCreate(
            ['email' => 'hybrid@example.com'],
            ['name' => 'Hybrid User', 'password' => Hash::make('password')]
        );
        $hybrid->syncRoles(['provider', 'buyer']);
    }
}
