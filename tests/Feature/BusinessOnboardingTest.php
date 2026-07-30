<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Services\BusinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BusinessOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_business_and_complete_onboarding(): void
    {
        $user = User::create([
            'name' => 'Jane Owner',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'business_id' => null,
        ]);

        $service = new BusinessService();
        $business = $service->createBusinessForUser($user, [
            'name' => 'Jane Boutique',
            'phone' => '+1234567890',
            'currency' => 'USD',
        ]);

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'name' => 'Jane Boutique',
            'slug' => 'jane-boutique',
        ]);

        $this->assertEquals($business->id, $user->fresh()->business_id);
        $this->assertEquals('owner', $user->fresh()->role);
    }
}
