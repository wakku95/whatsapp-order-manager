<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWhatsAppMessageJob;
use App\Models\WhatsAppConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle Meta Webhook Verification (GET)
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $expectedToken = config('services.whatsapp.verify_token');

        if ($mode === 'subscribe' && $token === $expectedToken) {
            return response($challenge, 200);
        }

        return response()->json(['error' => 'Verification failed'], 403);
    }

    /**
     * Handle Incoming Webhook Events (POST)
     */
    public function handle(Request $request)
    {
        // Signature is already verified by VerifyWhatsAppSignature middleware

        $payload = $request->all();

        // Ensure this is a WhatsApp Business Account message event
        if (($payload['object'] ?? '') !== 'whatsapp_business_account') {
            return response()->json(['status' => 'ignored'], 200);
        }

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? '') !== 'messages') {
                    continue;
                }

                $value = $change['value'] ?? [];
                
                // Get the destination phone number ID (the business's WhatsApp number)
                $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;

                if (!$phoneNumberId) {
                    continue;
                }

                // Check if this phone number belongs to an active connection on our platform
                $connection = WhatsAppConnection::where('phone_number_id', $phoneNumberId)->first();

                if (!$connection) {
                    Log::warning('WhatsApp Webhook received for unknown phone_number_id', [
                        'phone_number_id' => $phoneNumberId,
                    ]);
                    continue; // Ignore safely, don't dispatch job
                }

                Log::info('Connection found, processing messages...');

                // We only care about actual messages for now (ignoring statuses/read receipts)
                if (isset($value['messages']) && is_array($value['messages'])) {
                    foreach ($value['messages'] as $messageData) {
                        // Include the contacts array so the job can resolve the customer name if present
                        $contacts = $value['contacts'] ?? [];
                        Log::info('Dispatching job...');
                        
                        ProcessWhatsAppMessageJob::dispatch(
                            $connection->id,
                            $messageData,
                            $contacts,
                            $payload // pass the full raw payload for raw_payload storage
                        );
                    }
                } else {
                    Log::info('No messages found in payload', ['value' => $value]);
                }
            }
        }

        // Always return 200 OK immediately to Meta
        return response()->json(['status' => 'queued'], 200);
    }
}
