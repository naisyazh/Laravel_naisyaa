<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Antrian #{{ $antrian->nomor_antrian }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.materialdesignicons.com/7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tiket-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
        }
        .nomor-antrian {
            font-size: 120px;
            font-weight: bold;
            color: #667eea;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .nama-tamu {
            font-size: 32px;
            font-weight: 600;
            color: #333;
        }
        .status-badge {
            font-size: 18px;
            padding: 10px 30px;
            border-radius: 50px;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
    <button class="btn btn-light print-btn" onclick="window.print()">
        <i class="mdi mdi-printer"></i> Cetak
    </button>

    <div class="tiket-card p-5 text-center">
        <div class="mb-4">
            <i class="mdi mdi-ticket-confirmation" style="font-size: 60px; color: #667eea;"></i>
        </div>
        
        <h2 class="mb-4">Tiket Antrian Anda</h2>
        
        <div class="nomor-antrian mb-3">
            {{ str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT) }}
        </div>
        
        <div class="nama-tamu mb-4">
            {{ $antrian->nama }}
        </div>
        
        <div class="mb-4">
            <span class="badge status-badge bg-warning text-dark">
                <i class="mdi mdi-clock-outline"></i> MENUNGGU
            </span>
        </div>
        
        <hr class="my-4">
        
        <div class="text-muted">
            <p class="mb-2">
                <i class="mdi mdi-calendar"></i> 
                {{ $antrian->waktu_daftar->format('d F Y') }}
            </p>
            <p class="mb-0">
                <i class="mdi mdi-clock"></i> 
                {{ $antrian->waktu_daftar->format('H:i:s') }}
            </p>
        </div>
        
        <div class="alert alert-info mt-4" role="alert">
            <i class="mdi mdi-information"></i> 
            <strong>Harap tunggu nomor Anda dipanggil</strong>
            <br>
            <small>Pantau papan antrian atau dengarkan pengumuman</small>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                Simpan tiket ini sebagai bukti pendaftaran
            </small>
        </div>
    </div>

    <script>
        // Auto-refresh status setiap 5 detik
        setInterval(() => {
            location.reload();
        }, 5000);
    </script>
</body>
</html>
