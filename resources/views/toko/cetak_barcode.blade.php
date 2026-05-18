<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cetak Barcode Toko</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .barcode-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10mm;
            padding: 10mm;
        }

        .barcode-item {
            width: 85mm;
            height: 54mm;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5mm;
            box-sizing: border-box;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .barcode-item h3 {
            margin: 0 0 3mm 0;
            font-size: 14pt;
            color: #333;
        }

        .barcode-item .barcode-code {
            font-size: 18pt;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            margin: 3mm 0;
            color: #000;
        }

        .barcode-item .barcode-visual {
            margin: 3mm 0;
        }

        .barcode-item .barcode-visual svg {
            max-width: 100%;
            height: auto;
        }

        .barcode-item .toko-info {
            font-size: 9pt;
            color: #666;
            margin-top: 2mm;
        }

        .barcode-item .coordinates {
            font-size: 7pt;
            color: #999;
            margin-top: 1mm;
        }

        @media print {
            .barcode-container {
                gap: 5mm;
            }
        }
    </style>
</head>
<body>
    <div class="barcode-container">
        @foreach($tokos as $toko)
            <div class="barcode-item">
                <h3>{{ $toko->nama_toko }}</h3>
                
                <div class="barcode-visual">
                    <svg id="barcode-{{ $toko->id }}"></svg>
                </div>
                
                <div class="barcode-code">{{ $toko->barcode }}</div>
                
                <div class="toko-info">
                    @if($toko->alamat)
                        {{ Str::limit($toko->alamat, 50) }}
                    @endif
                </div>
                
                <div class="coordinates">
                    {{ $toko->latitude }}, {{ $toko->longitude }}
                </div>
            </div>
        @endforeach
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

    <!-- JsBarcode Library -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        // Generate barcodes for all tokos
        @foreach($tokos as $toko)
            JsBarcode("#barcode-{{ $toko->id }}", "{{ $toko->barcode }}", {
                format: "CODE39",
                width: 2,
                height: 50,
                displayValue: false,
                margin: 0
            });
        @endforeach

        // Download PDF function
        async function downloadPDF() {
            const button = event.target;
            button.disabled = true;
            button.textContent = '⏳ Generating PDF...';

            try {
                const { jsPDF } = window.jspdf;
                
                // Hide buttons before capture
                document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
                
                // Capture the container as canvas
                const canvas = await html2canvas(document.querySelector('.barcode-container'), {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#ffffff'
                });
                
                // Show buttons again
                document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
                
                // Calculate dimensions for A4
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4'
                });
                
                const imgWidth = 190; // A4 width minus margins
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                
                const imgData = canvas.toDataURL('image/png');
                pdf.addImage(imgData, 'PNG', 10, 10, imgWidth, imgHeight);
                
                // Download
                pdf.save('Barcode-Toko-{{ now()->format("Y-m-d") }}.pdf');
                
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
