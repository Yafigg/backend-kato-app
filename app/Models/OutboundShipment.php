<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutboundShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_code',
        'customer_name',
        'customer_address',
        'customer_phone',
        'delivery_method',
        'estimated_delivery_date',
        'status',
        'notes',
        'total_items',
        'total_weight',
        'shipped_at',
        'delivered_at',
        'user_id'
    ];

    protected $casts = [
        'estimated_delivery_date' => 'date',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Get the user who created this shipment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the inventory items in this shipment
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(OutboundShipmentItem::class);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'ready_for_pickup' => 'orange',
            'in_transit' => 'blue',
            'delivered' => 'green',
            'cancelled' => 'red',
            default => 'grey'
        };
    }

    /**
     * Get status text for UI
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'ready_for_pickup' => 'Siap Diambil',
            'in_transit' => 'Dalam Perjalanan',
            'delivered' => 'Terkirim',
            'cancelled' => 'Dibatalkan',
            default => 'Tidak Diketahui'
        };
    }
}
