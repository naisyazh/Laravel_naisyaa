<?php

namespace App\Services;

use App\Models\Penjualan;

class PaymentInstructionService
{
    public function build(Penjualan $penjualan): array
    {
        $payload = $penjualan->payment_payload ?? [];
        $instructions = [];

        if (($payload['payment_type'] ?? $penjualan->payment_type) === 'manual_transfer_demo') {
            $bankDetails = $payload['bank_details'] ?? [];

            if (! empty($bankDetails['bank_name'])) {
                $instructions[] = [
                    'label' => 'Bank',
                    'value' => $bankDetails['bank_name'],
                ];
            }

            if (! empty($bankDetails['account_number'])) {
                $instructions[] = [
                    'label' => 'Nomor Rekening',
                    'value' => $bankDetails['account_number'],
                ];
            }

            if (! empty($bankDetails['account_name'])) {
                $instructions[] = [
                    'label' => 'Atas Nama',
                    'value' => $bankDetails['account_name'],
                ];
            }

            if (! empty($bankDetails['note'])) {
                $instructions[] = [
                    'label' => 'Catatan Demo',
                    'value' => $bankDetails['note'],
                ];
            }
        }

        if (($payload['payment_type'] ?? $penjualan->payment_type) === 'bank_transfer') {
            if (! empty($payload['va_numbers'][0]['bank']) && ! empty($payload['va_numbers'][0]['va_number'])) {
                $instructions[] = [
                    'label' => 'Virtual Account',
                    'value' => strtoupper($payload['va_numbers'][0]['bank']) . ' - ' . $payload['va_numbers'][0]['va_number'],
                ];
            }

            if (! empty($payload['permata_va_number'])) {
                $instructions[] = [
                    'label' => 'Permata VA',
                    'value' => $payload['permata_va_number'],
                ];
            }

            if (! empty($payload['bill_key']) && ! empty($payload['biller_code'])) {
                $instructions[] = [
                    'label' => 'Mandiri Bill Key',
                    'value' => $payload['bill_key'],
                ];
                $instructions[] = [
                    'label' => 'Mandiri Biller Code',
                    'value' => $payload['biller_code'],
                ];
            }
        }

        if (($payload['payment_type'] ?? $penjualan->payment_type) === 'qris' && ! empty($payload['actions'])) {
            foreach ($payload['actions'] as $action) {
                if (($action['name'] ?? null) === 'generate-qr-code' && ! empty($action['url'])) {
                    $instructions[] = [
                        'label' => 'Link QRIS',
                        'value' => $action['url'],
                        'is_url' => true,
                    ];
                }
            }
        }

        return $instructions;
    }
}
