# Modul 8 - Barcode Scanner & QR Code Reader

Dokumentasi implementasi fitur Barcode Scanner dan QR Code Reader pada aplikasi Toko Buku Naisya.

## 📋 Daftar Isi

1. [Pendahuluan](#pendahuluan)
2. [Fitur yang Diimplementasikan](#fitur-yang-diimplementasikan)
3. [Struktur File](#struktur-file)
4. [Langkah-langkah Implementasi](#langkah-langkah-implementasi)
5. [Testing](#testing)
6. [Cara Penggunaan](#cara-penggunaan)
7. [Troubleshooting](#troubleshooting)

## 📖 Pendahuluan

Modul ini mengimplementasikan fitur Barcode Scanner dan QR Code Reader untuk:
- **Admin**: Scan barcode pada label barang untuk melihat detail barang
- **Vendor**: Scan QR Code pesanan customer untuk validasi
- **Customer**: Mendapatkan QR Code setelah pembayaran berhasil

## ✨ Fitur yang Diimplementasikan

### 1. Barcode Scanner (Admin)
- Scan barcode menggunakan kamera perangkat
- Notifikasi suara (beep) saat berhasil scan
- Auto-stop setelah berhasil membaca
- Menampilkan:
  - ID Barang
  - Nama Barang
  - Harga Barang

### 2. QR Code Generator (Customer)
- Generate QR Code setelah pembayaran berhasil
- QR Code berisi nomor transaksi
- Dapat diakses kembali meskipun halaman ditutup
- Berfungsi sebagai bukti transaksi digital

### 3. QR Code Scanner (Vendor)
- Scan QR Code pesanan customer
- Validasi pesanan
- Menampilkan detail pesanan lengkap

## 📁 Struktur File

```
app/
├── Http/Controllers/
│   ├── BarcodeQrController.php      # Controller utama
│   └── BarangController.php         # Updated untuk barcode
├── Services/
│   └── Code39BarcodeService.php     # Service generate barcode

resources/views/
├── scanner/
│   ├── barang/
│   │   └── index.blade.php          # Scanner barcode admin
│   ├── orders/
│   │   └── show.blade.php           # Detail pesanan + QR Code
│   └── vendor/
│       └── index.blade.php          # Scanner QR vendor

tests/
├── Feature/
│   └── BarcodeQrScannerModuleTest.php  # Feature test
└── Unit/
    └── Code39BarcodeServiceTest.php    # Unit test

public/assets/
└── audio/
    └── scanner-beep.mpeg            # Sound effect scanner
```

## 🚀 Langkah-langkah Implementasi

### Langkah 1: Verifikasi File yang Sudah Ada

Pastikan file-file berikut sudah ada di project Anda:

```bash
# Cek controller
ls app/Http/Controllers/BarcodeQrController.php

# Cek service
ls app/Services/Code39BarcodeService.php

# Cek views
ls resources/views/scanner/

# Cek test
ls tests/Feature/BarcodeQrScannerModuleTest.php
```

### Langkah 2: Install Dependencies JavaScript

Jika belum ada, install library untuk scanner:

```bash
npm install html5-qrcode --save
```

### Langkah 3: Verifikasi Routes

Routes sudah ditambahkan di `routes/web.php`. Verifikasi dengan:

```bash
php artisan route:list | grep -E "barang|scanner|pesanan|vendor"
```

Routes yang harus ada:
- `GET /barang/scanner` - Scanner barcode admin
- `GET /barang/{id_barang}` - Detail barang dari scan
- `GET /vendor/orders/scanner` - Scanner QR vendor
- `GET /pesanan/{nomor_transaksi}` - Detail pesanan + QR Code
- `GET /vendor/orders/lookup/{nomor_transaksi}` - API lookup pesanan

### Langkah 4: Setup Audio File

Copy file audio untuk beep sound:

```bash
# Pastikan folder ada
mkdir -p public/assets/audio

# File scanner-beep.mpeg harus ada di:
# public/assets/audio/scanner-beep.mpeg
```

Jika belum ada, Anda bisa:
1. Download beep sound dari internet
2. Atau generate menggunakan online tool
3. Simpan sebagai `scanner-beep.mpeg`

### Langkah 5: Generate Barcode untuk Barang

Update data barang yang sudah ada agar memiliki barcode:

```bash
php artisan tinker
```

```php
// Generate barcode untuk semua barang
$barangs = App\Models\Barang::all();
foreach ($barangs as $barang) {
    if (empty($barang->id_barang) || !str_starts_with($barang->id_barang, 'BRG')) {
        // Generate ID barang baru
        $lastId = App\Models\Barang::where('id_barang', 'like', 'BRG%')
            ->orderBy('id_barang', 'desc')
            ->first();
        
        if ($lastId) {
            $num = intval(substr($lastId->id_barang, 3)) + 1;
        } else {
            $num = 1;
        }
        
        $barang->id_barang = 'BRG' . str_pad($num, 5, '0', STR_PAD_LEFT);
        $barang->save();
        echo "Updated: {$barang->nama_barang} -> {$barang->id_barang}\n";
    }
}
exit;
```

### Langkah 6: Cetak Label Barcode

1. Login sebagai **Admin**
2. Buka menu **Master Barang**
3. Pilih barang yang ingin dicetak labelnya
4. Klik tombol **Cetak Label**
5. PDF akan ter-download dengan barcode

### Langkah 7: Test Barcode Scanner

1. Login sebagai **Admin**
2. Buka menu **Scanner Barcode** atau akses `/barang/scanner`
3. Klik tombol **Scan Ulang**
4. Arahkan kamera ke barcode yang sudah dicetak
5. Sistem akan:
   - Berbunyi "beep"
   - Menampilkan detail barang
   - Auto-stop scanner

### Langkah 8: Test QR Code (Customer Flow)

1. Login sebagai **User/Customer**
2. Buka menu **Checkout Toko Buku**
3. Tambahkan barang ke keranjang
4. Lakukan checkout dan pembayaran via Midtrans
5. Setelah pembayaran berhasil:
   - QR Code akan muncul
   - QR Code berisi nomor transaksi
   - Simpan atau screenshot QR Code

### Langkah 9: Test QR Scanner (Vendor Flow)

1. Login sebagai **Admin/Vendor**
2. Buka menu **Scanner Pesanan Vendor** atau akses `/vendor/orders/scanner`
3. Klik tombol **Scan Ulang**
4. Arahkan kamera ke QR Code customer
5. Sistem akan menampilkan:
   - Nomor transaksi
   - Detail customer
   - Status pembayaran
   - Daftar menu yang dipesan

### Langkah 10: Jalankan Test

```bash
# Test semua fitur barcode & QR
php artisan test --filter=BarcodeQrScannerModuleTest

# Test service barcode
php artisan test --filter=Code39BarcodeServiceTest

# Test dengan output detail
php artisan test --filter=BarcodeQrScannerModuleTest --verbose
```

## 🧪 Testing

### Feature Test

File: `tests/Feature/BarcodeQrScannerModuleTest.php`

Test yang dilakukan:
1. ✅ Admin dapat mengakses halaman scanner barcode
2. ✅ Admin dapat mengakses halaman scanner QR vendor
3. ✅ Sistem dapat fetch data barang berdasarkan scan barcode
4. ✅ Customer dapat mengakses halaman detail pesanan dengan QR Code
5. ✅ Vendor dapat lookup data pesanan via API JSON

Jalankan test:

```bash
php artisan test tests/Feature/BarcodeQrScannerModuleTest.php
```

Expected output:
```
PASS  Tests\Feature\BarcodeQrScannerModuleTest
✓ admin can open barcode and vendor scanner pages
✓ admin can fetch barang details from scanned barcode
✓ paid order page is accessible via pesanan route for customer
```

### Manual Testing Checklist

- [ ] Barcode dapat dicetak dengan jelas
- [ ] Scanner dapat membaca barcode
- [ ] Beep sound terdengar saat scan berhasil
- [ ] Data barang ditampilkan dengan benar
- [ ] QR Code muncul setelah pembayaran
- [ ] QR Code dapat di-scan oleh vendor
- [ ] Detail pesanan ditampilkan dengan benar
- [ ] Scanner dapat di-reset untuk scan ulang

## 📱 Cara Penggunaan

### Untuk Admin - Scan Barcode Barang

1. **Akses Scanner**
   - Login sebagai admin
   - Menu: Dashboard > Scanner Barcode
   - URL: `/barang/scanner`

2. **Mulai Scanning**
   - Klik tombol "Scan Ulang"
   - Izinkan akses kamera
   - Arahkan kamera ke barcode

3. **Hasil Scan**
   - Sistem berbunyi "beep"
   - Scanner otomatis berhenti
   - Detail barang ditampilkan:
     - ID Barang (contoh: BRG00001)
     - Nama Barang
     - Harga Barang

4. **Scan Ulang**
   - Klik tombol "Scan Ulang" untuk scan barcode lain

### Untuk Customer - Mendapatkan QR Code

1. **Checkout & Bayar**
   - Login sebagai user
   - Tambahkan barang ke keranjang
   - Checkout via Midtrans
   - Selesaikan pembayaran

2. **Dapatkan QR Code**
   - Setelah pembayaran berhasil
   - QR Code otomatis muncul
   - QR Code berisi nomor transaksi

3. **Akses Kembali**
   - Menu: Pesanan Saya
   - Atau akses: `/pesanan/{nomor_transaksi}`
   - QR Code tetap tersedia

### Untuk Vendor - Scan QR Pesanan

1. **Akses Scanner**
   - Login sebagai admin/vendor
   - Menu: Vendor Orders > Scanner
   - URL: `/vendor/orders/scanner`

2. **Scan QR Customer**
   - Klik tombol "Scan Ulang"
   - Arahkan kamera ke QR Code customer
   - Sistem berbunyi "beep"

3. **Validasi Pesanan**
   - Detail pesanan ditampilkan:
     - Nomor Transaksi
     - Nama Customer
     - Status Pembayaran
     - Daftar Menu
     - Total Pembayaran

## 🔧 Troubleshooting

### Scanner Tidak Berfungsi

**Problem**: Kamera tidak muncul atau error

**Solusi**:
1. Pastikan browser memiliki akses kamera
2. Gunakan HTTPS (atau localhost)
3. Cek console browser untuk error
4. Pastikan library `html5-qrcode` terinstall:
   ```bash
   npm install html5-qrcode
   npm run build
   ```

### Barcode Tidak Terbaca

**Problem**: Scanner tidak bisa membaca barcode

**Solusi**:
1. Pastikan barcode dicetak dengan jelas
2. Coba dengan pencahayaan yang lebih baik
3. Dekatkan/jauhkan kamera dari barcode
4. Pastikan barcode format Code39
5. Cek apakah `id_barang` valid (format: BRGxxxxx)

### QR Code Tidak Muncul

**Problem**: QR Code tidak ditampilkan setelah pembayaran

**Solusi**:
1. Pastikan pembayaran benar-benar berhasil (status: settlement)
2. Cek database tabel `penjualans`:
   ```sql
   SELECT nomor_transaksi, payment_status FROM penjualans WHERE user_id = ?;
   ```
3. Pastikan route `/pesanan/{nomor_transaksi}` accessible
4. Clear cache:
   ```bash
   php artisan cache:clear
   php artisan view:clear
   ```

### Beep Sound Tidak Terdengar

**Problem**: Tidak ada suara saat scan berhasil

**Solusi**:
1. Pastikan file audio ada:
   ```bash
   ls public/assets/audio/scanner-beep.mpeg
   ```
2. Cek volume browser/device
3. Cek console browser untuk error loading audio
4. Pastikan browser support audio playback

### Duplicate Scan

**Problem**: Scanner membaca barcode/QR berkali-kali

**Solusi**:
1. Pastikan scanner di-stop setelah berhasil scan
2. Cek implementasi `onScanSuccess` callback
3. Tambahkan flag untuk prevent duplicate:
   ```javascript
   let isScanning = false;
   
   function onScanSuccess(decodedText) {
       if (isScanning) return;
       isScanning = true;
       // ... process scan
   }
   ```

### Permission Denied (Camera)

**Problem**: Browser tidak mengizinkan akses kamera

**Solusi**:
1. Klik icon kamera di address bar
2. Pilih "Allow" untuk akses kamera
3. Refresh halaman
4. Untuk production, pastikan menggunakan HTTPS

### Barcode ID Tidak Valid

**Problem**: Error "Barang tidak ditemukan"

**Solusi**:
1. Cek format ID barang harus: `BRGxxxxx` (contoh: BRG00001)
2. Generate ulang ID barang:
   ```bash
   php artisan tinker
   ```
   ```php
   $barang = App\Models\Barang::find(1);
   $barang->id_barang = 'BRG00001';
   $barang->save();
   ```
3. Cetak ulang label barcode

## 📊 Database Schema

### Tabel: barang

```sql
CREATE TABLE barang (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    id_barang VARCHAR(255) UNIQUE,  -- Format: BRGxxxxx
    nama_barang VARCHAR(255),
    harga DECIMAL(10,2),
    stok INT,
    vendor_id BIGINT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Tabel: penjualans

```sql
CREATE TABLE penjualans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    nomor_transaksi VARCHAR(255) UNIQUE,  -- Untuk QR Code
    user_id BIGINT,
    total DECIMAL(10,2),
    status VARCHAR(50),
    payment_status VARCHAR(50),
    midtrans_order_id VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## 🔗 API Endpoints

### GET /barang/{id_barang}
Mendapatkan detail barang dari hasil scan barcode

**Response:**
```json
{
    "id_barang": "BRG00001",
    "nama_barang": "Buku Harry Potter",
    "harga": 75000
}
```

### GET /vendor/orders/lookup/{nomor_transaksi}
Mendapatkan detail pesanan dari hasil scan QR Code

**Response:**
```json
{
    "nomor_transaksi": "TRX-20260503-001",
    "customer": "Customer JualBeli",
    "status": "Lunas",
    "total": 21000,
    "items": [
        {
            "kode": "DK000006",
            "menu": "Ayam Geprek Sambal Matah - Nengsy Zahra Aja",
            "qty": 1,
            "harga": 21000
        }
    ]
}
```

## 📝 Notes

1. **Browser Compatibility**
   - Chrome/Edge: ✅ Full support
   - Firefox: ✅ Full support
   - Safari: ⚠️ Requires HTTPS
   - Mobile browsers: ✅ Full support

2. **Performance**
   - Scanner menggunakan device camera
   - Performa tergantung pada device
   - Pencahayaan mempengaruhi akurasi scan

3. **Security**
   - Scanner hanya accessible untuk role yang sesuai
   - Admin: Barcode scanner
   - Vendor: QR scanner
   - Customer: View QR Code only

4. **Best Practices**
   - Cetak barcode dengan printer berkualitas
   - Gunakan kertas label yang baik
   - Pastikan pencahayaan cukup saat scan
   - Test di berbagai device

## 🎯 Next Steps

Setelah modul ini selesai, Anda bisa:

1. **Tambah Fitur**
   - Batch scanning untuk multiple items
   - History scan
   - Export data scan ke Excel
   - Print receipt setelah scan

2. **Improve UX**
   - Tambah loading indicator
   - Better error messages
   - Scan animation
   - Haptic feedback (mobile)

3. **Analytics**
   - Track scan frequency
   - Popular items
   - Scan success rate
   - Performance metrics

## 📞 Support

Jika mengalami masalah:
1. Cek log Laravel: `storage/logs/laravel.log`
2. Cek console browser (F12)
3. Jalankan test: `php artisan test`
4. Baca dokumentasi: `README.md`

## 🔗 References

- [html5-qrcode Documentation](https://github.com/mebjas/html5-qrcode)
- [Code39 Barcode Specification](https://en.wikipedia.org/wiki/Code_39)
- [Laravel Testing](https://laravel.com/docs/testing)
- [DomPDF Documentation](https://github.com/barryvdh/laravel-dompdf)
