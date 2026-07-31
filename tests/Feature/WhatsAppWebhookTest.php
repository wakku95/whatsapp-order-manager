<?php

namespace Tests\Feature;

use App\Jobs\ProcessWhatsAppMessageJob;
use App\Models\Business;
use App\Models\WhatsAppConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.whatsapp.app_secret', 'test_secret');
        Config::set('services.whatsapp.verify_token', 'test_token');
    }

    public function test_valid_get_verification_returns_challenge()
    {
        $response = $this->get('/webhook/whatsapp?hub_mode=subscribe&hub_verify_token=test_token&hub_challenge=123456789');

        $response->assertStatus(200);
        $response->assertSee('123456789');
    }

    public function test_invalid_get_verification_is_rejected()
    {
        $response = $this->get('/webhook/whatsapp?hub_mode=subscribe&hub_verify_token=wrong_token&hub_challenge=123456789');

        $response->assertStatus(403);
    }

    public function test_valid_post_signature_is_accepted_and_job_dispatched()
    {
        $this->withoutExceptionHandling();
        Bus::fake();

        $business = Business::create(['name' => 'Biz', 'slug' => 'biz', 'status' => 'active', 'currency' => 'USD']);
        $connection = WhatsAppConnection::create([
            'business_id' => $business->id,
            'phone_number_id' => '12345',
            'access_token' => 'token',
            'verify_token' => 'token',
        ]);

        $payload = json_encode([
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'metadata' => ['phone_number_id' => '12345'],
                                'messages' => [['id' => 'msg_1', 'from' => '111222333']],
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test_secret');

        $response = $this->withHeaders([
            'X-Hub-Signature-256' => $signature,
        ])->postJson('/webhook/whatsapp', json_decode($payload, true));

        $response->assertStatus(200);
        $response->assertJson(['status' => 'queued']);

        Bus::assertDispatched(ProcessWhatsAppMessageJob::class);
    }

    public function test_invalid_post_signature_is_rejected()
    {
        Queue::fake();

        $payload = ['object' => 'whatsapp_business_account'];
        $signature = 'sha256=invalid_hash';

        $response = $this->withHeaders([
            'X-Hub-Signature-256' => $signature,
        ])->postJson('/webhook/whatsapp', $payload);

        $response->assertStatus(401);
        Queue::assertNothingPushed();
    }

    public function test_unknown_phone_number_id_is_ignored_and_not_queued()
    {
        Queue::fake();

        $payload = json_encode([
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'metadata' => ['phone_number_id' => 'UNKNOWN_ID'],
                                'messages' => [['id' => 'msg_1', 'from' => '111222333']],
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test_secret');

        $response = $this->withHeaders([
            'X-Hub-Signature-256' => $signature,
        ])->postJson('/webhook/whatsapp', json_decode($payload, true));

        // Returns 200 to prevent Meta from retrying, but does not queue anything
        $response->assertStatus(200);
        Queue::assertNothingPushed();
    }
}
