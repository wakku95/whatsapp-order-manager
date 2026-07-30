<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'business_id',
        'whatsapp_connection_id',
        'customer_id',
        'provider_message_id',
        'direction',
        'message_type',
        'content',
        'status',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
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
