<?php

namespace Tests\Feature;

use App\Contracts\AI\AIServiceInterface;
use App\Models\Business;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\WhatsAppConnection;
use App\Services\AI\AIOrderParser;
use App\Services\Catalog\ProductMatcher;
use App\Services\Order\OrderConversationHandler;
use App\Services\OrderService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class OrderConversationHandlerTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected Customer $customer;
    protected Conversation $conversation;
    protected Product $product1;
    protected Product $product2;
    protected Product $product3;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.whatsapp.confirmation_ttl_minutes', 15);

        $this->business = Business::create(['name' => 'Burger House', 'slug' => 'burger-house', 'status' => 'active', 'currency' => 'Rs.']);
        TenantContext::setTenant($this->business);

        $connection = WhatsAppConnection::create([
            'business_id' => $this->business->id,
            'phone_number_id' => '999',
            'access_token' => 'token',
            'verify_token' => 'token',
        ]);

        $this->customer = Customer::create(['business_id' => $this->business->id, 'phone' => '123456789', 'name' => 'John']);

        $this->conversation = Conversation::create([
            'business_id' => $this->business->id,
            'whatsapp_connection_id' => $connection->id,
            'customer_id' => $this->customer->id,
            'current_state' => 'idle',
        ]);

        $cat = Category::create(['name' => 'Burgers', 'slug' => 'burgers', 'is_active' => true]);

        $this->product1 = Product::create(['category_id' => $cat->id, 'name' => 'Zinger Burger', 'price' => 500.00, 'stock' => 10, 'is_active' => true]);
        $this->product2 = Product::create(['category_id' => $cat->id, 'name' => 'Beef Burger', 'price' => 600.00, 'stock' => 5, 'is_active' => true]);
        $this->product3 = Product::create(['category_id' => $cat->id, 'name' => 'Chicken Burger', 'price' => 450.00, 'stock' => 5, 'is_active' => true]);
    }

    public function test_order_creation_flow_asks_confirmation_and_creates_order_on_yes()
    {
        $mockAi = $this->createMock(AIServiceInterface::class);
        $mockAi->expects($this->once())->method('parseIntent')->willReturn([
            'intent' => 'create_order',
            'items' => [['product_query' => 'Zinger Burger', 'quantity' => 2]]
        ]);

        $handler = new OrderConversationHandler(
            new AIOrderParser($mockAi),
            new ProductMatcher(),
            app(OrderService::class)
        );

        // Step 1: Customer asks for 2 zinger burgers
        $reply1 = $handler->handleMessage($this->conversation, '2 zinger burgers');

        $this->assertStringContainsString('Your Order Summary:', $reply1);
        $this->assertStringContainsString('2x Zinger Burger', $reply1);
        $this->assertStringContainsString('Rs. 1,000.00', $reply1);
        $this->assertEquals('pending_confirmation', $this->conversation->fresh()->current_state);
        $this->assertEquals(0, Order::count());

        // Step 2: Customer replies "YES" (Deterministic fast-track, AI NOT called again)
        $reply2 = $handler->handleMessage($this->conversation->fresh(), 'YES');

        $this->assertStringContainsString('confirmed', $reply2);
        $this->assertStringContainsString('Rs. 1,000.00', $reply2);
        $this->assertEquals(1, Order::count());
        $this->assertEquals(8, $this->product1->fresh()->stock); // Stock decremented by 2
        $this->assertEquals('idle', $this->conversation->fresh()->current_state);
    }

    public function test_deterministic_cancellation_on_no()
    {
        $mockAi = $this->createMock(AIServiceInterface::class);
        $mockAi->method('parseIntent')->willReturn([
            'intent' => 'create_order',
            'items' => [['product_query' => 'Zinger Burger', 'quantity' => 1]]
        ]);

        $handler = new OrderConversationHandler(
            new AIOrderParser($mockAi),
            new ProductMatcher(),
            app(OrderService::class)
        );

        $handler->handleMessage($this->conversation, '1 zinger burger');
        $this->assertEquals('pending_confirmation', $this->conversation->fresh()->current_state);

        // Customer replies "NO"
        $reply = $handler->handleMessage($this->conversation->fresh(), 'NO');

        $this->assertStringContainsString('cancelled', $reply);
        $this->assertEquals(0, Order::count());
        $this->assertEquals('idle', $this->conversation->fresh()->current_state);
    }

    public function test_clarification_resolved_locally_without_ai_call()
    {
        // "burger" matches 3 items (Zinger Burger, Beef Burger, Chicken Burger)
        $mockAi = $this->createMock(AIServiceInterface::class);
        // We set expectation for parseIntent to be called ONCE only
        $mockAi->expects($this->once())->method('parseIntent')->willReturn([
            'intent' => 'create_order',
            'items' => [['product_query' => 'burger', 'quantity' => 1]]
        ]);

        $handler = new OrderConversationHandler(
            new AIOrderParser($mockAi),
            new ProductMatcher(),
            app(OrderService::class)
        );

        // Step 1: Initial query causes multiple matches
        $reply1 = $handler->handleMessage($this->conversation, '1 burger');

        $this->assertStringContainsString('multiple items matching', $reply1);
        $this->assertEquals('clarification_needed', $this->conversation->fresh()->current_state);

        // Step 2: Customer responds with "Beef" (Matches option locally without calling AI)
        $reply2 = $handler->handleMessage($this->conversation->fresh(), 'Beef');

        $this->assertStringContainsString('1x Beef Burger', $reply2);
        $this->assertEquals('pending_confirmation', $this->conversation->fresh()->current_state);
    }

    public function test_stale_confirmation_state_expires_after_ttl()
    {
        $mockAi = $this->createMock(AIServiceInterface::class);
        $mockAi->method('parseIntent')->willReturn([
            'intent' => 'create_order',
            'items' => [['product_query' => 'Zinger Burger', 'quantity' => 1]]
        ]);

        $handler = new OrderConversationHandler(
            new AIOrderParser($mockAi),
            new ProductMatcher(),
            app(OrderService::class)
        );

        $handler->handleMessage($this->conversation, '1 zinger burger');

        // Manually manipulate last_state_change to 20 minutes ago
        $context = $this->conversation->fresh()->context_data;
        $context['last_state_change'] = now()->subMinutes(20)->toDateTimeString();
        $this->conversation->update(['context_data' => $context]);

        // Customer replies "YES" after TTL
        $reply = $handler->handleMessage($this->conversation->fresh(), 'YES');

        // Should NOT confirm the order, state expired
        $this->assertEquals(0, Order::count());
        $this->assertStringContainsString("don't have a pending order", $reply);
    }

    public function test_price_change_before_confirmation_notifies_customer()
    {
        $mockAi = $this->createMock(AIServiceInterface::class);
        $mockAi->method('parseIntent')->willReturn([
            'intent' => 'create_order',
            'items' => [['product_query' => 'Zinger Burger', 'quantity' => 1]]
        ]);

        $handler = new OrderConversationHandler(
            new AIOrderParser($mockAi),
            new ProductMatcher(),
            app(OrderService::class)
        );

        // Render summary at 500.00
        $handler->handleMessage($this->conversation, '1 zinger burger');

        // Change DB price of Zinger Burger to 550.00 before confirmation
        $this->product1->update(['price' => 550.00]);

        // Customer replies "YES"
        $reply = $handler->handleMessage($this->conversation->fresh(), 'YES');

        // Should reject immediate creation, update display, and ask for re-confirmation
        $this->assertEquals(0, Order::count());
        $this->assertStringContainsString('Prices have updated', $reply);
        $this->assertStringContainsString('Rs. 550.00', $reply);

        // Confirming a second time with updated price creates order
        $reply2 = $handler->handleMessage($this->conversation->fresh(), 'YES');
        $this->assertEquals(1, Order::count());
        $this->assertEquals(550.00, Order::first()->total);
    }

    public function test_ai_provider_failure_handled_safely()
    {
        $mockAi = $this->createMock(AIServiceInterface::class);
        $mockAi->method('parseIntent')->willThrowException(new \RuntimeException('API Rate Limit Exceeded'));

        $handler = new OrderConversationHandler(
            new AIOrderParser($mockAi),
            new ProductMatcher(),
            app(OrderService::class)
        );

        $reply = $handler->handleMessage($this->conversation, 'order something');

        $this->assertStringContainsString("didn't quite catch that", $reply);
        $this->assertEquals('idle', $this->conversation->fresh()->current_state);
        $this->assertEquals(0, Order::count());
    }
}
