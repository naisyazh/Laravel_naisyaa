<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Penjualan;
use App\Services\WilayahCsvService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function __construct(
        private readonly WilayahCsvService $wilayahCsvService
    ) {
    }

    public function index(): View
    {
        return view('assignment', [
            'barangCount' => Barang::count(),
        ]);
    }

    public function provinces(): JsonResponse
    {
        return $this->successResponse(
            'Daftar provinsi berhasil diambil.',
            $this->wilayahCsvService->getProvinces()
        );
    }

    public function regencies(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_id' => 'required|string',
        ]);

        return $this->successResponse(
            'Daftar kota/kabupaten berhasil diambil.',
            $this->wilayahCsvService->getRegencies($validated['province_id'])
        );
    }

    public function districts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'regency_id' => 'required|string',
        ]);

        return $this->successResponse(
            'Daftar kecamatan berhasil diambil.',
            $this->wilayahCsvService->getDistricts($validated['regency_id'])
        );
    }

    public function villages(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'district_id' => 'required|string',
        ]);

        return $this->successResponse(
            'Daftar kelurahan berhasil diambil.',
            $this->wilayahCsvService->getVillages($validated['district_id'])
        );
    }

    public function lookupBarang(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:8',
        ]);

        $barang = Barang::find($validated['kode']);

        if (! $barang) {
            return $this->errorResponse('Barang dengan kode tersebut tidak ditemukan.', 404);
        }

        return $this->successResponse('Barang ditemukan.', [
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
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        $groupedItems = collect($validated['items'])
            ->groupBy('kode')
            ->map(function ($items) {
                return (int) collect($items)->sum('jumlah');
            });

        $barangs = Barang::query()
            ->whereIn('id_barang', $groupedItems->keys())
            ->get()
            ->keyBy('id_barang');

        if ($barangs->count() !== $groupedItems->count()) {
            $missingCodes = $groupedItems->keys()->diff($barangs->keys())->values()->implode(', ');

            throw ValidationException::withMessages([
                'items' => ["Kode barang tidak valid: {$missingCodes}"],
            ]);
        }

        $itemPayloads = [];
        $total = 0;

        foreach ($groupedItems as $kode => $jumlah) {
            $barang = $barangs->get($kode);
            $subtotal = (int) $barang->harga * $jumlah;
            $total += $subtotal;

            $itemPayloads[] = [
                'barang_id' => $barang->id_barang,
                'nama_barang' => $barang->nama,
                'harga' => (int) $barang->harga,
                'jumlah' => $jumlah,
                'subtotal' => $subtotal,
            ];
        }

        $penjualan = DB::transaction(function () use ($request, $itemPayloads, $total) {
            $penjualan = Penjualan::create([
                'nomor_transaksi' => $this->generateTransactionNumber(),
                'user_id' => $request->user()?->id,
                'customer_name' => $request->user()?->name,
                'customer_email' => $request->user()?->email,
                'total' => $total,
                'payment_status' => 'paid',
                'payment_type' => 'manual',
                'midtrans_transaction_status' => 'manual',
                'status_message' => 'Checkout modul assignment disimpan sebagai transaksi manual.',
                'paid_at' => now(),
            ]);

            $penjualan->items()->createMany($itemPayloads);

            return $penjualan->load('items');
        });

        return $this->successResponse('Pembayaran transaksi berhasil disimpan.', [
            'nomor_transaksi' => $penjualan->nomor_transaksi,
            'total' => (int) $penjualan->total,
            'items' => $penjualan->items->map(function ($item) {
                return [
                    'barang_id' => $item->barang_id,
                    'nama_barang' => $item->nama_barang,
                    'harga' => (int) $item->harga,
                    'jumlah' => (int) $item->jumlah,
                    'subtotal' => (int) $item->subtotal,
                ];
            })->values(),
        ], 201);
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

    private function generateTransactionNumber(): string
    {
        return Penjualan::generateTransactionNumber();
    }
}
