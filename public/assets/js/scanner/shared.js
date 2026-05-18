(function (window) {
    const ScannerApp = window.ScannerApp = window.ScannerApp || {};
    let html5QrcodeScriptPromise = null;

    ScannerApp.formatRupiah = function formatRupiah(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    };

    ScannerApp.createApiClient = function createApiClient(options = {}) {
        const csrfToken = options.csrfToken || document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        const parsePayload = async (response) => {
            const contentType = response.headers.get('content-type') || '';

            if (contentType.includes('application/json')) {
                try {
                    return await response.json();
                } catch (error) {
                    return {};
                }
            }

            return {
                rawText: await response.text(),
            };
        };

        const resolveHttpErrorMessage = (response, payload) => {
            if (payload?.message) {
                return payload.message;
            }

            if (response.redirected) {
                return 'Sesi login Anda kemungkinan sudah berakhir. Muat ulang halaman lalu login kembali.';
            }

            if (response.status === 401 || response.status === 419) {
                return 'Sesi login Anda sudah berakhir. Muat ulang halaman lalu login kembali.';
            }

            if (response.status === 403) {
                return 'Akses ke data hasil scan ditolak untuk akun yang sedang login.';
            }

            if (response.status === 404) {
                return 'Data hasil scan tidak ditemukan atau tidak termasuk data akun Anda.';
            }

            if (response.status === 422) {
                return 'Permintaan ke server tidak valid. Coba scan ulang.';
            }

            if (response.status >= 500) {
                return 'Server mengalami gangguan saat memproses data scan. Periksa log Laravel atau koneksi database.';
            }

            return 'Permintaan ke server gagal diproses.';
        };

        const request = async (url, fetchOptions = {}) => {
            let response;

            try {
                response = await fetch(url, {
                    method: fetchOptions.method || 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(fetchOptions.body ? {
                            'Content-Type': 'application/json',
                        } : {}),
                        ...(csrfToken ? {
                            'X-CSRF-TOKEN': csrfToken,
                        } : {}),
                        ...(fetchOptions.headers || {}),
                    },
                    body: fetchOptions.body ? JSON.stringify(fetchOptions.body) : undefined,
                });
            } catch (error) {
                throw new Error('Koneksi ke server gagal. Pastikan aplikasi Laravel sedang berjalan dan jaringan stabil.');
            }

            const payload = await parsePayload(response);

            if (!response.ok) {
                throw new Error(resolveHttpErrorMessage(response, payload));
            }

            if (!payload || typeof payload !== 'object') {
                throw new Error('Server mengembalikan respons yang tidak dikenali.');
            }

            return payload;
        };

        return {
            get(url) {
                return request(url);
            },
            post(url, body = {}) {
                return request(url, {
                    method: 'POST',
                    body,
                });
            },
        };
    };

    ScannerApp.createBeepPlayer = function createBeepPlayer(sourceUrl) {
        const audio = new Audio(sourceUrl);
        audio.preload = 'auto';

        return {
            async play() {
                try {
                    audio.pause();
                    audio.currentTime = 0;
                    await audio.play();
                } catch (error) {
                    // Ignore autoplay restrictions so the scan flow can continue.
                }
            },
        };
    };

    ScannerApp.loadHtml5Qrcode = function loadHtml5Qrcode() {
        if (window.Html5Qrcode && window.Html5QrcodeSupportedFormats) {
            return Promise.resolve();
        }

        if (!html5QrcodeScriptPromise) {
            html5QrcodeScriptPromise = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
                script.async = true;
                script.onload = () => resolve();
                script.onerror = () => reject(new Error('Library html5-qrcode gagal dimuat. Pastikan koneksi internet tersedia.'));
                document.head.appendChild(script);
            });
        }

        return html5QrcodeScriptPromise;
    };

    ScannerApp.createHtml5Scanner = function createHtml5Scanner(options = {}) {
        const state = {
            instance: null,
            isRunning: false,
            isHandlingResult: false,
        };

        const pickCamera = async () => {
            const cameras = await window.Html5Qrcode.getCameras();

            if (!cameras.length) {
                throw new Error('Kamera tidak ditemukan. Pastikan perangkat memiliki kamera yang aktif.');
            }

            return cameras.find((camera) => /back|rear|environment/i.test(camera.label || '')) || cameras[0];
        };

        const resolveFormats = () => {
            const formatNames = Array.isArray(options.formats) ? options.formats : ['QR_CODE'];

            return formatNames
                .map((formatName) => window.Html5QrcodeSupportedFormats?.[formatName])
                .filter((formatValue) => typeof formatValue !== 'undefined');
        };

        const normalizeScannerError = (error) => {
            const message = String(error?.message || error || '');

            if (/NotAllowedError|Permission denied|permission/i.test(message)) {
                return 'Izin kamera ditolak. Izinkan kamera di browser lalu klik "Scan Ulang".';
            }

            if (/NotFoundError|Requested device not found|camera/i.test(message)) {
                return 'Kamera tidak tersedia atau sedang dipakai aplikasi lain.';
            }

            if (/secure context|https/i.test(message)) {
                return 'Akses kamera membutuhkan koneksi HTTPS atau localhost yang aman.';
            }

            return message || 'Scanner gagal dijalankan.';
        };

        return {
            async start(onDecode) {
                if (state.isRunning) {
                    return;
                }

                await ScannerApp.loadHtml5Qrcode();

                const selectedCamera = await pickCamera();
                state.instance = new window.Html5Qrcode(options.elementId);

                try {
                    await state.instance.start(
                        selectedCamera.id,
                        {
                            fps: options.fps || 10,
                            qrbox: options.qrbox || {
                                width: 280,
                                height: 280,
                            },
                            aspectRatio: options.aspectRatio || 1.33,
                            rememberLastUsedCamera: true,
                            formatsToSupport: resolveFormats(),
                        },
                        async (decodedText, decodedResult) => {
                            if (state.isHandlingResult) {
                                return;
                            }

                            state.isHandlingResult = true;

                            try {
                                await onDecode(decodedText, decodedResult);
                            } finally {
                                if (!state.isRunning) {
                                    state.isHandlingResult = false;
                                }
                            }
                        }
                    );

                    state.isRunning = true;
                    state.isHandlingResult = false;
                } catch (error) {
                    await this.stop();
                    throw new Error(normalizeScannerError(error));
                }
            },

            async stop() {
                if (!state.instance) {
                    state.isRunning = false;
                    state.isHandlingResult = false;
                    return;
                }

                try {
                    if (state.isRunning) {
                        await state.instance.stop();
                    }
                } catch (error) {
                    // Ignore stop errors so the scanner can be reset cleanly.
                }

                try {
                    await state.instance.clear();
                } catch (error) {
                    // Ignore clear errors during shutdown.
                }

                state.instance = null;
                state.isRunning = false;
                state.isHandlingResult = false;
            },
        };
    };
})(window);
