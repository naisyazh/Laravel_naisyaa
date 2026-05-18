<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Penjualan;
use App\Services\DemoManualPaymentService;
use App\Services\MidtransService;
use App\Services\PaymentInstructionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class TokoController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtransService,
        private readonly PaymentInstructionService $paymentInstructionService,
        private readonly DemoManualPaymentService $demoManualPaymentService,
    ) {}

    public function index(): View
    {
        $quickBooks = Barang::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        $manualDemoPaymentEnabled = $this->demoManualPaymentService->isEnabled();
        $midtransConfigured = filled(config('services.midtrans.server_key')) && filled(config('services.midtrans.client_key'));
        $enabledPayments = collect(explode(',', (string) config('services.midtrans.enabled_payments', 'qris')))
            ->map(fn (string $paymentMethod) => trim($paymentMethod))
            ->filter()
            ->values();
        $primaryPaymentMethod = $enabledPayments->first() ?: 'qris';
        $paymentMethodLabel = $this->paymentMethodLabel($primaryPaymentMethod);
        $paymentMethodDescription = $this->paymentMethodDescription($primaryPaymentMethod);
        $paymentMethodNotice = $this->paymentMethodNotice($primaryPaymentMethod);
        $paymentButtonLabel = $manualDemoPaymentEnabled
            ? 'Bayar Transfer Demo'
            : 'Bayar dengan ' . $paymentMethodLabel . ' Midtrans';

        return view('toko.index', [
            'quickBooks' => $quickBooks,
            'quickBookCatalog' => $quickBooks->map(function (Barang $book) {
                return [
                    'kode' => $book->id_barang,
                    'nama' => $book->nama,
                    'harga' => (int) $book->harga,
                ];
            })->values(),
            'manualDemoPaymentEnabled' => $manualDemoPaymentEnabled,
            'manualDemoPaymentNotice' => $this->demoManualPaymentService->configurationNotice(),
            'manualDemoBankDetails' => $this->demoManualPaymentService->bankDetails(),
            'paymentGatewayReady' => $manualDemoPaymentEnabled || $midtransConfigured,
            'paymentButtonLabel' => $paymentButtonLabel,
            'midtransConfigured' => $midtransConfigured,
            'midtransConfigurationNotice' => $this->midtransService->configurationNotice(),
            'midtransClientKey' => config('services.midtrans.client_key'),
            'midtransSnapScriptUrl' => $this->midtransService->snapScriptUrl(),
            'midtransPaymentMethodLabel' => $paymentMethodLabel,
            'midtransPaymentMethodDescription' => $paymentMethodDescription,
            'midtransPaymentMethodNotice' => $paymentMethodNotice,
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:8',
        ]);

        $barang = Barang::query()
            ->where('id_barang', strtoupper($validated['kode']))
            ->where('is_active', true)
            ->first();

        if (! $barang) {
            return $this->errorResponse('Buku dengan kode tersebut tidak ditemukan atau sedang tidak aktif.', 404);
        }

        return $this->successResponse('Buku ditemukan.', [
            'kode' => $barang->id_barang,
            'nama' => $barang->nama,
            'harga' => (int) $barang->harga,
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.kode' => 'required|string|max:8',
            'items.*.jumlah' => 'required|integer|min:1|max:99',
        ]);

        $groupedItems = collect($validated['items'])
            ->groupBy('kode')
            ->map(fn($items) => (int) collect($items)->sum('jumlah'));

        $menus = Barang::query()
            ->where('is_active', true)
            ->whereIn('id_barang', $groupedItems->keys())
            ->get()
            ->keyBy('id_barang');

        if ($menus->count() !== $groupedItems->count()) {
            $missingCodes = $groupedItems->keys()->diff($menus->keys())->values()->implode(', ');

            throw ValidationException::withMessages([
                'items' => ["Kode buku tidak valid atau sedang tidak aktif: {$missingCodes}"],
            ]);
        }

        $itemPayloads = [];
        $total = 0;
        $vendorId = $menus->pluck('vendor_id')->filter()->unique()->first();

        foreach ($groupedItems as $kode => $jumlah) {
            $menu = $menus->get($kode);
            $subtotal = (int) $menu->harga * $jumlah;
            $total += $subtotal;

            $itemPayloads[] = [
                'barang_id' => $menu->id_barang,
                'nama_barang' => $menu->nama,
                'harga' => (int) $menu->harga,
                'jumlah' => $jumlah,
                'subtotal' => $subtotal,
            ];
        }

        try {
            $penjualan = DB::transaction(function () use ($request, $itemPayloads, $total, $vendorId) {
                $penjualan = Penjualan::create([
                    'nomor_transaksi' => Penjualan::generateTransactionNumber(),
                    'user_id' => $request->user()->id,
                    'vendor_id' => $vendorId,
                    'customer_name' => $request->user()->name,
                    'customer_email' => $request->user()->email,
                    'customer_phone' => null,
                    'total' => $total,
                    'payment_status' => 'pending',
                    'status_message' => 'Menunggu user menyelesaikan pembayaran buku di Midtrans.',
                ]);

                $penjualan->items()->createMany($itemPayloads);
                $penjualan->load(['items', 'vendor', 'user']);

                if ($this->demoManualPaymentService->isEnabled()) {
                    $penjualan->forceFill([
                        'payment_type' => 'manual_transfer_demo',
                        'midtrans_transaction_status' => 'manual_demo',
                        'status_message' => 'Silakan transfer ke rekening demo lalu klik tombol Saya Sudah Transfer.',
                        'payment_payload' => [
                            'payment_type' => 'manual_transfer_demo',
                            'bank_details' => $this->demoManualPaymentService->bankDetails(),
                        ],
                    ])->save();

                    return $penjualan->fresh(['items', 'vendor', 'user']);
                }

                $snapTransaction = $this->midtransService->createSnapTransaction($penjualan);

                $penjualan->forceFill([
                    'snap_token' => $snapTransaction['token'],
                    'snap_redirect_url' => $snapTransaction['redirect_url'],
                ])->save();

                return $penjualan->fresh(['items', 'vendor', 'user']);
            });
        } catch (RuntimeException $exception) {
            return $this->errorResponse($exception->getMessage(), 422);
        }

        if ($this->demoManualPaymentService->isManualDemoOrder($penjualan)) {
            return $this->successResponse('Instruksi transfer demo berhasil dibuat.', [
                'nomor_transaksi' => $penjualan->nomor_transaksi,
                'total' => (int) $penjualan->total,
                'payment_status' => $penjualan->payment_status,
                'payment_mode' => 'manual_demo',
                'redirect_only' => true,
                'order_url' => route('pesanan.show', $penjualan->nomor_transaksi),
            ], 201);
        }

        return $this->successResponse('Token pembayaran Midtrans berhasil dibuat.', [
            'nomor_transaksi' => $penjualan->nomor_transaksi,
            'total' => (int) $penjualan->total,
            'payment_status' => $penjualan->payment_status,
            'snap_token' => $penjualan->snap_token,
            'redirect_url' => $penjualan->snap_redirect_url,
            'order_url' => route('pesanan.show', $penjualan->nomor_transaksi),
        ], 201);
    }

    public function show(Penjualan $penjualan): View
    {
        abort_unless(request()->user()->role === 'user' && $penjualan->user_id === request()->user()->id, 403);

        $penjualan->loadMissing(['items', 'vendor', 'user']);

        return view('toko.show', [
            'penjualan' => $penjualan,
            'isManualDemoOrder' => $this->demoManualPaymentService->isManualDemoOrder($penjualan),
            'paymentInstructions' => $this->paymentInstructionService->build($penjualan),
        ]);
    }

    public function paidOrders(Request $request): View
    {
        abort_unless($request->user()->role === 'user', 403);

        $baseQuery = Penjualan::query()
            ->where('user_id', $request->user()->id)
            ->where('payment_status', 'paid');

        $orders = (clone $baseQuery)
            ->with(['items', 'vendor'])
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('toko.paid-orders', [
            'orders' => $orders,
            'paidStats' => [
                'count' => (clone $baseQuery)->count(),
                'total' => (int) (clone $baseQuery)->sum('total'),
            ],
        ]);
    }

    public function refreshStatus(Penjualan $penjualan): JsonResponse
    {
        abort_unless($requestUser = request()->user(), 403);
        abort_unless($requestUser->role === 'user' && $penjualan->user_id === $requestUser->id, 403);

        if ($this->demoManualPaymentService->isManualDemoOrder($penjualan)) {
            return $this->successResponse('Status pembayaran demo berhasil dimuat ulang.', [
                'nomor_transaksi' => $penjualan->nomor_transaksi,
                'payment_status' => $penjualan->payment_status,
                'payment_status_label' => $penjualan->paymentStatusLabel(),
                'paid_at' => optional($penjualan->paid_at)?->format('d M Y H:i'),
                'status_message' => $penjualan->status_message,
            ]);
        }

        try {
            $penjualan = $this->midtransService->syncTransactionStatus($penjualan);
        } catch (RuntimeException $exception) {
            return $this->errorResponse($exception->getMessage(), 422);
        }

        return $this->successResponse('Status pembayaran berhasil diperbarui.', [
            'nomor_transaksi' => $penjualan->nomor_transaksi,
            'payment_status' => $penjualan->payment_status,
            'payment_status_label' => $penjualan->paymentStatusLabel(),
            'paid_at' => optional($penjualan->paid_at)?->format('d M Y H:i'),
            'status_message' => $penjualan->status_message,
        ]);
    }

    public function confirmDemoPayment(Penjualan $penjualan): JsonResponse
    {
        abort_unless($requestUser = request()->user(), 403);
        abort_unless($requestUser->role === 'user' && $penjualan->user_id === $requestUser->id, 403);

        if (! $this->demoManualPaymentService->isManualDemoOrder($penjualan)) {
            return $this->errorResponse('Order ini tidak memakai mode transfer demo.', 422);
        }

        if ($penjualan->payment_status === 'paid') {
            return $this->successResponse('Pembayaran sudah berstatus lunas.', [
                'nomor_transaksi' => $penjualan->nomor_transaksi,
                'payment_status' => $penjualan->payment_status,
                'payment_status_label' => $penjualan->paymentStatusLabel(),
                'status_message' => $penjualan->status_message,
            ]);
        }

        $penjualan = $this->demoManualPaymentService->markProcessing($penjualan);

        return $this->successResponse('Pembayaran demo ditandai sedang diproses.', [
            'nomor_transaksi' => $penjualan->nomor_transaksi,
            'payment_status' => $penjualan->payment_status,
            'payment_status_label' => $penjualan->paymentStatusLabel(),
            'status_message' => $penjualan->status_message,
        ]);
    }

    public function recordSnapResult(Request $request, Penjualan $penjualan): JsonResponse
    {
        abort_unless($requestUser = $request->user(), 403);
        abort_unless($requestUser->role === 'user' && $penjualan->user_id === $requestUser->id, 403);

        $validated = $request->validate([
            'order_id' => 'required|string',
            'transaction_status' => 'nullable|string',
            'payment_type' => 'nullable|string',
            'transaction_id' => 'nullable|string',
            'fraud_status' => 'nullable|string',
            'status_message' => 'nullable|string',
            'gross_amount' => 'nullable|string',
            'settlement_time' => 'nullable|string',
        ]);

        if ($validated['order_id'] !== $penjualan->nomor_transaksi) {
            return $this->errorResponse('Nomor transaksi callback tidak sesuai dengan order yang sedang dibuka.', 422);
        }

        try {
            $penjualan = $this->midtransService->recordClientResult($validated);
        } catch (RuntimeException $exception) {
            return $this->errorResponse($exception->getMessage(), 422);
        }

        return $this->successResponse('Hasil pembayaran Snap berhasil direkam.', [
            'nomor_transaksi' => $penjualan->nomor_transaksi,
            'payment_status' => $penjualan->payment_status,
            'payment_status_label' => $penjualan->paymentStatusLabel(),
            'status_message' => $penjualan->status_message,
        ]);
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

    private function errorResponse(string $message, int $status = 422, array $errors = []): JsonResponse
    {
        return response()->json([
            'code' => $status,
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    private function paymentMethodLabel(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'credit_card' => 'Kartu Visa/Mastercard',
            'qris' => 'QRIS',
            default => Str::headline(str_replace('_', ' ', $paymentMethod)),
        };
    }

    private function paymentMethodDescription(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'credit_card' => 'via kartu sandbox Midtrans seperti Visa dummy untuk demo.',
            'qris' => 'via QRIS Midtrans.',
            default => 'via ' . $this->paymentMethodLabel($paymentMethod) . ' Midtrans.',
        };
    }

    private function paymentMethodNotice(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'credit_card' => 'Demo checkout ini diarahkan ke kartu sandbox Midtrans. Gunakan kartu Visa dummy sandbox pada popup Snap agar pembayaran bisa sampai status berhasil.',
            'qris' => 'Demo checkout ini dibatasi ke QRIS saja. Jika popup Snap menampilkan No payment channels available, biasanya QRIS belum aktif di akun Midtrans atau belum diaktifkan pada Settings > Snap Preferences > Payment Channels.',
            default => 'Demo checkout ini memakai channel ' . $this->paymentMethodLabel($paymentMethod) . ' dari Midtrans.',
        };
    }
}
