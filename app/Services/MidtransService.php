<?php

namespace App\Services;

use App\Models\Penjualan;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransService
{
    public function createSnapTransaction(Penjualan $penjualan): array
    {
        $this->ensureConfigured();
        $enabledPayments = $this->enabledPayments();
        $payload = [
            'transaction_details' => [
                'order_id' => $penjualan->nomor_transaksi,
                'gross_amount' => (int) $penjualan->total,
            ],
            'item_details' => $penjualan->items->map(function ($item) {
                return [
                    'id' => $item->barang_id,
                    'price' => (int) $item->harga,
                    'quantity' => (int) $item->jumlah,
                    'name' => Str::limit($item->nama_barang, 50, ''),
                ];
            })->values()->all(),
            'customer_details' => [
                'first_name' => $penjualan->customer_name ?: 'Guest Customer',
                'email' => $penjualan->customer_email ?: Str::lower($penjualan->nomor_transaksi) . '@example.test',
                'phone' => $penjualan->customer_phone ?: '081234567890',
            ],
        ];

        if ($enabledPayments !== []) {
            $payload['enabled_payments'] = $enabledPayments;
        }

        if ($this->shouldEnableCreditCard3ds($enabledPayments)) {
            $payload['credit_card'] = [
                'secure' => true,
            ];
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withBasicAuth($this->serverKey(), '')
                ->post($this->apiBaseUrl() . '/snap/v1/transactions', $payload)
                ->throw()
                ->json();
        } catch (ConnectionException) {
            throw new RuntimeException('Aplikasi gagal terhubung ke Midtrans. Pastikan internet aktif lalu coba lagi.');
        } catch (RequestException $exception) {
            throw new RuntimeException($this->requestExceptionMessage($exception, 'Midtrans menolak pembuatan transaksi.'));
        }

        if (! isset($response['token'], $response['redirect_url'])) {
            throw new RuntimeException('Midtrans tidak mengembalikan Snap token yang valid.');
        }

        return $response;
    }

    public function syncTransactionStatus(Penjualan $penjualan): Penjualan
    {
        $this->ensureConfigured();

        try {
            $payload = Http::acceptJson()
                ->withBasicAuth($this->serverKey(), '')
                ->get($this->apiBaseUrl() . '/v2/' . $penjualan->nomor_transaksi . '/status')
                ->throw()
                ->json();
        } catch (ConnectionException) {
            throw new RuntimeException('Aplikasi gagal terhubung ke Midtrans saat sinkronisasi status.');
        } catch (RequestException $exception) {
            if (in_array($exception->response?->status(), [404, 406], true)) {
                throw new RuntimeException('Status pembayaran belum tersedia di Midtrans. Silakan pilih metode pembayaran pada popup Snap terlebih dahulu.');
            }

            throw new RuntimeException($this->requestExceptionMessage($exception, 'Midtrans gagal mengirim status pembayaran terbaru.'));
        }

        return $this->persistTransactionStatus($payload);
    }

    public function handleNotification(array $payload): Penjualan
    {
        $this->ensureConfigured();
        $this->assertValidSignature($payload);

        return $this->persistTransactionStatus($payload);
    }

    public function recordClientResult(array $payload): Penjualan
    {
        $this->ensureConfigured();

        return $this->persistTransactionStatus($payload);
    }

    public function snapScriptUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    public function configurationNotice(): ?string
    {
        if ($this->serverKey() === '' || $this->clientKey() === '') {
            return 'Konfigurasi Midtrans belum lengkap. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY terlebih dahulu.';
        }

        if (! $this->isProduction()) {
            $usingProductionKey = ! Str::startsWith($this->serverKey(), 'SB-Mid-server-')
                || ! Str::startsWith($this->clientKey(), 'SB-Mid-client-');

            if ($usingProductionKey) {
                return 'Aplikasi sedang mode sandbox, tetapi key yang dipakai terlihat key production. Ganti ke SB-Mid-server dan SB-Mid-client, lalu jalankan php artisan config:clear agar pembayaran sandbox bisa sampai berhasil.';
            }
        }

        if ($this->isProduction()) {
            $usingSandboxKey = Str::startsWith($this->serverKey(), 'SB-Mid-server-')
                || Str::startsWith($this->clientKey(), 'SB-Mid-client-');

            if ($usingSandboxKey) {
                return 'Aplikasi sedang mode production, tetapi key yang dipakai terlihat key sandbox. Samakan environment dan key Midtrans terlebih dahulu, lalu jalankan php artisan config:clear.';
            }
        }

        return null;
    }

    private function persistTransactionStatus(array $payload): Penjualan
    {
        $orderId = (string) ($payload['order_id'] ?? '');

        if ($orderId === '') {
            throw new RuntimeException('Payload Midtrans tidak memiliki order_id.');
        }

        $penjualan = Penjualan::query()
            ->where('nomor_transaksi', $orderId)
            ->firstOrFail();

        $paymentStatus = $this->normalizePaymentStatus(
            (string) ($payload['transaction_status'] ?? ''),
            (string) ($payload['fraud_status'] ?? '')
        );

        $paidAt = $penjualan->paid_at;

        if ($paymentStatus === 'paid' && ! $paidAt) {
            $paidAt = isset($payload['settlement_time'])
                ? Carbon::parse($payload['settlement_time'])
                : now();
        }

        $penjualan->forceFill([
            'payment_status' => $paymentStatus,
            'payment_type' => $payload['payment_type'] ?? $penjualan->payment_type,
            'midtrans_transaction_status' => $payload['transaction_status'] ?? $penjualan->midtrans_transaction_status,
            'midtrans_transaction_id' => $payload['transaction_id'] ?? $penjualan->midtrans_transaction_id,
            'fraud_status' => $payload['fraud_status'] ?? $penjualan->fraud_status,
            'status_message' => $payload['status_message'] ?? $penjualan->status_message,
            'payment_payload' => $payload,
            'paid_at' => $paidAt,
        ])->save();

        return $penjualan->fresh(['items', 'vendor', 'user']);
    }

    private function normalizePaymentStatus(string $transactionStatus, string $fraudStatus): string
    {
        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'challenge' ? 'pending' : 'paid',
            'settlement' => 'paid',
            'pending', 'authorize' => 'pending',
            'deny', 'failure' => 'failed',
            'cancel' => 'cancelled',
            'expire' => 'expired',
            'refund', 'partial_refund', 'chargeback', 'partial_chargeback' => 'refunded',
            default => 'pending',
        };
    }

    private function assertValidSignature(array $payload): void
    {
        $receivedSignature = (string) ($payload['signature_key'] ?? '');

        if ($receivedSignature === '') {
            throw new RuntimeException('Signature Midtrans tidak ditemukan.');
        }

        $expectedSignature = hash(
            'sha512',
            (string) ($payload['order_id'] ?? '')
                . (string) ($payload['status_code'] ?? '')
                . (string) ($payload['gross_amount'] ?? '')
                . $this->serverKey()
        );

        if (! hash_equals($expectedSignature, $receivedSignature)) {
            throw new RuntimeException('Signature Midtrans tidak valid.');
        }
    }

    private function enabledPayments(): array
    {
        return collect(explode(',', (string) config('services.midtrans.enabled_payments', '')))
            ->map(fn($paymentMethod) => Str::lower(trim($paymentMethod)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function shouldEnableCreditCard3ds(array $enabledPayments): bool
    {
        return $enabledPayments === [] || in_array('credit_card', $enabledPayments, true);
    }

    private function requestExceptionMessage(RequestException $exception, string $fallback): string
    {
        $response = $exception->response;

        if (! $response) {
            return $fallback;
        }

        $message = $response->json('status_message')
            ?? $response->json('error_messages.0')
            ?? $response->json('message');

        return $message ?: $fallback;
    }

    private function ensureConfigured(): void
    {
        if ($this->serverKey() === '' || $this->clientKey() === '') {
            throw new RuntimeException('Konfigurasi Midtrans belum lengkap. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY pada file .env terlebih dahulu.');
        }
    }

    private function apiBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
    }

    private function isProduction(): bool
    {
        return (bool) config('services.midtrans.is_production', false);
    }

    private function serverKey(): string
    {
        return (string) config('services.midtrans.server_key', '');
    }

    private function clientKey(): string
    {
        return (string) config('services.midtrans.client_key', '');
    }
}
