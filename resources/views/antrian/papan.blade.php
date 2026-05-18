<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papan Antrian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.materialdesignicons.com/7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow: hidden;
        }
        .papan-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        .header-papan {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .main-display {
            flex: 1;
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .nomor-dipanggil {
            font-size: 180px;
            font-weight: bold;
            color: #667eea;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.1);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .nama-dipanggil {
            font-size: 48px;
            font-weight: 600;
            color: #333;
            margin-top: 20px;
        }
        .ruangan-info {
            font-size: 36px;
            color: #666;
            margin-top: 10px;
        }
        .status-connection {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            border-radius: 50px;
            background: rgba(255,255,255,0.9);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .waiting-message {
            font-size: 32px;
            color: #999;
            text-align: center;
        }
        .queue-list {
            margin-top: 30px;
            width: 100%;
            max-width: 800px;
        }
        .queue-item {
            background: #f8f9fa;
            padding: 15px 25px;
            margin: 10px 0;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <!-- Status Connection -->
    <div class="status-connection" id="connectionStatus">
        <i class="mdi mdi-loading mdi-spin"></i> Connecting...
    </div>

    <!-- Tombol Test Audio Manual -->
    <div style="position: fixed; top: 10px; left: 10px; z-index: 9999;">
        <button onclick="testAudioManual()" 
                style="background: rgba(255,255,255,0.9); border: none; border-radius: 50px; padding: 10px 18px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); cursor: pointer; font-size: 14px;">
            <i class="mdi mdi-volume-high"></i> Test Suara
        </button>
    </div>

    <div class="papan-container">
        <!-- Header -->
        <div class="header-papan text-center">
            <h1 class="mb-0">
                <i class="mdi mdi-monitor-dashboard"></i> 
                PAPAN ANTRIAN DIGITAL
            </h1>
            <p class="text-muted mb-0">Real-time Queue Display System</p>
        </div>

        <!-- Main Display -->
        <div class="main-display" id="mainDisplay">
            @if($dipanggil)
                <div class="text-center">
                    <h3 class="text-muted mb-3">NOMOR ANTRIAN</h3>
                    <div class="nomor-dipanggil">{{ str_pad($dipanggil->nomor_antrian, 3, '0', STR_PAD_LEFT) }}</div>
                    <div class="nama-dipanggil">{{ $dipanggil->nama }}</div>
                    @if($dipanggil->ruangan)
                        <div class="ruangan-info">
                            <i class="mdi mdi-door"></i> Ruangan {{ $dipanggil->ruangan }}
                        </div>
                    @endif
                    <div class="mt-4">
                        <span class="badge bg-success fs-5 px-4 py-2">
                            <i class="mdi mdi-phone-forward"></i> SILAKAN MASUK
                        </span>
                    </div>
                </div>
            @else
                <div class="waiting-message">
                    <i class="mdi mdi-clock-outline" style="font-size: 80px; display: block; margin-bottom: 20px;"></i>
                    Menunggu Panggilan Antrian...
                </div>
            @endif
        </div>
    </div>

    <!-- Audio for notification -->
    <audio id="audioNotif" preload="auto">
        <source src="{{ asset('assets/audio/ding-dong.mp3') }}" type="audio/mpeg">
    </audio>

    <script>
    let eventSource = null;
    let lastCalledId = null;
    let userInteracted = false;

    document.addEventListener('click', function() {
        userInteracted = true;
    }, { once: true });

    // Test Audio Manual
    function testAudioManual() {
        userInteracted = true;
        const audio = document.getElementById('audioNotif');

        if (!('speechSynthesis' in window)) {
            alert('Browser tidak mendukung Web Speech API');
            return;
        }

        window.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance('Ding dong. Test suara papan antrian.');
        utterance.lang = 'id-ID';
        utterance.rate = 0.85;
        utterance.pitch = 1.0;
        utterance.volume = 1.0;

        audio.currentTime = 0;
        audio.play().catch(() => {
            window.speechSynthesis.speak(utterance);
        });

        audio.onended = function() {
            window.speechSynthesis.speak(utterance);
        };
    }

    function initSSE() {
        const sseUrl = '{{ route('sse.antrian') }}';
        eventSource = new EventSource(sseUrl);

        eventSource.addEventListener('queue-update', function(event) {
            const data = JSON.parse(event.data);
            updateDisplay(data);
        });

        eventSource.onopen = function() {
            document.getElementById('connectionStatus').innerHTML = `
                <i class="mdi mdi-check-circle text-success"></i> Connected
            `;
        };

        eventSource.onerror = function(error) {
            console.error('SSE Error:', error);
            document.getElementById('connectionStatus').innerHTML = `
                <i class="mdi mdi-alert-circle text-danger"></i> Disconnected
            `;
        };
    }

    function updateDisplay(data) {
        const mainDisplay = document.getElementById('mainDisplay');

        if (data.dipanggil) {
            if (lastCalledId !== data.dipanggil.id) {
                lastCalledId = data.dipanggil.id;
                if (userInteracted) {
                    playNotification(data.dipanggil);
                }
            }

            mainDisplay.innerHTML = `
                <div class="text-center">
                    <h3 class="text-muted mb-3">NOMOR ANTRIAN</h3>
                    <div class="nomor-dipanggil">${String(data.dipanggil.nomor_antrian).padStart(3, '0')}</div>
                    <div class="nama-dipanggil">${data.dipanggil.nama}</div>
                    ${data.dipanggil.ruangan ? `
                        <div class="ruangan-info">
                            <i class="mdi mdi-door"></i> Ruangan ${data.dipanggil.ruangan}
                        </div>
                    ` : ''}
                    <div class="mt-4">
                        <span class="badge bg-success fs-5 px-4 py-2">
                            <i class="mdi mdi-phone-forward"></i> SILAKAN MASUK
                        </span>
                    </div>
                </div>
            `;
        } else {
            lastCalledId = null;
            mainDisplay.innerHTML = `
                <div class="waiting-message">
                    <i class="mdi mdi-clock-outline" style="font-size: 80px; display: block; margin-bottom: 20px;"></i>
                    Menunggu Panggilan Antrian...
                </div>
            `;
        }
    }

    function playNotification(antrian) {
        if (!('speechSynthesis' in window)) {
            console.warn('Browser tidak mendukung Web Speech API');
            return;
        }

        window.speechSynthesis.cancel();

        const audio = document.getElementById('audioNotif');

        // Format: "Ding dong, antrian 002 silakan masuk"
        const nomorFormatted = String(antrian.nomor_antrian).padStart(3, '0');
        let message = `Ding dong. Antrian ${nomorFormatted}. ${antrian.nama}.`;
        if (antrian.ruangan) {
            message += ` Silakan masuk ke ruangan ${antrian.ruangan}.`;
        } else {
            message += ` Silakan masuk.`;
        }

        const utterance = new SpeechSynthesisUtterance(message);
        utterance.lang = 'id-ID';
        utterance.rate = 0.85;
        utterance.pitch = 1.0;
        utterance.volume = 1.0;

        audio.currentTime = 0;
        audio.play().catch(err => console.warn('Audio play failed:', err));

        audio.onended = function() {
            window.speechSynthesis.speak(utterance);
        };

        setTimeout(() => {
            if (window.speechSynthesis.speaking === false) {
                window.speechSynthesis.speak(utterance);
            }
        }, 2000);
    }

    initSSE();

    window.addEventListener('beforeunload', function() {
        if (eventSource) eventSource.close();
    });

    setTimeout(() => {
        if (!userInteracted) {
            const instruction = document.createElement('div');
            instruction.className = 'alert alert-warning position-fixed bottom-0 start-50 translate-middle-x m-3';
            instruction.style.zIndex = '9999';
            instruction.innerHTML = `
                <i class="mdi mdi-hand-pointing-up"></i>
                <strong>Klik di mana saja</strong> untuk mengaktifkan suara notifikasi
            `;
            document.body.appendChild(instruction);
            document.addEventListener('click', function() {
                instruction.remove();
            }, { once: true });
        }
    }, 3000);
    </script>
</body>
</html>
