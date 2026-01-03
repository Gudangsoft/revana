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
                        <label class="form-label">Nomor Artikel <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('article_number') is-invalid @enderror" 
                               name="article_number" value="{{ old('article_number') }}" 
                               placeholder="Contoh: ART-2026-001" required>
                        @error('article_number')
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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username Reviewer <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('reviewer_username') is-invalid @enderror" 
                                   name="reviewer_username" value="{{ old('reviewer_username') }}" 
                                   placeholder="Username untuk reviewer" required>
                            @error('reviewer_username')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password Reviewer <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('reviewer_password') is-invalid @enderror" 
                                   name="reviewer_password" value="{{ old('reviewer_password') }}" 
                                   placeholder="Password untuk reviewer" required>
                            @error('reviewer_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
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
                        <label class="form-label">Pilih Reviewer 1 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control mb-2" id="searchReviewer1" placeholder="🔍 Cari reviewer (nama atau email)...">
                        <select class="form-select @error('reviewer_id') is-invalid @enderror" 
                                name="reviewer_id" id="reviewer1" size="5" required style="height: 200px;">
                            <option value="">-- Pilih Reviewer 1 --</option>
                            @foreach($reviewers as $reviewer)
                            <option value="{{ $reviewer->id }}" {{ old('reviewer_id') == $reviewer->id ? 'selected' : '' }}
                                    data-search="{{ strtolower($reviewer->name . ' ' . $reviewer->email) }}">
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

                    <div class="mb-3">
                        <label class="form-label">Pilih Reviewer 2 <span class="text-muted">(Opsional)</span></label>
                        <input type="text" class="form-control mb-2" id="searchReviewer2" placeholder="🔍 Cari reviewer (nama atau email)...">
                        <select class="form-select @error('reviewer_2_id') is-invalid @enderror" 
                                name="reviewer_2_id" id="reviewer2" size="5" style="height: 200px;">
                            <option value="">-- Pilih Reviewer 2 --</option>
                            @foreach($reviewers as $reviewer)
                            <option value="{{ $reviewer->id }}" {{ old('reviewer_2_id') == $reviewer->id ? 'selected' : '' }}
                                    data-search="{{ strtolower($reviewer->name . ' ' . $reviewer->email) }}">
                                {{ $reviewer->name }} - {{ $reviewer->email }}
                                @if($reviewer->article_languages)
                                    [{{ implode(', ', $reviewer->article_languages) }}]
                                @endif
                                ({{ $reviewer->completed_reviews }} reviews, {{ $reviewer->total_points }} pts)
                            </option>
                            @endforeach
                        </select>
                        @error('reviewer_2_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Reviewer 2 akan menerima tugas yang sama untuk review bersama</small>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reviewer1 = document.getElementById('reviewer1');
    const reviewer2 = document.getElementById('reviewer2');
    const searchReviewer1 = document.getElementById('searchReviewer1');
    const searchReviewer2 = document.getElementById('searchReviewer2');
    
    // Search functionality for Reviewer 1
    searchReviewer1.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const options = reviewer1.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
                return;
            }
            
            const searchData = option.getAttribute('data-search') || '';
            if (searchData.includes(searchTerm)) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    });
    
    // Search functionality for Reviewer 2
    searchReviewer2.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const options = reviewer2.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
                return;
            }
            
            const searchData = option.getAttribute('data-search') || '';
            if (searchData.includes(searchTerm)) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    });
    
    // Prevent selecting same reviewer
    reviewer1.addEventListener('change', function() {
        if (this.value && this.value === reviewer2.value) {
            alert('Reviewer 1 dan Reviewer 2 tidak boleh sama!');
            reviewer2.value = '';
        }
    });
    
    reviewer2.addEventListener('change', function() {
        if (this.value && this.value === reviewer1.value) {
            alert('Reviewer 2 dan Reviewer 1 tidak boleh sama!');
            this.value = '';
        }
    });
});
</script>
@endpush

@endsection

