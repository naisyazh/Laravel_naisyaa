<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Penjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_transaksi',
        'user_id',
        'vendor_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'total',
        'payment_status',
        'payment_type',
        'midtrans_transaction_status',
        'midtrans_transaction_id',
        'fraud_status',
        'snap_token',
        'snap_redirect_url',
        'status_message',
        'payment_payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PenjualanItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Lunas',
            'pending' => 'Menunggu Pembayaran',
            'processing' => 'Sedang Diproses',
            'failed' => 'Gagal',
            'expired' => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
            'refunded' => 'Refund',
            default => 'Belum Diketahui',
        };
    }

    public function paymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            'paid' => 'badge-gradient-success',
            'pending' => 'badge-gradient-warning',
            'processing' => 'badge-gradient-info',
            'failed', 'cancelled', 'expired' => 'badge-gradient-danger',
            'refunded' => 'badge-gradient-info',
            default => 'badge-gradient-secondary',
        };
    }

    public static function generateTransactionNumber(): string
    {
        do {
            $number = 'TRX-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
        } while (static::query()->where('nomor_transaksi', $number)->exists());

        return $number;
    }
}
