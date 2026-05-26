<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NfcAbsensi extends Model
{
    use HasFactory;

    protected $table = 'nfc_absensi';

    protected $fillable = [
        'nfc_kartu_id',
        'serial_number',
        'mata_kuliah',
        'status',
        'waktu_absen',
        'keterangan',
    ];

    protected $casts = [
        'waktu_absen' => 'datetime',
    ];

    /**
     * Relasi ke kartu NFC
     */
    public function kartu()
    {
        return $this->belongsTo(NfcKartu::class, 'nfc_kartu_id');
    }

    /**
     * Badge class untuk status
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'hadir'         => 'bg-success',
            'tidak_dikenal' => 'bg-danger',
            default         => 'bg-secondary',
        };
    }

    /**
     * Label status
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'hadir'         => 'HADIR',
            'tidak_dikenal' => 'TIDAK DIKENAL',
            default         => '-',
        };
    }
}
