@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Edit Sertifikat')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil"></i> Form Edit Sertifikat
            </div>
            <div class="card-body">
                <form action="{{ route('admin.certificates.update', $certificate) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama Sertifikat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name', $certificate->name) }}" 
                               placeholder="Contoh: Sertifikat Review Artikel 2026" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" rows="3" 
                                  placeholder="Deskripsi sertifikat (opsional)">{{ old('description', $certificate->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gambar Sertifikat Saat Ini</label>
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $certificate->file_path) }}" 
                                 alt="{{ $certificate->name }}" 
                                 style="max-width: 300px;"
                                 class="img-thumbnail">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload Gambar Baru <span class="text-muted">(Opsional)</span></label>
                        <input type="file" class="form-control @error('certificate_file') is-invalid @enderror" 
                               name="certificate_file" accept="image/jpeg,image/png,image/jpg">
                        @error('certificate_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar. Format: JPG, PNG. Maksimal 5MB</small>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="is_active" 
                               {{ old('is_active', $certificate->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Aktifkan sertifikat
                        </label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                        <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Informasi
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Dibuat:</strong> {{ $certificate->created_at->format('d M Y H:i') }}</p>
                <p class="mb-0"><strong>Diupdate:</strong> {{ $certificate->updated_at->format('d M Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
