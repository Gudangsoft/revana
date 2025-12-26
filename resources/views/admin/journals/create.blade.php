@extends('layouts.app')

@section('title', 'Tambah Jurnal - REVANA')
@section('page-title', 'Tambah Jurnal Baru')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.journals.index') }}" class="nav-link active">
        <i class="bi bi-journal-text"></i> Jurnal
    </a>
    <a href="{{ route('admin.assignments.index') }}" class="nav-link">
        <i class="bi bi-clipboard-check"></i> Review Assignments
    </a>
    <a href="{{ route('admin.reviewers.index') }}" class="nav-link">
        <i class="bi bi-people"></i> Reviewers
    </a>
    <a href="{{ route('admin.redemptions.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle"></i> Form Tambah Jurnal
            </div>
            <div class="card-body">
                <form action="{{ route('admin.journals.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Judul Jurnal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               name="title" value="{{ old('title') }}" required>
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link Jurnal <span class="text-danger">*</span></label>
                        <input type="url" class="form-control @error('link') is-invalid @enderror" 
                               name="link" value="{{ old('link') }}" 
                               placeholder="https://..." required>
                        @error('link')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Akreditasi <span class="text-danger">*</span></label>
                        <select class="form-select @error('accreditation') is-invalid @enderror" 
                                name="accreditation" required>
                            <option value="">Pilih Akreditasi</option>
                            <option value="SINTA 1" {{ old('accreditation') == 'SINTA 1' ? 'selected' : '' }}>SINTA 1 (100 points)</option>
                            <option value="SINTA 2" {{ old('accreditation') == 'SINTA 2' ? 'selected' : '' }}>SINTA 2 (80 points)</option>
                            <option value="SINTA 3" {{ old('accreditation') == 'SINTA 3' ? 'selected' : '' }}>SINTA 3 (60 points)</option>
                            <option value="SINTA 4" {{ old('accreditation') == 'SINTA 4' ? 'selected' : '' }}>SINTA 4 (40 points)</option>
                            <option value="SINTA 5" {{ old('accreditation') == 'SINTA 5' ? 'selected' : '' }}>SINTA 5 (20 points)</option>
                            <option value="SINTA 6" {{ old('accreditation') == 'SINTA 6' ? 'selected' : '' }}>SINTA 6 (10 points)</option>
                        </select>
                        @error('accreditation')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Points akan diberikan otomatis berdasarkan akreditasi</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Terbitan</label>
                        <input type="text" class="form-control @error('publisher') is-invalid @enderror" 
                               name="publisher" value="{{ old('publisher') }}" 
                               placeholder="Nama penerbit jurnal">
                        @error('publisher')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Marketing</label>
                        <input type="text" class="form-control @error('marketing') is-invalid @enderror" 
                               name="marketing" value="{{ old('marketing') }}" 
                               placeholder="Nama marketing">
                        @error('marketing')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">PIC (Person In Charge)</label>
                        <input type="text" class="form-control @error('pic') is-invalid @enderror" 
                               name="pic" value="{{ old('pic') }}" 
                               placeholder="Nama PIC">
                        @error('pic')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Author</label>
                        <input type="text" class="form-control @error('author_name') is-invalid @enderror" 
                               name="author_name" value="{{ old('author_name') }}" 
                               placeholder="Nama penulis jurnal">
                        @error('author_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link Turnitin</label>
                        <input type="url" class="form-control @error('turnitin_link') is-invalid @enderror" 
                               name="turnitin_link" value="{{ old('turnitin_link') }}" 
                               placeholder="https://...">
                        @error('turnitin_link')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <a href="{{ route('admin.journals.index') }}" class="btn btn-secondary">
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
                <h6>Sistem Point:</h6>
                <ul class="list-unstyled">
                    <li>🥇 SINTA 1 = 100 points</li>
                    <li>🥈 SINTA 2 = 80 points</li>
                    <li>🥉 SINTA 3 = 60 points</li>
                    <li>📊 SINTA 4 = 40 points</li>
                    <li>📈 SINTA 5 = 20 points</li>
                    <li>📉 SINTA 6 = 10 points</li>
                </ul>
                <hr>
                <small class="text-muted">
                    Point akan otomatis diberikan ke reviewer setelah hasil review disetujui.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
