<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    public function __construct()
    {
        // Pastikan user login password dulu
        $this->middleware('auth');
    }

    // 1. Tampilkan Halaman Input OTP
    public function showVerify()
    {
        // Jika sudah verifikasi OTP, jangan kasih masuk sini lagi, lempar ke dashboard
        if (session('otp_verified')) {
            return redirect()->route('otp.dashboard');
        }

        $user = Auth::user();

        // Logika: Hanya kirim OTP otomatis jika kolom 'otp' di database masih kosong
        // Ini mencegah pengiriman email berkali-kali saat halaman di-refresh
        if (empty($user->otp)) {
            $this->generateAndSendOtp($user);
        }

        session(['email' => $user->email]);
        return view('otp.verify');
    }

    // 2. Fungsi Internal untuk Generate dan Kirim (Biar rapi)
    private function generateAndSendOtp($user)
    {
        $otp = rand(100000, 999999);

        // Update ke DB dulu agar data siap
        DB::table('users')->where('id', $user->id)->update(['otp' => $otp]);

        try {
            Mail::raw(
                "Kode keamanan Anda adalah: $otp",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Verifikasi OTP Login');
                }
            );
        } catch (\Exception $e) {
            // Jika internet mati, hapus OTP di DB agar user bisa kirim ulang nanti
            DB::table('users')->where('id', $user->id)->update(['otp' => null]);

            // Redirect balik dengan pesan error yang ramah
            return back()->withErrors(['otp' => 'Koneksi ke server email bermasalah. Pastikan internet Anda aktif.']);
        }
    }

    // 3. Tombol "Kirim Ulang OTP" di halaman verifikasi
    public function sendOtp(Request $request)
    {
        $user = Auth::user();
        $this->generateAndSendOtp($user);

        return back()->with('success', 'Kode OTP baru telah dikirim ke email Anda.');
    }

    // 4. Proses Verifikasi
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = Auth::user();

        // Cek apakah kode cocok dengan database
        if ($user->otp == $request->otp) {
            // SET SESSION LOGIN OTP BERHASIL
            session([
                'otp_verified' => true,
                'user_name' => $user->name
            ]);

            // Hapus OTP agar tidak bisa dipakai lagi
            DB::table('users')
                ->where('id', $user->id)
                ->update(['otp' => null]);

            return view('otp.success');
        }

        return back()->withErrors(['otp' => 'Aduh, kode OTP-nya salah atau sudah kadaluarsa!']);
    }

    // --- PROTECTED MENUS (Dashboard, Sertifikat, Undangan) ---

    public function showDashboard()
    {
        // if (!session('otp_verified')) return redirect()->route('otp.verify.form');
        return view('otp.dashboard');
    }

    public function showSertifikat()
    {
        // if (!session('otp_verified')) return redirect()->route('otp.verify.form');

        // User hanya mengambil data miliknya sendiri
        $document = DB::table('documents')
            ->where('user_id', Auth::id())
            ->where('type', 'sertifikat')
            ->first();

        return view('otp.sertifikat', compact('document'));
    }

    public function showUndangan()
    {
        // if (!session('otp_verified')) return redirect()->route('otp.verify.form');

        // User hanya mengambil data miliknya sendiri
        $document = DB::table('documents')
            ->where('user_id', Auth::id())
            ->where('type', 'undangan')
            ->first();

        return view('otp.undangan', compact('document'));
    }
}
