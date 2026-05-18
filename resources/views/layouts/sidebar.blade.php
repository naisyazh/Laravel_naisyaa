<style>
    @media (min-width: 992px) {
        body {
            --app-sidebar-width: 260px;
            --app-sidebar-icon-width: 58px;
        }

        body.sidebar-icon-only {
            --app-sidebar-width: var(--app-sidebar-icon-width);
        }

        body.sidebar-hidden {
            --app-sidebar-width: 0px;
        }

        #sidebar {
            position: fixed;
            top: 70px;
            left: 0;
            width: var(--app-sidebar-width);
            height: calc(100vh - 70px);
            overflow-y: auto;
            z-index: 1000;
            transition: width 0.3s ease;
        }

        .main-panel {
            margin-left: var(--app-sidebar-width);
            width: calc(100% - var(--app-sidebar-width));
            transition: width 0.3s ease, margin-left 0.3s ease;
        }

        body.sidebar-icon-only .main-panel,
        body.sidebar-hidden .main-panel {
            margin-left: var(--app-sidebar-width);
            width: calc(100% - var(--app-sidebar-width));
        }

        body.sidebar-icon-only .navbar .navbar-brand-wrapper {
            width: var(--app-sidebar-icon-width);
        }

        body.sidebar-icon-only .navbar .navbar-menu-wrapper {
            width: calc(100% - var(--app-sidebar-icon-width));
        }

        body.sidebar-icon-only #sidebar .sidebar-section-label {
            display: none;
        }

        body.sidebar-icon-only #sidebar .nav .nav-item .nav-link {
            padding-left: 0;
            padding-right: 0;
        }

        body.sidebar-icon-only #sidebar .nav .nav-item .nav-link .menu-icon {
            margin-left: 0;
            margin-right: 0;
        }

        body.sidebar-icon-only #sidebar .nav .nav-item.hover-open .nav-link .menu-title,
        body.sidebar-icon-only #sidebar .nav .nav-item.hover-open .collapse,
        body.sidebar-icon-only #sidebar .nav .nav-item.hover-open .collapsing {
            left: var(--app-sidebar-icon-width);
        }
    }

    #sidebar::-webkit-scrollbar {
        width: 4px;
    }

    #sidebar::-webkit-scrollbar-thumb {
        background: #e0e0e0;
        border-radius: 10px;
    }
</style>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item nav-profile">
            <a href="#" class="nav-link">
                <div class="nav-profile-image">
                    <img src="{{ asset('assets/images/faces/face1.jpg') }}" alt="profile">
                    <span class="login-status online"></span>
                </div>
                <div class="nav-profile-text d-flex flex-column">
                    <span class="font-weight-bold mb-2">{{ Auth::user()->name ?? 'Guest' }}</span>
                    <span class="text-secondary text-small">{{ ucfirst(Auth::user()->role ?? '') }}</span>
                </div>
                <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
            </a>
        </li>
        <li class="nav-item {{ Request::is('dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('otp.dashboard') }}">
                <span class="menu-title">Dashboard</span>
                <i class="mdi mdi-home menu-icon text-primary"></i>
            </a>
        </li>
        @if (Auth::check() && Auth::user()->role == 'admin')
            <li class="nav-item sidebar-section-label">
                <div class="sidebar-heading"
                    style="padding: 15px 15px 5px 25px; font-size: 11px; font-weight: bold; color: #afafaf;">
                    ADMIN PANEL
                </div>
            </li>
            <li class="nav-item {{ Request::is('documents*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('documents.index') }}">
                    <span class="menu-title">Manajemen Dokumen</span>
                    <i class="mdi mdi-folder-multiple menu-icon text-info"></i>
                </a>
            </li>
            <li class="nav-item {{ Request::is('kategori*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('kategori.index') }}">
                    <span class="menu-title">Master Kategori</span>
                    <i class="mdi mdi-format-list-bulleted menu-icon text-success"></i>
                </a>
            </li>
            <li class="nav-item sidebar-section-label">
                <div class="sidebar-heading"
                    style="padding: 15px 15px 5px 25px; font-size: 11px; font-weight: bold; color: #afafaf;">
                    MODUL CUSTOMER
                </div>
            </li>
            <li class="nav-item {{ Request::is('customers') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('customers.index') }}">
                    <span class="menu-title">Data Customer</span>
                    <i class="mdi mdi-account-multiple menu-icon text-primary"></i>
                </a>
            </li>
            <li class="nav-item {{ Request::is('customers/create-blob') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('customers.create.blob') }}">
                    <span class="menu-title">Tambah Customer 1</span>
                    <i class="mdi mdi-database menu-icon text-info"></i>
                </a>
            </li>
            <li class="nav-item {{ Request::is('customers/create-file') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('customers.create.file') }}">
                    <span class="menu-title">Tambah Customer 2</span>
                    <i class="mdi mdi-file-image menu-icon text-warning"></i>
                </a>
            </li>
        @endif
        <li class="nav-item {{ Request::is('buku*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('buku.index') }}">
                <span class="menu-title">Koleksi Buku</span>
                <i class="mdi mdi-book-open-variant menu-icon text-info"></i>
            </a>
        </li>
        @if (Auth::check() && Auth::user()->role == 'admin')
            <li class="nav-item {{ (Request::is('barang') || Request::is('barang/*')) && !Request::is('barang/scanner') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('barang.index') }}">
                    <span class="menu-title">Master Buku Toko</span>
                    <i class="mdi mdi-tag-multiple menu-icon text-primary"></i>
                </a>
            </li>
            <li class="nav-item {{ Request::is('barang/scanner') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('barang.scanner') }}">
                    <span class="menu-title">Scan Barcode Barang</span>
                    <i class="mdi mdi-barcode menu-icon text-info"></i>
                </a>
            </li>
            <li class="nav-item {{ (Request::is('vendor/orders') || Request::is('vendor/orders/*')) && !Request::is('vendor/orders/scanner') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('vendor.orders.index') }}">
                    <span class="menu-title">Transaksi Buku</span>
                    <i class="mdi mdi-storefront menu-icon text-warning"></i>
                </a>
            </li>
            <li class="nav-item {{ Request::is('vendor/orders/scanner') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('vendor.orders.scanner') }}">
                    <span class="menu-title">Scan QR Pesanan</span>
                    <i class="mdi mdi-qrcode menu-icon text-success"></i>
                </a>
            </li>
            <li class="nav-item sidebar-section-label">
                <div class="sidebar-heading"
                    style="padding: 15px 15px 5px 25px; font-size: 11px; font-weight: bold; color: #afafaf;">
                    MODUL GEOLOCATION
                </div>
            </li>
            <li class="nav-item {{ Request::is('toko') || Request::is('toko/*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('toko.index') }}">
                    <span class="menu-title">Master Data Toko</span>
                    <i class="mdi mdi-store menu-icon text-primary"></i>
                </a>
            </li>
            <li class="nav-item sidebar-section-label">
                <div class="sidebar-heading"
                    style="padding: 15px 15px 5px 25px; font-size: 11px; font-weight: bold; color: #afafaf;">
                    MODUL SSE ANTRIAN
                </div>
            </li>
            <li class="nav-item {{ Request::is('antrian/guest') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('antrian.guest') }}">
                    <span class="menu-title">Daftar Antrian</span>
                    <i class="mdi mdi-account-plus menu-icon text-warning"></i>
                </a>
            </li>
            <li class="nav-item {{ Request::is('antrian/admin') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('antrian.admin') }}">
                    <span class="menu-title">Kelola Antrian</span>
                    <i class="mdi mdi-view-dashboard menu-icon text-primary"></i>
                </a>
            </li>
            <li class="nav-item {{ Request::is('papan-antrian') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('papan-antrian') }}" target="_blank">
                    <span class="menu-title">Papan Antrian</span>
                    <i class="mdi mdi-monitor-dashboard menu-icon text-success"></i>
                </a>
            </li>
        @endif
        @if (Auth::check() && Auth::user()->role == 'user')
            <li class="nav-item sidebar-section-label">
                <div class="sidebar-heading"
                    style="padding: 15px 15px 5px 25px; font-size: 11px; font-weight: bold; color: #afafaf;">
                    FITUR USER
                </div>
            </li>
            <li class="nav-item {{ Request::is('toko-buku') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('toko-buku.index') }}">
                    <span class="menu-title">Checkout Toko Buku</span>
                    <i class="mdi mdi-cart menu-icon text-warning"></i>
                </a>
            </li>
            <li class="nav-item {{ Request::is('toko-buku/orders/lunas*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('toko-buku.orders.paid') }}">
                    <span class="menu-title">Riwayat Lunas</span>
                    <i class="mdi mdi-receipt-text-check menu-icon text-success"></i>
                </a>
            </li>
            <li class="nav-item {{ Request::is('tugas-js') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('assignment') }}">
                    <span class="menu-title">Modul AJAX & Axios</span>
                    <i class="mdi mdi-code-tags menu-icon text-primary"></i>
                </a>
            </li>
            <li class="nav-item sidebar-section-label">
                <div class="sidebar-heading"
                    style="padding: 15px 15px 5px 25px; font-size: 11px; font-weight: bold; color: #afafaf;">
                    MODUL GEOLOCATION
                </div>
            </li>
            <li class="nav-item {{ Request::is('kunjungan-toko') && !Request::is('kunjungan-toko/riwayat') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('kunjungan-toko.index') }}">
                    <span class="menu-title">Kunjungan Toko</span>
                    <i class="mdi mdi-map-marker-check menu-icon text-success"></i>
                </a>
            </li>
            <li class="nav-item {{ Request::is('kunjungan-toko/riwayat') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('kunjungan-toko.riwayat') }}">
                    <span class="menu-title">Riwayat Kunjungan</span>
                    <i class="mdi mdi-history menu-icon text-info"></i>
                </a>
            </li>
            <li class="nav-item sidebar-section-label">
                <div class="sidebar-heading"
                    style="padding: 15px 15px 5px 25px; font-size: 11px; font-weight: bold; color: #afafaf;">
                    MODUL SSE ANTRIAN
                </div>
            </li>
            <li class="nav-item {{ Request::is('antrian/guest') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('antrian.guest') }}">
                    <span class="menu-title">Daftar Antrian</span>
                    <i class="mdi mdi-account-plus menu-icon text-warning"></i>
                </a>
            </li>
            <li class="nav-item {{ Request::is('antrian/admin') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('antrian.admin') }}">
                    <span class="menu-title">Kelola Antrian</span>
                    <i class="mdi mdi-view-dashboard menu-icon text-primary"></i>
                </a>
            </li>
            <li class="nav-item {{ Request::is('papan-antrian') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('papan-antrian') }}" target="_blank">
                    <span class="menu-title">Papan Antrian</span>
                    <i class="mdi mdi-monitor-dashboard menu-icon text-success"></i>
                </a>
            </li>
            <li class="nav-item sidebar-section-label">
                <div class="sidebar-heading"
                    style="padding: 15px 15px 5px 25px; font-size: 11px; font-weight: bold; color: #afafaf;">
                    DOKUMEN EKSKLUSIF
                </div>
            </li>
            <li class="nav-item {{ Request::is('sertifikat*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('otp.sertifikat') }}">
                    <span class="menu-title">Sertifikat Saya</span>
                    <i class="mdi mdi-certificate menu-icon text-danger"></i>
                </a>
            </li>
            <li class="nav-item {{ Request::is('undangan*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('otp.undangan') }}">
                    <span class="menu-title">Undangan Saya</span>
                    <i class="mdi mdi-email-seal menu-icon text-warning"></i>
                </a>
            </li>
        @endif

        <li class="nav-item mt-3">
            <a class="nav-link" href="#"
                onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();"
                style="cursor: pointer; z-index: 1001; position: relative;">
                <span class="menu-title text-danger">Logout</span>
                <i class="mdi mdi-power menu-icon text-danger"></i>
            </a>
        </li>
    </ul>
</nav>

<form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
