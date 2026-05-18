<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Services\DemoManualPaymentService;
use App\Services\MidtransService;
use App\Services\PaymentInstructionService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class VendorOrderController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtransService,
        private readonly PaymentInstructionService $paymentInstructionService,
        private readonly DemoManualPaymentService $demoManualPaymentService,
    ) {
    }

    public function index(Request $request): View
    {
        $statusFilter = $request->string('status')->toString() ?: 'paid';
        $allowedFilters = ['all', 'paid', 'pending', 'processing', 'failed', 'expired', 'cancelled', 'refunded'];

        if (! in_array($statusFilter, $allowedFilters, true)) {
            $statusFilter = 'paid';
        }

        $baseQuery = Penjualan::query()
            ->where('vendor_id', $request->user()->id);

        $orders = (clone $baseQuery)
            ->with(['items', 'user'])
            ->when($statusFilter !== 'all', function ($query) use ($statusFilter) {
                $query->where('payment_status', $statusFilter);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('vendor.orders.index', [
            'orders' => $orders,
            'statusFilter' => $statusFilter,
            'stats' => [
                'paid' => (clone $baseQuery)->where('payment_status', 'paid')->count(),
                'pending' => (clone $baseQuery)->where('payment_status', 'pending')->count(),
                'processing' => (clone $baseQuery)->where('payment_status', 'processing')->count(),
                'failed' => (clone $baseQuery)->whereIn('payment_status', ['failed', 'cancelled', 'expired'])->count(),
                'revenue' => (int) (clone $baseQuery)->where('payment_status', 'paid')->sum('total'),
            ],
        ]);
    }

    public function show(Request $request, Penjualan $penjualan): View
    {
        abort_unless($penjualan->vendor_id === $request->user()->id, 403);

        $penjualan->loadMissing(['items', 'vendor', 'user']);

        return view('vendor.orders.show', [
            'penjualan' => $penjualan,
            'isManualDemoOrder' => $this->demoManualPaymentService->isManualDemoOrder($penjualan),
            'paymentInstructions' => $this->paymentInstructionService->build($penjualan),
        ]);
    }

    public function refreshStatus(Request $request, Penjualan $penjualan)
    {
        abort_unless($penjualan->vendor_id === $request->user()->id, 403);

        if ($this->demoManualPaymentService->isManualDemoOrder($penjualan)) {
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Status pembayaran demo berhasil dimuat ulang.',
                'data' => [
                    'nomor_transaksi' => $penjualan->nomor_transaksi,
                    'payment_status' => $penjualan->payment_status,
                    'payment_status_label' => $penjualan->paymentStatusLabel(),
                    'paid_at' => optional($penjualan->paid_at)?->format('d M Y H:i'),
                    'status_message' => $penjualan->status_message,
                ],
            ]);
        }

        try {
            $penjualan = $this->midtransService->syncTransactionStatus($penjualan);
        } catch (RuntimeException $exception) {
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Status pembayaran berhasil diperbarui.',
            'data' => [
                'nomor_transaksi' => $penjualan->nomor_transaksi,
                'payment_status' => $penjualan->payment_status,
                'payment_status_label' => $penjualan->paymentStatusLabel(),
                'paid_at' => optional($penjualan->paid_at)?->format('d M Y H:i'),
                'status_message' => $penjualan->status_message,
            ],
        ]);
    }

    public function markPaid(Request $request, Penjualan $penjualan)
    {
        abort_unless($penjualan->vendor_id === $request->user()->id, 403);

        if (! $this->demoManualPaymentService->isManualDemoOrder($penjualan)) {
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'message' => 'Order ini tidak memakai mode transfer demo.',
            ], 422);
        }

        $penjualan = $this->demoManualPaymentService->markPaid($penjualan);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Pembayaran demo berhasil dikonfirmasi lunas.',
            'data' => [
                'nomor_transaksi' => $penjualan->nomor_transaksi,
                'payment_status' => $penjualan->payment_status,
                'payment_status_label' => $penjualan->paymentStatusLabel(),
                'paid_at' => optional($penjualan->paid_at)?->format('d M Y H:i'),
                'status_message' => $penjualan->status_message,
            ],
        ]);
    }

    /**
     * Cetak struk pesanan
     */
    public function cetakStruk(Request $request, Penjualan $penjualan)
    {
        abort_unless($request->user()->role === 'admin', 403);
        abort_unless((int) $penjualan->vendor_id === (int) $request->user()->id, 404);

        $penjualan->loadMissing(['items', 'user', 'vendor']);

        return view('vendor.orders.cetak_struk', [
            'penjualan' => $penjualan,
        ]);
    }
}
