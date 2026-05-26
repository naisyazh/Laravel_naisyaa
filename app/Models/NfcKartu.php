<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NfcKartu extends Model
{
    use HasFactory;

    protected $table = 'nfc_kartu';

    protected $fillable = [
        'serial_number',
        'nama_mahasiswa',
        'nim',
        'program_studi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke absensi
     */
    public function absensi()
    {
        return $this->hasMany(NfcAbsensi::class, 'nfc_kartu_id');
    }

    /**
     * Cari kartu berdasarkan serial number
     */
    public static function findBySerial(string $serial): ?self
    {
        return self::where('serial_number', $serial)
            ->where('is_active', true)
            ->first();
    }
}
