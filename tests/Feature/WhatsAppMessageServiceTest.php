<?php

namespace Tests\Feature;

use App\Jobs\ProcessWhatsAppMessageJob;
use App\Models\Business;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\WhatsAppConnection;
use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\WhatsAppMessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppMessageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected WhatsAppConnection $connection;
    protected WhatsAppMessageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->business = Business::create(['name' => 'Biz 1', 'slug' => 'biz-1', 'status' => 'active', 'currency' => 'USD']);
        $this->connection = WhatsAppConnection::create([
            'business_id' => $this->business->id,
            'phone_number_id' => '123',
            'access_token' => 'token',
            'verify_token' => 'token',
        ]);
        
        $this->service = new WhatsAppMessageService();
    }

    public function test_process_message_creates_customer_conversation_and_message()
    {
        $messageData = [
            'id' => 'msg_123',
            'type' => 'text',
            'from' => '111222333',
            'text' => ['body' => 'Hello']
        ];
        
        $this->service->processIncomingMessage(
            $this->connection,
            '111222333',
            'John Doe',
            $messageData,
            ['raw' => 'data']
        );

        $this->assertEquals(1, Customer::count());
        $customer = Customer::first();
        $this->assertEquals($this->business->id, $customer->business_id);
        $this->assertEquals('111222333', $customer->phone);
        $this->assertEquals('John Doe', $customer->name);

        $this->assertEquals(1, Conversation::count());
        $conversation = Conversation::first();
        $this->assertEquals($this->business->id, $conversation->business_id);
        $this->assertEquals($customer->id, $conversation->customer_id);

        $this->assertEquals(1, WhatsAppMessage::where('direction', 'inbound')->count());
        $message = WhatsAppMessage::where('direction', 'inbound')->first();
        $this->assertEquals('msg_123', $message->provider_message_id);
        $this->assertEquals('Hello', $message->content);
        $this->assertEquals($customer->id, $message->customer_id);
    }

    public function test_duplicate_webhook_is_gracefully_ignored_via_unique_constraint()
    {
        $messageData = [
            'id' => 'msg_123',
            'type' => 'text',
            'from' => '111222333',
            'text' => ['body' => 'Hello']
        ];
        
        // Process once
        $this->service->processIncomingMessage($this->connection, '111222333', 'John', $messageData, []);
        
        // Process again with exact same provider_message_id
        // Should catch the Integrity Constraint Violation and gracefully return
        $this->service->processIncomingMessage($this->connection, '111222333', 'John', $messageData, []);

        $this->assertEquals(1, WhatsAppMessage::where('direction', 'inbound')->count());
        $this->assertEquals(1, Customer::count());
        $this->assertEquals(1, Conversation::count());
    }

    public function test_strict_phone_normalization_in_job()
    {
        // This tests the Job's protected normalization logic using reflection or direct call if we make it public.
        // Instead, we will dispatch the job directly and assert the DB result.
        
        $job = new ProcessWhatsAppMessageJob(
            $this->connection->id,
            ['id' => 'msg_999', 'type' => 'text', 'from' => '00+1 (555) 123-4567'],
            [['wa_id' => '00+1 (555) 123-4567', 'profile' => ['name' => 'Jane']]],
            []
        );
        
        $job->handle(new WhatsAppMessageService());

        // Normalized phone should be 15551234567 
        // Logic: strip non-digits -> 0015551234567 -> strip 00 -> 15551234567
        $customer = Customer::first();
        $this->assertEquals('15551234567', $customer->phone);
    }

    public function test_tenant_isolation_same_phone_different_business()
    {
        $business1 = $this->business;
        $business2 = Business::create(['name' => 'Biz 2', 'slug' => 'biz-2', 'status' => 'active', 'currency' => 'USD']);
        $connection2 = WhatsAppConnection::create([
            'business_id' => $business2->id,
            'phone_number_id' => '456',
            'access_token' => 'token',
            'verify_token' => 'token',
        ]);

        $messageData = ['id' => 'msg_1', 'type' => 'text', 'from' => '9999999', 'text' => ['body' => 'Hi']];
        
        \App\Services\TenantContext::setTenant($business1);
        $this->service->processIncomingMessage($this->connection, '9999999', 'Bob', $messageData, []);
        
        $messageData2 = ['id' => 'msg_2', 'type' => 'text', 'from' => '9999999', 'text' => ['body' => 'Hi Biz 2']];
        \App\Services\TenantContext::setTenant($business2);
        $this->service->processIncomingMessage($connection2, '9999999', 'Bob', $messageData2, []);

        $this->assertEquals(2, Customer::withoutGlobalScopes()->count());
        
        $cust1 = Customer::withoutGlobalScopes()->where('business_id', $business1->id)->first();
        $cust2 = Customer::withoutGlobalScopes()->where('business_id', $business2->id)->first();
        
        $this->assertNotNull($cust1);
        $this->assertNotNull($cust2);
        $this->assertNotEquals($cust1->id, $cust2->id); // Completely separate customers
    }
}
