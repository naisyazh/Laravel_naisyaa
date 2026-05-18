<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Kategori;

class Buku extends Model
{
    use HasFactory;

    protected $table = 'bukus';
    protected $primaryKey = 'idbuku';
    public $timestamps = false;

    protected $fillable = [
        'kode',
        'judul',
        'pengarang',
        'idkategori'
    ];

    public function kategori()
    {
        return $this->belongsTo(
            Kategori::class,
            'idkategori',   // FK di tabel buku
            'idkategori'    // PK di tabel kategori
        );
    }
}
