(function (window, document) {
    const ScannerApp = window.ScannerApp = window.ScannerApp || {};

    ScannerApp.initBarangScannerPage = function initBarangScannerPage(config) {
        const api = ScannerApp.createApiClient();
        const beepPlayer = ScannerApp.createBeepPlayer(config.beepUrl);
        const scanner = ScannerApp.createHtml5Scanner({
            elementId: config.readerElementId,
            formats: ['CODE_39', 'CODE_128', 'EAN_13', 'EAN_8', 'UPC_A', 'UPC_E'],
            fps: 10,
            qrbox: {
                width: 340,
                height: 180,
            },
            aspectRatio: 1.77,
        });

        const statusNode = document.getElementById(config.statusElementId);
        const emptyNode = document.getElementById(config.emptyStateElementId);
        const resultCardNode = document.getElementById(config.resultCardElementId);
        const rescanButton = document.getElementById(config.rescanButtonElementId);
        const fields = {
            idBarang: document.getElementById(config.fields.idBarang),
            namaBarang: document.getElementById(config.fields.namaBarang),
            hargaBarang: document.getElementById(config.fields.hargaBarang),
        };

        const normalizeScannedValue = (decodedText) => {
            const rawValue = String(decodedText || '').trim().toUpperCase();
            const masterBarangMatch = rawValue.match(/BRG\d{5}/);
            const compactBarangMatch = rawValue.match(/^\d{5}$/);

            if (masterBarangMatch) {
                return masterBarangMatch[0];
            }

            if (compactBarangMatch) {
                return `BRG${compactBarangMatch[0]}`;
            }

            return rawValue.split('|')[0].trim();
        };

        const setStatus = (message, tone = 'info') => {
            if (!statusNode) {
                return;
            }

            statusNode.className = `alert alert-${tone} mb-0`;
            statusNode.textContent = message;
        };

        const showEmptyState = () => {
            emptyNode?.classList.remove('d-none');
            resultCardNode?.classList.add('d-none');
        };

        const showResult = (payload) => {
            if (!resultCardNode) {
                return;
            }

            fields.idBarang.textContent = payload.id_barang;
            fields.namaBarang.textContent = payload.nama_barang;
            fields.hargaBarang.textContent = ScannerApp.formatRupiah(payload.harga_barang);

            emptyNode?.classList.add('d-none');
            resultCardNode.classList.remove('d-none');
        };

        const buildLookupUrl = (scannedValue) => config.lookupUrlTemplate.replace('__ID__', encodeURIComponent(scannedValue));

        const startScanner = async () => {
            showEmptyState();
            rescanButton.disabled = true;
            setStatus('Meminta akses kamera...', 'info');

            try {
                await scanner.start(async (decodedText) => {
                    const scannedValue = normalizeScannedValue(decodedText);

                    setStatus(`Barcode ${scannedValue} terbaca. Memuat data barang...`, 'primary');
                    await beepPlayer.play();
                    await scanner.stop();

                    try {
                        const response = await api.get(buildLookupUrl(scannedValue));
                        showResult(response.data);
                        setStatus('Scan berhasil. Klik "Scan Ulang" untuk membaca barcode lain.', 'success');
                    } catch (error) {
                        showEmptyState();
                        setStatus(error.message, 'danger');
                    } finally {
                        rescanButton.disabled = false;
                    }
                });

                setStatus('Arahkan kamera ke barcode label barang.', 'info');
            } catch (error) {
                setStatus(error.message, 'danger');
                rescanButton.disabled = false;
            }
        };

        rescanButton?.addEventListener('click', startScanner);

        window.addEventListener('beforeunload', () => {
            scanner.stop();
        });

        startScanner();
    };
})(window, document);
