<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Crop extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'planting_date',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'planting_date' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the crop.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the age of the crop in days.
     */
    public function getAgeInDaysAttribute(): int
    {
        return $this->planting_date->diffInDays(now());
    }

    /**
     * Get the age of the crop in months.
     */
    public function getAgeInMonthsAttribute(): float
    {
        return round($this->planting_date->diffInDays(now()) / 30, 1);
    }

    /**
     * Scope a query to only include crops with specific status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include crops for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
