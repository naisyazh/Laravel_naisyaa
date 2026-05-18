<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganToko extends Model
{
    use HasFactory;

    protected $fillable = [
        'toko_id',
        'sales_id',
        'toko_latitude',
        'toko_longitude',
        'toko_accuracy',
        'sales_latitude',
        'sales_longitude',
        'sales_accuracy',
        'jarak_meter',
        'threshold_meter',
        'status',
        'keterangan',
        'waktu_kunjungan',
    ];

    protected $casts = [
        'toko_latitude' => 'decimal:8',
        'toko_longitude' => 'decimal:8',
        'toko_accuracy' => 'decimal:2',
        'sales_latitude' => 'decimal:8',
        'sales_longitude' => 'decimal:8',
        'sales_accuracy' => 'decimal:2',
        'jarak_meter' => 'decimal:2',
        'threshold_meter' => 'decimal:2',
        'waktu_kunjungan' => 'datetime',
    ];

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }

    public function sales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    /**
     * Check if kunjungan diterima
     */
    public function isDiterima(): bool
    {
        return $this->status === 'diterima';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return $this->isDiterima() ? 'badge-success' : 'badge-danger';
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return $this->isDiterima() ? 'DITERIMA ✓' : 'DITOLAK ✗';
    }
}
