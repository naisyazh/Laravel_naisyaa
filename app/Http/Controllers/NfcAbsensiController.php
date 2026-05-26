<?php

namespace App\Http\Controllers;

use App\Models\NfcKartu;
use App\Models\NfcAbsensi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NfcAbsensiController extends Controller
{
    /**
     * Halaman scanner NFC (untuk dosen/petugas)
     */
    public function scanner(): View
    {
        return view('nfc.scanner');
    }

    /**
     * Halaman daftar kartu NFC (admin)
     */
    public function kartu(): View
    {
        $kartu = NfcKartu::orderBy('created_at', 'desc')->get();
        return view('nfc.kartu', compact('kartu'));
    }

    /**
     * Simpan kartu NFC baru (admin)
     */
    public function simpanKartu(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'serial_number'  => 'required|string|unique:nfc_kartu,serial_number',
            'nama_mahasiswa' => 'required|string|max:255',
            'nim'            => 'required|string|unique:nfc_kartu,nim',
            'program_studi'  => 'nullable|string|max:255',
        ]);

        try {
            $kartu = NfcKartu::create($validated);

            return response()->json([
                'success' => true,
                'message' => "Kartu NFC berhasil didaftarkan untuk {$kartu->nama_mahasiswa}",
                'data'    => $kartu,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan kartu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus kartu NFC (admin)
     */
    public function hapusKartu(NfcKartu $kartu): JsonResponse
    {
        try {
            $kartu->delete();
            return response()->json(['success' => true, 'message' => 'Kartu berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Proses scan NFC — catat absensi
     */
    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'serial_number' => 'required|string',
            'mata_kuliah'   => 'nullable|string|max:255',
        ]);

        $serial      = $validated['serial_number'];
        $mataKuliah  = $validated['mata_kuliah'] ?? 'Pemrograman Web';

        // Cari kartu terdaftar
        $kartu = NfcKartu::findBySerial($serial);

        if (!$kartu) {
            // Kartu tidak dikenal — tetap catat log
            NfcAbsensi::create([
                'nfc_kartu_id' => null,
                'serial_number' => $serial,
                'mata_kuliah'   => $mataKuliah,
                'status'        => 'tidak_dikenal',
                'waktu_absen'   => now(),
                'keterangan'    => 'Serial number tidak terdaftar di sistem',
            ]);

            return response()->json([
                'success' => false,
                'status'  => 'tidak_dikenal',
                'message' => 'Kartu NFC tidak dikenal. Serial: ' . $serial,
            ], 404);
        }

        // Cek duplikat absensi hari ini (dalam 1 jam terakhir)
        $sudahAbsen = NfcAbsensi::where('nfc_kartu_id', $kartu->id)
            ->where('mata_kuliah', $mataKuliah)
            ->where('status', 'hadir')
            ->where('waktu_absen', '>=', now()->subHour())
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'success'  => true,
                'status'   => 'duplikat',
                'message'  => "{$kartu->nama_mahasiswa} sudah absen dalam 1 jam terakhir.",
                'mahasiswa' => [
                    'nama'          => $kartu->nama_mahasiswa,
                    'nim'           => $kartu->nim,
                    'program_studi' => $kartu->program_studi,
                ],
            ]);
        }

        // Catat absensi
        $absensi = NfcAbsensi::create([
            'nfc_kartu_id'  => $kartu->id,
            'serial_number' => $serial,
            'mata_kuliah'   => $mataKuliah,
            'status'        => 'hadir',
            'waktu_absen'   => now(),
            'keterangan'    => 'Absensi via NFC',
        ]);

        return response()->json([
            'success'  => true,
            'status'   => 'hadir',
            'message'  => "Absensi berhasil! Selamat datang, {$kartu->nama_mahasiswa}",
            'mahasiswa' => [
                'nama'          => $kartu->nama_mahasiswa,
                'nim'           => $kartu->nim,
                'program_studi' => $kartu->program_studi,
            ],
            'waktu_absen' => $absensi->waktu_absen->format('H:i:s'),
        ]);
    }

    /**
     * Halaman riwayat absensi (admin)
     */
    public function riwayat(): View
    {
        $absensi = NfcAbsensi::with('kartu')
            ->orderBy('waktu_absen', 'desc')
            ->paginate(20);

        return view('nfc.riwayat', compact('absensi'));
    }
}
