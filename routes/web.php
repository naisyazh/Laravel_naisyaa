<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BarcodeQrController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MidtransNotificationController;
use App\Http\Controllers\TokoController;
use App\Http\Controllers\VendorOrderController;
use App\Http\Controllers\TokoKunjunganController;
use App\Http\Controllers\AntrianController;
use App\Http\Controllers\NfcAbsensiController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
})->name('home');

/*
|--------------------------------------------------------------------------
| Midtrans Webhook (No CSRF Protection)
|--------------------------------------------------------------------------
*/

Route::post('/payments/midtrans/notification', [MidtransNotificationController::class, 'handle'])
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('payments.midtrans.notification');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Auth::routes(['register' => false]);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Requires Login)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | OTP Verification Routes (Before OTP Verification)
    |--------------------------------------------------------------------------
    */
    
    Route::get('/otp-verification', [OtpController::class, 'showVerify'])
        ->name('otp.verify.form');

    Route::post('/verify-otp', [OtpController::class, 'verifyOtp'])
        ->name('otp.verify');

    Route::post('/otp-resend', [OtpController::class, 'sendOtp'])
        ->name('otp.send');

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (After OTP Verification)
    |--------------------------------------------------------------------------
    | Note: Add 'verified.otp' middleware if you want to enforce OTP verification
    */

    // Dashboard
    Route::get('/dashboard', [OtpController::class, 'showDashboard'])
        ->name('dashboard');
    
    // Alias untuk backward compatibility
    Route::get('/dashboard', [OtpController::class, 'showDashboard'])
        ->name('otp.dashboard');

    // Alternative dashboard route
    Route::get('/home', [HomeController::class, 'index'])
        ->name('home.dashboard');

    // Order/Pesanan Routes
    Route::get('/pesanan/{penjualan:nomor_transaksi}', [BarcodeQrController::class, 'showPesanan'])
        ->name('pesanan.show');

    // Certificate & Invitation Routes
    Route::get('/sertifikat', [OtpController::class, 'showSertifikat'])
        ->name('otp.sertifikat');

    Route::get('/undangan', [OtpController::class, 'showUndangan'])
        ->name('otp.undangan');

    /*
    |--------------------------------------------------------------------------
    | Assignment/Tugas Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/tugas-js', [AssignmentController::class, 'index'])
        ->name('assignment.index');
    
    // Alias untuk backward compatibility
    Route::get('/tugas-js', [AssignmentController::class, 'index'])
        ->name('assignment');
    
    // Alias untuk backward compatibility
    Route::get('/tugas-js', [AssignmentController::class, 'index'])
        ->name('assignment');

    Route::prefix('tugas-js/api')->name('assignment.')->group(function () {
        Route::get('/regions/provinces', [AssignmentController::class, 'provinces'])
            ->name('regions.provinces');

        Route::get('/regions/regencies', [AssignmentController::class, 'regencies'])
            ->name('regions.regencies');

        Route::get('/regions/districts', [AssignmentController::class, 'districts'])
            ->name('regions.districts');

        Route::get('/regions/villages', [AssignmentController::class, 'villages'])
            ->name('regions.villages');

        Route::get('/barang', [AssignmentController::class, 'lookupBarang'])
            ->name('barang.lookup');

        Route::post('/checkout', [AssignmentController::class, 'checkout'])
            ->name('checkout');
    });

    /*
    |--------------------------------------------------------------------------
    | Kategori & Buku Routes (Public View)
    |--------------------------------------------------------------------------
    */

    Route::get('/kategori', [KategoriController::class, 'index'])
        ->name('kategori.index');

    Route::get('/buku', [BukuController::class, 'index'])
        ->name('buku.index');

    /*
    |--------------------------------------------------------------------------
    | Kunjungan Toko Routes (Geolocation Module)
    |--------------------------------------------------------------------------
    */

    // Kunjungan Toko (Sales/User)
    Route::get('/kunjungan-toko', [TokoKunjunganController::class, 'kunjungan'])
        ->name('kunjungan-toko.index');

    Route::get('/toko/{barcode}/detail', [TokoKunjunganController::class, 'showByBarcode'])
        ->name('toko.detail');

    Route::post('/kunjungan-toko/submit', [TokoKunjunganController::class, 'submitKunjungan'])
        ->name('kunjungan-toko.submit');

    Route::get('/kunjungan-toko/riwayat', [TokoKunjunganController::class, 'riwayat'])
        ->name('kunjungan-toko.riwayat');

    /*
    |--------------------------------------------------------------------------
    | User Routes (Customer/Pembeli)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['user'])->prefix('toko-buku')->name('toko-buku.')->group(function () {
        // Main Store Page
        Route::get('/', [TokoController::class, 'index'])
            ->name('index');

        // API for Book Lookup
        Route::get('/api/buku', [TokoController::class, 'lookup'])
            ->name('lookup');

        // Checkout
        Route::post('/checkout', [TokoController::class, 'checkout'])
            ->name('checkout');

        // Orders
        Route::prefix('orders')->name('orders.')->group(function () {
            // Paid Orders List
            Route::get('/lunas', [TokoController::class, 'paidOrders'])
                ->name('paid');

            // Order Detail
            Route::get('/{penjualan:nomor_transaksi}', [BarcodeQrController::class, 'showPesanan'])
                ->name('show');

            // Payment Actions
            Route::post('/{penjualan:nomor_transaksi}/confirm-demo-payment', [TokoController::class, 'confirmDemoPayment'])
                ->name('confirm-demo-payment');

            Route::post('/{penjualan:nomor_transaksi}/record-snap-result', [TokoController::class, 'recordSnapResult'])
                ->name('record-snap-result');

            Route::post('/{penjualan:nomor_transaksi}/refresh-status', [TokoController::class, 'refreshStatus'])
                ->name('refresh');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Pengelola/Vendor)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['admin'])->group(function () {
        
        // Toko Management (Geolocation Module)
        Route::prefix('toko')->name('toko.')->group(function () {
            Route::get('/', [TokoKunjunganController::class, 'index'])
                ->name('index');

            Route::get('/create', [TokoKunjunganController::class, 'create'])
                ->name('create');

            Route::post('/', [TokoKunjunganController::class, 'store'])
                ->name('store');

            Route::post('/cetak-barcode', [TokoKunjunganController::class, 'cetakBarcode'])
                ->name('cetak-barcode');
        });
        
        // Barcode/QR Scanner Routes
        Route::prefix('barang')->name('barang.')->group(function () {
            Route::get('/scanner', [BarcodeQrController::class, 'barangScanner'])
                ->name('scanner');

            Route::get('/{barang:id_barang}', [BarcodeQrController::class, 'showBarang'])
                ->name('scan.show');
        });

        // Barang Management
        Route::get('/barang', [BarangController::class, 'index'])
            ->name('barang.index');

        Route::post('/cetak-label-barang', [BarangController::class, 'cetakLabel'])
            ->name('barang.cetak');

        Route::resource('barang', BarangController::class)
            ->except(['index', 'create', 'show']);

        // Vendor Orders Management
        Route::prefix('vendor/orders')->name('vendor.orders.')->group(function () {
            Route::get('/', [VendorOrderController::class, 'index'])
                ->name('index');

            Route::get('/scanner', [BarcodeQrController::class, 'vendorScanner'])
                ->name('scanner');

            Route::get('/lookup/{penjualan:nomor_transaksi}', [BarcodeQrController::class, 'showPesananJson'])
                ->name('lookup');

            Route::get('/{penjualan:nomor_transaksi}', [VendorOrderController::class, 'show'])
                ->name('show');

            Route::post('/{penjualan:nomor_transaksi}/refresh-status', [VendorOrderController::class, 'refreshStatus'])
                ->name('refresh');

            Route::post('/{penjualan:nomor_transaksi}/mark-paid', [VendorOrderController::class, 'markPaid'])
                ->name('mark-paid');
            
            Route::post('/{penjualan:nomor_transaksi}/cetak-struk', [VendorOrderController::class, 'cetakStruk'])
                ->name('cetak-struk');
        });

        // Customer Management
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])
                ->name('index');

            Route::get('/create-blob', [CustomerController::class, 'createBlob'])
                ->name('create.blob');

            Route::post('/create-blob', [CustomerController::class, 'storeBlob'])
                ->name('store.blob');

            Route::get('/create-file', [CustomerController::class, 'createFile'])
                ->name('create.file');

            Route::post('/create-file', [CustomerController::class, 'storeFile'])
                ->name('store.file');
        });

        // Kategori Management
        Route::resource('kategori', KategoriController::class)
            ->except(['index', 'show']);

        // Buku Management
        Route::resource('buku', BukuController::class)
            ->except(['index', 'show']);

        // Document Management
        Route::resource('documents', DocumentController::class)
            ->only(['index', 'create', 'store', 'destroy']);
    });
});


/*
|--------------------------------------------------------------------------
| SSE Antrian Routes (Server-Sent Events Module)
|--------------------------------------------------------------------------
*/

// Guest Routes - Pendaftaran Antrian (Public)
Route::prefix('antrian')->name('antrian.')->group(function () {
    Route::get('/guest', [AntrianController::class, 'guest'])->name('guest');
    Route::post('/daftar', [AntrianController::class, 'daftar'])->name('daftar');
    Route::get('/tiket/{antrian}', [AntrianController::class, 'tiket'])->name('tiket');
});

// Papan Antrian - Display Publik (Public)
Route::get('/papan-antrian', [AntrianController::class, 'papan'])->name('papan-antrian');

// SSE Stream Endpoint (Public - untuk semua yang butuh real-time update)
Route::get('/sse/antrian', [AntrianController::class, 'stream'])->name('sse.antrian');

/*
|--------------------------------------------------------------------------
| NFC Absensi Routes (Web NFC API Module)
|--------------------------------------------------------------------------
*/

// NFC Routes
Route::prefix('nfc')->name('nfc.')->group(function () {

    // Scanner & scan endpoint - user (operator/dosen) only
    Route::middleware(['auth', 'user'])->group(function () {
        Route::get('/scanner', [NfcAbsensiController::class, 'scanner'])->name('scanner');
        Route::post('/scan', [NfcAbsensiController::class, 'scan'])->name('scan');
    });

    // Manajemen kartu & riwayat - admin only
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/kartu', [NfcAbsensiController::class, 'kartu'])->name('kartu');
        Route::post('/kartu', [NfcAbsensiController::class, 'simpanKartu'])->name('kartu.simpan');
        Route::delete('/kartu/{kartu}', [NfcAbsensiController::class, 'hapusKartu'])->name('kartu.hapus');
        Route::get('/riwayat', [NfcAbsensiController::class, 'riwayat'])->name('riwayat');
    });
});

// Admin Routes - Kelola Antrian (Requires Auth)
Route::middleware(['auth'])->prefix('antrian')->name('antrian.')->group(function () {
    Route::get('/admin', [AntrianController::class, 'admin'])->name('admin');
    Route::get('/poll', [AntrianController::class, 'poll'])->name('poll');
    Route::post('/panggil', [AntrianController::class, 'panggil'])->name('panggil');
    Route::post('/reset', [AntrianController::class, 'reset'])->name('reset');
    Route::post('/{antrian}/terlewat', [AntrianController::class, 'terlewat'])->name('terlewat');
    Route::post('/{antrian}/panggil-ulang', [AntrianController::class, 'panggilUlang'])->name('panggil-ulang');
});
