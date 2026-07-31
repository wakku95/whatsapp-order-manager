<?php

namespace App\Services\WhatsApp;

use App\Models\Conversation;
use App\Models\Customer;
use App\Models\WhatsAppConnection;
use App\Models\WhatsAppMessage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class WhatsAppMessageService
{
    /**
     * Process an incoming verified message.
     */
    public function processIncomingMessage(
        WhatsAppConnection $connection,
        string $normalizedPhone,
        ?string $customerName,
        array $messageData,
        array $rawPayload
    ): void {
        $messageId = $messageData['id'] ?? null;
        if (!$messageId) {
            return;
        }

        $businessId = $connection->business_id;
        $messageType = $messageData['type'] ?? 'unknown';
        
        $content = null;
        if ($messageType === 'text') {
            $content = $messageData['text']['body'] ?? null;
        }

        try {
            // Attempt to insert the message directly with a try-catch for the unique constraint.
            // This strictly prevents duplicate processing if Meta sends the webhook twice.
            $message = WhatsAppMessage::create([
                'business_id' => $businessId,
                'whatsapp_connection_id' => $connection->id,
                'provider_message_id' => $messageId,
                'direction' => 'inbound',
                'message_type' => $messageType,
                'content' => $content,
                'status' => 'received',
                'raw_payload' => $rawPayload,
            ]);
        } catch (QueryException $e) {
            // SQLSTATE 23000 is Integrity constraint violation
            if ($e->getCode() == 23000) {
                Log::info('Duplicate WhatsApp webhook ignored.', [
                    'provider_message_id' => $messageId,
                    'business_id' => $businessId,
                ]);
                return; // Gracefully exit, duplicate handled correctly
            }
            
            // Re-throw any other database exception (e.g. connection lost)
            throw $e;
        }

        // Now we know we are the sole processor of this message.
        // Find or create customer
        $customer = Customer::firstOrCreate(
            [
                'business_id' => $businessId,
                'phone' => $normalizedPhone,
            ],
            [
                'name' => $customerName,
                'last_interaction_at' => now(),
            ]
        );

        if ($customer->name === null && $customerName !== null) {
            $customer->update(['name' => $customerName]);
        }

        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            [
                'business_id' => $businessId,
                'whatsapp_connection_id' => $connection->id,
                'customer_id' => $customer->id,
            ],
            [
                'current_state' => 'idle',
            ]
        );

        // Update timestamps and relationships
        $customer->update(['last_interaction_at' => now()]);
        $conversation->update(['last_message_at' => now()]);
        
        $message->update(['customer_id' => $customer->id]);

        // Phase 4: Pass text messages to OrderConversationHandler for AI interpretation and ordering
        if ($content && $messageType === 'text') {
            $handler = app(\App\Services\Order\OrderConversationHandler::class);
            $replyText = $handler->handleMessage($conversation, $content);

            // Record outbound response message
            WhatsAppMessage::create([
                'business_id' => $businessId,
                'whatsapp_connection_id' => $connection->id,
                'customer_id' => $customer->id,
                'provider_message_id' => 'out_' . uniqid(),
                'direction' => 'outbound',
                'message_type' => 'text',
                'content' => $replyText,
                'status' => 'sent',
            ]);
        }
    }
}
