@extends('layouts.app')

@section('title', 'Scanner Absensi NFC')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="mdi mdi-nfc-variant"></i> Scanner Absensi NFC</h2>
        <p class="text-muted">Dekatkan kartu NFC mahasiswa ke HP untuk mencatat kehadiran</p>
    </div>
</div>

<!-- Alert NFC tidak didukung -->
<div class="alert alert-warning d-none" id="alertTidakDukung">
    <i class="mdi mdi-alert"></i>
    <strong>Browser tidak mendukung Web NFC API.</strong>
    Web NFC hanya berfungsi di <strong>Android Chrome versi 89+</strong>.
    Buka halaman ini di HP Android menggunakan Chrome.
</div>

<div class="row">
    <!-- Panel Scanner -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="mdi mdi-nfc"></i> Scanner NFC</h5>
            </div>
            <div class="card-body text-center py-5">
                <!-- Status NFC -->
                <div id="nfcIdle">
                    <i class="mdi mdi-nfc-variant" style="font-size: 80px; color: #ccc;"></i>
                    <p class="text-muted mt-3">NFC belum aktif</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mata Kuliah</label>
                        <input type="text" class="form-control text-center" id="mataKuliah"
                               value="Pemrograman Web" placeholder="Nama mata kuliah">
                    </div>
                    <button class="btn btn-primary btn-lg px-5" onclick="startNFC()">
                        <i class="mdi mdi-nfc"></i> Aktifkan NFC
                    </button>
                </div>

                <!-- Scanning -->
                <div id="nfcScanning" class="d-none">
                    <div class="mb-3">
                        <i class="mdi mdi-nfc-variant text-primary" style="font-size: 80px; animation: pulse 1.5s infinite;"></i>
                    </div>
                    <h5 class="text-primary">NFC Aktif</h5>
                    <p class="text-muted">Dekatkan kartu NFC ke bagian belakang HP...</p>
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-3">
                        <button class="btn btn-outline-danger btn-sm" onclick="stopNFC()">
                            <i class="mdi mdi-stop"></i> Stop
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Hasil -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="mdi mdi-account-check"></i> Hasil Scan Terakhir</h5>
            </div>
            <div class="card-body" id="hasilScan">
                <div class="text-center text-muted py-4">
                    <i class="mdi mdi-card-account-details-outline" style="font-size: 50px;"></i>
                    <p class="mt-2">Belum ada scan</p>
                </div>
            </div>
        </div>

        <!-- Log Scan Hari Ini -->
        <div class="card shadow-sm mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="mdi mdi-history"></i> Log Scan Sesi Ini</h6>
                <button class="btn btn-sm btn-outline-secondary" onclick="clearLog()">Clear</button>
            </div>
            <div class="card-body p-0">
                <div id="logScan" style="max-height: 250px; overflow-y: auto;">
                    <p class="text-center text-muted py-3 mb-0">Belum ada log</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.7; }
}
</style>

<script>
let ndef = null;
let isScanning = false;
let logItems = [];

// Cek dukungan Web NFC
document.addEventListener('DOMContentLoaded', function () {
    if (!('NDEFReader' in window)) {
        document.getElementById('alertTidakDukung').classList.remove('d-none');
        document.getElementById('nfcIdle').querySelector('button').disabled = true;
    }
});

async function startNFC() {
    if (!('NDEFReader' in window)) {
        alert('Browser tidak mendukung Web NFC API.\nGunakan Android Chrome 89+.');
        return;
    }

    try {
        ndef = new NDEFReader();
        await ndef.scan();

        isScanning = true;
        document.getElementById('nfcIdle').classList.add('d-none');
        document.getElementById('nfcScanning').classList.remove('d-none');

        ndef.addEventListener('reading', ({ serialNumber, message }) => {
            handleScan(serialNumber, message);
        });

        ndef.addEventListener('readingerror', () => {
            tampilkanError('Gagal membaca tag NFC. Coba lagi.');
        });

    } catch (err) {
        console.error('NFC Error:', err);
        tampilkanError('Error: ' + err.message);
    }
}

function stopNFC() {
    isScanning = false;
    ndef = null;
    document.getElementById('nfcIdle').classList.remove('d-none');
    document.getElementById('nfcScanning').classList.add('d-none');
}

async function handleScan(serialNumber, message) {
    console.log('Serial:', serialNumber);
    console.log('Records:', message.records.length);

    const mataKuliah = document.getElementById('mataKuliah').value || 'Pemrograman Web';

    // Tampilkan loading
    document.getElementById('hasilScan').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2 text-muted">Memproses...</p>
        </div>
    `;

    try {
        const response = await fetch('{{ route('nfc.scan') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                serial_number: serialNumber,
                mata_kuliah: mataKuliah
            })
        });

        const data = await response.json();
        tampilkanHasil(data, serialNumber);
        tambahLog(data, serialNumber);

    } catch (err) {
        tampilkanError('Gagal menghubungi server: ' + err.message);
    }
}

function tampilkanHasil(data, serial) {
    const hasilDiv = document.getElementById('hasilScan');

    if (data.status === 'hadir') {
        hasilDiv.innerHTML = `
            <div class="text-center py-3">
                <i class="mdi mdi-check-circle text-success" style="font-size: 60px;"></i>
                <h4 class="text-success mt-2">HADIR ✓</h4>
                <h5 class="mt-3">${data.mahasiswa.nama}</h5>
                <p class="text-muted mb-1">NIM: <strong>${data.mahasiswa.nim}</strong></p>
                <p class="text-muted mb-1">${data.mahasiswa.program_studi ?? '-'}</p>
                <p class="text-muted"><small>Pukul: ${data.waktu_absen}</small></p>
                <span class="badge bg-success px-3 py-2">${data.message}</span>
            </div>
        `;
    } else if (data.status === 'duplikat') {
        hasilDiv.innerHTML = `
            <div class="text-center py-3">
                <i class="mdi mdi-information text-warning" style="font-size: 60px;"></i>
                <h4 class="text-warning mt-2">SUDAH ABSEN</h4>
                <h5 class="mt-3">${data.mahasiswa.nama}</h5>
                <p class="text-muted">NIM: <strong>${data.mahasiswa.nim}</strong></p>
                <span class="badge bg-warning text-dark px-3 py-2">${data.message}</span>
            </div>
        `;
    } else {
        hasilDiv.innerHTML = `
            <div class="text-center py-3">
                <i class="mdi mdi-close-circle text-danger" style="font-size: 60px;"></i>
                <h4 class="text-danger mt-2">TIDAK DIKENAL</h4>
                <p class="text-muted mt-2">Serial: <code>${serial}</code></p>
                <span class="badge bg-danger px-3 py-2">Kartu tidak terdaftar</span>
            </div>
        `;
    }
}

function tampilkanError(pesan) {
    document.getElementById('hasilScan').innerHTML = `
        <div class="text-center py-3">
            <i class="mdi mdi-alert-circle text-danger" style="font-size: 60px;"></i>
            <p class="text-danger mt-2">${pesan}</p>
        </div>
    `;
}

function tambahLog(data, serial) {
    const waktu = new Date().toLocaleTimeString('id-ID');
    let badgeClass = 'bg-success';
    let label = 'HADIR';
    let nama = data.mahasiswa?.nama ?? serial;

    if (data.status === 'duplikat') { badgeClass = 'bg-warning text-dark'; label = 'DUPLIKAT'; }
    if (data.status === 'tidak_dikenal') { badgeClass = 'bg-danger'; label = 'TIDAK DIKENAL'; nama = serial; }

    logItems.unshift({ waktu, nama, badgeClass, label });

    const logDiv = document.getElementById('logScan');
    logDiv.innerHTML = logItems.map(item => `
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <div>
                <small class="text-muted">${item.waktu}</small>
                <div class="fw-bold">${item.nama}</div>
            </div>
            <span class="badge ${item.badgeClass}">${item.label}</span>
        </div>
    `).join('');
}

function clearLog() {
    logItems = [];
    document.getElementById('logScan').innerHTML = '<p class="text-center text-muted py-3 mb-0">Belum ada log</p>';
}
</script>
@endsection
