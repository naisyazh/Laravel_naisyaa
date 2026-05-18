@extends('layouts.app')

@section('title', 'Tambah Toko Baru')

@section('style')
    <style>
        .location-card {
            border: 2px dashed #d9d0ff;
            border-radius: 1rem;
            padding: 1.5rem;
            background: #faf8ff;
        }

        .location-info {
            background: white;
            border: 1px solid #ece7ff;
            border-radius: 0.85rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .accuracy-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }

        .accuracy-good { background-color: #28a745; }
        .accuracy-medium { background-color: #ffc107; }
        .accuracy-poor { background-color: #dc3545; }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-store-plus"></i>
            </span>
            Tambah Toko Baru
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('otp.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('toko.index') }}">Master Data Toko</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Toko</li>
            </ul>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Input Titik Awal</h4>
                    <p class="card-description">
                        Ambil koordinat GPS lokasi toko dengan accuracy terbaik
                    </p>

                    <form action="{{ route('toko.store') }}" method="POST" id="formToko">
                        @csrf

                        <div class="form-group">
                            <label for="nama_toko">Nama Toko <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_toko') is-invalid @enderror" 
                                   id="nama_toko" name="nama_toko" value="{{ old('nama_toko') }}" required>
                            @error('nama_toko')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea class="form-control @error('alamat') is-invalid @enderror" 
                                      id="alamat" name="alamat" rows="3">{{ old('alamat') }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="location-card">
                            <h5 class="mb-3">Geolocation</h5>

                            <div class="location-info" id="locationInfo" style="display: none;">
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Latitude</small>
                                        <strong id="displayLatitude">-</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Longitude</small>
                                        <strong id="displayLongitude">-</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Accuracy</small>
                                        <strong>
                                            <span class="accuracy-indicator" id="accuracyIndicator"></span>
                                            <span id="displayAccuracy">-</span> m
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="latitude" id="latitude" required>
                            <input type="hidden" name="longitude" id="longitude" required>
                            <input type="hidden" name="accuracy" id="accuracy" required>

                            <button type="button" class="btn btn-gradient-primary btn-lg w-100" id="btnAmbilLokasi">
                                <i class="mdi mdi-crosshairs-gps"></i> Ambil Lokasi
                            </button>

                            <div class="alert alert-info mt-3 mb-0" id="locationStatus">
                                Klik tombol "Ambil Lokasi" untuk mendapatkan koordinat GPS toko.
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-gradient-success me-2" id="btnSubmit" disabled>
                                <i class="mdi mdi-content-save"></i> Simpan Toko
                            </button>
                            <a href="{{ route('toko.index') }}" class="btn btn-light">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Panduan</h4>
                    
                    <div class="alert alert-warning">
                        <strong>Perhatian!</strong>
                        <ul class="mb-0 ps-3">
                            <li>Pastikan GPS aktif</li>
                            <li>Berada di lokasi toko</li>
                            <li>Tunggu accuracy ≤ 50m</li>
                            <li>Lokasi outdoor lebih akurat</li>
                        </ul>
                    </div>

                    <h5 class="mt-4">Accuracy Level:</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <span class="accuracy-indicator accuracy-good"></span>
                            <strong>≤ 20m:</strong> Sangat Baik
                        </li>
                        <li class="mb-2">
                            <span class="accuracy-indicator accuracy-medium"></span>
                            <strong>21-50m:</strong> Baik
                        </li>
                        <li class="mb-2">
                            <span class="accuracy-indicator accuracy-poor"></span>
                            <strong>> 50m:</strong> Kurang Akurat
                        </li>
                    </ul>

                    <div class="alert alert-info mt-3">
                        <small>
                            <strong>Tips:</strong> Sistem akan terus mencari posisi dengan accuracy terbaik 
                            hingga mencapai target ≤ 50m atau timeout 20 detik.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/js/geolocation/accurate-position.js') }}"></script>
    <script>
        const btnAmbilLokasi = document.getElementById('btnAmbilLokasi');
        const btnSubmit = document.getElementById('btnSubmit');
        const locationStatus = document.getElementById('locationStatus');
        const locationInfo = document.getElementById('locationInfo');

        btnAmbilLokasi.addEventListener('click', async function() {
            btnAmbilLokasi.disabled = true;
            btnAmbilLokasi.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Mengambil lokasi...';
            locationStatus.className = 'alert alert-info mt-3 mb-0';
            locationStatus.textContent = 'Mencari posisi GPS dengan accuracy terbaik...';

            try {
                const position = await getAccuratePosition(50, 20000);
                
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const acc = position.coords.accuracy;

                // Set values
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                document.getElementById('accuracy').value = acc;

                // Display values
                document.getElementById('displayLatitude').textContent = lat.toFixed(8);
                document.getElementById('displayLongitude').textContent = lng.toFixed(8);
                document.getElementById('displayAccuracy').textContent = acc.toFixed(2);

                // Set accuracy indicator color
                const indicator = document.getElementById('accuracyIndicator');
                if (acc <= 20) {
                    indicator.className = 'accuracy-indicator accuracy-good';
                } else if (acc <= 50) {
                    indicator.className = 'accuracy-indicator accuracy-medium';
                } else {
                    indicator.className = 'accuracy-indicator accuracy-poor';
                }

                // Show location info
                locationInfo.style.display = 'block';

                // Update status
                locationStatus.className = 'alert alert-success mt-3 mb-0';
                locationStatus.textContent = `Lokasi berhasil didapatkan dengan accuracy ${acc.toFixed(2)}m`;

                // Enable submit button
                btnSubmit.disabled = false;

                // Reset button
                btnAmbilLokasi.disabled = false;
                btnAmbilLokasi.innerHTML = '<i class="mdi mdi-crosshairs-gps"></i> Ambil Ulang Lokasi';

            } catch (error) {
                console.error('Error getting location:', error);
                
                locationStatus.className = 'alert alert-danger mt-3 mb-0';
                locationStatus.textContent = 'Gagal mendapatkan lokasi: ' + error.message;

                btnAmbilLokasi.disabled = false;
                btnAmbilLokasi.innerHTML = '<i class="mdi mdi-crosshairs-gps"></i> Coba Lagi';
            }
        });
    </script>
@endsection
