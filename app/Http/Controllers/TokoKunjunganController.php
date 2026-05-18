<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use App\Models\KunjunganToko;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class TokoKunjunganController extends Controller
{
    /**
     * Display list of tokos (Admin)
     */
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $tokos = Toko::with('vendor')
            ->when($request->user()->role === 'admin' && $request->user()->id !== 1, function ($query) use ($request) {
                $query->where('vendor_id', $request->user()->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('toko.index', compact('tokos'));
    }

    /**
     * Show form to create new toko
     */
    public function create(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('toko.create');
    }

    /**
     * Store new toko
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'nama_toko' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0',
        ]);

        $validated['barcode'] = Toko::generateBarcodeId();
        $validated['vendor_id'] = $request->user()->id;

        $toko = Toko::create($validated);

        return redirect()->route('toko.index')
            ->with('success', 'Toko berhasil ditambahkan dengan barcode: ' . $toko->barcode);
    }

    /**
     * Show toko detail by barcode (API for scanner)
     */
    public function showByBarcode(Request $request, string $barcode): JsonResponse
    {
        $toko = Toko::where('barcode', $barcode)
            ->where('is_active', true)
            ->first();

        if (!$toko) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'message' => 'Toko tidak ditemukan atau tidak aktif.',
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Data toko berhasil dimuat.',
            'data' => [
                'id' => $toko->id,
                'barcode' => $toko->barcode,
                'nama_toko' => $toko->nama_toko,
                'alamat' => $toko->alamat,
                'latitude' => (float) $toko->latitude,
                'longitude' => (float) $toko->longitude,
                'accuracy' => (float) $toko->accuracy,
            ],
        ]);
    }

    /**
     * Show kunjungan toko page (Sales)
     */
    public function kunjungan(Request $request): View
    {
        abort_unless($request->user(), 403);

        return view('toko.kunjungan');
    }

    /**
     * Submit kunjungan toko
     */
    public function submitKunjungan(Request $request): JsonResponse
    {
        abort_unless($request->user(), 403);

        $validated = $request->validate([
            'toko_id' => 'required|exists:tokos,id',
            'toko_latitude' => 'required|numeric|between:-90,90',
            'toko_longitude' => 'required|numeric|between:-180,180',
            'toko_accuracy' => 'required|numeric|min:0',
            'sales_latitude' => 'required|numeric|between:-90,90',
            'sales_longitude' => 'required|numeric|between:-180,180',
            'sales_accuracy' => 'required|numeric|min:0',
            'threshold_meter' => 'nullable|numeric|min:0',
        ]);

        $toko = Toko::findOrFail($validated['toko_id']);

        // Hitung jarak menggunakan Haversine formula
        $jarak = $this->calculateHaversineDistance(
            $validated['toko_latitude'],
            $validated['toko_longitude'],
            $validated['sales_latitude'],
            $validated['sales_longitude']
        );

        // Threshold efektif = threshold + accuracy toko + accuracy sales
        $thresholdBase = $validated['threshold_meter'] ?? 300;
        $thresholdEfektif = $thresholdBase + $validated['toko_accuracy'] + $validated['sales_accuracy'];

        // Validasi jarak
        $status = $jarak <= $thresholdEfektif ? 'diterima' : 'ditolak';
        $keterangan = $status === 'diterima'
            ? sprintf('Kunjungan diterima. Jarak: %.2fm ≤ Threshold: %.2fm (%.0f + %.0f + %.0f)', 
                $jarak, $thresholdEfektif, $thresholdBase, $validated['toko_accuracy'], $validated['sales_accuracy'])
            : sprintf('Kunjungan ditolak. Jarak: %.2fm > Threshold: %.2fm (%.0f + %.0f + %.0f)', 
                $jarak, $thresholdEfektif, $thresholdBase, $validated['toko_accuracy'], $validated['sales_accuracy']);

        // Simpan kunjungan
        $kunjungan = KunjunganToko::create([
            'toko_id' => $validated['toko_id'],
            'sales_id' => $request->user()->id,
            'toko_latitude' => $validated['toko_latitude'],
            'toko_longitude' => $validated['toko_longitude'],
            'toko_accuracy' => $validated['toko_accuracy'],
            'sales_latitude' => $validated['sales_latitude'],
            'sales_longitude' => $validated['sales_longitude'],
            'sales_accuracy' => $validated['sales_accuracy'],
            'jarak_meter' => $jarak,
            'threshold_meter' => $thresholdBase,
            'status' => $status,
            'keterangan' => $keterangan,
            'waktu_kunjungan' => now(),
        ]);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => $status === 'diterima' ? 'Kunjungan berhasil dicatat!' : 'Kunjungan ditolak karena jarak terlalu jauh.',
            'data' => [
                'id' => $kunjungan->id,
                'status' => $status,
                'jarak_meter' => round($jarak, 2),
                'threshold_efektif' => round($thresholdEfektif, 2),
                'keterangan' => $keterangan,
                'diterima' => $status === 'diterima',
            ],
        ]);
    }

    /**
     * Show riwayat kunjungan
     */
    public function riwayat(Request $request): View
    {
        abort_unless($request->user(), 403);

        $query = KunjunganToko::with(['toko', 'sales']);

        // Admin bisa lihat semua, user hanya miliknya
        if ($request->user()->role !== 'admin' || $request->user()->id !== 1) {
            $query->where('sales_id', $request->user()->id);
        }

        $kunjungans = $query->orderBy('waktu_kunjungan', 'desc')
            ->paginate(20);

        return view('toko.riwayat', compact('kunjungans'));
    }

    /**
     * Generate barcode PDF for toko
     */
    public function cetakBarcode(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'toko_ids' => 'required|array',
            'toko_ids.*' => 'exists:tokos,id',
        ]);

        $tokos = Toko::whereIn('id', $validated['toko_ids'])->get();

        return view('toko.cetak_barcode', compact('tokos'));
    }

    /**
     * Calculate distance using Haversine formula
     * Returns distance in meters
     */
    private function calculateHaversineDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $R = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }

    /**
     * Authorize admin access
     */
    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }
}
