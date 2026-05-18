<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login OTP - Purple Admin</title>
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth">
                <div class="row flex-grow">
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-light text-left p-5 shadow-lg" style="border-radius: 15px;">
                            <div class="brand-logo text-center">
                                <h2 class="text-primary font-weight-bold">KOLEKSI BUKU</h2>
                            </div>
                            <h4 class="text-dark font-weight-bold text-center">Selamat Datang! ✨</h4>
                            <h6 class="font-weight-light text-center text-muted mb-4">Masukkan email Anda untuk menerima kode OTP keamanan.</h6>
                            
                            <form class="pt-3" method="POST" action="{{ route('otp.send') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="email" class="text-small font-weight-bold">Alamat Email</label>
                                    <input type="email" name="email" class="form-control form-control-lg border-primary" id="email" placeholder="contoh@email.com" required>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-block btn-gradient-primary btn-lg font-weight-medium auth-form-btn">
                                        <i class="mdi mdi-email-send-outline me-2"></i> KIRIM KODE OTP
                                    </button>
                                </div>

                                <div class="my-2 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <label class="form-check-label text-muted">
                                            <input type="checkbox" class="form-check-input"> Ingat saya </label>
                                    </div>
                                    <a href="#" class="auth-link text-black text-small">Butuh bantuan?</a>
                                </div>

                                @if ($errors->any())
                                <div class="alert alert-danger p-2 mt-3 rounded">
                                    <ul class="mb-0 small">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </form>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted small">&copy; 2026 Proyek Koleksi Buku - Naisya Zahra</p>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    <script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('assets/js/misc.js') }}"></script>
</body>
</html>