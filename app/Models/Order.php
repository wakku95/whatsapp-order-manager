<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'business_id',
        'customer_id',
        'whatsapp_connection_id',
        'order_number',
        'status',
        'subtotal',
        'delivery_fee',
        'discount',
        'total',
        'cod_amount',
        'cod_status',
        'shipping_address',
        'notes',
        'cancelled_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'cod_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function whatsappConnection(): BelongsTo
    {
        return $this->belongsTo(WhatsAppConnection::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(OrderAudit::class);
    }
}
