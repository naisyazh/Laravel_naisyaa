/**
 * Get accurate GPS position with best accuracy
 * Based on Lampiran 1 from Geolocation module
 * 
 * @param {number} targetAccuracy - Target accuracy in meters (default: 50)
 * @param {number} maxWait - Maximum wait time in milliseconds (default: 20000)
 * @returns {Promise<GeolocationPosition>}
 */
function getAccuratePosition(targetAccuracy = 50, maxWait = 20000) {
    return new Promise((resolve, reject) => {
        let bestResult = null;
        const startTime = Date.now();

        const watchId = navigator.geolocation.watchPosition(
            (position) => {
                const acc = position.coords.accuracy;

                // Simpan hasil terbaik sejauh ini
                if (!bestResult || acc < bestResult.coords.accuracy) {
                    bestResult = position;
                }

                // Kalau sudah cukup akurat, berhenti
                if (acc <= targetAccuracy) {
                    navigator.geolocation.clearWatch(watchId);
                    resolve(bestResult);
                }

                // Kalau timeout, pakai hasil terbaik yang ada
                if (Date.now() - startTime >= maxWait) {
                    navigator.geolocation.clearWatch(watchId);
                    if (bestResult) resolve(bestResult);
                    else reject(new Error("Timeout, tidak dapat posisi"));
                }
            },
            (error) => {
                navigator.geolocation.clearWatch(watchId);
                
                let errorMessage = 'Gagal mendapatkan lokasi';
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Izin lokasi ditolak. Izinkan akses lokasi di browser.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'Informasi lokasi tidak tersedia.';
                        break;
                    case error.TIMEOUT:
                        errorMessage = 'Timeout mendapatkan lokasi.';
                        break;
                }
                
                reject(new Error(errorMessage));
            },
            { 
                enableHighAccuracy: true, 
                maximumAge: 0, 
                timeout: maxWait 
            }
        );
    });
}

// Export for use in other scripts
if (typeof window !== 'undefined') {
    window.getAccuratePosition = getAccuratePosition;
}
