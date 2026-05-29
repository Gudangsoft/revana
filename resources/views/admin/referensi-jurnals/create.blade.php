@extends('layouts.app')

@section('title', 'Tambah Referensi Jurnal - ' . $appSettings['app_name'])
@section('page-title', 'Tambah Referensi Jurnal')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<style>
.form-card { border:none; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.08); }
.form-card .card-header {
    background: linear-gradient(135deg,#4f46e5,#7c3aed);
    color: #fff; border-radius: 14px 14px 0 0;
    padding: 1.1rem 1.5rem;
}
.form-label { font-weight: 600; font-size: .84rem; color: #374151; margin-bottom: 4px; }
.form-control, .form-select {
    border-radius: 8px; border-color: #d1d5db; font-size:.88rem;
    transition: border-color .15s, box-shadow .15s;
}
.form-control:focus, .form-select:focus {
    border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.15);
}
.field-hint { font-size:.76rem; color:#9ca3af; margin-top:3px; }
.char-counter { font-size:.72rem; color:#9ca3af; float:right; }
</style>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card form-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-plus-circle-fill me-2"></i>Tambah Referensi Jurnal</span>
                <a href="{{ route('admin.referensi-jurnals.index') }}" class="btn btn-sm btn-light btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.referensi-jurnals.store') }}" method="POST" id="refForm">
                    @csrf

                    {{-- Nama Jurnal --}}
                    <div class="mb-3">
                        <label for="nama_jurnal" class="form-label">
                            Nama Jurnal <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('nama_jurnal') is-invalid @enderror"
                               id="nama_jurnal" name="nama_jurnal"
                               value="{{ old('nama_jurnal') }}"
                               placeholder="Nama lengkap jurnal" required maxlength="255">
                        <div class="field-hint">Nama jurnal seperti tertulis di publikasi</div>
                        @error('nama_jurnal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        {{-- Jenis Jurnal --}}
                        <div class="col-md-6">
                            <label for="jenis_jurnal" class="form-label">
                                Jenis Jurnal <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('jenis_jurnal') is-invalid @enderror"
                                   id="jenis_jurnal" name="jenis_jurnal"
                                   value="{{ old('jenis_jurnal') }}"
                                   list="jenis-list"
                                   placeholder="Pilih atau ketik..." required maxlength="100">
                            <datalist id="jenis-list">
                                <option value="Jurnal Nasional">
                                <option value="Jurnal Internasional">
                                <option value="Jurnal Nasional Terakreditasi">
                                <option value="Prosiding Nasional">
                                <option value="Prosiding Internasional">
                                @foreach($jenisOptions as $j)
                                    <option value="{{ $j }}">
                                @endforeach
                            </datalist>
                            @error('jenis_jurnal')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Bidang Ilmu --}}
                        <div class="col-md-6">
                            <label for="bidang_ilmu" class="form-label">
                                Bidang Ilmu <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('bidang_ilmu') is-invalid @enderror"
                                   id="bidang_ilmu" name="bidang_ilmu"
                                   value="{{ old('bidang_ilmu') }}"
                                   list="bidang-list"
                                   placeholder="Pilih atau ketik..." required maxlength="100">
                            <datalist id="bidang-list">
                                @foreach($bidangOptions as $b)
                                    <option value="{{ $b }}">
                                @endforeach
                            </datalist>
                            @error('bidang_ilmu')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Tahun --}}
                    <div class="mb-3">
                        <label for="tahun" class="form-label">
                            Tahun Publikasi <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               class="form-control @error('tahun') is-invalid @enderror"
                               id="tahun" name="tahun"
                               value="{{ old('tahun', date('Y')) }}"
                               min="1900" max="{{ date('Y') + 1 }}" required
                               style="max-width:160px;">
                        @error('tahun')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Referensi --}}
                    <div class="mb-3">
                        <label for="referensi" class="form-label">
                            Referensi <span class="text-danger">*</span>
                            <span class="char-counter" id="refCount">0 karakter</span>
                        </label>
                        <textarea class="form-control @error('referensi') is-invalid @enderror"
                                  id="referensi" name="referensi" rows="3"
                                  placeholder="Penulis, A. (2024). Judul artikel. Nama Jurnal, Vol(No), hal. https://doi.org/..."
                                  required>{{ old('referensi') }}</textarea>
                        <div class="field-hint">Gunakan format APA, IEEE, Vancouver, atau format lainnya secara konsisten.</div>
                        @error('referensi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Kutipan --}}
                    <div class="mb-4">
                        <label for="kutipan" class="form-label">
                            Kutipan
                            <span class="badge bg-light text-muted border ms-1" style="font-size:.7rem;font-weight:500;">Opsional</span>
                            <span class="char-counter" id="kutCount">0 karakter</span>
                        </label>
                        <textarea class="form-control @error('kutipan') is-invalid @enderror"
                                  id="kutipan" name="kutipan" rows="3"
                                  placeholder='A. Penulis, "Judul," Nama Jurnal, vol. 10, no. 2, pp. 1–10, 2024.'>{{ old('kutipan') }}</textarea>
                        <div class="field-hint">Teks singkat yang siap disalin ke dalam dokumen / daftar pustaka.</div>
                        @error('kutipan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-3">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('admin.referensi-jurnals.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateCounter(textareaId, counterId) {
    const ta = document.getElementById(textareaId);
    const el = document.getElementById(counterId);
    if (!ta || !el) return;
    el.textContent = ta.value.length + ' karakter';
    ta.addEventListener('input', () => el.textContent = ta.value.length + ' karakter');
}
updateCounter('referensi', 'refCount');
updateCounter('kutipan',   'kutCount');
</script>
@endpush
