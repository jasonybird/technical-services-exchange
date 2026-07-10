<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_registration_assigns_provider_role(): void
    {
        Role::findOrCreate('provider');
        Role::findOrCreate('buyer');

        $this->post('/register', [
            'name' => 'Provider One',
            'email' => 'provider-one@example.com',
            'account_type' => 'provider',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/dashboard');

        $user = User::where('email', 'provider-one@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('provider'));
        $this->assertFalse($user->hasRole('buyer'));
    }

    public function test_hybrid_registration_assigns_provider_and_buyer_roles(): void
    {
        Role::findOrCreate('provider');
        Role::findOrCreate('buyer');

        $this->post('/register', [
            'name' => 'Hybrid One',
            'email' => 'hybrid-one@example.com',
            'account_type' => 'both',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/dashboard');

        $user = User::where('email', 'hybrid-one@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('provider'));
        $this->assertTrue($user->hasRole('buyer'));
    }
}
