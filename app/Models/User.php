<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_guest',
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
            'is_guest' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Barang::class, 'vendor_id');
    }

    public function customerOrders(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'user_id');
    }

    public function vendorOrders(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'vendor_id');
    }

    public function isVendor(): bool
    {
        return $this->role === 'admin';
    }
}
