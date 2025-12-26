@extends('layouts.app')

@section('title', 'Upload Hasil Review - REVANA')
@section('page-title', 'Upload Hasil Review')

@section('sidebar')
    <a href="{{ route('reviewer.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('reviewer.tasks.index') }}" class="nav-link active">
        <i class="bi bi-clipboard-check"></i> My Tasks
    </a>
    <a href="{{ route('reviewer.rewards.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Rewards
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-upload"></i> Form Upload Hasil Review
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Jurnal:</strong> {{ $assignment->journal->title }}<br>
                    <strong>Reward:</strong> <span class="badge bg-success">{{ $assignment->journal->points }} Points</span>
                </div>

                <form action="{{ route('reviewer.results.store', $assignment) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Link Google Drive Hasil Review <span class="text-danger">*</span></label>
                        <input type="url" class="form-control @error('google_drive_link') is-invalid @enderror" 
                               name="google_drive_link" 
                               value="{{ old('google_drive_link') }}"
                               placeholder="https://drive.google.com/file/d/..." required>
                        @error('google_drive_link')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Pastikan file sudah di-set "Anyone with the link can view"
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Review (Opsional)</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  name="notes" rows="8"
                                  placeholder="Tuliskan ringkasan hasil review Anda, poin-poin penting, saran, dan kesimpulan... (Opsional)">{{ old('notes') }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Catatan review bersifat opsional, tetapi direkomendasikan untuk memperjelas hasil review</small>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> 
                        <ul class="mb-0 mt-2">
                            <li>Pastikan link Google Drive dapat diakses</li>
                            <li>File review sudah lengkap dan sesuai format</li>
                            <li>Catatan berisi ringkasan penting dari review</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-send"></i> Submit Review
                        </button>
                        <a href="{{ route('reviewer.tasks.show', $assignment) }}" class="btn btn-secondary">
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
                <i class="bi bi-info-circle"></i> Cara Upload ke Google Drive
            </div>
            <div class="card-body">
                <h6>Langkah-langkah:</h6>
                <ol class="small">
                    <li>Upload file review ke Google Drive</li>
                    <li>Klik kanan file → <strong>Share</strong></li>
                    <li>Pilih <strong>Anyone with the link</strong></li>
                    <li>Set permission <strong>Viewer</strong></li>
                    <li>Klik <strong>Copy link</strong></li>
                    <li>Paste link di form</li>
                </ol>
                <hr>
                <h6>Checklist:</h6>
                <ul class="small">
                    <li>✅ Review sudah lengkap</li>
                    <li>✅ Link dapat diakses</li>
                    <li>✅ Catatan sudah diisi</li>
                </ul>
                <hr>
                <small class="text-muted">
                    Setelah submit, admin akan validasi. Jika disetujui, dapat 
                    <strong>{{ $assignment->journal->points }} points</strong>.
                </small>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-trophy"></i> Reward
            </div>
            <div class="card-body text-center">
                <h2 class="text-success">{{ $assignment->journal->points }}</h2>
                <p class="mb-0">Points akan didapat jika disetujui</p>
            </div>
        </div>
    </div>
</div>
@endsection
