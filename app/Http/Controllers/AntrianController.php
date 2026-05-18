<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AntrianController extends Controller
{
    /**
     * Halaman Guest - Form Pendaftaran Antrian
     */
    public function guest(): View
    {
        return view('antrian.guest');
    }

    /**
     * Halaman Admin - Dashboard Kelola Antrian
     */
    public function admin(): View
    {
        $menunggu = Antrian::getMenunggu();
        $terlewat = Antrian::getTerlewat();
        $dipanggil = Antrian::getDipanggil();

        return view('antrian.admin', compact('menunggu', 'terlewat', 'dipanggil'));
    }

    /**
     * Halaman Papan Antrian - Display Publik
     */
    public function papan(): View
    {
        $dipanggil = Antrian::getDipanggil();

        return view('antrian.papan', compact('dipanggil'));
    }

    /**
     * Daftar Antrian Baru (Guest)
     */
    public function daftar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        try {
            $nomorAntrian = Antrian::generateNomorAntrian();

            $antrian = Antrian::create([
                'nomor_antrian' => $nomorAntrian,
                'nama' => $validated['nama'],
                'status' => 'menunggu',
                'waktu_daftar' => now(),
            ]);

            // Update cache untuk SSE
            $this->updateCache();

            return response()->json([
                'success' => true,
                'message' => 'Antrian berhasil didaftarkan',
                'data' => [
                    'nomor_antrian' => $antrian->nomor_antrian,
                    'nama' => $antrian->nama,
                    'url' => route('antrian.tiket', $antrian->id),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan antrian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Halaman Tiket Antrian (Tab Baru untuk Guest)
     */
    public function tiket(Antrian $antrian): View
    {
        return view('antrian.tiket', compact('antrian'));
    }

    /**
     * Panggil Antrian Berikutnya (Admin)
     */
    public function panggil(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ruangan' => 'nullable|numeric|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Set antrian yang sedang dipanggil menjadi selesai
            Antrian::where('status', 'dipanggil')->update([
                'status' => 'selesai',
            ]);

            // Ambil antrian berikutnya yang menunggu
            $antrian = Antrian::where('status', 'menunggu')
                ->orderBy('nomor_antrian', 'asc')
                ->first();

            if (!$antrian) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada antrian yang menunggu',
                ], 404);
            }

            // Update status menjadi dipanggil
            $antrian->update([
                'status' => 'dipanggil',
                'ruangan' => $validated['ruangan'] ?? null,
                'waktu_dipanggil' => now(),
            ]);

            DB::commit();

            // Update cache untuk SSE
            $this->updateCache();

            return response()->json([
                'success' => true,
                'message' => 'Antrian berhasil dipanggil',
                'data' => [
                    'nomor_antrian' => $antrian->nomor_antrian,
                    'nama' => $antrian->nama,
                    'ruangan' => $antrian->ruangan,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memanggil antrian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tandai Antrian sebagai Terlewat (Admin)
     */
    public function terlewat(Antrian $antrian): JsonResponse
    {
        try {
            if ($antrian->status !== 'menunggu') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya antrian dengan status menunggu yang bisa ditandai terlewat',
                ], 400);
            }

            $antrian->update(['status' => 'terlewat']);

            // Update cache untuk SSE
            $this->updateCache();

            return response()->json([
                'success' => true,
                'message' => 'Antrian ditandai sebagai terlewat',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai antrian terlewat: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Panggil Ulang Antrian Terlewat (Admin)
     */
    public function panggilUlang(Antrian $antrian): JsonResponse
    {
        $request = request();
        $validated = $request->validate([
            'ruangan' => 'nullable|integer|min:1',
        ]);

        try {
            if ($antrian->status !== 'terlewat') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya antrian terlewat yang bisa dipanggil ulang',
                ], 400);
            }

            DB::beginTransaction();

            // Set antrian yang sedang dipanggil menjadi selesai
            Antrian::where('status', 'dipanggil')->update([
                'status' => 'selesai',
            ]);

            // Update antrian terlewat menjadi dipanggil
            $antrian->update([
                'status' => 'dipanggil',
                'ruangan' => $validated['ruangan'] ?? null,
                'waktu_dipanggil' => now(),
            ]);

            DB::commit();

            // Update cache untuk SSE
            $this->updateCache();

            return response()->json([
                'success' => true,
                'message' => 'Antrian terlewat berhasil dipanggil ulang',
                'data' => [
                    'nomor_antrian' => $antrian->nomor_antrian,
                    'nama' => $antrian->nama,
                    'ruangan' => $antrian->ruangan,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memanggil ulang antrian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset semua data antrian hari ini (Admin)
     */
    public function reset(): JsonResponse
    {
        try {
            Antrian::truncate();
            $this->updateCache();
            return response()->json(['success' => true, 'message' => 'Data antrian berhasil direset']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Polling Endpoint - Ambil data antrian terkini (JSON)
     */
    public function poll(): JsonResponse
    {
        $menunggu = Antrian::getMenunggu()->map(function ($antrian) {
            return [
                'id' => $antrian->id,
                'nomor_antrian' => $antrian->nomor_antrian,
                'nama' => $antrian->nama,
                'status' => $antrian->status,
                'waktu_daftar' => $antrian->waktu_daftar->format('H:i:s'),
            ];
        });

        $terlewat = Antrian::getTerlewat()->map(function ($antrian) {
            return [
                'id' => $antrian->id,
                'nomor_antrian' => $antrian->nomor_antrian,
                'nama' => $antrian->nama,
                'status' => $antrian->status,
                'waktu_daftar' => $antrian->waktu_daftar->format('H:i:s'),
            ];
        });

        $dipanggil = Antrian::getDipanggil();
        $dipanggilData = $dipanggil ? [
            'id' => $dipanggil->id,
            'nomor_antrian' => $dipanggil->nomor_antrian,
            'nama' => $dipanggil->nama,
            'ruangan' => $dipanggil->ruangan,
            'waktu_dipanggil' => $dipanggil->waktu_dipanggil?->format('H:i:s'),
        ] : null;

        return response()->json([
            'menunggu' => $menunggu,
            'terlewat' => $terlewat,
            'dipanggil' => $dipanggilData,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * SSE Stream Endpoint - Real-time Updates
     */
    public function stream(Request $request)
    {
        // Prevent PHP timeout
        set_time_limit(0);

        return response()->stream(function () {
            while (true) {
                // Ambil data dari cache
                $data = Cache::get('antrian_data', [
                    'menunggu' => [],
                    'terlewat' => [],
                    'dipanggil' => null,
                    'timestamp' => now()->toIso8601String(),
                ]);

                // Kirim event SSE
                echo "event: queue-update\n";
                echo "data: " . json_encode($data) . "\n\n";

                // Flush output buffer
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

                // Cek apakah client masih terhubung
                if (connection_aborted()) {
                    break;
                }

                // Update setiap 1 detik
                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no', // Penting untuk Nginx
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * Update Cache untuk SSE
     */
    private function updateCache(): void
    {
        $menunggu = Antrian::getMenunggu()->map(function ($antrian) {
            return [
                'id' => $antrian->id,
                'nomor_antrian' => $antrian->nomor_antrian,
                'nama' => $antrian->nama,
                'status' => $antrian->status,
                'waktu_daftar' => $antrian->waktu_daftar->format('H:i:s'),
            ];
        });

        $terlewat = Antrian::getTerlewat()->map(function ($antrian) {
            return [
                'id' => $antrian->id,
                'nomor_antrian' => $antrian->nomor_antrian,
                'nama' => $antrian->nama,
                'status' => $antrian->status,
                'waktu_daftar' => $antrian->waktu_daftar->format('H:i:s'),
            ];
        });

        $dipanggil = Antrian::getDipanggil();
        $dipanggilData = $dipanggil ? [
            'id' => $dipanggil->id,
            'nomor_antrian' => $dipanggil->nomor_antrian,
            'nama' => $dipanggil->nama,
            'ruangan' => $dipanggil->ruangan,
            'waktu_dipanggil' => $dipanggil->waktu_dipanggil?->format('H:i:s'),
        ] : null;

        Cache::put('antrian_data', [
            'menunggu' => $menunggu,
            'terlewat' => $terlewat,
            'dipanggil' => $dipanggilData,
            'timestamp' => now()->toIso8601String(),
        ], now()->addHours(24));
    }
}
