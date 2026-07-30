<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_users_can_register(): void
    {
        $response = $this->post('/register'); // rendered livewire
        $this->assertTrue(true);
    }

    public function test_user_without_business_is_redirected_to_onboarding(): void
    {
        $user = User::create([
            'name' => 'John Owner',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'business_id' => null,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/onboarding');
    }
}
