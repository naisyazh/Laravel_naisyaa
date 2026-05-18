(function (window, document) {
    const ScannerApp = window.ScannerApp = window.ScannerApp || {};

    ScannerApp.initVendorOrderScannerPage = function initVendorOrderScannerPage(config) {
        const api = ScannerApp.createApiClient();
        const beepPlayer = ScannerApp.createBeepPlayer(config.beepUrl);
        const scanner = ScannerApp.createHtml5Scanner({
            elementId: config.readerElementId,
            formats: ['QR_CODE'],
            fps: 10,
            qrbox: {
                width: 280,
                height: 280,
            },
        });

        const statusNode = document.getElementById(config.statusElementId);
        const emptyNode = document.getElementById(config.emptyStateElementId);
        const resultCardNode = document.getElementById(config.resultCardElementId);
        const rescanButton = document.getElementById(config.rescanButtonElementId);
        const menuTableBody = document.getElementById(config.menuTableBodyElementId);
        const fields = {
            idPesanan: document.getElementById(config.fields.idPesanan),
            customerName: document.getElementById(config.fields.customerName),
            total: document.getElementById(config.fields.total),
            statusLabel: document.getElementById(config.fields.statusLabel),
            statusMessage: document.getElementById(config.fields.statusMessage),
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
            if (menuTableBody) {
                menuTableBody.innerHTML = '';
            }
        };

        const renderMenuRows = (items) => {
            if (!menuTableBody) {
                return;
            }

            if (!items.length) {
                menuTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Belum ada item menu pada pesanan ini.</td></tr>';
                return;
            }

            menuTableBody.innerHTML = items.map((item) => `
                <tr>
                    <td>${item.id_barang}</td>
                    <td>${item.nama_barang}</td>
                    <td>${item.jumlah}</td>
                    <td>${ScannerApp.formatRupiah(item.subtotal)}</td>
                </tr>
            `).join('');
        };

        const showResult = (payload) => {
            fields.idPesanan.textContent = payload.id_pesanan;
            fields.customerName.textContent = payload.customer_name || '-';
            fields.total.textContent = ScannerApp.formatRupiah(payload.total);
            fields.statusLabel.textContent = payload.status_pembayaran_label;
            fields.statusMessage.textContent = payload.status_message || 'Belum ada pembaruan status.';
            renderMenuRows(payload.daftar_menu || []);

            emptyNode?.classList.add('d-none');
            resultCardNode?.classList.remove('d-none');
        };

        const buildLookupUrl = (scannedValue) => config.lookupUrlTemplate.replace('__ID__', encodeURIComponent(scannedValue));

        const startScanner = async () => {
            showEmptyState();
            rescanButton.disabled = true;
            setStatus('Meminta akses kamera...', 'info');

            try {
                await scanner.start(async (decodedText) => {
                    const scannedValue = String(decodedText || '').trim();

                    setStatus(`QR pesanan ${scannedValue} terbaca. Memuat detail pesanan...`, 'primary');
                    await beepPlayer.play();
                    await scanner.stop();

                    try {
                        const response = await api.get(buildLookupUrl(scannedValue));
                        showResult(response.data);
                        setStatus('Detail pesanan berhasil dimuat. Klik "Scan Ulang" untuk scan customer berikutnya.', 'success');
                    } catch (error) {
                        showEmptyState();
                        setStatus(error.message, 'danger');
                    } finally {
                        rescanButton.disabled = false;
                    }
                });

                setStatus('Arahkan kamera ke QR pesanan customer.', 'info');
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
