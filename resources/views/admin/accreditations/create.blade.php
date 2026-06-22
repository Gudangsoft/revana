@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Tambah Akreditasi Baru')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle"></i> Form Tambah Akreditasi
            </div>
            <div class="card-body">
                <form action="{{ route('admin.accreditations.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Nama Akreditasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name') }}" 
                               placeholder="Contoh: SINTA 1" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Nama akreditasi harus unik</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Logo SINTA</label>
                        <input type="file" class="form-control @error('logo_sinta') is-invalid @enderror"
                               name="logo_sinta" id="logo_sinta" accept="image/*"
                               onchange="previewLogo(this)">
                        @error('logo_sinta')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">JPG, PNG, WebP, SVG — maks 2MB</small>
                        <div id="logo_preview" class="mt-2" style="display:none">
                            <img id="logo_img" src="" alt="Preview" style="max-height:80px;border:1px solid #dee2e6;border-radius:6px;padding:4px">
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active"
                               id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Aktif
                        </label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
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
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Informasi
            </div>
            <div class="card-body">
                <h6>Contoh Akreditasi:</h6>
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
                    Akreditasi yang ditambahkan akan otomatis tersedia untuk dipilih saat membuat jurnal baru.
                </small>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
function previewLogo(input) {
    var preview = document.getElementById('logo_preview');
    var img = document.getElementById('logo_img');
    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}
</script>
@endpush
@endsection
