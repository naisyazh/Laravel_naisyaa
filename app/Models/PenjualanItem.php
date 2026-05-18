<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenjualanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'penjualan_id',
        'barang_id',
        'nama_barang',
        'harga',
        'jumlah',
        'subtotal',
    ];

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id_barang');
    }
}
