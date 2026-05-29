@extends('layouts.app')

@section('title', 'Referensi Jurnal - ' . $appSettings['app_name'])
@section('page-title', 'Referensi Jurnal')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<style>
.stat-card {
    border: none;
    border-radius: 14px;
    transition: transform .18s, box-shadow .18s;
    overflow: hidden;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.13) !important; }
.stat-icon {
    width: 52px; height: 52px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; flex-shrink: 0;
}
.filter-card {
    border: none;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
}
.badge-jenis-nas  { background: linear-gradient(135deg,#6366f1,#818cf8); color:#fff; }
.badge-jenis-int  { background: linear-gradient(135deg,#f59e0b,#fbbf24); color:#fff; }
.badge-jenis-other{ background: linear-gradient(135deg,#6b7280,#9ca3af); color:#fff; }
.ref-text {
    font-size: .82rem;
    color: #374151;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.copy-btn {
    padding: 1px 6px; font-size: .72rem;
    border-radius: 6px; border: 1px solid #d1d5db;
    background: #f9fafb; cursor: pointer; transition: background .15s;
}
.copy-btn:hover { background: #e5e7eb; }
.copy-btn.copied { background: #d1fae5; border-color: #6ee7b7; color: #065f46; }
.table thead th {
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6b7280;
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
    white-space: nowrap;
    padding: 10px 12px;
}
.table tbody td { padding: 10px 12px; vertical-align: middle; }
.table tbody tr:hover { background: #f0f9ff; }
.active-filter-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .75rem; padding: 3px 10px;
    background: #e0e7ff; color: #3730a3;
    border-radius: 999px; font-weight: 500;
}
.active-filter-badge .rm { cursor: pointer; font-size: .85rem; color: #6366f1; }
.active-filter-badge .rm:hover { color: #ef4444; }
.empty-state { padding: 60px 20px; text-align: center; color: #9ca3af; }
.empty-state i { font-size: 3rem; margin-bottom: 12px; display: block; opacity: .4; }
</style>

{{-- ══════════ STAT CARDS ══════════ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#ede9fe;">
                    <i class="bi bi-bookmark-star-fill" style="color:#7c3aed;"></i>
                </div>
                <div>
                    <div class="fw-bold fs-3 lh-1">{{ number_format($totalCount) }}</div>
                    <div class="text-muted small">Total Referensi</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#e0e7ff;">
                    <i class="bi bi-journal-text" style="color:#4f46e5;"></i>
                </div>
                <div>
                    <div class="fw-bold fs-3 lh-1">{{ number_format($nasionalCount) }}</div>
                    <div class="text-muted small">Jurnal Nasional</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fef3c7;">
                    <i class="bi bi-globe" style="color:#d97706;"></i>
                </div>
                <div>
                    <div class="fw-bold fs-3 lh-1">{{ number_format($internasionalCount) }}</div>
                    <div class="text-muted small">Internasional</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#d1fae5;">
                    <i class="bi bi-diagram-3-fill" style="color:#059669;"></i>
                </div>
                <div>
                    <div class="fw-bold fs-3 lh-1">{{ $bidangOptions->count() }}</div>
                    <div class="text-muted small">Bidang Ilmu</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════ FILTER PANEL ══════════ --}}
<div class="card filter-card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.referensi-jurnals.index') }}" id="filterForm">
            <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        <i class="bi bi-search"></i> Cari
                    </label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Nama jurnal, referensi, kutipan..."
                           value="{{ request('search') }}" autocomplete="off">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        <i class="bi bi-bookmarks"></i> Jenis Jurnal
                    </label>
                    <select name="jenis_jurnal" class="form-select form-select-sm">
                        <option value="">Semua Jenis</option>
                        @foreach($jenisOptions as $jenis)
                            <option value="{{ $jenis }}" {{ request('jenis_jurnal') === $jenis ? 'selected' : '' }}>
                                {{ $jenis }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        <i class="bi bi-mortarboard"></i> Bidang Ilmu
                    </label>
                    <select name="bidang_ilmu" class="form-select form-select-sm">
                        <option value="">Semua Bidang</option>
                        @foreach($bidangOptions as $bidang)
                            <option value="{{ $bidang }}" {{ request('bidang_ilmu') === $bidang ? 'selected' : '' }}>
                                {{ $bidang }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        <i class="bi bi-calendar3"></i> Tahun
                    </label>
                    <select name="tahun" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        @foreach($tahunOptions as $tahun)
                            <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                {{ $tahun }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    @if(request()->hasAny(['search','jenis_jurnal','bidang_ilmu','tahun']))
                    <a href="{{ route('admin.referensi-jurnals.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Active filter pills --}}
        @if(request()->hasAny(['search','jenis_jurnal','bidang_ilmu','tahun']))
        <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">
            <span class="small text-muted me-1">Filter aktif:</span>
            @if(request('search'))
            <span class="active-filter-badge">
                <i class="bi bi-search" style="font-size:.7rem;"></i> "{{ Str::limit(request('search'),30) }}"
                <a href="{{ request()->fullUrlWithoutQuery(['search']) }}" class="rm">&times;</a>
            </span>
            @endif
            @if(request('jenis_jurnal'))
            <span class="active-filter-badge">
                <i class="bi bi-bookmarks" style="font-size:.7rem;"></i> {{ request('jenis_jurnal') }}
                <a href="{{ request()->fullUrlWithoutQuery(['jenis_jurnal']) }}" class="rm">&times;</a>
            </span>
            @endif
            @if(request('bidang_ilmu'))
            <span class="active-filter-badge">
                <i class="bi bi-mortarboard" style="font-size:.7rem;"></i> {{ request('bidang_ilmu') }}
                <a href="{{ request()->fullUrlWithoutQuery(['bidang_ilmu']) }}" class="rm">&times;</a>
            </span>
            @endif
            @if(request('tahun'))
            <span class="active-filter-badge">
                <i class="bi bi-calendar3" style="font-size:.7rem;"></i> {{ request('tahun') }}
                <a href="{{ request()->fullUrlWithoutQuery(['tahun']) }}" class="rm">&times;</a>
            </span>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- ══════════ MAIN CARD ══════════ --}}
<div class="card border-0 shadow-sm" style="border-radius:14px;overflow:hidden;">
    <div class="card-header d-flex justify-content-between align-items-center py-3"
         style="background:#fff; border-bottom:1px solid #e5e7eb;">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-bookmark-star-fill" style="color:#7c3aed; font-size:1.1rem;"></i>
            <span class="fw-semibold">Data Referensi Jurnal</span>
            @if($referensiJurnals->total())
            <span class="badge rounded-pill" style="background:#ede9fe;color:#5b21b6;font-weight:600;">
                {{ number_format($referensiJurnals->total()) }}
            </span>
            @endif
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.referensi-jurnals.template') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-file-earmark-arrow-down"></i> Template
            </a>
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-upload"></i> Import Excel
            </button>
            <a href="{{ route('admin.referensi-jurnals.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle-fill"></i> Tambah
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show m-3 mb-0 rounded-3">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show m-3 mb-0 rounded-3">
        <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="dataTable">
                <thead>
                    <tr>
                        <th style="width:46px;">#</th>
                        <th>Nama Jurnal</th>
                        <th style="width:140px;">Jenis</th>
                        <th style="width:160px;">Bidang Ilmu</th>
                        <th style="width:70px;">Tahun</th>
                        <th>Referensi</th>
                        <th>Kutipan</th>
                        <th style="width:100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referensiJurnals as $item)
                    @php
                        $jenis = $item->jenis_jurnal ?? '';
                        $jenisClass = str_contains(strtolower($jenis), 'internasional')
                            ? 'badge-jenis-int'
                            : (str_contains(strtolower($jenis), 'nasional') ? 'badge-jenis-nas' : 'badge-jenis-other');
                    @endphp
                    <tr>
                        <td class="text-muted small">
                            {{ $loop->iteration + ($referensiJurnals->currentPage() - 1) * $referensiJurnals->perPage() }}
                        </td>
                        <td>
                            <div class="fw-semibold" style="font-size:.88rem; line-height:1.4;">
                                {{ $item->nama_jurnal }}
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $jenisClass }}" style="font-size:.72rem; padding:4px 8px; border-radius:8px;">
                                {{ $jenis ?: '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size:.72rem; padding:4px 8px; border-radius:8px;">
                                {{ $item->bidang_ilmu ?: '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-semibold text-secondary">{{ $item->tahun }}</span>
                        </td>
                        <td style="max-width:240px;">
                            <div class="ref-text">{{ $item->referensi }}</div>
                            <button class="btn copy-btn mt-1"
                                    data-text="{{ $item->referensi }}"
                                    onclick="copyText(this)" title="Salin referensi">
                                <i class="bi bi-clipboard me-1"></i>Salin
                            </button>
                        </td>
                        <td style="max-width:220px;">
                            @if($item->kutipan)
                            <div class="ref-text">{{ $item->kutipan }}</div>
                            <button class="btn copy-btn mt-1"
                                    data-text="{{ $item->kutipan }}"
                                    onclick="copyText(this)" title="Salin kutipan">
                                <i class="bi bi-clipboard me-1"></i>Salin
                            </button>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-info"
                                        onclick="showDetail({{ $item->id }},
                                            @js($item->nama_jurnal),
                                            @js($item->jenis_jurnal),
                                            @js($item->bidang_ilmu),
                                            {{ $item->tahun }},
                                            @js($item->referensi),
                                            @js($item->kutipan)
                                        )"
                                        title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="{{ route('admin.referensi-jurnals.edit', $item) }}"
                                   class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.referensi-jurnals.destroy', $item) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-bookmark-x"></i>
                                <div class="fw-semibold mb-1">Belum ada data referensi jurnal</div>
                                <div class="small mb-3">
                                    @if(request()->hasAny(['search','jenis_jurnal','bidang_ilmu','tahun']))
                                        Tidak ada hasil untuk filter yang diterapkan.
                                        <a href="{{ route('admin.referensi-jurnals.index') }}">Reset filter</a>
                                    @else
                                        Mulai tambahkan referensi jurnal pertama Anda.
                                    @endif
                                </div>
                                <a href="{{ route('admin.referensi-jurnals.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-circle"></i> Tambah Referensi
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($referensiJurnals->hasPages())
    <div class="card-footer bg-white border-top" style="border-radius:0 0 14px 14px;">
        @include('partials.per-page-selector', ['paginator' => $referensiJurnals])
    </div>
    @endif
</div>

{{-- ══════════ DETAIL MODAL ══════════ --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                <div>
                    <h5 class="modal-title text-white mb-0" id="detailNama">—</h5>
                    <div class="mt-1" id="detailMeta"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-muted mb-0">
                            <i class="bi bi-file-text me-1" style="color:#4f46e5;"></i> Referensi Lengkap
                        </h6>
                        <button class="btn copy-btn" id="copyRefBtn" onclick="copyModalText('detailRef','copyRefBtn')">
                            <i class="bi bi-clipboard me-1"></i>Salin
                        </button>
                    </div>
                    <div class="p-3 rounded-3" style="background:#f8f9fa;font-size:.88rem;line-height:1.7;border:1px solid #e5e7eb;" id="detailRef">—</div>
                </div>
                <div id="kutipanSection">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-muted mb-0">
                            <i class="bi bi-quote me-1" style="color:#7c3aed;"></i> Kutipan
                        </h6>
                        <button class="btn copy-btn" id="copyKutBtn" onclick="copyModalText('detailKut','copyKutBtn')">
                            <i class="bi bi-clipboard me-1"></i>Salin
                        </button>
                    </div>
                    <div class="p-3 rounded-3" style="background:#f5f3ff;font-size:.88rem;line-height:1.7;border:1px solid #ddd6fe;font-style:italic;" id="detailKut">—</div>
                </div>
            </div>
            <div class="modal-footer justify-content-between" style="border-top:1px solid #e5e7eb;">
                <span class="text-muted small" id="detailEditLink"></span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════ IMPORT MODAL ══════════ --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#059669,#10b981);">
                <h5 class="modal-title text-white">
                    <i class="bi bi-upload me-2"></i>Import Referensi Jurnal
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.referensi-jurnals.import') }}" method="POST"
                  enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="file"
                               accept=".xlsx,.xls,.csv" required id="importFileInput">
                        <small class="text-muted">Format: .xlsx / .xls / .csv — Maks. 5 MB</small>
                    </div>

                    {{-- Preview nama file --}}
                    <div id="filePreview" class="d-none mb-3">
                        <div class="p-3 rounded-3 d-flex align-items-center gap-3"
                             style="background:#f0fdf4;border:1px solid #bbf7d0;">
                            <i class="bi bi-file-earmark-excel-fill text-success fs-4"></i>
                            <div>
                                <div class="fw-semibold small" id="filePreviewName"></div>
                                <div class="text-muted" style="font-size:.75rem;" id="filePreviewSize"></div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info rounded-3" style="font-size:.85rem;">
                        <h6 class="alert-heading fw-bold mb-2">
                            <i class="bi bi-info-circle-fill me-1"></i>Petunjuk Import
                        </h6>
                        <ul class="mb-0 ps-3">
                            <li>Download <strong>template</strong> terlebih dahulu untuk format yang benar</li>
                            <li>Kolom wajib: <code>nama_jurnal</code>, <code>jenis_jurnal</code>, <code>bidang_ilmu</code>, <code>tahun</code>, <code>referensi</code></li>
                            <li>Kolom opsional: <code>kutipan</code></li>
                            <li>Jika <em>nama_jurnal + tahun</em> sudah ada → data diperbarui</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e5e7eb;">
                    <a href="{{ route('admin.referensi-jurnals.template') }}"
                       class="btn btn-outline-secondary btn-sm me-auto">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i>Download Template
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm" id="importSubmitBtn">
                        <i class="bi bi-upload me-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/* ── Copy to clipboard (inline tabel) ── */
function copyText(btn) {
    const text = btn.dataset.text;
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.classList.add('copied');
        btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Tersalin';
        setTimeout(() => { btn.classList.remove('copied'); btn.innerHTML = orig; }, 1800);
    });
}

/* ── Copy to clipboard (modal) ── */
function copyModalText(elId, btnId) {
    const text = document.getElementById(elId).innerText;
    const btn  = document.getElementById(btnId);
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.classList.add('copied');
        btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Tersalin';
        setTimeout(() => { btn.classList.remove('copied'); btn.innerHTML = orig; }, 1800);
    });
}

/* ── Detail Modal ── */
function showDetail(id, nama, jenis, bidang, tahun, referensi, kutipan) {
    const jenisHtml = jenis
        ? `<span class="badge badge-jenis-${ jenis.toLowerCase().includes('internasional') ? 'int' : 'nas' }" style="font-size:.72rem;padding:3px 8px;border-radius:7px;">${jenis}</span>`
        : '';
    const bidangHtml = bidang
        ? `<span class="badge bg-light text-dark border" style="font-size:.72rem;padding:3px 8px;border-radius:7px;">${bidang}</span>`
        : '';

    document.getElementById('detailNama').textContent = nama;
    document.getElementById('detailMeta').innerHTML   = `${jenisHtml} ${bidangHtml} <span class="badge bg-white text-dark opacity-75 ms-1" style="font-size:.72rem;">${tahun}</span>`;
    document.getElementById('detailRef').textContent  = referensi || '—';
    document.getElementById('detailKut').textContent  = kutipan   || '—';

    const editUrl = `/admin/referensi-jurnals/${id}/edit`;
    document.getElementById('detailEditLink').innerHTML =
        `<a href="${editUrl}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>`;

    // Sembunyikan section kutipan jika kosong
    document.getElementById('kutipanSection').style.display = kutipan ? '' : 'none';

    new bootstrap.Modal(document.getElementById('detailModal')).show();
}

/* ── File preview di modal import ── */
document.getElementById('importFileInput').addEventListener('change', function () {
    const preview  = document.getElementById('filePreview');
    const nameEl   = document.getElementById('filePreviewName');
    const sizeEl   = document.getElementById('filePreviewSize');
    if (this.files.length) {
        const f    = this.files[0];
        const size = f.size < 1024 * 1024
            ? (f.size / 1024).toFixed(1) + ' KB'
            : (f.size / 1024 / 1024).toFixed(2) + ' MB';
        nameEl.textContent = f.name;
        sizeEl.textContent = size;
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
});

/* ── Spinner saat import submit ── */
document.getElementById('importForm').addEventListener('submit', function () {
    const btn = document.getElementById('importSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengimport...';
});
</script>
@endpush
