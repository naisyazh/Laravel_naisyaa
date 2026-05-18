(function (window, document) {
    const ScannerApp = window.ScannerApp = window.ScannerApp || {};

    ScannerApp.initOrderPage = function initOrderPage(config) {
        const api = ScannerApp.createApiClient({
            csrfToken: config.csrfToken,
        });

        const confirmButton = document.getElementById(config.confirmButtonElementId);
        const qrContainer = document.getElementById(config.qrElementId);
        const qrValueNode = document.getElementById(config.qrValueElementId);

        const readFromStorage = () => {
            if (!config.storageKey) {
                return null;
            }

            try {
                const rawPayload = window.localStorage.getItem(config.storageKey);
                return rawPayload ? JSON.parse(rawPayload) : null;
            } catch (error) {
                return null;
            }
        };

        const saveToStorage = (payload) => {
            if (!config.storageKey || !payload || !payload.id_pesanan) {
                return;
            }

            try {
                window.localStorage.setItem(config.storageKey, JSON.stringify(payload));
            } catch (error) {
                // Ignore storage errors so the page still works without localStorage.
            }
        };

        const renderQrCode = (orderPayload) => {
            if (!config.isPaid || !qrContainer || typeof window.QRCode === 'undefined') {
                return;
            }

            const qrValue = orderPayload?.id_pesanan;

            if (!qrValue) {
                return;
            }

            qrContainer.innerHTML = '';

            new window.QRCode(qrContainer, {
                text: qrValue,
                width: 180,
                height: 180,
                colorDark: '#111827',
                colorLight: '#ffffff',
                correctLevel: window.QRCode.CorrectLevel.M,
            });

            if (qrValueNode) {
                qrValueNode.textContent = qrValue;
            }
        };

        const bindConfirmDemoPayment = () => {
            if (!confirmButton) {
                return;
            }

            confirmButton.addEventListener('click', async () => {
                confirmButton.disabled = true;
                confirmButton.textContent = 'Memproses...';

                try {
                    const response = await api.post(confirmButton.dataset.url);

                    await window.Swal.fire({
                        icon: 'success',
                        title: response.data.payment_status_label,
                        text: response.data.status_message,
                    });

                    window.location.reload();
                } catch (error) {
                    await window.Swal.fire({
                        icon: 'error',
                        title: 'Konfirmasi gagal',
                        text: error.message,
                    });
                } finally {
                    confirmButton.disabled = false;
                    confirmButton.textContent = 'Saya Sudah Transfer';
                }
            });
        };

        const persistedOrder = readFromStorage();
        const orderPayload = persistedOrder?.id_pesanan === config.order.id_pesanan
            ? {
                ...config.order,
                ...persistedOrder,
            }
            : config.order;

        if (config.isPaid) {
            saveToStorage(orderPayload);
        }

        renderQrCode(orderPayload);
        bindConfirmDemoPayment();
    };
})(window, document);
