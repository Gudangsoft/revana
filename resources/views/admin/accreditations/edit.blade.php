@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Edit Akreditasi')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil-square"></i> Form Edit Akreditasi
            </div>
            <div class="card-body">
                <form action="{{ route('admin.accreditations.update', $accreditation) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama Akreditasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name', $accreditation->name) }}" 
                               placeholder="Contoh: SINTA 1" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Nama akreditasi harus unik</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Points <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('points') is-invalid @enderror" 
                               name="points" value="{{ old('points', $accreditation->points) }}" 
                               placeholder="Contoh: 100" min="0" required>
                        @error('points')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Jumlah poin yang akan diberikan untuk akreditasi ini</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" rows="3" 
                                  placeholder="Deskripsi opsional tentang akreditasi ini">{{ old('description', $accreditation->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" 
                               id="is_active" value="1" {{ old('is_active', $accreditation->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Aktif
                        </label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                        <a href="{{ route('admin.accreditations.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-exclamation-triangle"></i> Perhatian
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Jurnal yang menggunakan:</strong><br>
                    <span class="badge bg-info">{{ $accreditation->journals()->count() }} jurnal</span>
                </p>
                <hr>
                <small class="text-muted">
                    Perubahan pada nama akreditasi dapat mempengaruhi jurnal yang sudah ada. 
                    Pastikan untuk memeriksa kembali jurnal terkait setelah melakukan perubahan.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
