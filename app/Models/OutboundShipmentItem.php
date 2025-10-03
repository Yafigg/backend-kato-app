<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundShipmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'outbound_shipment_id',
        'inventory_id',
        'quantity',
        'unit',
        'weight'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    /**
     * Get the shipment this item belongs to
     */
    public function outboundShipment(): BelongsTo
    {
        return $this->belongsTo(OutboundShipment::class);
    }

    /**
     * Get the inventory item
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'id');
    }
}
