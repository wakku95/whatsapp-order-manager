<?php

namespace App\Services\AI;

use App\Contracts\AI\AIServiceInterface;
use App\Enums\OrderIntentEnum;
use Illuminate\Support\Facades\Validator;

class AIOrderParser
{
    public function __construct(
        protected AIServiceInterface $aiService
    ) {}

    public function parse(string $message): array
    {
        $rawOutput = $this->aiService->parseIntent($message);

        $validator = Validator::make($rawOutput, [
            'intent' => 'required|string',
            'items' => 'present|array',
            'items.*.product_query' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return [
                'intent' => OrderIntentEnum::UNKNOWN,
                'items' => [],
            ];
        }

        $intentEnum = OrderIntentEnum::tryFrom($rawOutput['intent']);

        if (!$intentEnum) {
            return [
                'intent' => OrderIntentEnum::UNKNOWN,
                'items' => [],
            ];
        }

        // Sanitize items
        $sanitizedItems = [];
        foreach ($rawOutput['items'] as $item) {
            $sanitizedItems[] = [
                'product_query' => trim($item['product_query']),
                'quantity' => (int) $item['quantity'],
            ];
        }

        return [
            'intent' => $intentEnum,
            'items' => $sanitizedItems,
        ];
    }
}
