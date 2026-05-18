@extends('layouts.app')

@section('title', 'Pendaftaran Antrian')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h3><i class="mdi mdi-account-plus"></i> Pendaftaran Antrian</h3>
                </div>
                <div class="card-body">
                    <form id="formDaftar">
                        @csrf
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control form-control-lg" id="nama" name="nama" 
                                   placeholder="Masukkan nama Anda" required autofocus>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="btnDaftar">
                                <i class="mdi mdi-check-circle"></i> Daftar Antrian
                            </button>
                        </div>
                    </form>

                    <div id="alertContainer" class="mt-3"></div>
                </div>
                <div class="card-footer text-center text-muted">
                    <small>Setelah mendaftar, tiket antrian Anda akan muncul di tab baru</small>
                </div>
            </div>

            <div class="card mt-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="mdi mdi-information"></i> Informasi</h5>
                    <ul class="mb-0">
                        <li>Masukkan nama lengkap Anda</li>
                        <li>Klik tombol "Daftar Antrian"</li>
                        <li>Tiket antrian akan muncul di tab baru</li>
                        <li>Tunggu nomor Anda dipanggil</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formDaftar').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btnDaftar = document.getElementById('btnDaftar');
    const alertContainer = document.getElementById('alertContainer');
    const nama = document.getElementById('nama').value;
    
    // Disable button
    btnDaftar.disabled = true;
    btnDaftar.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Mendaftar...';
    
    try {
        const response = await fetch('{{ route('antrian.daftar') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ nama: nama })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message
            alertContainer.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-circle"></i> ${result.message}
                    <br><strong>Nomor Antrian: ${result.data.nomor_antrian}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Open tiket in new tab
            window.open(result.data.url, '_blank');
            
            // Reset form
            document.getElementById('formDaftar').reset();
        } else {
            alertContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-alert-circle"></i> ${result.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }
    } catch (error) {
        alertContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-alert-circle"></i> Terjadi kesalahan: ${error.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    } finally {
        // Enable button
        btnDaftar.disabled = false;
        btnDaftar.innerHTML = '<i class="mdi mdi-check-circle"></i> Daftar Antrian';
    }
});
</script>
@endsection
