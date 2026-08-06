@extends('layouts.app')

@section('title', 'Setting LOA — ' . $journal->nama_jurnal)
@section('page-title', 'Setting LOA')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">

        {{-- Breadcrumb --}}
        <nav class="mb-3" style="font-size:.85rem;">
            <a href="{{ route('admin.loa-master.index') }}" class="text-decoration-none">Master LOA</a>
            <span class="text-muted mx-1">/</span>
            <span class="text-muted">{{ $journal->nama_jurnal }}</span>
        </nav>

        <form action="{{ route('admin.loa-master.update', $journal) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- ── Preview header realtime ──────────────────────────────── --}}
            <div class="card mb-4">
                <div id="previewHeader" style="background:{{ $journal->primary_color ?? '#1A237E' }};
                     color:#fff; padding:12px 20px; border-radius:8px 8px 0 0;
                     display:flex; align-items:center; gap:14px;">
                    <div id="previewLogo" style="width:54px;height:54px;border-radius:50%;
                         background:rgba(255,255,255,.2); border:3px solid rgba(255,255,255,.5);
                         display:flex;align-items:center;justify-content:center;
                         font-weight:bold;font-size:14px;flex-shrink:0;">
                        @if($journal->logo_path)
                        <img src="{{ Storage::url($journal->logo_path) }}"
                             style="width:100%;height:100%;border-radius:50%;object-fit:cover;" alt="Logo">
                        @else
                        {{ strtoupper(substr($journal->kode_singkat ?: $journal->nama_jurnal, 0, 2)) }}
                        @endif
                    </div>
                    <div>
                        <div id="previewKode" style="font-size:16px;font-weight:900;letter-spacing:1px;">
                            {{ $journal->kode_singkat ?: Str::words($journal->nama_jurnal, 2, '') }}
                        </div>
                        <div style="font-size:10px;opacity:.8;">{{ $journal->nama_jurnal }}</div>
                    </div>
                    <div style="margin-left:auto;font-size:10px;opacity:.85;text-align:right;">
                        @if($journal->e_issn)E-ISSN: <span id="previewIssn">{{ $journal->e_issn }}</span>@endif
                    </div>
                </div>
                <div id="previewSubbar"
                     style="background:{{ $journal->secondary_color ?? '#8B6914' }};
                            color:#fff;font-size:8px;padding:3px 20px;border-radius:0 0 8px 8px;">
                    Preview template LOA
                </div>
            </div>

            {{-- ── Bahasa LOA ───────────────────────────────────────────── --}}
            <div class="card mb-3 border-primary">
                <div class="card-header bg-primary bg-opacity-10 fw-semibold">
                    <i class="bi bi-translate me-1"></i> Bahasa Dokumen LOA
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold mb-2">Pilih Bahasa</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="loa_language" id="langEn" value="en"
                                           {{ old('loa_language', $journal->loa_language ?? 'en') === 'en' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="langEn">
                                        <strong>English</strong>
                                        <span class="text-muted small d-block">Bahasa Inggris</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="loa_language" id="langId" value="id"
                                           {{ old('loa_language', $journal->loa_language ?? 'en') === 'id' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="langId">
                                        <strong>Bahasa Indonesia</strong>
                                        <span class="text-muted small d-block">Indonesia</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div id="langPreviewEn" class="alert alert-light border mb-0 small" style="display:none;">
                                <strong>Preview (EN):</strong><br>
                                "We, the Editorial Board of <em>[Journal Name]</em>, hereby inform you that your manuscript has been peer-reviewed and formally <strong>ACCEPTED</strong> for publication..."
                            </div>
                            <div id="langPreviewId" class="alert alert-light border mb-0 small" style="display:none;">
                                <strong>Preview (ID):</strong><br>
                                "Kami, Dewan Redaksi <em>[Nama Jurnal]</em>, dengan ini menyampaikan bahwa manuskrip Saudara/i telah melalui penelaahan mitra bestari dan secara resmi <strong>DITERIMA</strong> untuk dipublikasikan..."
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── TTD & Nama Editor ────────────────────────────────────── --}}
            <div class="card mb-3 border-{{ $journal->loa_show_signature ?? true ? 'success' : 'secondary' }}">
                <div class="card-header d-flex justify-content-between align-items-center
                            {{ ($journal->loa_show_signature ?? true) ? 'bg-success bg-opacity-10' : '' }}">
                    <span>
                        <i class="bi bi-pen {{ ($journal->loa_show_signature ?? true) ? 'text-success' : 'text-muted' }} me-1"></i>
                        Tanda Tangan &amp; Nama Editor
                    </span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch"
                               name="loa_show_signature" id="swShowSignature" value="1"
                               {{ old('loa_show_signature', $journal->loa_show_signature ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="swShowSignature" id="swShowSignatureLabel">
                            {{ ($journal->loa_show_signature ?? true) ? 'Ditampilkan' : 'Disembunyikan' }}
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap Editor</label>
                            <input type="text" class="form-control" name="editor_name"
                                   value="{{ old('editor_name', $journal->editor_name) }}"
                                   placeholder="Dr. John Doe, M.T.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanda Tangan Editor
                                <small class="text-muted">(PNG transparan, maks 2MB)</small>
                            </label>
                            @if($journal->editor_signature_path)
                            <div class="mb-2">
                                <img src="{{ Storage::url($journal->editor_signature_path) }}" height="50"
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
                    <div class="alert alert-info py-2 mb-0 mt-3 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Kalau switch di atas dinonaktifkan, nama &amp; tanda tangan tetap tersimpan di sini
                        tapi tidak ikut tampil di dokumen LOA (kolom lain tetap seperti biasa).
                    </div>
                </div>
            </div>

            {{-- ── Identitas LOA ─────────────────────────────────────────── --}}
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-journal-text me-1"></i> Identitas Jurnal (untuk LOA)</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Kode Singkat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('kode_singkat') is-invalid @enderror"
                                   name="kode_singkat" id="inpKode"
                                   value="{{ old('kode_singkat', $journal->kode_singkat) }}"
                                   maxlength="20" placeholder="PAF / ISMaT / BDAS">
                            <div class="form-text">Muncul di nomor LOA & header</div>
                            @error('kode_singkat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">E-ISSN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('e_issn') is-invalid @enderror"
                                   name="e_issn" id="inpIssn"
                                   value="{{ old('e_issn', $journal->e_issn) }}"
                                   maxlength="20" placeholder="XXXX-XXXX" required>
                            @error('e_issn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">P-ISSN <span class="text-muted fw-normal small">(opsional)</span></label>
                            <input type="text" class="form-control @error('p_issn') is-invalid @enderror"
                                   name="p_issn"
                                   value="{{ old('p_issn', $journal->p_issn) }}"
                                   maxlength="20" placeholder="XXXX-XXXX">
                            @error('p_issn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Kota TTD</label>
                            <input type="text" class="form-control" name="loa_kota"
                                   value="{{ old('loa_kota', $journal->loa_kota ?? 'Semarang') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Status Akreditasi</label>
                            <textarea name="loa_status" rows="3"
                                      class="form-control @error('loa_status') is-invalid @enderror"
                                      placeholder="Contoh: Terakreditasi SINTA 5 (SK Dirjen Pendidikan Tinggi, Riset, dan Teknologi Kemendikbudristek RI No. 156/C/C3/KPT/2026, Tanggal 7 April 2026, Volume 1 Nomor 3 Tahun 2023 sampai Volume 6 Nomor 2 Tahun 2028)">{{ old('loa_status', $journal->loa_status) }}</textarea>
                            @error('loa_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Ditampilkan di baris Status pada dokumen LOA. Kosongkan jika tidak ada.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Periode Akreditasi Berakhir</label>
                            <div class="row g-2">
                                <div class="col-4 col-md-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Vol.</span>
                                        <input type="number" min="1" class="form-control @error('accreditation_end_volume') is-invalid @enderror"
                                               name="accreditation_end_volume"
                                               value="{{ old('accreditation_end_volume', $journal->accreditation_end_volume) }}">
                                    </div>
                                    @error('accreditation_end_volume')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-4 col-md-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">No.</span>
                                        <input type="number" min="1" class="form-control @error('accreditation_end_nomor') is-invalid @enderror"
                                               name="accreditation_end_nomor"
                                               value="{{ old('accreditation_end_nomor', $journal->accreditation_end_nomor) }}">
                                    </div>
                                    @error('accreditation_end_nomor')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-4 col-md-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Thn</span>
                                        <input type="number" min="2000" max="2100" class="form-control @error('accreditation_end_tahun') is-invalid @enderror"
                                               name="accreditation_end_tahun"
                                               value="{{ old('accreditation_end_tahun', $journal->accreditation_end_tahun) }}">
                                    </div>
                                    @error('accreditation_end_tahun')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="form-text">
                                Sesuai teks SK akreditasi (mis. "...sampai Volume 6 Nomor 1 Tahun 2027"). Dipakai untuk
                                <a href="{{ route('admin.monitoring-akreditasi.index') }}">Monitoring Akreditasi</a> — peringatan otomatis kalau mendekati kedaluwarsa (berbasis Tahun).
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Logo Akreditasi ─────────────────────────────────────── --}}
            @php $masterLogo = $accreditation->logo_sinta ?? null; @endphp
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-award me-1"></i> Logo Akreditasi</div>
                <div class="card-body">
                    @if($masterLogo)
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="background:#1A237E;padding:8px 14px;border-radius:6px;display:inline-flex;align-items:center;">
                            <img src="{{ asset('storage/' . $masterLogo) }}" height="36"
                                 style="display:block;" alt="{{ $journal->accreditation }}">
                        </div>
                        <div>
                            <div class="fw-semibold small">{{ $journal->accreditation }}</div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle mt-1">
                                <i class="bi bi-link-45deg"></i> Otomatis dari master akreditasi
                            </span>
                            <div class="text-muted mt-1" style="font-size:.75rem;">
                                Ganti logo di <a href="{{ route('admin.accreditations.index') }}">Master Akreditasi</a>
                            </div>
                        </div>
                    </div>
                    @elseif($journal->accreditation)
                    <div class="alert alert-warning py-2 small mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Jurnal ini terakreditasi <strong>{{ $journal->accreditation }}</strong> tapi belum ada logo di
                        <a href="{{ route('admin.accreditations.index') }}">Master Akreditasi</a>.
                    </div>
                    @else
                    <p class="text-muted small mb-3">Jurnal belum memiliki akreditasi.</p>
                    @endif

                    <div>
                        <label class="form-label fw-semibold">
                            <i class="bi bi-link-45deg me-1"></i> Link SK Akreditasi
                            <span class="fw-normal text-muted small ms-1">— logo di LOA akan dapat diklik menuju link ini</span>
                        </label>
                        <input type="url" name="link_sk_akreditasi" id="link_sk_akreditasi"
                               class="form-control @error('link_sk_akreditasi') is-invalid @enderror"
                               value="{{ old('link_sk_akreditasi', $journal->link_sk_akreditasi) }}"
                               placeholder="https://sinta.kemdikbud.go.id/journals/...">
                        @error('link_sk_akreditasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Kosongkan jika tidak ada. Contoh: link profil jurnal di SINTA atau link SK PDF.</div>
                    </div>
                </div>
            </div>

            {{-- ── Gambar Header LOA ────────────────────────────────────── --}}
            <div class="card mb-3 border-info">
                <div class="card-header bg-info bg-opacity-10">
                    <i class="bi bi-card-image me-1"></i> Gambar Header LOA
                    <span class="text-muted small fw-normal ms-2">— opsional, menggantikan header otomatis</span>
                </div>
                <div class="card-body">
                    <div class="form-text mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Jika diisi, gambar ini akan tampil sebagai header penuh di dokumen LOA, menggantikan header warna + logo yang dibuat otomatis.
                        Format JPG/PNG, lebar 210mm (A4), maks 4MB.
                    </div>
                    @if($journal->header_image_path)
                    <div class="mb-3">
                        <div class="fw-semibold small mb-1 text-muted">Header saat ini:</div>
                        <img src="{{ Storage::url($journal->header_image_path) }}"
                             style="width:100%;max-width:600px;border:1px solid #ddd;border-radius:4px;display:block;" alt="Header LOA">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_header_image" value="1" id="rmHeader">
                            <label class="form-check-label text-danger small" for="rmHeader">Hapus gambar header (gunakan header otomatis)</label>
                        </div>
                    </div>
                    @endif
                    <div>
                        <label class="form-label fw-semibold">{{ $journal->header_image_path ? 'Ganti Gambar Header' : 'Upload Gambar Header' }}</label>
                        <input type="file" class="form-control @error('header_image') is-invalid @enderror"
                               name="header_image" accept="image/*" id="inpHeaderImg">
                        @error('header_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div id="headerImgPreview" class="mt-2" style="display:none;">
                        <div class="fw-semibold small mb-1 text-muted">Preview upload:</div>
                        <img id="headerImgPreviewImg"
                             style="width:100%;max-width:600px;border:1px solid #0dcaf0;border-radius:4px;display:block;" alt="Preview">
                    </div>
                </div>
            </div>

            {{-- ── LOA Otomatis ────────────────────────────────────────── --}}
            <div class="card mb-4 border-{{ $journal->loa_auto_send ? 'success' : 'secondary' }}">
                <div class="card-header d-flex justify-content-between align-items-center
                            {{ $journal->loa_auto_send ? 'bg-success bg-opacity-10' : '' }}">
                    <span>
                        <i class="bi bi-lightning-charge-fill {{ $journal->loa_auto_send ? 'text-success' : 'text-muted' }} me-1"></i>
                        LOA Otomatis
                    </span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch"
                               name="loa_auto_send" id="swAutoSend" value="1"
                               {{ old('loa_auto_send', $journal->loa_auto_send) ? 'checked' : '' }}>
                        <label class="form-check-label" for="swAutoSend" id="swLabel">
                            {{ $journal->loa_auto_send ? 'Aktif' : 'Non-aktif' }}
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kirim LOA Otomatis Saat</label>
                            <select class="form-select" name="loa_auto_trigger" id="selTrigger">
                                @foreach($triggerOptions as $val => $label)
                                <option value="{{ $val }}"
                                    {{ old('loa_auto_trigger', $journal->loa_auto_trigger ?? 'manual') == $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="alert alert-info py-2 mb-0 small w-100">
                                <i class="bi bi-info-circle me-1"></i>
                                Email LOA dikirim otomatis ke <strong>email penulis</strong> yang diisi di data submission.
                                Pastikan email penulis terisi sebelum mengaktifkan fitur ini.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Actions ─────────────────────────────────────────────── --}}
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.loa-master.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save"></i> Simpan Setting LOA
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // ── Color picker sync ───────────────────────────────────────
    function syncColor(picker, text, target) {
        picker.addEventListener('input', function () {
            text.value = this.value;
            target.style.background = this.value;
        });
        text.addEventListener('change', function () {
            picker.value = this.value;
            target.style.background = this.value;
        });
    }

    var picP = document.getElementById('picPrimary');
    var txtP = document.getElementById('txtPrimary');
    var picS = document.getElementById('picSecondary');
    var txtS = document.getElementById('txtSecondary');
    var prevH = document.getElementById('previewHeader');
    var prevSub = document.getElementById('previewSubbar');

    if (picP) syncColor(picP, txtP, prevH);
    if (picS) syncColor(picS, txtS, prevSub);

    // ── Kode singkat → update preview ───────────────────────────
    var inpKode = document.getElementById('inpKode');
    var prevKode = document.getElementById('previewKode');
    if (inpKode && prevKode) {
        inpKode.addEventListener('input', function () {
            prevKode.textContent = this.value || '{{ Str::words($journal->nama_jurnal, 2, "") }}';
        });
    }

    // ── E-ISSN → update preview ──────────────────────────────────
    var inpIssn = document.getElementById('inpIssn');
    var prevIssn = document.getElementById('previewIssn');
    if (inpIssn && prevIssn) {
        inpIssn.addEventListener('input', function () {
            prevIssn.textContent = this.value;
        });
    }

    // ── Auto-send switch label ───────────────────────────────────
    var sw = document.getElementById('swAutoSend');
    var swLbl = document.getElementById('swLabel');
    if (sw && swLbl) {
        sw.addEventListener('change', function () {
            swLbl.textContent = this.checked ? 'Aktif' : 'Non-aktif';
        });
    }

    // ── Show-signature switch label ────────────────────────────────
    var swSig = document.getElementById('swShowSignature');
    var swSigLbl = document.getElementById('swShowSignatureLabel');
    if (swSig && swSigLbl) {
        swSig.addEventListener('change', function () {
            swSigLbl.textContent = this.checked ? 'Ditampilkan' : 'Disembunyikan';
        });
    }

    // ── Accreditation logo preview ───────────────────────────────
    var inpAcc = document.getElementById('inpAccLogo');
    var accPrev = document.getElementById('accLogoPreview');
    var accPrevImg = document.getElementById('accLogoPreviewImg');
    if (inpAcc) {
        inpAcc.addEventListener('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    accPrevImg.src = e.target.result;
                    accPrev.style.display = '';
                };
                reader.readAsDataURL(file);
            } else {
                accPrev.style.display = 'none';
            }
        });
    }

    // ── Header image preview ─────────────────────────────────────
    var inpHdr = document.getElementById('inpHeaderImg');
    var hdrPrev = document.getElementById('headerImgPreview');
    var hdrPrevImg = document.getElementById('headerImgPreviewImg');
    if (inpHdr) {
        inpHdr.addEventListener('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    hdrPrevImg.src = e.target.result;
                    hdrPrev.style.display = '';
                };
                reader.readAsDataURL(file);
            } else {
                hdrPrev.style.display = 'none';
            }
        });
    }

    // ── Footer image preview ─────────────────────────────────────
    var inpFtr = document.getElementById('inpFooterImg');
    var ftrPrev = document.getElementById('footerImgPreview');
    var ftrPrevImg = document.getElementById('footerImgPreviewImg');
    if (inpFtr) {
        inpFtr.addEventListener('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    ftrPrevImg.src = e.target.result;
                    ftrPrev.style.display = '';
                };
                reader.readAsDataURL(file);
            } else {
                ftrPrev.style.display = 'none';
            }
        });
    }

    // ── Language preview ─────────────────────────────────────────
    var langInputs = document.querySelectorAll('input[name="loa_language"]');
    var prevEn = document.getElementById('langPreviewEn');
    var prevId = document.getElementById('langPreviewId');
    function updateLangPreview() {
        var val = document.querySelector('input[name="loa_language"]:checked')?.value;
        if (prevEn) prevEn.style.display = val === 'en' ? '' : 'none';
        if (prevId) prevId.style.display = val === 'id' ? '' : 'none';
    }
    langInputs.forEach(function(r) { r.addEventListener('change', updateLangPreview); });
    updateLangPreview();
})();
</script>
@endpush
