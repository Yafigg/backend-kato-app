<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'supplier_id',
        'customer_id',
        'inventory_id',
        'quantity',
        'unit_price',
        'total_price',
        'status',
        'requested_delivery_date',
        'notes',
        'rejection_reason',
        'tracking_number',
        'approved_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_delivery_date' => 'date',
            'approved_at' => 'datetime',
            'delivered_at' => 'datetime',
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isInProduction()
    {
        return $this->status === 'in_production';
    }

    public function isReadyForDelivery()
    {
        return $this->status === 'ready_for_delivery';
    }

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }
}
