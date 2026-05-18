@extends('layouts.app')

@section('title', 'Scan Barcode Barang')

@section('style')
    <style>
        .scanner-shell .scanner-frame {
            border: 1px solid #e7e1ff;
            border-radius: 1rem;
            padding: 1rem;
            background: #fff;
            min-height: 360px;
        }

        .scanner-shell #barang_scanner_reader {
            width: 100%;
            overflow: hidden;
            border-radius: 0.85rem;
        }

        .scanner-shell #barang_scanner_reader video {
            width: 100% !important;
            height: auto !important;
            border-radius: 0.85rem;
        }

        .scanner-shell .result-panel {
            border: 1px dashed #d9d0ff;
            border-radius: 1rem;
            padding: 1rem;
            background: #faf8ff;
        }

        .scanner-shell .result-item {
            padding: 0.9rem 1rem;
            border-radius: 0.85rem;
            background: #fff;
            border: 1px solid #ece7ff;
        }
    </style>
@endsection

@section('content')
    <div class="scanner-shell">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-barcode"></i>
                </span>
                Scan Barcode Barang
            </h3>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('otp.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('barang.index') }}">Master Buku Toko</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Scanner Barcode</li>
                </ul>
            </nav>
        </div>

        <div class="row">
            <div class="col-lg-7 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h4 class="card-title mb-1">Kamera Scanner</h4>
                                <p class="card-description mb-0">
                                    Arahkan kamera ke label barcode barang. Scanner akan berhenti otomatis setelah kode terbaca.
                                </p>
                            </div>
                            <button type="button" class="btn btn-gradient-primary" id="restart_barang_scanner">
                                Scan Ulang
                            </button>
                        </div>

                        <div class="scanner-frame">
                            <div id="barang_scanner_reader"></div>
                        </div>

                        <div class="alert alert-info mt-3 mb-0" id="barang_scanner_status">
                            Menyiapkan kamera scanner...
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 grid-margin stretch-card">
                <div class="card w-100">
                    <div class="card-body">
                        <h4 class="card-title mb-1">Hasil Scan</h4>
                        <p class="card-description mb-3">
                            Setelah barcode berhasil dibaca, sistem menampilkan detail barang dari database.
                        </p>

                        <div class="result-panel" id="barang_result_empty">
                            Belum ada barcode yang terbaca. Siapkan label barang lalu arahkan kamera ke barcode.
                        </div>

                        <div class="d-none" id="barang_result_card">
                            <div class="result-item mb-3">
                                <small class="text-muted d-block">ID Barang</small>
                                <strong id="result_id_barang">-</strong>
                            </div>
                            <div class="result-item mb-3">
                                <small class="text-muted d-block">Nama Barang</small>
                                <strong id="result_nama_barang">-</strong>
                            </div>
                            <div class="result-item">
                                <small class="text-muted d-block">Harga Barang</small>
                                <strong id="result_harga_barang">-</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/js/scanner/shared.js') }}"></script>
    <script src="{{ asset('assets/js/scanner/barcode-page.js') }}"></script>
    <script>
        window.ScannerApp.initBarangScannerPage({
            beepUrl: @json($beepAudioUrl),
            lookupUrlTemplate: @json(url('/barang/__ID__')),
            readerElementId: 'barang_scanner_reader',
            statusElementId: 'barang_scanner_status',
            emptyStateElementId: 'barang_result_empty',
            resultCardElementId: 'barang_result_card',
            rescanButtonElementId: 'restart_barang_scanner',
            fields: {
                idBarang: 'result_id_barang',
                namaBarang: 'result_nama_barang',
                hargaBarang: 'result_harga_barang',
            },
        });
    </script>
@endsection
