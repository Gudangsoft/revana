@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Upload Hasil Review')

@section('sidebar')
    <a href="{{ route('reviewer.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('reviewer.tasks.index') }}" class="nav-link active">
        <i class="bi bi-clipboard-check"></i> My Tasks
    </a>
    <a href="{{ route('reviewer.certificates.index') }}" class="nav-link">
        <i class="bi bi-award-fill"></i> Sertifikat
    </a>
    <a href="{{ route('reviewer.rewards.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Rewards
    </a>
    <a href="{{ route('reviewer.leaderboard.index') }}" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="{{ route('reviewer.profile.edit') }}" class="nav-link">
        <i class="bi bi-person-circle"></i> My Profile
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
                    <strong>Artikel:</strong> {{ $assignment->article_title ?? 'N/A' }}<br>
                    <strong>Bahasa:</strong> <span class="badge bg-secondary">{{ $assignment->language ?? 'N/A' }}</span>
                    <strong>Deadline:</strong> 
                    @if($assignment->deadline)
                        <span class="badge bg-warning text-dark">{{ $assignment->deadline->format('d M Y') }}</span>
                    @else
                        <span class="badge bg-secondary">N/A</span>
                    @endif
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
                        <label class="form-label">Rekomendasi <span class="text-danger">*</span></label>
                        <select class="form-select @error('recommendation') is-invalid @enderror" 
                                name="recommendation" required>
                            <option value="">Pilih Rekomendasi</option>
                            <option value="ACCEPT" {{ old('recommendation') == 'ACCEPT' ? 'selected' : '' }}>Accept (Diterima)</option>
                            <option value="MINOR REVISION" {{ old('recommendation') == 'MINOR REVISION' ? 'selected' : '' }}>Minor Revision (Revisi Kecil)</option>
                            <option value="MAJOR REVISION" {{ old('recommendation') == 'MAJOR REVISION' ? 'selected' : '' }}>Major Revision (Revisi Besar)</option>
                            <option value="REJECT" {{ old('recommendation') == 'REJECT' ? 'selected' : '' }}>Reject (Ditolak)</option>
                        </select>
                        @error('recommendation')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                    Setelah submit, admin akan validasi hasil review Anda.
                </small>
            </div>
        </div>

    </div>
</div>
@endsection
