<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'user_id',
        'crop_id',
        'product_name',
        'description',
        'category',
        'subcategory',
        'quantity',
        'unit',
        'price_per_unit',
        'original_price',
        'discount_percentage',
        'rating',
        'review_count',
        'is_featured',
        'harvest_date',
        'estimated_ready_date',
        'packaging_type',
        'delivery_method',
        'season',
        'photos',
        'status',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'harvest_date' => 'date',
            'estimated_ready_date' => 'date',
            'photos' => 'array',
            'quantity' => 'decimal:2',
            'price_per_unit' => 'decimal:2',
            'original_price' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'rating' => 'decimal:2',
            'is_featured' => 'boolean',
            'metadata' => 'array',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
