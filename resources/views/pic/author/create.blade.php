@extends('pic.layouts.app')

@section('title', 'Input Artikel Baru')
@section('page-title', 'Input Artikel Baru')

@section('sidebar')
    <a href="{{ route('pic.author.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('pic.author.create') }}" class="nav-link active">
        <i class="bi bi-plus-circle"></i> Input Artikel Baru
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-journal-plus"></i> Form Input Artikel
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('pic.author.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="slot" class="form-label">Nomor Artikel <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('slot') is-invalid @enderror" 
                                   id="slot" name="slot" value="{{ old('slot') }}" 
                                   min="1" placeholder="Masukkan nomor artikel" required>
                            @error('slot')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Contoh: 1, 2, 3, dst.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="volume" class="form-label">Volume</label>
                            <input type="number" class="form-control @error('volume') is-invalid @enderror" 
                                   id="volume" name="volume" value="{{ old('volume') }}" 
                                   min="1" placeholder="Masukkan volume jurnal">
                            @error('volume')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Opsional</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" 
                               placeholder="Masukkan judul artikel" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="link" class="form-label">Link Jurnal</label>
                        <input type="url" class="form-control @error('link') is-invalid @enderror" 
                               id="link" name="link" value="{{ old('link') }}" 
                               placeholder="https://contoh.com/jurnal">
                        @error('link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">URL lengkap jurnal (opsional)</small>
                    </div>

                    <div class="mb-3">
                        <label for="marketing_id" class="form-label">PIC Marketing <span class="text-danger">*</span></label>
                        <select class="form-select @error('marketing_id') is-invalid @enderror" 
                                id="marketing_id" name="marketing_id" required>
                            <option value="">Pilih PIC Marketing</option>
                            @foreach($marketings as $marketing)
                                <option value="{{ $marketing->id }}" {{ old('marketing_id') == $marketing->id ? 'selected' : '' }}>
                                    {{ $marketing->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('marketing_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="author_username" class="form-label">Username Author <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('author_username') is-invalid @enderror" 
                                   id="author_username" name="author_username" value="{{ old('author_username') }}" 
                                   placeholder="Masukkan username author" required>
                            @error('author_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="author_password" class="form-label">Password Author <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('author_password') is-invalid @enderror" 
                                   id="author_password" name="author_password" value="{{ old('author_password') }}" 
                                   placeholder="Masukkan password author" required>
                            @error('author_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Password akan disimpan dalam bentuk teks biasa</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('pic.author.dashboard') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
