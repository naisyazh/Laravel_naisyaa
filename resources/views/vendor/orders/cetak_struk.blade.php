<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Pesanan - {{ $penjualan->nomor_transaksi }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            padding: 10mm;
            width: 80mm;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px dashed #000;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header .vendor-name {
            font-size: 14px;
            font-weight: bold;
        }

        .header .vendor-info {
            font-size: 10px;
            margin-top: 3px;
        }

        .section {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .info-label {
            font-weight: bold;
        }

        .items-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .items-table th {
            text-align: left;
            font-weight: bold;
            padding-bottom: 5px;
            border-bottom: 1px solid #000;
        }

        .items-table td {
            padding: 5px 0;
        }

        .items-table .item-name {
            font-weight: bold;
        }

        .items-table .item-code {
            font-size: 10px;
            color: #666;
        }

        .items-table .text-right {
            text-align: right;
        }

        .total-section {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #000;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .total-row.grand-total {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #000;
        }

        .payment-status {
            text-align: center;
            margin: 10px 0;
            padding: 8px;
            background: #000;
            color: #fff;
            font-weight: bold;
            font-size: 14px;
        }

        .payment-status.paid {
            background: #2ecc71;
        }

        .payment-status.pending {
            background: #f39c12;
        }

        .qr-section {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            border: 2px dashed #000;
        }

        .qr-section .qr-title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .qr-code {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            background: #fff;
            padding: 5px;
            border: 1px solid #000;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px dashed #000;
            font-size: 10px;
        }

        .footer .thank-you {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
        }

        @media print {
            body {
                padding: 5mm;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>STRUK PESANAN</h1>
        <div class="vendor-name">{{ $penjualan->vendor->name ?? 'Toko Buku' }}</div>
        <div class="vendor-info">{{ $penjualan->vendor->email ?? '' }}</div>
    </div>

    <!-- Transaction Info -->
    <div class="section">
        <div class="section-title">Informasi Transaksi</div>
        <div class="info-row">
            <span class="info-label">No. Transaksi:</span>
            <span>{{ $penjualan->nomor_transaksi }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal:</span>
            <span>{{ $penjualan->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Customer:</span>
            <span>{{ $penjualan->customer_name ?? $penjualan->user->name ?? '-' }}</span>
        </div>
        @if($penjualan->customer_email)
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span>{{ $penjualan->customer_email }}</span>
        </div>
        @endif
    </div>

    <!-- Items -->
    <div class="section">
        <div class="section-title">Detail Pesanan</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penjualan->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->nama_barang }}</div>
                        <div class="item-code">{{ $item->barang_id }}</div>
                    </td>
                    <td class="text-right">{{ $item->jumlah }}</td>
                    <td class="text-right">{{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Total -->
    <div class="total-section">
        <div class="total-row grand-total">
            <span>TOTAL:</span>
            <span>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Payment Status -->
    <div class="payment-status {{ $penjualan->isPaid() ? 'paid' : 'pending' }}">
        {{ $penjualan->paymentStatusLabel() }}
    </div>

    <!-- Payment Info -->
    <div class="section">
        <div class="section-title">Informasi Pembayaran</div>
        <div class="info-row">
            <span class="info-label">Metode:</span>
            <span>{{ strtoupper($penjualan->payment_type ?? 'BELUM DIPILIH') }}</span>
        </div>
        @if($penjualan->isPaid() && $penjualan->paid_at)
        <div class="info-row">
            <span class="info-label">Dibayar:</span>
            <span>{{ $penjualan->paid_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
        @if($penjualan->status_message)
        <div style="margin-top: 5px; font-size: 10px;">
            {{ $penjualan->status_message }}
        </div>
        @endif
    </div>

    <!-- QR Code (only if paid) -->
    @if($penjualan->isPaid())
    <div class="qr-section">
        <div class="qr-title">QR CODE PESANAN</div>
        <div class="qr-code" id="qr_code"></div>
        <div style="margin-top: 10px; font-size: 10px;">
            Scan QR code ini untuk validasi pesanan
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div class="thank-you">TERIMA KASIH</div>
        <div>Atas kepercayaan Anda berbelanja di toko kami</div>
        <div style="margin-top: 5px;">
            Dicetak: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <!-- Download Button (Hidden on print) -->
    <div style="text-align: center; margin: 20px 0; page-break-after: avoid;" class="no-print">
        <button onclick="downloadPDF()" style="background: #3498db; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: bold;">
            📥 Download PDF
        </button>
        <button onclick="window.print()" style="background: #2ecc71; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: bold; margin-left: 10px;">
            🖨️ Print
        </button>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>

    <!-- Scripts -->
    @if($penjualan->isPaid())
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    @endif
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        @if($penjualan->isPaid())
        // Generate QR Code
        new QRCode(document.getElementById('qr_code'), {
            text: '{{ $penjualan->nomor_transaksi }}',
            width: 150,
            height: 150,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
        @endif

        // Download PDF function
        async function downloadPDF() {
            const button = event.target;
            button.disabled = true;
            button.textContent = '⏳ Generating PDF...';

            try {
                const { jsPDF } = window.jspdf;
                
                // Hide buttons before capture
                document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
                
                // Capture the body as canvas
                const canvas = await html2canvas(document.body, {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#ffffff'
                });
                
                // Show buttons again
                document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
                
                // Calculate dimensions for 80mm width
                const imgWidth = 80; // 80mm
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                
                // Create PDF
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: [80, imgHeight + 10] // 80mm width, auto height
                });
                
                const imgData = canvas.toDataURL('image/png');
                pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
                
                // Download
                pdf.save('Struk-{{ $penjualan->nomor_transaksi }}.pdf');
                
                button.disabled = false;
                button.textContent = '📥 Download PDF';
            } catch (error) {
                console.error('Error generating PDF:', error);
                alert('Gagal generate PDF. Silakan coba lagi.');
                button.disabled = false;
                button.textContent = '📥 Download PDF';
            }
        }
    </script>

</body>
</html>
