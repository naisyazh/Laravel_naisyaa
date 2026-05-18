<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class MidtransNotificationController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtransService,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        try {
            $penjualan = $this->midtransService->handleNotification($request->all());
        } catch (RuntimeException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi Midtrans berhasil diproses.',
            'data' => [
                'nomor_transaksi' => $penjualan->nomor_transaksi,
                'payment_status' => $penjualan->payment_status,
            ],
        ]);
    }
}
