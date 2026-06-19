@extends('layouts.app')

@section('title', 'Edit Jurnal - ' . $appSettings['app_name'])
@section('page-title', 'Edit Jurnal')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-journal-text"></i> Edit Data Jurnal
            </div>
            <div class="card-body">
                <form action="{{ route('admin.journal-masters.update', $journalMaster) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="kode_jurnal" class="form-label">Kode Jurnal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('kode_jurnal') is-invalid @enderror" id="kode_jurnal" name="kode_jurnal" value="{{ old('kode_jurnal', $journalMaster->kode_jurnal) }}" required>
                        @error('kode_jurnal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nama_jurnal" class="form-label">Nama Jurnal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_jurnal') is-invalid @enderror" id="nama_jurnal" name="nama_jurnal" value="{{ old('nama_jurnal', $journalMaster->nama_jurnal) }}" required>
                        @error('nama_jurnal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="publisher" class="form-label">Publisher <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('publisher') is-invalid @enderror" id="publisher" name="publisher" value="{{ old('publisher', $journalMaster->publisher) }}" required>
                        @error('publisher')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="link_jurnal" class="form-label">Link Jurnal <span class="text-danger">*</span></label>
                        <input type="url" class="form-control @error('link_jurnal') is-invalid @enderror" id="link_jurnal" name="link_jurnal" value="{{ old('link_jurnal', $journalMaster->link_jurnal) }}" placeholder="https://" required>
                        @error('link_jurnal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="accreditation" class="form-label">Akreditasi</label>
                        <select class="form-select @error('accreditation') is-invalid @enderror" id="accreditation" name="accreditation">
                            <option value="">-- Pilih Akreditasi --</option>
                            @foreach($accreditations as $acc)
                                <option value="{{ $acc->name }}" {{ old('accreditation', $journalMaster->accreditation) == $acc->name ? 'selected' : '' }}>
                                    {{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('accreditation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kategori" class="form-label">Kategori</label>
                                <select class="form-select @error('kategori') is-invalid @enderror" id="kategori" name="kategori">
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Penelitian" {{ old('kategori', $journalMaster->kategori) == 'Penelitian' ? 'selected' : '' }}>Penelitian</option>
                                    <option value="PKM" {{ old('kategori', $journalMaster->kategori) == 'PKM' ? 'selected' : '' }}>PKM</option>
                                </select>
                                @error('kategori')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jenis_jurnal" class="form-label">Jenis Jurnal</label>
                                <select class="form-select @error('jenis_jurnal') is-invalid @enderror" id="jenis_jurnal" name="jenis_jurnal">
                                    <option value="">-- Pilih Jenis Jurnal --</option>
                                    <option value="Jurnal Nasional" {{ old('jenis_jurnal', $journalMaster->jenis_jurnal) == 'Jurnal Nasional' ? 'selected' : '' }}>Jurnal Nasional</option>
                                    <option value="Jurnal Internasional" {{ old('jenis_jurnal', $journalMaster->jenis_jurnal) == 'Jurnal Internasional' ? 'selected' : '' }}>Jurnal Internasional</option>
                                </select>
                                @error('jenis_jurnal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $journalMaster->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Aktif
                            </label>
                        </div>
                    </div>

                    {{-- ── LOA Settings ──────────────────────────────── --}}
                    <hr class="my-4">
                    <h6 class="text-primary mb-3"><i class="bi bi-file-earmark-check"></i> Pengaturan LOA (Letter of Acceptance)</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Kode Singkat <small class="text-muted">(PAF, ISMaT, BDAS…)</small></label>
                                <input type="text" class="form-control @error('kode_singkat') is-invalid @enderror"
                                       name="kode_singkat" value="{{ old('kode_singkat', $journalMaster->kode_singkat) }}"
                                       maxlength="20" placeholder="PAF">
                                @error('kode_singkat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">E-ISSN</label>
                                <input type="text" class="form-control @error('e_issn') is-invalid @enderror"
                                       name="e_issn" value="{{ old('e_issn', $journalMaster->e_issn) }}"
                                       maxlength="20" placeholder="XXXX-XXXX">
                                @error('e_issn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Kota (untuk TTD)</label>
                                <input type="text" class="form-control" name="loa_kota"
                                       value="{{ old('loa_kota', $journalMaster->loa_kota ?? 'Semarang') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Editor-in-Chief</label>
                                <input type="text" class="form-control" name="editor_name"
                                       value="{{ old('editor_name', $journalMaster->editor_name) }}"
                                       placeholder="Dr. John Doe, M.T.">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jabatan Editor</label>
                                <input type="text" class="form-control" name="editor_title"
                                       value="{{ old('editor_title', $journalMaster->editor_title ?? 'Editor in Chief') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Warna Header</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" class="form-control form-control-color"
                                           name="primary_color"
                                           value="{{ old('primary_color', $journalMaster->primary_color ?? '#1A237E') }}"
                                           style="width:50px; height:38px;">
                                    <input type="text" class="form-control form-control-sm font-monospace"
                                           id="primaryColorText"
                                           value="{{ old('primary_color', $journalMaster->primary_color ?? '#1A237E') }}"
                                           style="width:90px;" maxlength="7">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Warna Aksen</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" class="form-control form-control-color"
                                           name="secondary_color"
                                           value="{{ old('secondary_color', $journalMaster->secondary_color ?? '#8B6914') }}"
                                           style="width:50px; height:38px;">
                                    <input type="text" class="form-control form-control-sm font-monospace"
                                           id="secondaryColorText"
                                           value="{{ old('secondary_color', $journalMaster->secondary_color ?? '#8B6914') }}"
                                           style="width:90px;" maxlength="7">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Resmi LOA <small class="text-muted">(kosong = hari ini)</small></label>
                                <input type="date" class="form-control" name="loa_tanggal"
                                       value="{{ old('loa_tanggal', $journalMaster->loa_tanggal) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Logo Jurnal <small class="text-muted">(PNG/JPG, maks 2MB)</small></label>
                                @if($journalMaster->logo_path)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($journalMaster->logo_path) }}" height="60"
                                         style="border-radius:50%; border:2px solid #ddd;" alt="Logo">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="remove_logo" id="remove_logo" value="1">
                                        <label class="form-check-label text-danger small" for="remove_logo">Hapus logo</label>
                                    </div>
                                </div>
                                @endif
                                <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                       name="logo" accept="image/*">
                                @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanda Tangan Editor <small class="text-muted">(PNG transparan, maks 2MB)</small></label>
                                @if($journalMaster->editor_signature_path)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($journalMaster->editor_signature_path) }}" height="50"
                                         style="border:1px solid #eee; padding:4px; background:#fff;" alt="TTD">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="remove_signature" id="remove_signature" value="1">
                                        <label class="form-check-label text-danger small" for="remove_signature">Hapus TTD</label>
                                    </div>
                                </div>
                                @endif
                                <input type="file" class="form-control @error('editor_signature') is-invalid @enderror"
                                       name="editor_signature" accept="image/*">
                                @error('editor_signature')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- LOA preview link --}}
                    @if($journalMaster->slots()->exists())
                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Setelah menyimpan, buka detail submission lalu klik tombol <strong>LOA</strong> untuk pratinjau.
                    </div>
                    @endif

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.journal-masters.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                    </div>
                </form>

@push('scripts')
<script>
(function () {
    // Sync color picker ↔ text input for primary
    var p = document.querySelector('[name="primary_color"]');
    var pt = document.getElementById('primaryColorText');
    if (p && pt) {
        p.addEventListener('input', function(){ pt.value = this.value; });
        pt.addEventListener('change', function(){ p.value = this.value; });
    }
    // Sync color picker ↔ text input for secondary
    var s = document.querySelector('[name="secondary_color"]');
    var st = document.getElementById('secondaryColorText');
    if (s && st) {
        s.addEventListener('input', function(){ st.value = this.value; });
        st.addEventListener('change', function(){ s.value = this.value; });
    }
})();
</script>
@endpush
            </div>
        </div>
    </div>
</div>
@endsection
