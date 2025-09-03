<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'bank_account',
        'user_type',
        'management_subrole',
        'is_verified',
        'verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'verified_at' => 'datetime',
        ];
    }

    // Relationships
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function supplierOrders()
    {
        return $this->hasMany(Order::class, 'supplier_id');
    }

    public function customerOrders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    // Scopes
    public function scopePetani($query)
    {
        return $query->where('user_type', 'petani');
    }

    public function scopeManagement($query)
    {
        return $query->where('user_type', 'management');
    }

    public function scopeCustomer($query)
    {
        return $query->where('user_type', 'customer');
    }

    public function scopeAdmin($query)
    {
        return $query->where('user_type', 'admin');
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }

    public function isPetani()
    {
        return $this->user_type === 'petani';
    }

    public function isManagement()
    {
        return $this->user_type === 'management';
    }

    public function isCustomer()
    {
        return $this->user_type === 'customer';
    }

    public function canSelfRegister()
    {
        return $this->user_type === 'customer';
    }
}
