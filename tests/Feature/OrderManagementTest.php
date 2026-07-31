<?php

namespace Tests\Feature;

use App\Exceptions\OrderCreationException;
use App\Models\Business;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Business $business;
    protected Customer $customer;
    protected Product $product1;
    protected Product $product2;
    protected OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Test Business',
            'status' => 'active',
            'slug' => 'test-biz-' . uniqid(),
            'currency' => 'USD',
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'business_id' => $this->business->id,
            'is_active' => true,
        ]);

        TenantContext::setTenant($this->business);

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
        ]);

        $category = Category::create([
            'business_id' => $this->business->id,
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        $this->product1 = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'price' => 1000.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'name' => 'Mouse',
            'sku' => 'MOU-001',
            'price' => 50.00,
            'stock' => 100,
            'is_active' => true,
        ]);

        $this->orderService = new OrderService();
    }

    public function test_order_can_be_created_successfully()
    {
        $items = [
            ['product_id' => $this->product1->id, 'quantity' => 2],
        ];

        $order = $this->orderService->createOrder($items, $this->customer->id);

        $this->assertNotNull($order->id);
        $this->assertEquals($this->business->id, $order->business_id);
        $this->assertEquals($this->customer->id, $order->customer_id);
        $this->assertEquals('pending', $order->status);
        $this->assertCount(1, $order->items);
        $this->assertEquals(2000.00, $order->total);
        $this->assertEquals(2000.00, $order->subtotal);

        // Verify stock was decremented
        $this->product1->refresh();
        $this->assertEquals(8, $this->product1->stock);
    }

    public function test_correct_product_price_comes_from_database_and_client_fake_price_is_ignored()
    {
        $items = [
            // Simulating a fake client-supplied price that should be completely ignored
            ['product_id' => $this->product1->id, 'quantity' => 1, 'price' => 1.00],
        ];

        $order = $this->orderService->createOrder($items, $this->customer->id);

        $item = $order->items->first();
        $this->assertEquals(1000.00, $item->unit_price);
        $this->assertEquals(1000.00, $item->subtotal);
        $this->assertEquals(1000.00, $order->total);
    }

    public function test_correct_quantity_and_subtotal_is_calculated()
    {
        $items = [
            ['product_id' => $this->product1->id, 'quantity' => 3], // 3 * 1000 = 3000
            ['product_id' => $this->product2->id, 'quantity' => 5], // 5 * 50 = 250
        ];

        $order = $this->orderService->createOrder($items, $this->customer->id);

        $this->assertEquals(3250.00, $order->subtotal);
        $this->assertEquals(3250.00, $order->total);

        $item1 = $order->items->where('product_id', $this->product1->id)->first();
        $this->assertEquals(3, $item1->quantity);
        $this->assertEquals(3000.00, $item1->subtotal);

        $item2 = $order->items->where('product_id', $this->product2->id)->first();
        $this->assertEquals(5, $item2->quantity);
        $this->assertEquals(250.00, $item2->subtotal);
    }

    public function test_delivery_fee_and_discount_are_included_correctly()
    {
        $items = [
            ['product_id' => $this->product1->id, 'quantity' => 1], // 1000
        ];

        $order = $this->orderService->createOrder($items, $this->customer->id, 50.00, 20.00);

        $this->assertEquals(1000.00, $order->subtotal);
        $this->assertEquals(50.00, $order->delivery_fee);
        $this->assertEquals(20.00, $order->discount);
        $this->assertEquals(1030.00, $order->total); // 1000 + 50 - 20
    }

    public function test_negative_delivery_fee_is_rejected()
    {
        $this->expectException(OrderCreationException::class);
        $this->expectExceptionMessage('Delivery fee cannot be negative.');

        $items = [['product_id' => $this->product1->id, 'quantity' => 1]];
        $this->orderService->createOrder($items, $this->customer->id, -10.00, 0);
    }

    public function test_negative_discount_is_rejected()
    {
        $this->expectException(OrderCreationException::class);
        $this->expectExceptionMessage('Discount cannot be negative.');

        $items = [['product_id' => $this->product1->id, 'quantity' => 1]];
        $this->orderService->createOrder($items, $this->customer->id, 0, -50.00);
    }

    public function test_discount_cannot_produce_negative_total()
    {
        $this->expectException(OrderCreationException::class);
        $this->expectExceptionMessage('Order total cannot be negative.');

        $items = [['product_id' => $this->product2->id, 'quantity' => 1]]; // 50
        $this->orderService->createOrder($items, $this->customer->id, 0, 100.00);
    }

    public function test_zero_or_negative_quantity_is_rejected()
    {
        $this->expectException(OrderCreationException::class);
        $this->expectExceptionMessage('Quantity must be greater than zero.');

        $items = [['product_id' => $this->product1->id, 'quantity' => 0]];
        $this->orderService->createOrder($items, $this->customer->id);
    }

    public function test_insufficient_stock_is_rejected()
    {
        $this->expectException(OrderCreationException::class);
        $this->expectExceptionMessage('Insufficient stock for product');

        $items = [['product_id' => $this->product1->id, 'quantity' => 20]]; // Only 10 in stock
        $this->orderService->createOrder($items, $this->customer->id);
    }

    public function test_product_from_another_business_is_rejected()
    {
        $otherBusiness = Business::create(['name' => 'Other Biz', 'slug' => 'other-biz', 'currency' => 'USD', 'status' => 'active']);
        $otherProduct = Product::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Other Laptop',
            'price' => 1000.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->expectException(OrderCreationException::class);
        $this->expectExceptionMessage('One or more products are invalid or do not belong to the current business.');

        $items = [['product_id' => $otherProduct->id, 'quantity' => 1]];
        $this->orderService->createOrder($items, $this->customer->id);
    }

    public function test_customer_from_another_business_is_rejected()
    {
        $otherBusiness = Business::create(['name' => 'Other Biz', 'slug' => 'other-biz-2', 'currency' => 'USD', 'status' => 'active']);
        $otherCustomer = Customer::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Other Cust',
            'phone' => '0987654321',
        ]);

        $this->expectException(ModelNotFoundException::class);

        $items = [['product_id' => $this->product1->id, 'quantity' => 1]];
        $this->orderService->createOrder($items, $otherCustomer->id);
    }

    public function test_product_price_changes_after_an_order_do_not_change_historical_order_item_price()
    {
        $items = [['product_id' => $this->product1->id, 'quantity' => 2]]; // Price is 1000, subtotal 2000
        $order = $this->orderService->createOrder($items, $this->customer->id);

        // Change the product price
        $this->product1->update(['price' => 1500.00]);

        $order->refresh();
        $item = $order->items->first();

        // Historical data remains accurate
        $this->assertEquals(1000.00, $item->unit_price);
        $this->assertEquals(2000.00, $item->subtotal);
        $this->assertEquals(2000.00, $order->total);
    }

    public function test_order_creation_is_transactional_and_rolls_back_stock_on_failure()
    {
        $initialStock = $this->product1->stock;

        $items = [
            ['product_id' => $this->product1->id, 'quantity' => 2], // This is valid
            ['product_id' => $this->product2->id, 'quantity' => -1], // This will fail validation
        ];

        try {
            $this->orderService->createOrder($items, $this->customer->id);
        } catch (OrderCreationException $e) {
            // Expected
        }

        $this->product1->refresh();

        // Stock should be unchanged because transaction rolled back
        $this->assertEquals($initialStock, $this->product1->stock);

        // Order should not be created
        $this->assertEquals(0, Order::count());
    }

    public function test_inactive_product_is_rejected()
    {
        $this->product2->update(['is_active' => false]);

        $this->expectException(OrderCreationException::class);
        $this->expectExceptionMessage("Product '{$this->product2->name}' is not active.");

        $items = [['product_id' => $this->product2->id, 'quantity' => 1]];
        $this->orderService->createOrder($items, $this->customer->id);
    }
}
