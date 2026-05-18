<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $table = 'customer';

    protected $fillable = [
        'kode_customer',
        'nama',
        'email',
        'telepon',
        'alamat',
        'capture_mode',
        'photo_blob',
        'photo_blob_mime',
        'photo_path',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function captureModeLabel(): string
    {
        return $this->capture_mode === 'blob'
            ? 'Database Blob'
            : 'File Path';
    }

    public function previewSource(): ?string
    {
        if ($this->capture_mode === 'blob') {
            return $this->blobDataUri();
        }

        return filled($this->photo_path) ? asset($this->photo_path) : null;
    }

    public function blobDataUri(): ?string
    {
        if ($this->photo_blob === null || $this->photo_blob === '') {
            return null;
        }

        $binary = is_resource($this->photo_blob)
            ? stream_get_contents($this->photo_blob)
            : $this->photo_blob;

        if (! is_string($binary) || $binary === '') {
            return null;
        }

        if (str_starts_with($binary, '\\x')) {
            $hexPayload = substr($binary, 2);
            $decodedBinary = hex2bin($hexPayload);

            if ($decodedBinary !== false) {
                $binary = $decodedBinary;
            }
        }

        $mime = $this->photo_blob_mime ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode($binary);
    }

    public static function generateCode(): string
    {
        $lastNumber = static::query()
            ->pluck('kode_customer')
            ->map(function (?string $kodeCustomer) {
                return (int) preg_replace('/\D/', '', $kodeCustomer ?? '');
            })
            ->max() ?? 0;

        do {
            $lastNumber++;
            $candidate = 'CST' . str_pad((string) $lastNumber, 5, '0', STR_PAD_LEFT);
        } while (static::query()->where('kode_customer', $candidate)->exists());

        return $candidate;
    }
}
