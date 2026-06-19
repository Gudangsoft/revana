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
                            <label class="form-label fw-semibold">E-ISSN</label>
                            <input type="text" class="form-control" name="e_issn" id="inpIssn"
                                   value="{{ old('e_issn', $journal->e_issn) }}"
                                   maxlength="20" placeholder="XXXX-XXXX">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Kota TTD</label>
                            <input type="text" class="form-control" name="loa_kota"
                                   value="{{ old('loa_kota', $journal->loa_kota ?? 'Semarang') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Editor ──────────────────────────────────────────────── --}}
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-person-badge me-1"></i> Editor-in-Chief</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Editor</label>
                            <input type="text" class="form-control" name="editor_name"
                                   value="{{ old('editor_name', $journal->editor_name) }}"
                                   placeholder="Dr. John Doe, M.T.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jabatan</label>
                            <input type="text" class="form-control" name="editor_title"
                                   value="{{ old('editor_title', $journal->editor_title ?? 'Editor in Chief') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Warna ───────────────────────────────────────────────── --}}
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-palette me-1"></i> Warna Template</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Warna Header</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="color" class="form-control form-control-color"
                                       name="primary_color" id="picPrimary"
                                       value="{{ old('primary_color', $journal->primary_color ?? '#1A237E') }}"
                                       style="width:48px;height:40px;">
                                <input type="text" class="form-control font-monospace" id="txtPrimary"
                                       value="{{ old('primary_color', $journal->primary_color ?? '#1A237E') }}"
                                       maxlength="7" style="width:100px;">
                                <span class="text-muted small">Contoh: #1A237E (biru), #4A148C (ungu), #1B5E20 (hijau)</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Warna Aksen / Footer</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="color" class="form-control form-control-color"
                                       name="secondary_color" id="picSecondary"
                                       value="{{ old('secondary_color', $journal->secondary_color ?? '#8B6914') }}"
                                       style="width:48px;height:40px;">
                                <input type="text" class="form-control font-monospace" id="txtSecondary"
                                       value="{{ old('secondary_color', $journal->secondary_color ?? '#8B6914') }}"
                                       maxlength="7" style="width:100px;">
                                <span class="text-muted small">Contoh: #8B6914 (emas), #BF360C (oranye)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Gambar ──────────────────────────────────────────────── --}}
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-image me-1"></i> Logo & Tanda Tangan</div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Logo Jurnal</label>
                            <div class="form-text mb-2">PNG/JPG, ukuran bulat, maks 2MB</div>
                            @if($journal->logo_path)
                            <div class="mb-2 d-flex align-items-center gap-3">
                                <img src="{{ Storage::url($journal->logo_path) }}" height="64" width="64"
                                     style="border-radius:50%;border:3px solid #ddd;object-fit:cover;" alt="Logo">
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="rmLogo">
                                        <label class="form-check-label text-danger small" for="rmLogo">Hapus logo</label>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                   name="logo" accept="image/*">
                            @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanda Tangan Editor</label>
                            <div class="form-text mb-2">PNG transparan lebih bagus, maks 2MB</div>
                            @if($journal->editor_signature_path)
                            <div class="mb-2 d-flex align-items-center gap-3">
                                <img src="{{ Storage::url($journal->editor_signature_path) }}" height="48"
                                     style="border:1px solid #eee;padding:4px;background:#fff;" alt="TTD">
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remove_signature" value="1" id="rmSign">
                                        <label class="form-check-label text-danger small" for="rmSign">Hapus TTD</label>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <input type="file" class="form-control @error('editor_signature') is-invalid @enderror"
                                   name="editor_signature" accept="image/*">
                            @error('editor_signature')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
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
})();
</script>
@endpush
