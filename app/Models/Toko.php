<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Toko extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode',
        'nama_toko',
        'alamat',
        'latitude',
        'longitude',
        'accuracy',
        'vendor_id',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function kunjungans(): HasMany
    {
        return $this->hasMany(KunjunganToko::class);
    }

    /**
     * Generate barcode ID untuk toko baru
     */
    public static function generateBarcodeId(): string
    {
        $lastToko = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastToko ? ((int) substr($lastToko->barcode, 3)) + 1 : 1;
        
        return 'TKO' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
