<?php

namespace Tests\Feature;

use App\Contracts\AI\AIServiceInterface;
use App\Enums\OrderIntentEnum;
use App\Services\AI\AIOrderParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AIOrderParserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.gemini.api_key', 'test_key');
        Config::set('services.gemini.model', 'gemini-3.5-flash');
    }

    public function test_gemini_service_uses_configured_model()
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode(['intent' => 'create_order', 'items' => [['product_query' => 'zinger', 'quantity' => 1]]])]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $parser = app(AIOrderParser::class);
        $result = $parser->parse('1 zinger burger');

        $this->assertEquals(OrderIntentEnum::CREATE_ORDER, $result['intent']);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('zinger', $result['items'][0]['product_query']);
        $this->assertEquals(1, $result['items'][0]['quantity']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'gemini-3.5-flash');
        });
    }

    public function test_valid_structured_json_is_accepted()
    {
        $mock = $this->createMock(AIServiceInterface::class);
        $mock->method('parseIntent')->willReturn([
            'intent' => 'create_order',
            'items' => [
                ['product_query' => 'coke', 'quantity' => 2]
            ]
        ]);

        $parser = new AIOrderParser($mock);
        $result = $parser->parse('2 cokes');

        $this->assertEquals(OrderIntentEnum::CREATE_ORDER, $result['intent']);
        $this->assertEquals('coke', $result['items'][0]['product_query']);
        $this->assertEquals(2, $result['items'][0]['quantity']);
    }

    public function test_malformed_json_returns_unknown_intent()
    {
        $mock = $this->createMock(AIServiceInterface::class);
        $mock->method('parseIntent')->willReturn(['corrupted' => 'data']);

        $parser = new AIOrderParser($mock);
        $result = $parser->parse('some message');

        $this->assertEquals(OrderIntentEnum::UNKNOWN, $result['intent']);
        $this->assertEmpty($result['items']);
    }

    public function test_invalid_intent_returns_unknown_intent()
    {
        $mock = $this->createMock(AIServiceInterface::class);
        $mock->method('parseIntent')->willReturn([
            'intent' => 'INVALID_INTENT_STRING',
            'items' => []
        ]);

        $parser = new AIOrderParser($mock);
        $result = $parser->parse('some message');

        $this->assertEquals(OrderIntentEnum::UNKNOWN, $result['intent']);
    }

    public function test_invalid_negative_quantity_returns_unknown_intent()
    {
        $mock = $this->createMock(AIServiceInterface::class);
        $mock->method('parseIntent')->willReturn([
            'intent' => 'create_order',
            'items' => [
                ['product_query' => 'coke', 'quantity' => -5]
            ]
        ]);

        $parser = new AIOrderParser($mock);
        $result = $parser->parse('some message');

        $this->assertEquals(OrderIntentEnum::UNKNOWN, $result['intent']);
    }
}
