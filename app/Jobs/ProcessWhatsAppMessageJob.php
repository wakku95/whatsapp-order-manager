<?php

namespace App\Jobs;

use App\Models\WhatsAppConnection;
use App\Services\TenantContext;
use App\Services\WhatsApp\WhatsAppMessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $connectionId,
        public array $messageData,
        public array $contacts,
        public array $rawPayload
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WhatsAppMessageService $messageService): void
    {
        $connection = WhatsAppConnection::with('business')->find($this->connectionId);

        if (!$connection || !$connection->business) {
            Log::error('ProcessWhatsAppMessageJob failed: Connection or Business not found', [
                'connection_id' => $this->connectionId,
            ]);
            return;
        }

        // Extremely important: Explicitly set tenant context for this job
        TenantContext::setTenant($connection->business);

        $fromNumber = $this->messageData['from'] ?? null;
        
        if (!$fromNumber) {
            Log::error('ProcessWhatsAppMessageJob failed: No sender number found');
            return;
        }

        $customerName = null;
        foreach ($this->contacts as $contact) {
            if (($contact['wa_id'] ?? '') === $fromNumber) {
                $customerName = $contact['profile']['name'] ?? null;
                break;
            }
        }

        $messageService->processIncomingMessage(
            $connection,
            $this->normalizePhoneNumber($fromNumber),
            $customerName,
            $this->messageData,
            $this->rawPayload
        );
    }

    /**
     * Strict E.164-style normalization for international consistency.
     * Ensures only digits remain.
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        // Strip everything except digits
        $normalized = preg_replace('/[^0-9]/', '', $phone);
        
        // Remove leading '00' international prefix if mistakenly present in some formats
        if (str_starts_with($normalized, '00')) {
            $normalized = substr($normalized, 2);
        }
        
        return $normalized;
    }
}
