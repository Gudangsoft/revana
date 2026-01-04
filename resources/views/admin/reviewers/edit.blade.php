@extends('layouts.app')

@section('title', 'Edit Reviewer - ' . $appSettings['app_name'])
@section('page-title', 'Edit Data Reviewer')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-pencil-square"></i> Edit Data Reviewer
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('admin.reviewers.update', $reviewer) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h5 class="border-bottom pb-2 mb-3">Informasi Akun</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name', $reviewer->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email', $reviewer->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimal 8 karakter</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" 
                                   name="password_confirmation" placeholder="Ketik ulang password baru">
                        </div>
                    </div>

                    <h5 class="border-bottom pb-2 mb-3 mt-4">Data Pribadi</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-whatsapp text-success"></i> No. WhatsApp
                            </label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   name="phone" 
                                   value="{{ old('phone', $reviewer->phone) }}"
                                   placeholder="08123456789 atau 628123456789"
                                   id="phoneInput">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="alert alert-info mt-2 p-2" style="font-size: 0.85rem;">
                                <i class="bi bi-info-circle"></i> <strong>Format Nomor WhatsApp:</strong><br>
                                • Mulai dengan <code>08</code> (08xxx) atau <code>628</code> (628xxx)<br>
                                • Contoh: <code>081234567890</code> atau <code>6281234567890</code><br>
                                • Nomor akan dikonversi otomatis untuk WhatsApp
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIDN</label>
                            <input type="text" class="form-control @error('nidn') is-invalid @enderror" 
                                   name="nidn" value="{{ old('nidn', $reviewer->nidn) }}">
                            @error('nidn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      name="address" rows="2">{{ old('address', $reviewer->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h5 class="border-bottom pb-2 mb-3 mt-4">Data Akademik</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Institusi</label>
                            <input type="text" class="form-control @error('institution') is-invalid @enderror" 
                                   name="institution" value="{{ old('institution', $reviewer->institution) }}">
                            @error('institution')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" class="form-control @error('position') is-invalid @enderror" 
                                   name="position" value="{{ old('position', $reviewer->position) }}">
                            @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pendidikan Terakhir</label>
                            <input type="text" class="form-control @error('education_level') is-invalid @enderror" 
                                   name="education_level" value="{{ old('education_level', $reviewer->education_level) }}">
                            @error('education_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bidang Keahlian</label>
                            <input type="text" class="form-control @error('specialization') is-invalid @enderror" 
                                   name="specialization" value="{{ old('specialization', $reviewer->specialization) }}">
                            @error('specialization')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Bahasa Artikel yang Bisa Direview</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="article_languages[]" 
                                       value="Indonesia" id="lang_indo"
                                       {{ in_array('Indonesia', old('article_languages', $reviewer->article_languages ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="lang_indo">
                                    <i class="bi bi-flag-fill text-danger"></i> Indonesia
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="article_languages[]" 
                                       value="English" id="lang_eng"
                                       {{ in_array('English', old('article_languages', $reviewer->article_languages ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="lang_eng">
                                    <i class="bi bi-flag-fill text-primary"></i> English (Inggris)
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">SINTA ID</label>
                            <input type="text" class="form-control @error('sinta_id') is-invalid @enderror" 
                                   name="sinta_id" value="{{ old('sinta_id', $reviewer->sinta_id) }}">
                            @error('sinta_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Scopus ID</label>
                            <input type="text" class="form-control @error('scopus_id') is-invalid @enderror" 
                                   name="scopus_id" value="{{ old('scopus_id', $reviewer->scopus_id) }}">
                            @error('scopus_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Google Scholar</label>
                            <input type="url" class="form-control @error('google_scholar') is-invalid @enderror" 
                                   name="google_scholar" value="{{ old('google_scholar', $reviewer->google_scholar) }}"
                                   placeholder="https://scholar.google.com/...">
                            @error('google_scholar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Bio</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" 
                                      name="bio" rows="3">{{ old('bio', $reviewer->bio) }}</textarea>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Data
                        </button>
                        <a href="{{ route('admin.reviewers.show', $reviewer) }}" class="btn btn-secondary">
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
                <p><strong>Role:</strong> {{ ucfirst($reviewer->role) }}</p>
                <p><strong>Total Points:</strong> {{ $reviewer->total_points }}</p>
                <p><strong>Available Points:</strong> {{ $reviewer->available_points }}</p>
                <p><strong>Completed Reviews:</strong> {{ $reviewer->completed_reviews }}</p>
                <p><strong>Terdaftar:</strong> {{ $reviewer->created_at->format('d M Y') }}</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phoneInput');
    
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            
            // Real-time validation feedback
            if (value.length > 0) {
                if (value.startsWith('0') && value.length >= 10) {
                    e.target.classList.remove('is-invalid');
                    e.target.classList.add('is-valid');
                } else if (value.startsWith('62') && value.length >= 11) {
                    e.target.classList.remove('is-invalid');
                    e.target.classList.add('is-valid');
                } else {
                    e.target.classList.remove('is-valid');
                }
            } else {
                e.target.classList.remove('is-valid', 'is-invalid');
            }
        });
    }
});
</script>
@endsection
