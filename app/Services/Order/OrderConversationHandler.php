<?php

namespace App\Services\Order;

use App\Enums\OrderIntentEnum;
use App\Models\Business;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Product;
use App\Services\AI\AIOrderParser;
use App\Services\Catalog\ProductMatcher;
use App\Services\OrderService;
use App\Services\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class OrderConversationHandler
{
    public function __construct(
        protected AIOrderParser $parser,
        protected ProductMatcher $matcher,
        protected OrderService $orderService
    ) {}

    public function handleMessage(Conversation $conversation, string $rawMessage): string
    {
        $message = trim($rawMessage);
        $normalizedMessage = strtolower($message);
        $business = $conversation->business;

        // Ensure TenantContext is set
        TenantContext::setTenant($business);

        $currency = $business->currency ?? 'Rs.';

        // 1. Check TTL Expiration on Pending State
        $this->checkStateExpiration($conversation);

        $currentState = $conversation->current_state ?? 'idle';

        // 2. Fast-Track: Local Deterministic Confirmation / Cancellation
        if (in_array($normalizedMessage, ['yes', 'confirm', 'yup', 'haan', 'ha', 'ok', 'okay', '1'])) {
            if ($currentState === 'pending_confirmation') {
                return $this->processFinalConfirmation($conversation, $business, $currency);
            }
            return "You don't have a pending order to confirm. Please tell me what you'd like to order!";
        }

        if (in_array($normalizedMessage, ['no', 'cancel', 'nahi', 'nah', 'exit', '2'])) {
            if ($currentState === 'pending_confirmation' || $currentState === 'building_order' || $currentState === 'clarification_needed') {
                $this->resetState($conversation);
                return "Your pending order has been cancelled.";
            }
        }

        // 3. Fast-Track: Local Clarification Resolution
        if ($currentState === 'clarification_needed') {
            $context = $conversation->context_data ?? [];
            $options = $context['clarification_options'] ?? [];

            $selectedProductId = null;

            // Direct index selection (e.g., "1", "2")
            if (is_numeric($message) && isset($options[(int) $message])) {
                $selectedProductId = $options[(int) $message]['id'];
            } else {
                // Name match against pending choices
                foreach ($options as $opt) {
                    if (str_contains(strtolower($opt['name']), $normalizedMessage)) {
                        $selectedProductId = $opt['id'];
                        break;
                    }
                }
            }

            if ($selectedProductId) {
                // Resolved choice locally without AI call!
                $product = Product::find($selectedProductId);
                if ($product && $product->is_active) {
                    $pendingQty = $context['pending_clarification_qty'] ?? 1;
                    
                    // Add resolved item to cart
                    $cart = $context['cart'] ?? [];
                    $cart[] = [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'quantity' => $pendingQty,
                    ];

                    // Clear clarification state
                    unset($context['clarification_options'], $context['pending_clarification_qty']);
                    $context['cart'] = $cart;

                    $conversation->update([
                        'current_state' => 'building_order',
                        'context_data' => $context,
                    ]);

                    return $this->renderCartAndRequestConfirmation($conversation, $business, $currency);
                }
            }
        }

        // 4. Natural Language AI Parsing
        try {
            $parsed = $this->parser->parse($message);
            $intent = $parsed['intent'];
            $items = $parsed['items'];
        } catch (\Throwable $e) {
            Log::error('AI parsing failed: ' . $e->getMessage());
            $intent = OrderIntentEnum::UNKNOWN;
            $items = [];
        }

        switch ($intent) {
            case OrderIntentEnum::CONFIRM_ORDER:
                if ($currentState === 'pending_confirmation') {
                    return $this->processFinalConfirmation($conversation, $business, $currency);
                }
                return "You don't have a pending order to confirm. Please tell me what you'd like to order!";

            case OrderIntentEnum::CANCEL_ORDER:
                $this->resetState($conversation);
                return "Your order has been cancelled.";

            case OrderIntentEnum::CREATE_ORDER:
            case OrderIntentEnum::ADD_ITEM:
                return $this->processAddItems($conversation, $business, $items, $currency);

            case OrderIntentEnum::ASK_QUESTION:
                return "I can help you place an order! Type what items you would like (e.g., '2 Zinger Burgers and 1 Coke').";

            case OrderIntentEnum::UNKNOWN:
            default:
                return "I didn't quite catch that. You can tell me what you'd like to order, e.g., '2 Zinger Burgers and 1 Coke'.";
        }
    }

    protected function processAddItems(Conversation $conversation, Business $business, array $items, string $currency): string
    {
        if (empty($items)) {
            return "What items would you like to order?";
        }

        $context = $conversation->context_data ?? [];
        $cart = $context['cart'] ?? [];

        foreach ($items as $item) {
            $query = $item['product_query'];
            $qty = $item['quantity'];

            $matchResult = $this->matcher->match($query);

            if ($matchResult['status'] === 'none') {
                return "Sorry, we couldn't find any active product matching '{$query}'. Please check our menu and try again!";
            }

            if ($matchResult['status'] === 'multiple') {
                // Ambiguous match -> trigger clarification_needed state locally
                $options = [];
                $optionIdx = 1;
                $optionTextList = [];

                foreach ($matchResult['matches'] as $prod) {
                    $options[$optionIdx] = [
                        'id' => $prod->id,
                        'name' => $prod->name,
                    ];
                    $optionTextList[] = "{$optionIdx}. {$prod->name}";
                    $optionIdx++;
                }

                $context['clarification_options'] = $options;
                $context['pending_clarification_qty'] = $qty;

                $conversation->update([
                    'current_state' => 'clarification_needed',
                    'context_data' => $context,
                ]);

                $choicesStr = implode("\n", $optionTextList);
                return "We have multiple items matching '{$query}':\n{$choicesStr}\n\nPlease reply with the number or name of your choice.";
            }

            if ($matchResult['status'] === 'single') {
                $product = $matchResult['product'];
                
                // Add to cart
                $cart[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $qty,
                ];
            }
        }

        $context['cart'] = $cart;
        $conversation->update([
            'current_state' => 'building_order',
            'context_data' => $context,
        ]);

        return $this->renderCartAndRequestConfirmation($conversation, $business, $currency);
    }

    protected function renderCartAndRequestConfirmation(Conversation $conversation, Business $business, string $currency): string
    {
        $context = $conversation->context_data ?? [];
        $cart = $context['cart'] ?? [];

        if (empty($cart)) {
            $this->resetState($conversation);
            return "Your cart is empty.";
        }

        $lines = [];
        $displayTotal = 0.00;

        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || !$product->is_active) {
                return "Sorry, {$item['name']} is currently unavailable.";
            }

            if ($product->stock < $item['quantity']) {
                return "Sorry, we only have {$product->stock} of {$product->name} in stock.";
            }

            $itemSubtotal = $product->price * $item['quantity'];
            $displayTotal += $itemSubtotal;

            $formattedPrice = number_format($product->price, 2);
            $formattedSubtotal = number_format($itemSubtotal, 2);
            $lines[] = "{$item['quantity']}x {$product->name} @ {$currency} {$formattedPrice} = {$currency} {$formattedSubtotal}";
        }

        $formattedTotal = number_format($displayTotal, 2);

        // Store snapshot ONLY to detect if price changes before confirmation
        $context['price_at_display'] = $displayTotal;
        $context['last_state_change'] = now()->toDateTimeString();

        $conversation->update([
            'current_state' => 'pending_confirmation',
            'context_data' => $context,
        ]);

        $orderSummary = implode("\n", $lines);

        return "Your Order Summary:\n\n{$orderSummary}\n\nTotal: {$currency} {$formattedTotal}\n\nReply YES to confirm or NO to cancel.";
    }

    protected function processFinalConfirmation(Conversation $conversation, Business $business, string $currency): string
    {
        $context = $conversation->context_data ?? [];
        $cart = $context['cart'] ?? [];
        $displayedPrice = $context['price_at_display'] ?? null;

        if (empty($cart)) {
            $this->resetState($conversation);
            return "No items found in your order.";
        }

        // 1. Re-verify products, stock, and calculate authoritative fresh total
        $orderItemsPayload = [];
        $currentCalculatedTotal = 0.00;

        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);

            if (!$product || !$product->is_active) {
                $this->resetState($conversation);
                return "Order failed: {$item['name']} is no longer available.";
            }

            if ($product->stock < $item['quantity']) {
                return "Order failed: Insufficient stock for {$product->name}. We only have {$product->stock} available.";
            }

            $currentCalculatedTotal += ($product->price * $item['quantity']);

            $orderItemsPayload[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
            ];
        }

        // 2. Staleness / Price Change Protection
        if ($displayedPrice !== null && abs($currentCalculatedTotal - (float)$displayedPrice) > 0.001) {
            // Price changed! Re-render summary and ask for confirmation again.
            return "Notice: Prices have updated since your last message.\n\n" . 
                   $this->renderCartAndRequestConfirmation($conversation, $business, $currency);
        }

        // 3. Call OrderService as the authoritative source
        try {
            $customer = $conversation->customer;
            
            $order = $this->orderService->createOrder($orderItemsPayload, $customer->id);

            $this->resetState($conversation);

            $formattedTotal = number_format($order->total, 2);

            return "Thank you! Your order #{$order->id} has been confirmed.\nTotal: {$currency} {$formattedTotal}.";

        } catch (\Throwable $e) {
            Log::error('Failed to create order via OrderService: ' . $e->getMessage());
            return "Sorry, there was an issue processing your order. Please try again.";
        }
    }

    protected function checkStateExpiration(Conversation $conversation): void
    {
        $context = $conversation->context_data ?? [];
        $lastStateChange = $context['last_state_change'] ?? null;

        if ($lastStateChange) {
            $ttlMinutes = config('services.whatsapp.confirmation_ttl_minutes', 15);
            $expiresAt = Carbon::parse($lastStateChange)->addMinutes($ttlMinutes);

            if (now()->greaterThan($expiresAt)) {
                $this->resetState($conversation);
            }
        }
    }

    protected function resetState(Conversation $conversation): void
    {
        $conversation->current_state = 'idle';
        $conversation->context_data = null;
        $conversation->save();
    }
}
