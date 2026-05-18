@extends('layouts.app')

@section('title', 'Detail Pesanan')

@section('style')
    <style>
        .order-detail-shell .order-qr-card {
            border: 1px dashed #d8d3ee;
            border-radius: 1rem;
            padding: 1rem;
            background: linear-gradient(135deg, #f8f7ff, #ffffff);
        }

        .order-detail-shell .order-qr-box {
            width: 210px;
            min-height: 210px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            background: #fff;
            border: 1px solid #ebe7ff;
        }

        .order-detail-shell .order-qr-box img {
            max-width: 100%;
            height: auto;
        }
    </style>
@endsection

@section('content')
    <div class="order-detail-shell">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-receipt"></i>
                </span>
                Detail Pesanan
            </h3>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('otp.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $penjualan->nomor_transaksi }}</li>
                </ul>
            </nav>
        </div>

        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                            <div>
                                <h4 class="font-weight-normal mb-2">Ringkasan Pesanan Customer</h4>
                                <h2 class="mb-2">{{ $penjualan->nomor_transaksi }}</h2>
                                <p class="mb-0">
                                    QR pesanan akan tersedia otomatis setelah pembayaran berhasil dan tetap bisa diakses kembali lewat halaman ini.
                                </p>
                            </div>
                            <div class="text-lg-end">
                                <span class="badge {{ $penjualan->paymentStatusBadgeClass() }} px-3 py-2">
                                    {{ $penjualan->paymentStatusLabel() }}
                                </span>
                                <div class="mt-3 d-flex gap-2 justify-content-lg-end flex-wrap">
                                    <a href="{{ $backUrl }}" class="btn btn-light text-primary">{{ $backLabel }}</a>
                                    @if ($showConfirmDemoPayment)
                                        <button type="button" class="btn btn-warning" id="confirm_demo_payment"
                                            data-url="{{ route('toko-buku.orders.confirm-demo-payment', $penjualan->nomor_transaksi) }}">
                                            Saya Sudah Transfer
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Daftar Menu Pesanan</h4>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Kode Barang</th>
                                        <th>Nama Menu</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($penjualan->items as $item)
                                        <tr>
                                            <td>{{ $item->barang_id }}</td>
                                            <td>{{ $item->nama_barang }}</td>
                                            <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                            <td>{{ $item->jumlah }}</td>
                                            <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 grid-margin stretch-card">
                <div class="card w-100">
                    <div class="card-body">
                        @if ($penjualan->isPaid())
                            <div class="order-qr-card mb-4">
                                <h5 class="mb-2">QR Pesanan</h5>
                                <p class="text-muted mb-3">
                                    QR ini berisi ID pesanan untuk kebutuhan validasi vendor.
                                </p>
                                <div class="d-flex flex-column align-items-start gap-3">
                                    <div class="order-qr-box" id="order_qr_code" aria-label="QR pesanan {{ $penjualan->nomor_transaksi }}"></div>
                                    <div class="w-100">
                                        <small class="text-muted d-block">ID Pesanan</small>
                                        <strong id="qr_order_value">{{ $penjualan->nomor_transaksi }}</strong>
                                        <div class="text-muted small mt-2">
                                            Status lunas pada
                                            {{ optional($penjualan->paid_at)->format('d M Y H:i') ?? now()->format('d M Y H:i') }}.
                                        </div>
                                        <div class="mt-3">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="downloadQRPDF()">
                                                <i class="mdi mdi-download"></i> Download QR PDF
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success" onclick="downloadQRImage()">
                                                <i class="mdi mdi-image"></i> Download QR PNG
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                QR pesanan akan muncul otomatis setelah pembayaran berstatus <strong>Lunas</strong>.
                            </div>
                        @endif

                        <h4 class="card-title">Ringkasan Pembayaran</h4>
                        <div class="mb-3">
                            <small class="text-muted d-block">Customer</small>
                            <strong>{{ $penjualan->customer_name ?? ($penjualan->user?->name ?? '-') }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Email</small>
                            <strong>{{ $penjualan->customer_email ?? '-' }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Metode</small>
                            <strong>{{ strtoupper($penjualan->payment_type ?? 'BELUM DIPILIH') }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Total</small>
                            <strong>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</strong>
                        </div>
                        <div class="mb-4">
                            <small class="text-muted d-block">Status Message</small>
                            <span>{{ $penjualan->status_message ?? 'Belum ada pembaruan status.' }}</span>
                        </div>

                        <h5 class="mb-3">Instruksi Pembayaran</h5>
                        @if ($paymentInstructions)
                            @foreach ($paymentInstructions as $instruction)
                                <div class="border rounded p-3 mb-2">
                                    <small class="text-muted d-block">{{ $instruction['label'] }}</small>
                                    @if (! empty($instruction['is_url']))
                                        <a href="{{ $instruction['value'] }}" target="_blank" rel="noopener">
                                            {{ $instruction['value'] }}
                                        </a>
                                    @else
                                        <strong>{{ $instruction['value'] }}</strong>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">
                                @if ($isManualDemoOrder)
                                    Selesaikan transfer demo lalu klik <strong>Saya Sudah Transfer</strong> untuk memperbarui status pembayaran.
                                @else
                                    Pembayaran diproses otomatis oleh sistem sesuai hasil transaksi.
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if ($penjualan->isPaid())
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    @endif
    <script src="{{ asset('assets/js/scanner/shared.js') }}"></script>
    <script src="{{ asset('assets/js/scanner/order-page.js') }}"></script>
    <script>
        window.ScannerApp.initOrderPage({
            csrfToken: @json(csrf_token()),
            isPaid: @json($penjualan->isPaid()),
            storageKey: @json('pesanan-qr:' . $penjualan->nomor_transaksi),
            confirmButtonElementId: 'confirm_demo_payment',
            qrElementId: 'order_qr_code',
            qrValueElementId: 'qr_order_value',
            order: @json($qrPayload),
        });

        @if ($penjualan->isPaid())
        // Download QR as PDF
        function downloadQRPDF() {
            const button = event.target;
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Generating...';

            try {
                const { jsPDF } = window.jspdf;
                const qrElement = document.getElementById('order_qr_code');
                const canvas = qrElement.querySelector('canvas');
                
                if (!canvas) {
                    alert('QR code belum siap. Tunggu sebentar lalu coba lagi.');
                    button.disabled = false;
                    button.innerHTML = originalText;
                    return;
                }

                // Create PDF
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4'
                });

                // Add title
                pdf.setFontSize(16);
                pdf.text('QR Code Pesanan', 105, 20, { align: 'center' });
                
                pdf.setFontSize(12);
                pdf.text('{{ $penjualan->nomor_transaksi }}', 105, 30, { align: 'center' });
                
                // Add QR code
                const imgData = canvas.toDataURL('image/png');
                const qrSize = 80; // 80mm
                const x = (210 - qrSize) / 2; // Center on A4 width
                pdf.addImage(imgData, 'PNG', x, 40, qrSize, qrSize);
                
                // Add info
                pdf.setFontSize(10);
                pdf.text('Customer: {{ $penjualan->customer_name ?? $penjualan->user->name ?? "-" }}', 105, 130, { align: 'center' });
                pdf.text('Total: Rp {{ number_format($penjualan->total, 0, ",", ".") }}', 105, 140, { align: 'center' });
                pdf.text('Status: Lunas', 105, 150, { align: 'center' });
                pdf.text('Tanggal: {{ optional($penjualan->paid_at)->format("d/m/Y H:i") ?? now()->format("d/m/Y H:i") }}', 105, 160, { align: 'center' });

                // Download
                pdf.save('QR-{{ $penjualan->nomor_transaksi }}.pdf');
                
                button.disabled = false;
                button.innerHTML = originalText;
            } catch (error) {
                console.error('Error generating PDF:', error);
                alert('Gagal generate PDF. Silakan coba lagi.');
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        // Download QR as PNG
        function downloadQRImage() {
            const button = event.target;
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Downloading...';

            try {
                const qrElement = document.getElementById('order_qr_code');
                const canvas = qrElement.querySelector('canvas');
                
                if (!canvas) {
                    alert('QR code belum siap. Tunggu sebentar lalu coba lagi.');
                    button.disabled = false;
                    button.innerHTML = originalText;
                    return;
                }

                // Convert canvas to blob and download
                canvas.toBlob(function(blob) {
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'QR-{{ $penjualan->nomor_transaksi }}.png';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                    
                    button.disabled = false;
                    button.innerHTML = originalText;
                });
            } catch (error) {
                console.error('Error downloading image:', error);
                alert('Gagal download gambar. Silakan coba lagi.');
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }
        @endif
    </script>
@endsection
