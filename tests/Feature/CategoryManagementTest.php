<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CategoryService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Test helpers
    // =========================================================================

    private function createBusinessWithOwner(): array
    {
        $business = Business::create([
            'name'     => 'Test Business',
            'slug'     => 'test-business',
            'phone'    => '+1234567890',
            'currency' => 'USD',
            'status'   => 'active',
        ]);

        $user = User::create([
            'name'        => 'Owner',
            'email'       => 'owner@example.com',
            'password'    => bcrypt('password'),
            'business_id' => $business->id,
            'role'        => 'owner',
            'is_active'   => true,
        ]);

        TenantContext::setTenant($business);

        return [$business, $user];
    }

    private function createSecondBusiness(): array
    {
        $business = Business::create([
            'name'     => 'Other Business',
            'slug'     => 'other-business',
            'phone'    => '+9876543210',
            'currency' => 'USD',
            'status'   => 'active',
        ]);

        $user = User::create([
            'name'        => 'Other Owner',
            'email'       => 'other@example.com',
            'password'    => bcrypt('password'),
            'business_id' => $business->id,
            'role'        => 'owner',
            'is_active'   => true,
        ]);

        return [$business, $user];
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    // =========================================================================
    // CategoryService tests
    // =========================================================================

    /** @test */
    public function it_creates_a_category_for_the_current_tenant(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        $service  = new CategoryService();
        $category = $service->create([
            'name'      => 'Electronics',
            'slug'      => 'electronics',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('categories', [
            'business_id' => $business->id,
            'name'        => 'Electronics',
            'slug'        => 'electronics',
            'is_active'   => true,
        ]);

        $this->assertEquals($business->id, $category->business_id);
    }

    /** @test */
    public function it_updates_a_category(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        $service  = new CategoryService();
        $category = $service->create(['name' => 'Electronics', 'slug' => 'electronics', 'is_active' => true]);

        $updated = $service->update($category, [
            'name'      => 'Consumer Electronics',
            'slug'      => 'consumer-electronics',
            'is_active' => true,
        ]);

        $this->assertEquals('Consumer Electronics', $updated->name);
        $this->assertEquals('consumer-electronics', $updated->slug);
    }

    /** @test */
    public function it_toggles_category_active_state(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        $service  = new CategoryService();
        $category = $service->create(['name' => 'Test', 'slug' => 'test', 'is_active' => true]);

        $this->assertTrue($category->is_active);

        $toggled = $service->toggleActive($category);
        $this->assertFalse($toggled->is_active);

        $toggled2 = $service->toggleActive($toggled);
        $this->assertTrue($toggled2->is_active);
    }

    /** @test */
    public function it_deletes_an_empty_category(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        $service  = new CategoryService();
        $category = $service->create(['name' => 'Empty', 'slug' => 'empty', 'is_active' => true]);

        $id = $category->id;
        $service->delete($category);

        $this->assertDatabaseMissing('categories', ['id' => $id]);
    }

    /** @test */
    public function it_prevents_deleting_a_category_that_has_products(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        $service  = new CategoryService();
        $category = $service->create(['name' => 'Has Products', 'slug' => 'has-products', 'is_active' => true]);

        // Attach a product to the category
        Product::create([
            'business_id' => $business->id,
            'category_id' => $category->id,
            'name'        => 'Test Product',
            'price'       => '10.00',
            'stock'       => 5,
            'is_active'   => true,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/contains products/i');

        $service->delete($category);
    }

    // =========================================================================
    // Tenant isolation tests
    // =========================================================================

    /** @test */
    public function categories_are_scoped_to_current_tenant(): void
    {
        [$businessA, $userA] = $this->createBusinessWithOwner();
        $service = new CategoryService();
        $catA    = $service->create(['name' => 'Business A Category', 'slug' => 'biz-a-cat', 'is_active' => true]);

        // Switch to business B
        [$businessB, $userB] = $this->createSecondBusiness();
        TenantContext::setTenant($businessB);

        // Business B cannot see Business A's category
        $this->assertNull(Category::find($catA->id));

        // Business B creates its own category
        $catB = $service->create(['name' => 'Business B Category', 'slug' => 'biz-b-cat', 'is_active' => true]);
        $this->assertEquals($businessB->id, $catB->business_id);

        // Switch back to Business A — should NOT see Business B's category
        TenantContext::setTenant($businessA);
        $this->assertNull(Category::find($catB->id));
    }

    // =========================================================================
    // Livewire component tests
    // =========================================================================

    /** @test */
    public function category_index_renders_for_authenticated_user(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Catalog\Categories\CategoryIndex::class)
            ->assertOk();
    }

    /** @test */
    public function category_can_be_created_via_livewire(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Catalog\Categories\CategoryIndex::class)
            ->call('openCreate')
            ->set('formName', 'Clothing')
            ->set('formSlug', 'clothing')
            ->set('formIsActive', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'business_id' => $business->id,
            'name'        => 'Clothing',
            'slug'        => 'clothing',
        ]);
    }

    /** @test */
    public function category_slug_must_be_unique_within_business(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        // Create a category first
        Category::create(['business_id' => $business->id, 'name' => 'Existing', 'slug' => 'existing', 'is_active' => true]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Catalog\Categories\CategoryIndex::class)
            ->call('openCreate')
            ->set('formName', 'Another Category')
            ->set('formSlug', 'existing')   // duplicate slug
            ->call('save')
            ->assertHasErrors(['formSlug']);
    }

    /** @test */
    public function same_slug_is_allowed_across_different_businesses(): void
    {
        [$businessA, $userA] = $this->createBusinessWithOwner();

        // Business A creates "electronics"
        Category::create([
            'business_id' => $businessA->id,
            'name'        => 'Electronics',
            'slug'        => 'electronics',
            'is_active'   => true,
        ]);

        // Business B can also use "electronics" slug
        [$businessB, $userB] = $this->createSecondBusiness();
        TenantContext::setTenant($businessB);

        Livewire::actingAs($userB)
            ->test(\App\Livewire\Catalog\Categories\CategoryIndex::class)
            ->call('openCreate')
            ->set('formName', 'Electronics')
            ->set('formSlug', 'electronics')  // same slug — different business, OK
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'business_id' => $businessB->id,
            'slug'        => 'electronics',
        ]);
    }

    /** @test */
    public function category_name_is_required(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Catalog\Categories\CategoryIndex::class)
            ->call('openCreate')
            ->set('formName', '')
            ->set('formSlug', 'test')
            ->call('save')
            ->assertHasErrors(['formName']);
    }

    /** @test */
    public function deleting_category_with_products_shows_error_message(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        $category = Category::create([
            'business_id' => $business->id,
            'name'        => 'Has Products',
            'slug'        => 'has-products',
            'is_active'   => true,
        ]);

        Product::create([
            'business_id' => $business->id,
            'category_id' => $category->id,
            'name'        => 'Test Product',
            'price'       => '10.00',
            'stock'       => 1,
            'is_active'   => true,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Catalog\Categories\CategoryIndex::class)
            ->call('confirmDelete', $category->id)
            ->call('delete')
            ->assertSet('errorMessage', fn ($msg) => str_contains(strtolower($msg), 'products'));

        // Category must still exist in DB
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
