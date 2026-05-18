@extends('layouts.app')

@section('title', 'Tambah Buku')

@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Tambah Buku Baru</h4>
                <p class="card-description"> Masukkan detail buku </p>
                
                <form class="forms-sample" action="{{ route('buku.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label>Kode Buku</label>
                        <input type="text" class="form-control" name="kode" placeholder="Contoh: NV-01" required>
                    </div>

                    <div class="form-group">
                        <label>Judul Buku</label>
                        <input type="text" class="form-control" name="judul" placeholder="Judul Lengkap" required>
                    </div>

                    <div class="form-group">
                        <label>Pengarang</label>
                        <input type="text" class="form-control" name="pengarang" placeholder="Nama Pengarang" required>
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <select class="form-control" name="idkategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategori as $k)
                                <option value="{{ $k->idkategori }}">{{ $k->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-gradient-primary mr-2">Simpan</button>
                    <a href="{{ route('buku.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection