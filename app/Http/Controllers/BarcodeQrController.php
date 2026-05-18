<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Penjualan;
use App\Services\DemoManualPaymentService;
use App\Services\PaymentInstructionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BarcodeQrController extends Controller
{
    public function __construct(
        private readonly PaymentInstructionService $paymentInstructionService,
        private readonly DemoManualPaymentService $demoManualPaymentService,
    ) {
    }

    public function barangScanner(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('scanner.barang.index', [
            'beepAudioUrl' => asset('assets/audio/scanner-beep.mpeg'),
        ]);
    }

    public function showBarang(Request $request, Barang $barang): JsonResponse
    {
        $this->authorizeAdmin($request);

        if ($barang->vendor_id !== null && (int) $barang->vendor_id !== (int) $request->user()->id) {
            abort(404);
        }

        return $this->successResponse('Data barang berhasil dimuat.', [
            'id_barang' => $barang->id_barang,
            'nama_barang' => $barang->nama,
            'harga_barang' => (int) $barang->harga,
            'is_active' => (bool) $barang->is_active,
        ]);
    }

    public function showPesanan(Request $request, Penjualan $penjualan): JsonResponse|View
    {
        $this->authorizeOrderAccess($request, $penjualan);

        $penjualan->loadMissing(['items', 'vendor', 'user']);

        if ($request->expectsJson()) {
            return $this->buildOrderJsonResponse($penjualan);
        }

        $isCustomerView = $request->user()?->role === 'user';
        $isManualDemoOrder = $this->demoManualPaymentService->isManualDemoOrder($penjualan);

        return view('scanner.orders.show', [
            'penjualan' => $penjualan,
            'paymentInstructions' => $this->paymentInstructionService->build($penjualan),
            'isManualDemoOrder' => $isManualDemoOrder,
            'showConfirmDemoPayment' => $isCustomerView
                && $isManualDemoOrder
                && $penjualan->payment_status === 'pending',
            'backUrl' => $isCustomerView
                ? ($penjualan->isPaid() ? route('toko-buku.orders.paid') : route('toko-buku.index'))
                : route('vendor.orders.index'),
            'backLabel' => $isCustomerView
                ? ($penjualan->isPaid() ? 'Kembali ke Riwayat Lunas' : 'Kembali ke Checkout')
                : 'Kembali ke Transaksi Vendor',
            'qrPayload' => $this->buildOrderPayload($penjualan),
        ]);
    }

    public function showPesananJson(Request $request, Penjualan $penjualan): JsonResponse
    {
        $this->authorizeOrderAccess($request, $penjualan);

        $penjualan->loadMissing(['items', 'vendor', 'user']);

        return $this->buildOrderJsonResponse($penjualan);
    }

    public function vendorScanner(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('scanner.vendor.index', [
            'beepAudioUrl' => asset('assets/audio/scanner-beep.mpeg'),
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }

    private function authorizeOrderAccess(Request $request, Penjualan $penjualan): void
    {
        $user = $request->user();

        abort_unless($user, 403);

        if ($user->role === 'user') {
            abort_unless((int) $penjualan->user_id === (int) $user->id, 403);

            return;
        }

        if ($user->role === 'admin') {
            $belongsToVendor = $penjualan->vendor_id === null || (int) $penjualan->vendor_id === (int) $user->id;

            abort_unless($belongsToVendor, 404);

            return;
        }

        abort(403);
    }

    private function buildOrderPayload(Penjualan $penjualan): array
    {
        return [
            'id_pesanan' => $penjualan->nomor_transaksi,
            'status_pembayaran' => $penjualan->payment_status,
            'status_pembayaran_label' => $penjualan->paymentStatusLabel(),
            'status_message' => $penjualan->status_message,
            'total' => (int) $penjualan->total,
            'customer_name' => $penjualan->customer_name ?? $penjualan->user?->name ?? '-',
            'customer_email' => $penjualan->customer_email,
            'vendor_name' => $penjualan->vendor?->name,
            'paid_at' => optional($penjualan->paid_at)?->toIso8601String(),
            'daftar_menu' => $penjualan->items->map(function ($item) {
                return [
                    'id_barang' => $item->barang_id,
                    'nama_barang' => $item->nama_barang,
                    'harga_barang' => (int) $item->harga,
                    'jumlah' => (int) $item->jumlah,
                    'subtotal' => (int) $item->subtotal,
                ];
            })->values()->all(),
        ];
    }

    private function buildOrderJsonResponse(Penjualan $penjualan): JsonResponse
    {
        return $this->successResponse('Data pesanan berhasil dimuat.', $this->buildOrderPayload($penjualan));
    }

    private function successResponse(string $message, mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'code' => $status,
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
