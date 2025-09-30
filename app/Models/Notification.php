<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'status',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function markAsRead()
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    public function markAsUnread()
    {
        $this->update([
            'status' => 'unread',
            'read_at' => null,
        ]);
    }

    public function isUnread()
    {
        return $this->status === 'unread';
    }

    public function isRead()
    {
        return $this->status === 'read';
    }

    // Static methods for creating notifications
    public static function createOrderNotification($userId, $orderId, $type = 'order')
    {
        $titles = [
            'order' => 'Pesanan Baru',
            'payment' => 'Pembayaran Diterima',
            'crop' => 'Update Tanaman',
            'system' => 'Notifikasi Sistem',
            'promotion' => 'Promo Spesial',
        ];

        $messages = [
            'order' => 'Anda memiliki pesanan baru yang perlu dikonfirmasi.',
            'payment' => 'Pembayaran untuk pesanan telah diterima.',
            'crop' => 'Tanaman Anda memerlukan perhatian.',
            'system' => 'Ada update sistem yang perlu Anda ketahui.',
            'promotion' => 'Ada promo menarik untuk Anda!',
        ];

        return self::create([
            'user_id' => $userId,
            'title' => $titles[$type] ?? 'Notifikasi',
            'message' => $messages[$type] ?? 'Anda memiliki notifikasi baru.',
            'type' => $type,
            'data' => ['order_id' => $orderId],
        ]);
    }
}
