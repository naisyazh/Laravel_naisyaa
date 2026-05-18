<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Buku;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategoris';
    protected $primaryKey = 'idkategori';
    public $timestamps = false;

    protected $fillable = ['nama_kategori'];

    public function buku()
    {
        return $this->hasMany(
            Buku::class,
            'idkategori',   // FK di tabel buku
            'idkategori'     // PK di tabel kategori
        );
    }
}
