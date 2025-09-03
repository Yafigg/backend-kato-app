<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'stage',
        'status',
        'temperature',
        'humidity',
        'notes',
        'quality_metrics',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'quality_metrics' => 'array',
            'temperature' => 'decimal:2',
            'humidity' => 'decimal:2',
        ];
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopeByStage($query, $stage)
    {
        return $query->where('stage', $stage);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function getStageDisplayName()
    {
        $stages = [
            'gudang_in' => 'Gudang Masuk',
            'sorting' => 'Sorting',
            'grading' => 'Grading',
            'drying' => 'Pengeringan',
            'packaging' => 'Pengemasan',
            'gudang_out' => 'Gudang Keluar',
            'quality_check' => 'Quality Check',
        ];

        return $stages[$this->stage] ?? $this->stage;
    }
}
