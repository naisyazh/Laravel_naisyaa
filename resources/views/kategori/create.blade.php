@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Tambah Kategori Baru</h4>
                <p class="card-description"> Masukkan nama kategori baru </p>
                
                <form class="forms-sample" action="{{ route('kategori.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label for="nama_kategori">Nama Kategori</label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" placeholder="Contoh: Sains Fiksi" required>
                    </div>
                    
                    <button type="submit" class="btn btn-gradient-primary mr-2">Simpan</button>
                    <a href="{{ route('kategori.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection