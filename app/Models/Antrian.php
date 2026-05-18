<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Antrian extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_antrian',
        'nama',
        'status',
        'ruangan',
        'waktu_daftar',
        'waktu_dipanggil',
    ];

    protected $casts = [
        'waktu_daftar' => 'datetime',
        'waktu_dipanggil' => 'datetime',
    ];

    /**
     * Generate nomor antrian berikutnya
     */
    public static function generateNomorAntrian(): int
    {
        $lastAntrian = self::whereDate('created_at', today())->orderBy('nomor_antrian', 'desc')->first();
        
        if (!$lastAntrian) {
            return 1; // Mulai dari 1 setiap hari
        }
        
        return $lastAntrian->nomor_antrian + 1;
    }

    /**
     * Get antrian yang sedang menunggu
     */
    public static function getMenunggu()
    {
        return self::where('status', 'menunggu')
            ->orderBy('nomor_antrian', 'asc')
            ->get();
    }

    /**
     * Get antrian yang terlewat
     */
    public static function getTerlewat()
    {
        return self::where('status', 'terlewat')
            ->orderBy('nomor_antrian', 'asc')
            ->get();
    }

    /**
     * Get antrian yang sedang dipanggil
     */
    public static function getDipanggil()
    {
        return self::where('status', 'dipanggil')
            ->orderBy('waktu_dipanggil', 'desc')
            ->first();
    }

    /**
     * Check if status is menunggu
     */
    public function isMenunggu(): bool
    {
        return $this->status === 'menunggu';
    }

    /**
     * Check if status is dipanggil
     */
    public function isDipanggil(): bool
    {
        return $this->status === 'dipanggil';
    }

    /**
     * Check if status is terlewat
     */
    public function isTerlewat(): bool
    {
        return $this->status === 'terlewat';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'menunggu' => 'badge-warning',
            'dipanggil' => 'badge-success',
            'selesai' => 'badge-secondary',
            'terlewat' => 'badge-danger',
            default => 'badge-light',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'menunggu' => 'MENUNGGU',
            'dipanggil' => 'DIPANGGIL',
            'selesai' => 'SELESAI',
            'terlewat' => 'TERLEWAT',
            default => 'UNKNOWN',
        };
    }
}
