<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Test helpers
    // =========================================================================

    private function createBusinessWithOwner(string $suffix = ''): array
    {
        $slug = 'test-business' . ($suffix ? '-' . $suffix : '');

        $business = Business::create([
            'name'     => 'Test Business ' . strtoupper($suffix),
            'slug'     => $slug,
            'phone'    => '+1234567890',
            'currency' => 'USD',
            'status'   => 'active',
        ]);

        $user = User::create([
            'name'        => 'Owner ' . strtoupper($suffix),
            'email'       => 'owner' . $suffix . '@example.com',
            'password'    => bcrypt('password'),
            'business_id' => $business->id,
            'role'        => 'owner',
            'is_active'   => true,
        ]);

        TenantContext::setTenant($business);

        return [$business, $user];
    }

    private function makeCategory(int $businessId, string $slug = 'test-cat'): Category
    {
        return Category::create([
            'business_id' => $businessId,
            'name'        => ucwords(str_replace('-', ' ', $slug)),
            'slug'        => $slug,
            'is_active'   => true,
        ]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    // =========================================================================
    // ProductService — create / update
    // =========================================================================

    /** @test */
    public function it_creates_a_product_for_the_current_tenant(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        $service = new ProductService();
        $product = $service->create([
            'name'      => 'Wireless Headphones',
            'price'     => '99.99',
            'stock'     => 50,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('products', [
            'business_id' => $business->id,
            'name'        => 'Wireless Headphones',
            'price'       => '99.99',
            'stock'       => 50,
        ]);
    }

    /** @test */
    public function it_assigns_category_to_product(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        $category = $this->makeCategory($business->id);
        $service  = new ProductService();

        $product = $service->create([
            'name'        => 'Test Product',
            'category_id' => $category->id,
            'price'       => '10.00',
            'stock'       => 5,
            'is_active'   => true,
        ]);

        $this->assertEquals($category->id, $product->category_id);
    }

    /** @test */
    public function it_updates_a_product(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        $service = new ProductService();
        $product = $service->create(['name' => 'Old Name', 'price' => '10.00', 'stock' => 5, 'is_active' => true]);
        $updated = $service->update($product, ['name' => 'New Name', 'price' => '20.00', 'stock' => 10, 'is_active' => true]);

        $this->assertEquals('New Name', $updated->name);
        $this->assertEquals('20.00', $updated->price);
    }

    // =========================================================================
    // Safe-delete rule
    // =========================================================================

    /** @test */
    public function it_physically_deletes_a_product_with_no_order_history(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        $service = new ProductService();
        $product = $service->create(['name' => 'No Orders', 'price' => '5.00', 'stock' => 1, 'is_active' => true]);
        $id      = $product->id;

        $deleted = $service->delete($product);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('products', ['id' => $id]);
    }

    /** @test */
    public function it_deactivates_instead_of_deleting_when_product_has_order_items(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        $service = new ProductService();
        $product = $service->create(['name' => 'Has Orders', 'price' => '15.00', 'stock' => 10, 'is_active' => true]);

        // Simulate an order item referencing this product
        // OrderItem requires an order, so we create a minimal order/item directly.
        // We'll use DB insert to avoid needing the full order pipeline.
        \Illuminate\Support\Facades\DB::table('customers')->insert([
            'business_id' => $business->id,
            'name'        => 'Test Customer',
            'phone'       => '+123',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $customerId = \Illuminate\Support\Facades\DB::getPdo()->lastInsertId();

        \Illuminate\Support\Facades\DB::table('orders')->insert([
            'business_id'  => $business->id,
            'customer_id'  => $customerId,
            'order_number' => 'ORD-001',
            'status'       => 'pending',
            'subtotal'     => '15.00',
            'total'        => '15.00',
            'cod_amount'   => '0.00',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
        $orderId = \Illuminate\Support\Facades\DB::getPdo()->lastInsertId();

        \Illuminate\Support\Facades\DB::table('order_items')->insert([
            'order_id'      => $orderId,
            'product_id'    => $product->id,
            'product_name'  => $product->name,
            'unit_price'    => '15.00',
            'quantity'      => 1,
            'subtotal'      => '15.00',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $physicallyDeleted = $service->delete($product);

        $this->assertFalse($physicallyDeleted, 'Product with order items should NOT be physically deleted');

        // Must still exist in DB — deactivated
        $this->assertDatabaseHas('products', [
            'id'        => $product->id,
            'is_active' => false,
        ]);
    }

    // =========================================================================
    // Price validation
    // =========================================================================

    /** @test */
    public function price_must_be_numeric_and_non_negative(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Catalog\Products\ProductIndex::class)
            ->call('openCreate')
            ->set('formName', 'Test')
            ->set('formPrice', '-1')
            ->set('formStock', 0)
            ->call('save')
            ->assertHasErrors(['formPrice']);
    }

    /** @test */
    public function price_may_not_exceed_two_decimal_places(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Catalog\Products\ProductIndex::class)
            ->call('openCreate')
            ->set('formName', 'Test')
            ->set('formPrice', '10.999')   // 3 decimal places — invalid
            ->set('formStock', 0)
            ->call('save')
            ->assertHasErrors(['formPrice']);
    }

    /** @test */
    public function price_with_two_decimal_places_is_valid(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Catalog\Products\ProductIndex::class)
            ->call('openCreate')
            ->set('formName', 'Valid Price Product')
            ->set('formPrice', '49.99')
            ->set('formStock', 1)
            ->set('formIsActive', true)
            ->call('save')
            ->assertHasNoErrors(['formPrice']);
    }

    // =========================================================================
    // Stock validation
    // =========================================================================

    /** @test */
    public function stock_cannot_be_negative(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Catalog\Products\ProductIndex::class)
            ->call('openCreate')
            ->set('formName', 'Test')
            ->set('formPrice', '10.00')
            ->set('formStock', -1)
            ->call('save')
            ->assertHasErrors(['formStock']);
    }

    // =========================================================================
    // SKU uniqueness
    // =========================================================================

    /** @test */
    public function sku_must_be_unique_within_business(): void
    {
        [$business, $user] = $this->createBusinessWithOwner();

        // Pre-existing product with SKU
        Product::create([
            'business_id' => $business->id,
            'name'        => 'Existing',
            'sku'         => 'DUPE-SKU',
            'price'       => '10.00',
            'stock'       => 1,
            'is_active'   => true,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Catalog\Products\ProductIndex::class)
            ->call('openCreate')
            ->set('formName', 'New Product')
            ->set('formSku', 'DUPE-SKU')   // duplicate
            ->set('formPrice', '10.00')
            ->set('formStock', 1)
            ->call('save')
            ->assertHasErrors(['formSku']);
    }

    /** @test */
    public function same_sku_is_allowed_across_different_businesses(): void
    {
        [$businessA, $userA] = $this->createBusinessWithOwner('a');

        Product::create([
            'business_id' => $businessA->id,
            'name'        => 'Product A',
            'sku'         => 'SHARED-SKU',
            'price'       => '10.00',
            'stock'       => 1,
            'is_active'   => true,
        ]);

        // Business B
        $businessB = Business::create([
            'name' => 'Business B', 'slug' => 'business-b',
            'phone' => '+111', 'currency' => 'USD', 'status' => 'active',
        ]);
        $userB = User::create([
            'name' => 'Owner B', 'email' => 'ownerb@example.com',
            'password' => bcrypt('password'),
            'business_id' => $businessB->id, 'role' => 'owner', 'is_active' => true,
        ]);
        TenantContext::setTenant($businessB);

        Livewire::actingAs($userB)
            ->test(\App\Livewire\Catalog\Products\ProductIndex::class)
            ->call('openCreate')
            ->set('formName', 'Product B')
            ->set('formSku', 'SHARED-SKU')  // same SKU — different business, OK
            ->set('formPrice', '20.00')
            ->set('formStock', 5)
            ->call('save')
            ->assertHasNoErrors(['formSku']);
    }

    // =========================================================================
    // Cross-tenant category protection
    // =========================================================================

    /** @test */
    public function product_cannot_be_assigned_to_category_from_another_business(): void
    {
        [$businessA, $userA] = $this->createBusinessWithOwner('a');

        // Category belongs to Business A
        $catA = $this->makeCategory($businessA->id, 'cat-a');

        // Business B tries to assign Business A's category to their product
        $businessB = Business::create([
            'name' => 'B', 'slug' => 'b', 'phone' => '+22', 'currency' => 'USD', 'status' => 'active',
        ]);
        $userB = User::create([
            'name' => 'B', 'email' => 'b@example.com',
            'password' => bcrypt('password'),
            'business_id' => $businessB->id, 'role' => 'owner', 'is_active' => true,
        ]);
        TenantContext::setTenant($businessB);

        Livewire::actingAs($userB)
            ->test(\App\Livewire\Catalog\Products\ProductIndex::class)
            ->call('openCreate')
            ->set('formName', 'Hijack Attempt')
            ->set('formPrice', '10.00')
            ->set('formStock', 1)
            ->set('formCategoryId', (string) $catA->id)  // A's category ID
            ->call('save')
            ->assertHasErrors(['formCategoryId']);
    }

    // =========================================================================
    // Image storage (using fake disk)
    // =========================================================================

    /** @test */
    public function it_stores_product_image_with_uuid_filename(): void
    {
        Storage::fake('public');

        [$business, $user] = $this->createBusinessWithOwner();

        $file    = \Illuminate\Http\UploadedFile::fake()->create('product.jpg', 100, 'image/jpeg');
        $service = new ProductService();

        $product = $service->create([
            'name'      => 'With Image',
            'price'     => '25.00',
            'stock'     => 10,
            'is_active' => true,
        ], $file);

        $this->assertNotNull($product->image_path);
        $this->assertStringStartsWith("products/{$business->id}/", $product->image_path);

        // UUID filename — should not contain "product.jpg"
        $this->assertStringNotContainsString('product.jpg', basename($product->image_path));

        Storage::disk('public')->assertExists($product->image_path);
    }

    /** @test */
    public function updating_product_image_deletes_old_file(): void
    {
        Storage::fake('public');

        [$business, $user] = $this->createBusinessWithOwner();

        $file1   = \Illuminate\Http\UploadedFile::fake()->create('first.jpg', 100, 'image/jpeg');
        $service = new ProductService();

        $product  = $service->create(['name' => 'P', 'price' => '10.00', 'stock' => 1, 'is_active' => true], $file1);
        $oldPath  = $product->image_path;

        Storage::disk('public')->assertExists($oldPath);

        $file2   = \Illuminate\Http\UploadedFile::fake()->create('second.jpg', 100, 'image/jpeg');
        $updated = $service->update($product, ['name' => 'P', 'price' => '10.00', 'stock' => 1, 'is_active' => true], $file2);

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($updated->image_path);
        $this->assertNotEquals($oldPath, $updated->image_path);
    }

    /** @test */
    public function removing_product_image_deletes_file_and_sets_path_to_null(): void
    {
        Storage::fake('public');

        [$business, $user] = $this->createBusinessWithOwner();

        $service = new ProductService();
        $file    = \Illuminate\Http\UploadedFile::fake()->create('img.jpg', 100, 'image/jpeg');
        $product = $service->create(['name' => 'P', 'price' => '10.00', 'stock' => 1, 'is_active' => true], $file);
        $oldPath = $product->image_path;

        $updated = $service->update(
            $product,
            ['name' => 'P', 'price' => '10.00', 'stock' => 1, 'is_active' => true],
            null,
            true  // removeImage = true
        );

        $this->assertNull($updated->image_path);
        Storage::disk('public')->assertMissing($oldPath);
    }

    // =========================================================================
    // Tenant isolation — products
    // =========================================================================

    /** @test */
    public function products_are_scoped_to_current_tenant(): void
    {
        [$businessA, $userA] = $this->createBusinessWithOwner('x');

        Product::create([
            'business_id' => $businessA->id,
            'name'        => 'Business A Product',
            'price'       => '10.00',
            'stock'       => 1,
            'is_active'   => true,
        ]);

        $businessB = Business::create([
            'name' => 'Biz B', 'slug' => 'biz-b',
            'phone' => '+333', 'currency' => 'USD', 'status' => 'active',
        ]);
        TenantContext::setTenant($businessB);

        // Business B sees zero products — tenant scoped
        $this->assertEquals(0, Product::count());
    }
}
