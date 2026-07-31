<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Services\Catalog\ProductMatcher;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductMatcherTest extends TestCase
{
    use RefreshDatabase;

    protected Business $businessA;
    protected Business $businessB;
    protected ProductMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->businessA = Business::create(['name' => 'Biz A', 'slug' => 'biz-a', 'status' => 'active', 'currency' => 'Rs.']);
        $this->businessB = Business::create(['name' => 'Biz B', 'slug' => 'biz-b', 'status' => 'active', 'currency' => 'USD']);

        TenantContext::setTenant($this->businessA);

        $catA = Category::create(['name' => 'Food', 'slug' => 'food', 'is_active' => true]);
        Product::create(['category_id' => $catA->id, 'name' => 'Zinger Burger', 'price' => 550.00, 'stock' => 10, 'is_active' => true]);
        Product::create(['category_id' => $catA->id, 'name' => 'Beef Burger', 'price' => 600.00, 'stock' => 5, 'is_active' => true]);
        Product::create(['category_id' => $catA->id, 'name' => 'Chicken Burger', 'price' => 500.00, 'stock' => 5, 'is_active' => true]);
        Product::create(['category_id' => $catA->id, 'name' => 'Coke 500ml', 'price' => 100.00, 'stock' => 20, 'is_active' => true]);

        TenantContext::setTenant($this->businessB);
        $catB = Category::create(['name' => 'Food B', 'slug' => 'food-b', 'is_active' => true]);
        Product::create(['category_id' => $catB->id, 'name' => 'Zinger Burger', 'price' => 999.00, 'stock' => 50, 'is_active' => true]);

        $this->matcher = new ProductMatcher();
    }

    public function test_exact_product_match()
    {
        TenantContext::setTenant($this->businessA);

        $result = $this->matcher->match('zinger burger');

        $this->assertEquals('single', $result['status']);
        $this->assertEquals('Zinger Burger', $result['product']->name);
        $this->assertEquals(550.00, $result['product']->price);
    }

    public function test_multiple_product_matches_trigger_clarification()
    {
        TenantContext::setTenant($this->businessA);

        $result = $this->matcher->match('burger');

        $this->assertEquals('multiple', $result['status']);
        $this->assertCount(3, $result['matches']);
    }

    public function test_no_product_match()
    {
        TenantContext::setTenant($this->businessA);

        $result = $this->matcher->match('pizza');

        $this->assertEquals('none', $result['status']);
        $this->assertNull($result['product']);
    }

    public function test_tenant_isolation_cross_business_product_leakage_is_impossible()
    {
        TenantContext::setTenant($this->businessA);

        // Searching for zinger burger in Business A should yield Business A's product (550.00), not Business B's (999.00)
        $result = $this->matcher->match('zinger burger');

        $this->assertEquals($this->businessA->id, $result['product']->business_id);
        $this->assertEquals(550.00, $result['product']->price);
    }
}
