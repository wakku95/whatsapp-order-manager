<?php

namespace App\Services\AI;

use App\Contracts\AI\AIServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAIService implements AIServiceInterface
{
    public function parseIntent(string $message): array
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-3.5-flash');

        if (!$apiKey) {
            Log::warning('Gemini API key is not configured.');
            return ['intent' => 'unknown', 'items' => []];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $systemPrompt = "You are an AI order understanding assistant for a restaurant/store. " .
            "Extract the customer's intent and items requested from their message. " .
            "Do NOT invent prices, totals, stock, or business IDs. " .
            "Allowed intents: create_order, add_item, remove_item, change_quantity, confirm_order, cancel_order, ask_question, unknown.";

        $jsonSchema = [
            'type' => 'OBJECT',
            'properties' => [
                'intent' => [
                    'type' => 'STRING',
                    'enum' => ['create_order', 'add_item', 'remove_item', 'change_quantity', 'confirm_order', 'cancel_order', 'ask_question', 'unknown']
                ],
                'items' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'product_query' => ['type' => 'STRING'],
                            'quantity' => ['type' => 'INTEGER']
                        ],
                        'required' => ['product_query', 'quantity']
                    ]
                ]
            ],
            'required' => ['intent', 'items']
        ];

        try {
            $response = Http::timeout(10)->post($url, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $systemPrompt . "\n\nCustomer message: " . $message]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $jsonSchema,
                ]
            ]);

            if ($response->failed()) {
                Log::error('Gemini API request failed', ['status' => $response->status(), 'body' => $response->body()]);
                return ['intent' => 'unknown', 'items' => []];
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            
            $decoded = json_decode($text, true);
            
            return is_array($decoded) ? $decoded : ['intent' => 'unknown', 'items' => []];

        } catch (\Throwable $e) {
            Log::error('Gemini API exception: ' . $e->getMessage());
            return ['intent' => 'unknown', 'items' => []];
        }
    }
}
