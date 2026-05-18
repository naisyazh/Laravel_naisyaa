<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Verifikasi OTP - Purple Admin</title>
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />
    <style>
        .otp-input {
            letter-spacing: 15px;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            border: 2px solid #ebedf2;
            border-radius: 10px;
            color: #b66dff;
        }
        .otp-input:focus {
            border-color: #b66dff;
            box-shadow: 0 0 10px rgba(182, 109, 255, 0.2);
            outline: none;
        }
    </style>
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth">
                <div class="row flex-grow">
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-light text-left p-5">
                            <div class="brand-logo text-center">
                                <h3 class="text-primary font-weight-bold">VERIFIKASI OTP</h3>
                            </div>
                            <h4 class="text-center">Cek Email Anda 💌</h4>
                            <h6 class="font-weight-light text-center mb-4">
                                Kode telah dikirim ke: <br>
                                <span class="text-info"><strong>{{ session('email') }}</strong></span>
                            </h6>

                            <form class="pt-3" method="POST" action="{{ route('otp.verify') }}">
                                @csrf
                                <input type="hidden" name="email" value="{{ session('email') }}">
                                
                                <div class="form-group">
                                    <label class="small text-muted d-block text-center mb-3">MASUKKAN 6 DIGIT KODE</label>
                                    <input type="text" name="otp" 
                                           class="form-control form-control-lg otp-input" 
                                           id="otp" maxlength="6" 
                                           placeholder="000000" required>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-block btn-gradient-primary btn-lg font-weight-medium auth-form-btn">
                                        VERIFIKASI & MASUK
                                    </button>
                                </div>

                                <div class="text-center mt-4 font-weight-light"> 
    Salah alamat email atau ingin keluar? 
    <a href="{{ route('login') }}" class="text-primary">Kembali ke Login</a>
</div>

                                @if ($errors->any())
                                <div class="alert alert-danger mt-3 small p-2">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </form>
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