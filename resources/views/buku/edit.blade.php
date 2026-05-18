@extends('layouts.app')

@section('title', 'Edit Buku')

@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Data Buku</h4>
                <p class="card-description"> Perbarui informasi buku </p>
                
                <form class="forms-sample" action="{{ route('buku.update', $buku->idbuku) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label>Kode Buku</label>
                        <input type="text" class="form-control" name="kode" value="{{ $buku->kode }}" required>
                    </div>

                    <div class="form-group">
                        <label>Judul Buku</label>
                        <input type="text" class="form-control" name="judul" value="{{ $buku->judul }}" required>
                    </div>

                    <div class="form-group">
                        <label>Pengarang</label>
                        <input type="text" class="form-control" name="pengarang" value="{{ $buku->pengarang }}" required>
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <select class="form-control" name="idkategori" required>
                            @foreach($kategori as $k)
                                <option value="{{ $k->idkategori }}" {{ $buku->idkategori == $k->idkategori ? 'selected' : '' }}>
                                    {{ $k->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-gradient-primary mr-2">Update</button>
                    <a href="{{ route('buku.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection