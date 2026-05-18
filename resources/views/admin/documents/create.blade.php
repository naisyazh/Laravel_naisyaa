@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Unggah Dokumen User</h4>
                <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="forms-sample">
                    @csrf
                    
                    <div class="form-group">
                        <label for="user_id">Pilih User (Penerima)</label>
                        <select name="user_id" class="form-control" id="user_id" required>
                            <option value="">-- Pilih Nama User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="type">Jenis Dokumen</label>
                        <select name="type" class="form-control" id="type" required>
                            <option value="sertifikat">Sertifikat Digital</option>
                            <option value="undangan">Undangan Eksklusif</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>File Gambar (JPG/PNG)</label>
                        <input type="file" name="file" class="form-control" accept="image/*" required>
                        <small class="text-muted">Maksimal ukuran file: 2MB</small>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-gradient-primary mr-2">Unggah Sekarang</button>
                        <a href="{{ route('documents.index') }}" class="btn btn-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection