@extends('layouts.app')

@section('title', 'Kunjungan Toko')

@section('style')
    <style>
        .scanner-frame {
            border: 1px solid #e7e1ff;
            border-radius: 1rem;
            padding: 1rem;
            background: #fff;
            min-height: 360px;
        }

        #barcode_scanner_reader {
            width: 100%;
            overflow: hidden;
            border-radius: 0.85rem;
        }

        #barcode_scanner_reader video {
            width: 100% !important;
            height: auto !important;
            border-radius: 0.85rem;
        }

        .result-panel {
            border: 2px dashed #d9d0ff;
            border-radius: 1rem;
            padding: 1.5rem;
            background: #faf8ff;
        }

        .info-box {
            background: white;
            border: 1px solid #ece7ff;
            border-radius: 0.85rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .status-diterima {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .status-ditolak {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-map-marker-check"></i>
            </span>
            Kunjungan Toko
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('otp.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kunjungan Toko</li>
            </ul>
        </nav>
    </div>

    <div class="row">
        <!-- Scanner Section -->
        <div class="col-lg-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Barcode Scanner</h4>
                    
                    <div class="scanner-frame">
                        <div id="barcode_scanner_reader"></div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0" id="scanner_status">
                        Menyiapkan scanner...
                    </div>

                    <button type="button" class="btn btn-gradient-primary w-100 mt-3" id="btn_scan_ulang" style="display: none;">
                        <i class="mdi mdi-barcode-scan"></i> Scan Ulang
                    </button>
                </div>
            </div>
        </div>

        <!-- Result Section -->
        <div class="col-lg-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Data Toko & Kunjungan</h4>

                    <!-- Empty State -->
                    <div class="result-panel" id="empty_state">
                        <p class="text-muted mb-0">
                            Scan barcode toko untuk memulai proses kunjungan.
                        </p>
                    </div>

                    <!-- Toko Info -->
                    <div id="toko_info" style="display: none;">
                        <h5 class="mb-3">Data Toko (dari Database)</h5>
                        <div class="info-box">
                            <small class="text-muted d-block">Barcode</small>
                            <strong id="toko_barcode">-</strong>
                        </div>
                        <div class="info-box">
                            <small class="text-muted d-block">Nama Toko</small>
                            <strong id="toko_nama">-</strong>
                        </div>
                        <div class="info-box">
                            <small class="text-muted d-block">Alamat</small>
                            <div id="toko_alamat">-</div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="info-box">
                                    <small class="text-muted d-block">Latitude</small>
                                    <strong id="toko_lat">-</strong>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="info-box">
                                    <small class="text-muted d-block">Longitude</small>
                                    <strong id="toko_lng">-</strong>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="info-box">
                                    <small class="text-muted d-block">Accuracy</small>
                                    <strong id="toko_acc">-</strong> m
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-gradient-success btn-lg w-100 mt-3" id="btn_ambil_lokasi">
                            <i class="mdi mdi-crosshairs-gps"></i> Ambil Lokasi Saya
                        </button>
                    </div>

                    <!-- Kunjungan Result -->
                    <div id="kunjungan_result" style="display: none;">
                        <h5 class="mb-3">Hasil Validasi Kunjungan</h5>
                        <div class="alert" id="result_status">
                            <h5 id="result_status_label">-</h5>
                            <hr>
                            <p class="mb-2"><strong>Jarak:</strong> <span id="result_jarak">-</span> meter</p>
                            <p class="mb-2"><strong>Threshold:</strong> <span id="result_threshold">-</span> meter</p>
                            <p class="mb-0"><small id="result_keterangan">-</small></p>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="info-box">
                                    <small class="text-muted d-block">Sales Lat</small>
                                    <strong id="sales_lat">-</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box">
                                    <small class="text-muted d-block">Sales Lng</small>
                                    <strong id="sales_lng">-</strong>
                                </div>
                            </div>
                        </div>
                        <div class="info-box">
                            <small class="text-muted d-block">Sales Accuracy</small>
                            <strong id="sales_acc">-</strong> m
                        </div>

                        <a href="{{ route('kunjungan-toko.riwayat') }}" class="btn btn-gradient-info w-100 mt-3">
                            <i class="mdi mdi-history"></i> Lihat Riwayat Kunjungan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/js/scanner/shared.js') }}"></script>
    <script src="{{ asset('assets/js/geolocation/accurate-position.js') }}"></script>
    <script src="{{ asset('assets/js/geolocation/kunjungan-page.js') }}"></script>
    <script>
        window.KunjunganApp.init({
            beepUrl: '{{ asset('assets/audio/scanner-beep.mpeg') }}',
            lookupTokoUrl: '{{ url('/toko') }}/__BARCODE__/detail',
            submitKunjunganUrl: '{{ route('kunjungan-toko.submit') }}',
            csrfToken: '{{ csrf_token() }}',
        });
    </script>
@endsection
