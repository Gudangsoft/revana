@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Tugaskan Reviewer')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-plus"></i> Form Assign Reviewer
            </div>
            <div class="card-body">
                <form action="{{ route('admin.assignments.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('article_title') is-invalid @enderror" 
                               name="article_title" value="{{ old('article_title') }}" 
                               placeholder="Masukkan judul artikel" required>
                        @error('article_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link Submit <span class="text-danger">*</span></label>
                        <input type="url" class="form-control @error('submit_link') is-invalid @enderror" 
                               name="submit_link" value="{{ old('submit_link') }}" 
                               placeholder="https://" required>
                        @error('submit_link')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">User Akun <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('account_username') is-invalid @enderror" 
                                   name="account_username" value="{{ old('account_username') }}" 
                                   placeholder="Username akun" required>
                            @error('account_username')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pass Akun <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('account_password') is-invalid @enderror" 
                                   name="account_password" value="{{ old('account_password') }}" 
                                   placeholder="Password akun" required>
                            @error('account_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Surat Tugas (Link) <span class="text-danger">*</span></label>
                        <input type="url" class="form-control @error('assignment_letter_link') is-invalid @enderror" 
                               name="assignment_letter_link" value="{{ old('assignment_letter_link') }}" 
                               placeholder="https://" required>
                        @error('assignment_letter_link')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link Sertifikat <span class="text-danger">*</span></label>
                        <input type="url" class="form-control @error('certificate_link') is-invalid @enderror" 
                               name="certificate_link" value="{{ old('certificate_link') }}" 
                               placeholder="https://" required>
                        @error('certificate_link')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deadline <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('deadline') is-invalid @enderror" 
                               name="deadline" value="{{ old('deadline') }}" required>
                        @error('deadline')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bahasa <span class="text-danger">*</span></label>
                        <select class="form-select @error('language') is-invalid @enderror" 
                                name="language" required>
                            <option value="Indonesia" {{ old('language') == 'Indonesia' ? 'selected' : '' }}>Indonesia</option>
                            <option value="Inggris" {{ old('language') == 'Inggris' ? 'selected' : '' }}>Inggris</option>
                        </select>
                        @error('language')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih Reviewer <span class="text-danger">*</span></label>
                        <select class="form-select @error('reviewer_id') is-invalid @enderror" 
                                name="reviewer_id" required>
                            <option value="">-- Pilih Reviewer --</option>
                            @foreach($reviewers as $reviewer)
                            <option value="{{ $reviewer->id }}" {{ old('reviewer_id') == $reviewer->id ? 'selected' : '' }}>
                                {{ $reviewer->name }} - {{ $reviewer->email }}
                                @if($reviewer->article_languages)
                                    [{{ implode(', ', $reviewer->article_languages) }}]
                                @endif
                                ({{ $reviewer->completed_reviews }} reviews, {{ $reviewer->total_points }} pts)
                            </option>
                            @endforeach
                        </select>
                        @error('reviewer_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Assign Reviewer
                        </button>
                        <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">
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
                <h6>Tips Assign Reviewer:</h6>
                <ul class="small">
                    <li>Pilih reviewer yang sesuai dengan bidang jurnal</li>
                    <li>Perhatikan beban kerja reviewer saat ini</li>
                    <li>Reviewer akan menerima notifikasi tugas baru</li>
                    <li>Reviewer bisa menerima atau menolak tugas</li>
                </ul>
                <hr>
                <p class="mb-0 small text-muted">
                    Setelah reviewer menyelesaikan dan hasil review disetujui, 
                    reviewer akan mendapatkan point sesuai akreditasi jurnal.
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-people"></i> Statistik Reviewer
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Total Reviewer:</strong> {{ $reviewers->count() }}</p>
                <p class="mb-0"><strong>Jurnal Tersedia:</strong> {{ $journals->count() }}</p>
            </div>
        </div>
    </div>
</div>

@endsection

