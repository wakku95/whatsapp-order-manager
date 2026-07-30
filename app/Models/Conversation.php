<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'business_id',
        'whatsapp_connection_id',
        'customer_id',
        'current_state',
        'context_data',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'context_data' => 'array',
            'last_message_at' => 'datetime',
        ];
    }

    public function whatsappConnection(): BelongsTo
    {
        return $this->belongsTo(WhatsAppConnection::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
