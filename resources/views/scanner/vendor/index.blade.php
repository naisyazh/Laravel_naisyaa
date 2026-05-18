@extends('layouts.app')

@section('title', 'Scan QR Pesanan Customer')

@section('style')
    <style>
        .vendor-scanner-shell .scanner-frame {
            border: 1px solid #e7e1ff;
            border-radius: 1rem;
            padding: 1rem;
            background: #fff;
            min-height: 360px;
        }

        .vendor-scanner-shell #vendor_order_scanner_reader {
            width: 100%;
            overflow: hidden;
            border-radius: 0.85rem;
        }

        .vendor-scanner-shell #vendor_order_scanner_reader video {
            width: 100% !important;
            height: auto !important;
            border-radius: 0.85rem;
        }

        .vendor-scanner-shell .result-summary {
            border: 1px dashed #d9d0ff;
            border-radius: 1rem;
            padding: 1rem;
            background: #faf8ff;
        }

        .vendor-scanner-shell .summary-item {
            padding: 0.85rem 1rem;
            border-radius: 0.85rem;
            background: #fff;
            border: 1px solid #ece7ff;
        }
    </style>
@endsection

@section('content')
    <div class="vendor-scanner-shell">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-qrcode"></i>
                </span>
                Scan QR Pesanan Customer
            </h3>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('otp.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vendor.orders.index') }}">Transaksi Buku</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Scanner QR</li>
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
                                    Gunakan kamera untuk membaca QR pesanan customer. Scanner berhenti otomatis setelah berhasil scan.
                                </p>
                            </div>
                            <button type="button" class="btn btn-gradient-primary" id="restart_vendor_scanner">
                                Scan Ulang
                            </button>
                        </div>

                        <div class="scanner-frame">
                            <div id="vendor_order_scanner_reader"></div>
                        </div>

                        <div class="alert alert-info mt-3 mb-0" id="vendor_scanner_status">
                            Menyiapkan kamera scanner...
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 grid-margin stretch-card">
                <div class="card w-100">
                    <div class="card-body">
                        <h4 class="card-title mb-1">Detail Pesanan</h4>
                        <p class="card-description mb-3">
                            Hasil scan akan menampilkan menu yang dipesan customer dan status pembayaran saat ini.
                        </p>

                        <div class="result-summary" id="vendor_result_empty">
                            Belum ada QR yang terbaca. Arahkan kamera ke QR pesanan customer untuk memulai validasi.
                        </div>

                        <div class="d-none" id="vendor_result_card">
                            <div class="summary-item mb-3">
                                <small class="text-muted d-block">ID Pesanan</small>
                                <strong id="vendor_result_id_pesanan">-</strong>
                            </div>
                            <div class="summary-item mb-3">
                                <small class="text-muted d-block">Customer</small>
                                <strong id="vendor_result_customer_name">-</strong>
                            </div>
                            <div class="summary-item mb-3">
                                <small class="text-muted d-block">Status Pembayaran</small>
                                <strong id="vendor_result_status_label">-</strong>
                                <div class="text-muted small mt-2" id="vendor_result_status_message">-</div>
                            </div>
                            <div class="summary-item mb-3">
                                <small class="text-muted d-block">Total Pesanan</small>
                                <strong id="vendor_result_total">-</strong>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Menu</th>
                                            <th>Qty</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="vendor_result_menu_body"></tbody>
                                </table>
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
    <script src="{{ asset('assets/js/scanner/vendor-order-page.js') }}"></script>
    <script>
        window.ScannerApp.initVendorOrderScannerPage({
            beepUrl: @json($beepAudioUrl),
            lookupUrlTemplate: @json(url('/vendor/orders/lookup/__ID__')),
            readerElementId: 'vendor_order_scanner_reader',
            statusElementId: 'vendor_scanner_status',
            emptyStateElementId: 'vendor_result_empty',
            resultCardElementId: 'vendor_result_card',
            rescanButtonElementId: 'restart_vendor_scanner',
            menuTableBodyElementId: 'vendor_result_menu_body',
            fields: {
                idPesanan: 'vendor_result_id_pesanan',
                customerName: 'vendor_result_customer_name',
                total: 'vendor_result_total',
                statusLabel: 'vendor_result_status_label',
                statusMessage: 'vendor_result_status_message',
            },
        });
    </script>
@endsection
