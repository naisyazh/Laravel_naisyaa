(function (window, document) {
    const KunjunganApp = window.KunjunganApp = window.KunjunganApp || {};

    let currentToko = null;
    let scanner = null;
    let config = null;

    KunjunganApp.init = function(options) {
        config = options;
        
        const api = window.ScannerApp.createApiClient();
        const beepPlayer = window.ScannerApp.createBeepPlayer(config.beepUrl);
        
        scanner = window.ScannerApp.createHtml5Scanner({
            elementId: 'barcode_scanner_reader',
            formats: ['CODE_39', 'CODE_128', 'EAN_13', 'EAN_8', 'UPC_A', 'UPC_E'],
            fps: 10,
            qrbox: {
                width: 340,
                height: 180,
            },
            aspectRatio: 1.77,
        });

        const statusNode = document.getElementById('scanner_status');
        const btnScanUlang = document.getElementById('btn_scan_ulang');
        const btnAmbilLokasi = document.getElementById('btn_ambil_lokasi');

        const setStatus = (message, tone = 'info') => {
            if (!statusNode) return;
            statusNode.className = `alert alert-${tone} mt-3 mb-0`;
            statusNode.textContent = message;
        };

        const showTokoInfo = (toko) => {
            document.getElementById('empty_state').style.display = 'none';
            document.getElementById('toko_info').style.display = 'block';
            document.getElementById('kunjungan_result').style.display = 'none';

            document.getElementById('toko_barcode').textContent = toko.barcode;
            document.getElementById('toko_nama').textContent = toko.nama_toko;
            document.getElementById('toko_alamat').textContent = toko.alamat || '-';
            document.getElementById('toko_lat').textContent = toko.latitude.toFixed(8);
            document.getElementById('toko_lng').textContent = toko.longitude.toFixed(8);
            document.getElementById('toko_acc').textContent = toko.accuracy.toFixed(2);

            currentToko = toko;
        };

        const showKunjunganResult = (result) => {
            document.getElementById('toko_info').style.display = 'none';
            document.getElementById('kunjungan_result').style.display = 'block';

            const resultStatus = document.getElementById('result_status');
            const statusLabel = document.getElementById('result_status_label');

            if (result.diterima) {
                resultStatus.className = 'alert status-diterima';
                statusLabel.innerHTML = '<i class="mdi mdi-check-circle"></i> KUNJUNGAN DITERIMA ✓';
            } else {
                resultStatus.className = 'alert status-ditolak';
                statusLabel.innerHTML = '<i class="mdi mdi-close-circle"></i> KUNJUNGAN DITOLAK ✗';
            }

            document.getElementById('result_jarak').textContent = result.jarak_meter;
            document.getElementById('result_threshold').textContent = result.threshold_efektif;
            document.getElementById('result_keterangan').textContent = result.keterangan;

            btnScanUlang.style.display = 'block';
        };

        // Start scanner
        (async () => {
            try {
                setStatus('Arahkan kamera ke barcode toko.', 'info');

                await scanner.start(async (decodedText) => {
                    await beepPlayer.play();
                    await scanner.stop();

                    setStatus('Memuat data toko...', 'info');

                    try {
                        const url = config.lookupTokoUrl.replace('__BARCODE__', decodedText);
                        const response = await api.get(url);

                        showTokoInfo(response.data);
                        setStatus('Data toko berhasil dimuat. Klik "Ambil Lokasi Saya" untuk validasi kunjungan.', 'success');

                    } catch (error) {
                        setStatus(error.message, 'danger');
                        btnScanUlang.style.display = 'block';
                    }
                });

            } catch (error) {
                setStatus(error.message, 'danger');
            }
        })();

        // Scan ulang button
        btnScanUlang.addEventListener('click', () => {
            window.location.reload();
        });

        // Ambil lokasi button
        btnAmbilLokasi.addEventListener('click', async () => {
            if (!currentToko) {
                alert('Data toko tidak ditemukan');
                return;
            }

            btnAmbilLokasi.disabled = true;
            btnAmbilLokasi.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Mengambil lokasi...';

            try {
                // Get accurate position
                const position = await getAccuratePosition(50, 20000);

                const salesLat = position.coords.latitude;
                const salesLng = position.coords.longitude;
                const salesAcc = position.coords.accuracy;

                // Submit kunjungan
                const response = await api.post(config.submitKunjunganUrl, {
                    toko_id: currentToko.id,
                    toko_latitude: currentToko.latitude,
                    toko_longitude: currentToko.longitude,
                    toko_accuracy: currentToko.accuracy,
                    sales_latitude: salesLat,
                    sales_longitude: salesLng,
                    sales_accuracy: salesAcc,
                    threshold_meter: 300,
                });

                // Show result
                document.getElementById('sales_lat').textContent = salesLat.toFixed(8);
                document.getElementById('sales_lng').textContent = salesLng.toFixed(8);
                document.getElementById('sales_acc').textContent = salesAcc.toFixed(2);

                showKunjunganResult(response.data);

                if (response.data.diterima) {
                    await beepPlayer.play();
                }

            } catch (error) {
                alert('Gagal: ' + error.message);
                btnAmbilLokasi.disabled = false;
                btnAmbilLokasi.innerHTML = '<i class="mdi mdi-crosshairs-gps"></i> Coba Lagi';
            }
        });
    };

})(window, document);
