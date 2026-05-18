@extends('layouts.app')

@section('title', $title)

@section('style')
    <style>
        .camera-stage {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            border: 1px solid #ebe7f8;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            min-height: 360px;
        }

        .camera-stage video,
        .camera-stage img {
            width: 100%;
            min-height: 360px;
            object-fit: cover;
            display: block;
        }

        .camera-guide {
            border-radius: 1rem;
            background: #f6f4ff;
            border: 1px solid #ebe7f8;
            padding: 1rem;
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-camera-enhance"></i>
            </span>
            {{ $title }}
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('otp.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customer</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ul>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-5 grid-margin stretch-card">
            <div class="card w-100">
                <div class="card-body">
                    <h4 class="card-title mb-1">{{ $heading }}</h4>
                    <p class="text-muted mb-4">{{ $description }}</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form action="{{ $formAction }}" method="POST" id="camera_capture_form">
                        @csrf
                        <div class="form-group">
                            <label>Nama Customer</label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="form-group">
                            <label>Telepon</label>
                            <input type="text" name="telepon" class="form-control" value="{{ old('telepon') }}">
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea name="alamat" rows="3" class="form-control">{{ old('alamat') }}</textarea>
                        </div>

                        <input type="hidden" name="captured_photo" id="captured_photo" value="{{ old('captured_photo') }}">

                        <div class="camera-guide mb-4">
                            <strong class="d-block mb-2">Alur singkat</strong>
                            <div class="text-muted">1. Klik tombol nyalakan kamera.</div>
                            <div class="text-muted">2. Izinkan akses kamera di browser.</div>
                            <div class="text-muted">3. Ambil foto lalu simpan customer.</div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-gradient-primary" id="save_customer" disabled>
                                {{ $submitLabel }}
                            </button>
                            <a href="{{ route('customers.index') }}" class="btn btn-light">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7 grid-margin stretch-card">
            <div class="card w-100">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                        <div>
                            <h4 class="card-title mb-1">Preview Kamera</h4>
                            <p class="text-muted mb-0">Foto hasil tangkapan akan masuk ke form secara otomatis.</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-gradient-info" id="start_camera">Nyalakan Kamera</button>
                            <button type="button" class="btn btn-gradient-primary" id="capture_photo" disabled>Ambil Foto</button>
                            <button type="button" class="btn btn-light" id="retake_photo" disabled>Ambil Ulang</button>
                        </div>
                    </div>

                    <div class="camera-stage">
                        <video id="camera_preview" autoplay playsinline muted></video>
                        <img id="camera_snapshot" class="d-none" alt="Hasil tangkapan customer">
                    </div>

                    <div class="alert alert-info mt-3 mb-0" id="camera_message">
                        Kamera belum aktif. Klik <strong>Nyalakan Kamera</strong> untuk memulai.
                    </div>

                    <canvas id="camera_canvas" class="d-none"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (() => {
            const startButton = document.getElementById('start_camera');
            const captureButton = document.getElementById('capture_photo');
            const retakeButton = document.getElementById('retake_photo');
            const saveButton = document.getElementById('save_customer');
            const preview = document.getElementById('camera_preview');
            const snapshot = document.getElementById('camera_snapshot');
            const canvas = document.getElementById('camera_canvas');
            const messageBox = document.getElementById('camera_message');
            const hiddenInput = document.getElementById('captured_photo');

            let activeStream = null;

            const stopCamera = () => {
                if (!activeStream) {
                    return;
                }

                activeStream.getTracks().forEach((track) => track.stop());
                activeStream = null;
            };

            const showMessage = (message, type = 'info') => {
                messageBox.className = `alert alert-${type} mt-3 mb-0`;
                messageBox.innerHTML = message;
            };

            const startCamera = async () => {
                stopCamera();

                try {
                    activeStream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user',
                            width: { ideal: 1280 },
                            height: { ideal: 720 },
                        },
                    });

                    preview.srcObject = activeStream;
                    preview.classList.remove('d-none');
                    snapshot.classList.add('d-none');
                    captureButton.disabled = false;
                    retakeButton.disabled = true;
                    saveButton.disabled = hiddenInput.value === '';
                    showMessage('Kamera aktif. Arahkan wajah customer ke frame lalu klik <strong>Ambil Foto</strong>.', 'success');
                } catch (error) {
                    showMessage('Browser gagal membuka kamera. Pastikan izin kamera diberikan dan perangkat memiliki webcam aktif.', 'danger');
                }
            };

            const capturePhoto = () => {
                if (!preview.srcObject) {
                    return;
                }

                const width = preview.videoWidth || 1280;
                const height = preview.videoHeight || 720;
                const context = canvas.getContext('2d');

                canvas.width = width;
                canvas.height = height;
                context.drawImage(preview, 0, 0, width, height);

                const dataUrl = canvas.toDataURL('image/jpeg', 0.92);

                hiddenInput.value = dataUrl;
                snapshot.src = dataUrl;
                snapshot.classList.remove('d-none');
                preview.classList.add('d-none');
                captureButton.disabled = true;
                retakeButton.disabled = false;
                saveButton.disabled = false;
                stopCamera();
                showMessage('Foto customer berhasil diambil. Anda bisa langsung simpan atau klik <strong>Ambil Ulang</strong>.', 'primary');
            };

            const retakePhoto = async () => {
                hiddenInput.value = '';
                saveButton.disabled = true;
                await startCamera();
            };

            startButton.addEventListener('click', async () => {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    showMessage('Browser ini belum mendukung akses kamera HTML5.', 'danger');
                    return;
                }

                await startCamera();
            });

            captureButton.addEventListener('click', capturePhoto);
            retakeButton.addEventListener('click', retakePhoto);

            window.addEventListener('beforeunload', stopCamera);
        })();
    </script>
@endsection
