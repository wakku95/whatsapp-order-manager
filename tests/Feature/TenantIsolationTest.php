<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_a_cannot_access_business_b_data(): void
    {
        // 1. Create Business A & User A
        $businessA = Business::create(['name' => 'Business A', 'slug' => 'business-a']);
        $userA = User::create([
            'name' => 'User A',
            'email' => 'usera@example.com',
            'password' => Hash::make('password'),
            'business_id' => $businessA->id,
            'role' => 'owner',
        ]);

        // 2. Create Business B & User B
        $businessB = Business::create(['name' => 'Business B', 'slug' => 'business-b']);
        $userB = User::create([
            'name' => 'User B',
            'email' => 'userb@example.com',
            'password' => Hash::make('password'),
            'business_id' => $businessB->id,
            'role' => 'owner',
        ]);

        // 3. Create Product for Business A
        TenantContext::setTenant($businessA);
        $productA = Product::create([
            'name' => 'Product A',
            'price' => 100.00,
            'stock' => 10,
        ]);

        // 4. Create Product for Business B
        TenantContext::setTenant($businessB);
        $productB = Product::create([
            'name' => 'Product B',
            'price' => 200.00,
            'stock' => 20,
        ]);

        $this->assertEquals($businessA->id, $productA->business_id);
        $this->assertEquals($businessB->id, $productB->business_id);

        // 5. Authenticate as User A and verify zero-trust isolation
        TenantContext::setTenant($businessA);

        $allProducts = Product::all();
        $this->assertCount(1, $allProducts);
        $this->assertEquals('Product A', $allProducts->first()->name);

        // Assert direct lookup of Product B returns NULL
        $fetchedProductB = Product::find($productB->id);
        $this->assertNull($fetchedProductB);
    }
}
