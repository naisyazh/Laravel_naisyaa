<?php

namespace App\Services;

use App\Models\Penjualan;

class DemoManualPaymentService
{
    public function isEnabled(): bool
    {
        return (bool) config('services.payment_demo.enabled', false);
    }

    public function bankDetails(): array
    {
        return [
            'bank_name' => (string) config('services.payment_demo.bank_name', 'Bank Demo'),
            'account_number' => (string) config('services.payment_demo.account_number', '1234567890'),
            'account_name' => (string) config('services.payment_demo.account_name', 'Nama Pemilik Rekening'),
            'note' => (string) config('services.payment_demo.note', 'Transfer demo untuk presentasi tugas.'),
        ];
    }

    public function configurationNotice(): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $details = $this->bankDetails();

        if ($details['bank_name'] === '' || $details['account_number'] === '' || $details['account_name'] === '') {
            return 'Mode transfer demo aktif, tetapi data rekening demo belum lengkap. Isi PAYMENT_DEMO_BANK_NAME, PAYMENT_DEMO_ACCOUNT_NUMBER, dan PAYMENT_DEMO_ACCOUNT_NAME pada file .env.';
        }

        return null;
    }

    public function isManualDemoOrder(Penjualan $penjualan): bool
    {
        return $penjualan->payment_type === 'manual_transfer_demo';
    }

    public function markProcessing(Penjualan $penjualan): Penjualan
    {
        $payload = $penjualan->payment_payload ?? [];
        $payload['customer_confirmed_at'] = now()->toDateTimeString();

        $penjualan->forceFill([
            'payment_status' => 'processing',
            'status_message' => 'Customer sudah menekan tombol bayar demo. Pembayaran sedang diproses dan menunggu konfirmasi admin/vendor.',
            'payment_payload' => $payload,
        ])->save();

        return $penjualan->fresh(['items', 'vendor', 'user']);
    }

    public function markPaid(Penjualan $penjualan): Penjualan
    {
        $payload = $penjualan->payment_payload ?? [];
        $payload['vendor_confirmed_at'] = now()->toDateTimeString();

        $penjualan->forceFill([
            'payment_status' => 'paid',
            'status_message' => 'Pembayaran transfer demo sudah dikonfirmasi admin/vendor.',
            'payment_payload' => $payload,
            'paid_at' => $penjualan->paid_at ?: now(),
            'midtrans_transaction_status' => $penjualan->midtrans_transaction_status ?: 'manual_demo',
        ])->save();

        return $penjualan->fresh(['items', 'vendor', 'user']);
    }
}
