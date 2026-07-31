<?php

namespace App\Services;

use App\Exceptions\OrderCreationException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order with strict server-side validation and pricing.
     *
     * @param array $items Array of items: [['product_id' => 1, 'quantity' => 2], ...]
     * @param int $customerId Required customer ID
     * @param float|string $deliveryFee
     * @param float|string $discount
     * @param string|null $notes
     * @return Order
     * @throws OrderCreationException
     */
    public function createOrder(array $items, int $customerId, $deliveryFee = 0.00, $discount = 0.00, ?string $notes = null): Order
    {
        $businessId = TenantContext::getTenantId();

        if (!$businessId) {
            throw new OrderCreationException("No active tenant context found.");
        }

        if (empty($items)) {
            throw new OrderCreationException("Order must contain at least one item.");
        }

        $deliveryFee = round((float) $deliveryFee, 2);
        $discount = round((float) $discount, 2);

        if ($deliveryFee < 0) {
            throw new OrderCreationException("Delivery fee cannot be negative.");
        }

        if ($discount < 0) {
            throw new OrderCreationException("Discount cannot be negative.");
        }

        // Validate customer
        // TenantScope ensures this will throw ModelNotFoundException if the customer belongs to another business
        Customer::findOrFail($customerId);

        return DB::transaction(function () use ($items, $customerId, $deliveryFee, $discount, $notes, $businessId) {
            $productIds = collect($items)->pluck('product_id')->unique()->toArray();

            // Lock products for update to prevent concurrent stock issues
            // TenantScope automatically scopes this to the current business
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            if ($products->count() !== count($productIds)) {
                throw new OrderCreationException("One or more products are invalid or do not belong to the current business.");
            }

            $orderSubtotal = 0.0;
            $orderItemsData = [];

            foreach ($items as $item) {
                $productId = $item['product_id'] ?? null;
                $quantity = $item['quantity'] ?? null;

                if (!$productId || $quantity === null) {
                    throw new OrderCreationException("Invalid item payload. Must contain product_id and quantity.");
                }

                $quantity = (int) $quantity;

                if ($quantity <= 0) {
                    throw new OrderCreationException("Quantity must be greater than zero.");
                }

                $product = $products->get($productId);

                if (!$product->is_active) {
                    throw new OrderCreationException("Product '{$product->name}' is not active.");
                }

                if ($product->stock < $quantity) {
                    throw new OrderCreationException("Insufficient stock for product '{$product->name}'.");
                }

                $unitPrice = round((float) $product->price, 2);
                $itemSubtotal = round($unitPrice * $quantity, 2);

                $orderSubtotal += $itemSubtotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal,
                ];

                // Decrement stock immediately inside the transaction
                $product->stock -= $quantity;
                $product->save();
            }

            $orderSubtotal = round($orderSubtotal, 2);
            $orderTotal = round($orderSubtotal + $deliveryFee - $discount, 2);

            if ($orderTotal < 0) {
                throw new OrderCreationException("Order total cannot be negative.");
            }

            $orderNumber = $this->generateOrderNumber($businessId);

            $order = Order::create([
                'business_id' => $businessId,
                'customer_id' => $customerId, // nullable in table schema? Wait, let's check!
                'order_number' => $orderNumber,
                'status' => 'pending',
                'subtotal' => $orderSubtotal,
                'delivery_fee' => $deliveryFee,
                'discount' => $discount,
                'total' => $orderTotal,
                'cod_amount' => $orderTotal, // Assuming COD for V1
                'notes' => $notes,
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            return $order->load('items');
        });
    }

    protected function generateOrderNumber(int $businessId): string
    {
        // Simple implementation: ORD-{YYMMDD}-{RANDOM}
        return 'ORD-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }
}
