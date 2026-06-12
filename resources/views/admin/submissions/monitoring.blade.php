@extends('layouts.app')

@section('title', 'Monitoring Proses' . (request('program') ? ' ' . strtoupper(request('program')) : '') . ' - ' . $appSettings['app_name'])
@section('page-title', 'Monitoring Proses' . (request('program') ? ' ' . strtoupper(request('program')) : ''))

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<style>
/* Sticky Table Styles for Monitoring */
.monitoring-scroll-wrapper {
    overflow-x: auto;
    overflow-y: visible;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    scrollbar-width: thin;
    scrollbar-color: #6c757d #dee2e6;
}

.monitoring-scroll-wrapper::-webkit-scrollbar {
    height: 14px;
    width: 12px;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-track {
    background: #e9ecef;
    border-radius: 6px;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb {
    background: #0d6efd;
    border-radius: 6px;
    border: 3px solid #e9ecef;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: #0b5ed7;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-corner {
    background: #dee2e6;
}

.drag-hint {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.7rem;
    color: #6c757d;
    background: #e9ecef;
    padding: 3px 8px;
    border-radius: 12px;
}

/* Credential code: klik untuk copy */
code.copyable {
    cursor: pointer;
    user-select: all;
    -webkit-user-select: all;
    padding: 1px 4px;
    border-radius: 3px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    transition: background 0.15s;
    display: inline-block;
}
code.copyable:hover {
    background: #dbeafe;
    border-color: #93c5fd;
}
code.copyable.copied {
    background: #dcfce7 !important;
    border-color: #86efac !important;
    color: #166534;
}

/* Inline assignment dropdown */
.inline-assign-select {
    font-size: 0.7rem;
    padding: 2px 4px;
    min-width: 80px;
    max-width: 100px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    background: #fff;
    cursor: pointer;
}
.inline-assign-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
}
.inline-assign-select.has-value {
    background-color: #d1e7dd;
    border-color: #198754;
}
.inline-assign-select.saving {
    opacity: 0.6;
    pointer-events: none;
}

/* Inline credential input */
.inline-credential-input {
    font-size: 0.65rem;
    padding: 2px 4px;
    width: 70px;
    border: 1px solid #dee2e6;
    border-radius: 3px;
    background: #fff;
    font-family: monospace;
    cursor: text;
    user-select: text;
    -webkit-user-select: text;
}
.inline-credential-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
    outline: none;
}
.inline-credential-input.has-value {
    background-color: #fff3cd;
}
.inline-credential-input.saving {
    opacity: 0.6;
}
.credential-group {
    display: flex;
    gap: 2px;
    align-items: center;
}

.table-monitoring {
    border-collapse: collapse;
    border-spacing: 0;
    font-size: 0.72rem;
    line-height: 1.2;
}

/* ── HEADER REDESIGN ─────────────────────────────────────────────── */
.table-monitoring thead th {
    position: sticky;
    top: 0;
    z-index: 3;
    white-space: nowrap;
    padding: 3px 7px;
    height: 28px;
    line-height: 1;
    vertical-align: middle;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    border: 1px solid #0a0e1a !important;
}

/* Row 1 — group label */
.table-monitoring thead tr:first-child th {
    background: #0f172a !important;
    color: #cbd5e1 !important;
    border-bottom: 1px solid #0a0e1a !important;
    height: 28px;
}

/* Colored group headers — row 1
   bg-dark (Author Access): no left-border accent — sits flush against base columns
   Other groups: 4px colored left border as group divider                          */
.table-monitoring thead th.bg-dark      { background:#1e293b !important;color:#94a3b8 !important;border-left:1px solid #0a0e1a !important;border-right:1px solid #0a0e1a !important; }
.table-monitoring thead th.bg-info      { background:#075985 !important;color:#bae6fd !important;border-left:4px solid #38bdf8 !important;border-right:1px solid #0a0e1a !important; }
.table-monitoring thead th.bg-warning   { background:#78350f !important;color:#fde68a !important;border-left:4px solid #fbbf24 !important;border-right:1px solid #0a0e1a !important; }
.table-monitoring thead th.bg-primary   { background:#3730a3 !important;color:#c7d2fe !important;border-left:4px solid #818cf8 !important;border-right:1px solid #0a0e1a !important; }
.table-monitoring thead th.bg-success   { background:#14532d !important;color:#86efac !important;border-left:4px solid #4ade80 !important;border-right:1px solid #0a0e1a !important; }
.table-monitoring thead th.bg-validator { background:#4a1d96 !important;color:#ddd6fe !important;border-left:4px solid #a78bfa !important;border-right:1px solid #0a0e1a !important; }
.table-monitoring thead th.text-dark    { color:#fde68a !important; }

/* Row 2 — sub-headers (light tint + dark text, selalu terbaca) */
.table-monitoring thead tr:nth-child(2) th {
    top: 29px;
    height: 28px;
    line-height: 1;
    font-size: 0.65rem;
    font-weight: 700;
    vertical-align: middle;
    background: #f1f5f9 !important;
    color: #334155 !important;
    border-top: 2px solid #0a0e1a !important;
    border-bottom: 1px solid #cbd5e1 !important;
}
.table-monitoring thead tr:nth-child(2) th.bg-dark      { background:#e2e8f0 !important;color:#1e293b !important;border-left:1px solid #94a3b8 !important; }
.table-monitoring thead tr:nth-child(2) th.bg-info      { background:#bae6fd !important;color:#0369a1 !important;border-left:4px solid #0ea5e9 !important; }
.table-monitoring thead tr:nth-child(2) th.bg-warning   { background:#fde68a !important;color:#92400e !important;border-left:4px solid #f59e0b !important; }
.table-monitoring thead tr:nth-child(2) th.bg-primary   { background:#c7d2fe !important;color:#3730a3 !important;border-left:4px solid #6366f1 !important; }
.table-monitoring thead tr:nth-child(2) th.bg-success   { background:#bbf7d0 !important;color:#15803d !important;border-left:4px solid #22c55e !important; }
.table-monitoring thead tr:nth-child(2) th.bg-validator { background:#e9d5ff !important;color:#7c3aed !important;border-left:4px solid #8b5cf6 !important; }

/* Sticky headers */
.table-monitoring thead th.sticky-first,
.table-monitoring thead th.sticky-second {
    z-index: 5;
    background: #0f172a !important;
    color: #e2e8f0 !important;
}

.table-monitoring tbody td.td-validator {
    background-color: #f3e8ff;
}
.table-monitoring tbody tr:hover td.td-validator {
    background-color: #e9d5ff !important;
}

/* Sticky first column (Kode Submit) */
.table-monitoring th.sticky-first,
.table-monitoring td.sticky-first {
    position: sticky;
    left: 0;
    z-index: 2;
    background: #fff;
    min-width: 120px;
    box-shadow: 2px 0 4px -2px rgba(0,0,0,0.12);
}

.table-monitoring thead th.sticky-first {
    z-index: 6;
    background: #0f172a !important;
    box-shadow: none;
}

/* Sticky second column (ID Artikel) */
.table-monitoring th.sticky-second,
.table-monitoring td.sticky-second {
    position: sticky;
    left: 120px;
    z-index: 2;
    background: #fff;
    min-width: 100px;
    box-shadow: 2px 0 4px -2px rgba(0,0,0,0.12);
}

.table-monitoring thead th.sticky-second {
    z-index: 6;
    background: #0f172a !important;
    box-shadow: none;
}

.table-monitoring tbody td {
    white-space: nowrap;
    padding: 2px 5px;
    border: 1px solid #dee2e6;
    vertical-align: middle;
}

.table-monitoring tbody tr:hover td {
    background-color: #e8f4fd !important;
}

.table-monitoring tbody tr:hover td.sticky-first,
.table-monitoring tbody tr:hover td.sticky-second {
    background-color: #e8f4fd !important;
}

/* Row colors by workflow stage */
.row-new     td { background-color: #fff !important; }
.row-pending td { background-color: #fffbeb !important; }
.row-pending td.sticky-first, .row-pending td.sticky-second { background-color: #fffbeb !important; }
.row-progress td { background-color: #f0f7ff !important; }
.row-progress td.sticky-first, .row-progress td.sticky-second { background-color: #f0f7ff !important; }
.row-published td { background-color: #d1fae5 !important; }
.row-published td.sticky-first, .row-published td.sticky-second { background-color: #d1fae5 !important; }
.row-rejected td { background-color: #fee2e2 !important; }
.row-rejected td.sticky-first, .row-rejected td.sticky-second { background-color: #fee2e2 !important; }
.table-monitoring tbody tr:hover td { background-color: #dbeafe !important; }

/* Status badge */
.status-badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.62rem;
    font-weight: 600;
    white-space: nowrap;
    line-height: 1.4;
}
.status-badge.status-submitted  { background:#e2e8f0; color:#475569; }
.status-badge.status-editor     { background:#dbeafe; color:#1d4ed8; }
.status-badge.status-author     { background:#fef9c3; color:#a16207; }
.status-badge.status-reviewer   { background:#ede9fe; color:#6d28d9; }
.status-badge.status-production { background:#dcfce7; color:#15803d; }
.status-badge.status-validator  { background:#f3e8ff; color:#7e22ce; }
.status-badge.status-published  { background:#bbf7d0; color:#166534; border: 1px solid #86efac; }
.status-badge.status-rejected   { background:#fee2e2; color:#b91c1c; }
.status-badge.status-pending    { background:#fef08a; color:#854d0e; }

/* Progress counter */
.progress-counter {
    font-size: 0.6rem;
    color: #6b7280;
    white-space: nowrap;
}
.progress-counter .done { color: #16a34a; font-weight: 700; }

/* Thead drag-scroll cursor */
.table-monitoring thead {
    cursor: grab;
}
.table-monitoring thead:active {
    cursor: grabbing;
}

/* Scroll controls */
.scroll-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 6px;
}

.scroll-nav-btn {
    padding: 6px 12px;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.scroll-nav-btn:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

.scroll-nav-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.scroll-position-indicator {
    display: flex;
    align-items: center;
    gap: 10px;
}

.scroll-position-bar {
    width: 200px;
    height: 6px;
    background: #dee2e6;
    border-radius: 3px;
    overflow: hidden;
}

.scroll-position-fill {
    height: 100%;
    background: linear-gradient(90deg, #0d6efd, #0dcaf0);
    border-radius: 3px;
    transition: width 0.1s;
}

/* Quick navigation buttons */
.quick-nav {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}

.quick-nav-btn {
    padding: 4px 8px;
    font-size: 0.7rem;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.quick-nav-btn:hover {
    background: #e9ecef;
}

.quick-nav-btn.active {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

/* Pending validation alert animation */
@keyframes pulse-warning {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
.pending-validation-alert {
    animation: pulse-warning 2s infinite;
}

/* ── BORDER CLEANUP ─────────────────────────────────────────────── */
/* Hapus semua border vertikal di tbody — hanya sisakan garis horizontal bawah */
.table-monitoring tbody td {
    border-left: none !important;
    border-right: none !important;
    border-top: none !important;
    border-bottom: 1px solid #e2e8f0 !important;
}

/* Group boundary — border kiri berwarna sesuai aksen header */
.table-monitoring tbody td.grp-dark     { border-left: 2px solid #64748b !important; }
.table-monitoring tbody td.grp-info     { border-left: 4px solid #38bdf8 !important; }
.table-monitoring tbody td.grp-warning  { border-left: 4px solid #fbbf24 !important; }
.table-monitoring tbody td.grp-primary  { border-left: 4px solid #818cf8 !important; }
.table-monitoring tbody td.grp-success  { border-left: 4px solid #4ade80 !important; }
.table-monitoring tbody td.grp-validator{ border-left: 4px solid #a78bfa !important; }

/* ── INPUT CLEANUP ──────────────────────────────────────────────── */
/* Input transparan saat kosong, muncul saat hover/focus */
.inline-credential-input {
    border-color: transparent !important;
    background: transparent !important;
}
.inline-credential-input:hover {
    border-color: #ced4da !important;
    background: #fff !important;
}
.inline-credential-input:focus {
    border-color: #86b7fe !important;
    background: #fff !important;
    box-shadow: 0 0 0 2px rgba(13,110,253,0.15) !important;
}
/* Saat has-value: tetap kuning tanpa border */
.inline-credential-input.has-value {
    background: #fff3cd !important;
    border-color: transparent !important;
}
.inline-credential-input.has-value:hover,
.inline-credential-input.has-value:focus {
    border-color: #fbbf24 !important;
}
/* Separator "/" di credential-group jadi abu tipis */
.credential-group span { color: #cbd5e1; }
</style>

@include('partials.auto-refresh', ['interval' => 60, 'arId' => 'admin-mon'])

{{-- Notifikasi Tugas Menunggu Validasi --}}
@if($pendingCount > 0)
<div class="alert alert-warning alert-dismissible fade show pending-validation-alert mb-4" role="alert">
    <div class="d-flex align-items-center">
        <div class="me-3">
            <i class="bi bi-hourglass-split" style="font-size: 2.5rem;"></i>
        </div>
        <div class="flex-grow-1">
            <h5 class="alert-heading mb-1">
                <i class="bi bi-bell-fill"></i> Ada {{ $pendingCount }} Tugas Menunggu Validasi!
            </h5>
            <p class="mb-2">PIC sudah menyelesaikan pekerjaan dan menunggu validasi dari Admin.</p>
            <div class="d-flex flex-wrap gap-2">
                @foreach($pendingValidations as $pending)
                <a href="{{ route('admin.submissions.process', $pending) }}" class="btn btn-sm btn-warning">
                    <i class="bi bi-check-lg"></i> {{ $pending->kode_submit }} 
                    <span class="badge bg-dark">{{ str_replace('_SUBMITTED', '', $pending->status) }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Total</h6>
                        <h2 class="card-title mb-0">{{ $stats['total'] }}</h2>
                    </div>
                    <i class="bi bi-file-earmark-text fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Submitted</h6>
                        <h2 class="card-title mb-0">{{ $stats['submitted'] }}</h2>
                    </div>
                    <i class="bi bi-clock fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Dalam Proses</h6>
                        <h2 class="card-title mb-0">{{ $stats['in_process'] }}</h2>
                    </div>
                    <i class="bi bi-gear fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Published</h6>
                        <h2 class="card-title mb-0">{{ $stats['published'] }}</h2>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-bar-chart"></i> Monitoring Proses Submit
                    @if(isset($program) && $program)
                        <span class="badge bg-{{ $program == 'bkd' ? 'info' : 'success' }} ms-2">{{ strtoupper($program) }}</span>
                    @endif
                </span>
                <div class="d-flex gap-2">
                    @feature('fasttrack')
                    <a href="{{ route('admin.fasttrack-management.monitoring.index') }}" class="btn btn-warning btn-sm fw-semibold">
                        <i class="bi bi-lightning-charge-fill"></i> Lihat Fasttrack
                    </a>
                    @endfeature
                    <a href="{{ route('admin.submissions.index', array_filter(['program' => $program ?? null])) }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <form action="{{ route('admin.submissions.monitoring') }}" method="GET" class="mb-4">
                    @if(isset($program) && $program)
                        <input type="hidden" name="program" value="{{ $program }}">
                    @endif
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label for="tanggal_dari" class="form-label small mb-1">Tanggal Dari</label>
                            <input type="date" class="form-control form-control-sm" id="tanggal_dari" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="tanggal_sampai" class="form-label small mb-1">Tanggal Sampai</label>
                            <input type="date" class="form-control form-control-sm" id="tanggal_sampai" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="journal_master_id" class="form-label small mb-1">Jurnal</label>
                            <select class="form-select form-select-sm" id="journal_master_id" name="journal_master_id">
                                <option value="">-- Semua --</option>
                                @foreach($journals as $journal)
                                    <option value="{{ $journal->id }}" {{ request('journal_master_id') == $journal->id ? 'selected' : '' }}>
                                        {{ Str::limit($journal->nama_jurnal, 20) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label small mb-1">Status</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="">-- Semua --</option>
                                @foreach($statusOptions as $key => $value)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort_by" class="form-label small mb-1">Urutkan</label>
                            <select class="form-select form-select-sm" id="sort_by" name="sort_by">
                                <option value="date_asc"  {{ request('sort_by', 'date_asc') == 'date_asc'   ? 'selected' : '' }}>↑ Terlama</option>
                                <option value="date_desc" {{ request('sort_by') == 'date_desc' ? 'selected' : '' }}>↓ Terbaru</option>
                                <option value="title_asc" {{ request('sort_by') == 'title_asc' ? 'selected' : '' }}>↑ Judul A→Z</option>
                                <option value="title_desc"{{ request('sort_by') == 'title_desc'? 'selected' : '' }}>↓ Judul Z→A</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1 invisible">.</label>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.submissions.monitoring', array_filter(['program' => $program ?? null])) }}" class="btn btn-outline-secondary btn-sm" title="Reset filter">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                                <a href="{{ route('admin.submissions.export', request()->query()) }}" class="btn btn-success btn-sm" title="Export Excel">
                                    <i class="bi bi-file-earmark-excel"></i>
                                </a>
                                <button type="button" class="btn btn-outline-success btn-sm" title="Import" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="bi bi-upload"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Bulk Assignment Controls -->
                <div class="card bg-light mb-3">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                    <label class="form-check-label" for="selectAll">
                                        <strong>Pilih Semua</strong>
                                    </label>
                                </div>
                                <span class="text-muted" id="selectedCount">0 dipilih</span>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-info btn-sm" id="bulkEditorBtn" disabled data-bs-toggle="modal" data-bs-target="#bulkEditorModal">
                                    <i class="bi bi-people"></i> Tugaskan Editor
                                </button>
                                <button type="button" class="btn btn-warning btn-sm" id="bulkAuthorBtn" disabled data-bs-toggle="modal" data-bs-target="#bulkAuthorModal">
                                    <i class="bi bi-person-check"></i> Tugaskan Author
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" id="bulkReviewerBtn" disabled data-bs-toggle="modal" data-bs-target="#bulkReviewerModal">
                                    <i class="bi bi-journal-check"></i> Tugaskan Reviewer
                                </button>
                                <button type="button" class="btn btn-success btn-sm" id="bulkProductionBtn" disabled data-bs-toggle="modal" data-bs-target="#bulkProductionModal">
                                    <i class="bi bi-gear"></i> Tugaskan Production
                                </button>
                                <button type="button" class="btn btn-sm" id="bulkValidatorBtn" disabled data-bs-toggle="modal" data-bs-target="#bulkValidatorModal" style="background:#6f42c1;color:white;">
                                    <i class="bi bi-shield-check"></i> Tugaskan Validator
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scroll Controls -->
                <div class="scroll-controls">
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" class="scroll-nav-btn" id="scrollStartBtn" title="Ke Awal">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <button type="button" class="scroll-nav-btn" id="scrollLeftBtn" title="Scroll Kiri">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <div class="scroll-position-indicator">
                            <div class="scroll-position-bar">
                                <div class="scroll-position-fill" id="scrollPositionFill" style="width: 0%"></div>
                            </div>
                            <small class="text-muted" id="scrollPositionText">0%</small>
                            <span class="drag-hint" title="Klik dan tahan di area HEADER KOLOM lalu geser kiri/kanan untuk scroll. Area data dapat diseleksi teks.">
                                <i class="bi bi-grip-horizontal"></i> Geser di header kolom
                            </span>
                        </div>
                        <button type="button" class="scroll-nav-btn" id="scrollRightBtn" title="Scroll Kanan">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                        <button type="button" class="scroll-nav-btn" id="scrollEndBtn" title="Ke Akhir">
                            <i class="bi bi-chevron-bar-right"></i>
                        </button>
                    </div>
                    <div class="quick-nav">
                        <span class="text-muted me-2" style="font-size: 0.75rem;">Lompat ke:</span>
                        <button type="button" class="quick-nav-btn" data-target="submit">Submit</button>
                        <button type="button" class="quick-nav-btn" data-target="editor1">Editor1</button>
                        <button type="button" class="quick-nav-btn" data-target="author1">Author1</button>
                        <button type="button" class="quick-nav-btn" data-target="editor2">Editor2</button>
                        <button type="button" class="quick-nav-btn" data-target="reviewer1">Reviewer1</button>
                        <button type="button" class="quick-nav-btn" data-target="reviewer2">Reviewer2</button>
                        <button type="button" class="quick-nav-btn" data-target="editor3">Editor3</button>
                        <button type="button" class="quick-nav-btn" data-target="author2">Author2</button>
                        <button type="button" class="quick-nav-btn" data-target="production">Production</button>
                        <button type="button" class="quick-nav-btn" data-target="validator" style="background:#6f42c1;color:white;border-color:#6f42c1;">Validasi</button>
                    </div>
                </div>

                <!-- Data Table with Full Process Columns -->
                <div class="monitoring-scroll-wrapper" id="monitoringScrollWrapper">
                    <table class="table table-monitoring table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th rowspan="2" class="align-middle text-center" style="width: 40px; min-width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="selectAllTable">
                                </th>
                                <th rowspan="2" class="align-middle sticky-first">Kode Submit</th>
                                <th rowspan="2" class="align-middle sticky-second">ID Artikel</th>
                                <th rowspan="2" class="align-middle text-center" style="min-width:90px;">Status</th>
                                <th rowspan="2" class="align-middle text-center" style="min-width:75px;">Tgl Submit</th>
                                <th rowspan="2" class="align-middle">Judul</th>
                                <th rowspan="2" class="align-middle text-center" style="min-width:80px; background:#fff3cd; color:#856404;" title="Catatan dari Marketing">
                                    <i class="bi bi-megaphone-fill"></i><br><small>Mkt Note</small>
                                </th>
                                <th rowspan="2" class="align-middle">Link</th>
                                <th rowspan="2" class="align-middle">Penulis</th>
                                <th rowspan="2" class="align-middle">No HP / Email</th>
                                <th colspan="4" class="text-center bg-dark" id="colSubmit">🔑 Author Access</th>
                                <th colspan="3" class="text-center bg-info" id="colEditor1">① Editor 1</th>
                                <th colspan="2" class="text-center bg-warning text-dark" id="colAuthor1">② Author 1</th>
                                <th colspan="2" class="text-center bg-info" id="colEditor2">③ Editor 2</th>
                                <th colspan="4" class="text-center bg-primary" id="colReviewer1">④ Reviewer 1</th>
                                <th colspan="4" class="text-center bg-primary" id="colReviewer2">⑤ Reviewer 2</th>
                                <th colspan="2" class="text-center bg-info" id="colEditor3">⑥ Editor 3</th>
                                <th colspan="2" class="text-center bg-warning text-dark" id="colAuthor2">⑦ Author 2</th>
                                <th colspan="5" class="text-center bg-success" id="colProduction">⑧ Production</th>
                                <th colspan="3" class="text-center bg-validator" id="colValidator">⑨ Validasi</th>
                            </tr>
                            <tr>
                                <!-- Author Access sub-headers (4 cols) -->
                                <th class="bg-dark">PIC Marketing</th>
                                <th class="bg-dark">Petugas Submit</th>
                                <th class="bg-dark">Username</th>
                                <th class="bg-dark">Password</th>
                                <!-- Editor 1 sub-headers (3 cols) -->
                                <th class="bg-info">Petugas</th>
                                <th class="bg-info">User/Pass</th>
                                <th class="bg-info">Valid</th>
                                <!-- Author 1 sub-headers (2 cols) -->
                                <th class="bg-warning">Petugas</th>
                                <th class="bg-warning">Valid</th>
                                <!-- Editor 2 sub-headers (2 cols) -->
                                <th class="bg-info">Petugas</th>
                                <th class="bg-info">Valid</th>
                                <!-- Reviewer 1 sub-headers (4 cols) -->
                                <th class="bg-primary">Petugas</th>
                                <th class="bg-primary">User/Pass</th>
                                <th class="bg-primary">Catatan</th>
                                <th class="bg-primary">Valid</th>
                                <!-- Reviewer 2 sub-headers (4 cols) -->
                                <th class="bg-primary">Petugas</th>
                                <th class="bg-primary">User/Pass</th>
                                <th class="bg-primary">Catatan</th>
                                <th class="bg-primary">Valid</th>
                                <!-- Editor 3 sub-headers (2 cols) -->
                                <th class="bg-info">Petugas</th>
                                <th class="bg-info">Valid</th>
                                <!-- Author 2 sub-headers (2 cols) -->
                                <th class="bg-warning">Petugas</th>
                                <th class="bg-warning">Valid</th>
                                <!-- Production sub-headers (5 cols) -->
                                <th class="bg-success">Petugas</th>
                                <th class="bg-success">User Editor</th>
                                <th class="bg-success">Pass Editor</th>
                                <th class="bg-success">Link Publish</th>
                                <th class="bg-success">Valid</th>
                                <!-- Validator sub-headers (3 cols) -->
                                <th class="bg-validator">Petugas</th>
                                <th class="bg-validator">Catatan</th>
                                <th class="bg-validator">Valid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $s)
                            @php
                                $isCompleted = $s->validator_valid == 1;

                                // Row color by stage
                                $rowClass = match(true) {
                                    $s->status === 'PUBLISHED'            => 'row-published',
                                    $s->status === 'REJECTED'             => 'row-rejected',
                                    str_contains($s->status, '_SUBMITTED')=> 'row-pending',
                                    $s->status === 'SUBMITTED'            => 'row-new',
                                    default                               => 'row-progress',
                                };

                                // Status badge config
                                $statusBadgeClass = match(true) {
                                    $s->status === 'PUBLISHED'                              => 'status-published',
                                    $s->status === 'REJECTED'                               => 'status-rejected',
                                    str_contains($s->status, '_SUBMITTED')                  => 'status-pending',
                                    $s->status === 'SUBMITTED'                              => 'status-submitted',
                                    str_contains($s->status, 'EDITOR')                     => 'status-editor',
                                    str_contains($s->status, 'AUTHOR')                     => 'status-author',
                                    str_contains($s->status, 'REVIEWER')                   => 'status-reviewer',
                                    str_contains($s->status, 'PRODUCTION')                 => 'status-production',
                                    str_contains($s->status, 'VALIDATOR')                  => 'status-validator',
                                    default                                                 => 'status-submitted',
                                };
                                $statusLabel = $statusOptions[$s->status] ?? $s->status;

                                // Progress counter X/9
                                $validCount = collect(['editor1_valid','author1_valid','editor2_valid','reviewer1_valid','reviewer2_valid','editor3_valid','author2_valid','production_valid','validator_valid'])
                                    ->filter(fn($f) => $s->$f)->count();
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input submission-checkbox" value="{{ $s->id }}" data-kode="{{ $s->kode_submit }}" data-title="{{ Str::limit($s->judul_artikel, 40) }}">
                                </td>
                                <td class="sticky-first"
                                    title="{{ $s->journalSlot ? ($s->journalSlot->journalMaster?->nama_jurnal ?? '-').' — Vol.'.$s->journalSlot->volume.' No.'.$s->journalSlot->nomor : '' }}">
                                    <a href="{{ route('admin.submissions.process', $s) }}" class="text-decoration-none">
                                        <code class="text-primary" style="font-size:0.7rem;">{{ $s->kode_submit }}</code>
                                    </a>
                                    @if($isCompleted)
                                        <span class="badge bg-success ms-1" style="font-size:0.55rem;"><i class="bi bi-check-circle-fill"></i> SELESAI</span>
                                    @endif
                                    @if($s->journalSlot)
                                        <div style="font-size:0.6rem; color:#6b7280; line-height:1.2; margin-top:2px;">
                                            {{ Str::limit($s->journalSlot->journalMaster?->nama_jurnal ?? '', 20) }}
                                        </div>
                                    @endif
                                    <div style="margin-top:3px; background:#e5e7eb; border-radius:3px; height:3px; width:80px;">
                                        <div style="background:{{ $validCount>=9 ? '#16a34a' : ($validCount>=5 ? '#2563eb' : '#f59e0b') }};width:{{ round($validCount/9*100) }}%;height:3px;border-radius:3px;"></div>
                                    </div>
                                    @php $scr = $s->screeningForm; @endphp
                                    <a href="{{ route('admin.screenings.show', $s) }}"
                                       class="badge mt-1 text-decoration-none {{ $scr ? ($scr->keputusan === 'diterima' ? 'bg-success' : ($scr->keputusan === 'revisi' ? 'bg-warning text-dark' : 'bg-danger')) : 'bg-light text-secondary border' }}"
                                       style="font-size:0.55rem;" title="Screening Awal">
                                        <i class="bi bi-clipboard2-check"></i>
                                        {{ $scr ? strtoupper($scr->keputusan) : 'SCREENING' }}
                                    </a>
                                </td>
                                <td class="sticky-second">{{ $s->id_artikel }}</td>
                                <td class="text-center">
                                    <span class="status-badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                    <div class="progress-counter mt-1">
                                        <span class="done">{{ $validCount }}</span><span>/9 ✓</span>
                                    </div>
                                </td>
                                <td class="text-center" style="font-size:0.65rem; color:#6b7280;">
                                    {{ $s->tanggal_submit ? \Carbon\Carbon::parse($s->tanggal_submit)->format('d/m/y') : '—' }}
                                </td>
                                <td title="{{ $s->judul_artikel }}">{{ Str::limit($s->judul_artikel, 25) }}</td>
                                <td class="text-center" style="background:#fffbf0;">
                                    @if($s->catatan_marketing)
                                        <span class="badge"
                                              style="background:#fd7e14;color:#fff;white-space:normal;line-height:1.3;max-width:90px;display:inline-block;cursor:help;"
                                              title="{{ $s->catatan_marketing }}"
                                              data-bs-toggle="tooltip"
                                              data-bs-placement="top">
                                            <i class="bi bi-megaphone-fill"></i>
                                            {{ Str::limit($s->catatan_marketing, 25) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($s->link_artikel)
                                        <a href="{{ $s->link_artikel }}" target="_blank"><i class="bi bi-link-45deg"></i></a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ Str::limit($s->nama_penulis, 15) }}</td>
                                <td style="min-width:110px;">
                                    @if($s->no_hp_penulis)
                                        @php
                                            $waNumber = preg_replace('/[^0-9]/', '', $s->no_hp_penulis);
                                            if (substr($waNumber, 0, 1) === '0') { $waNumber = '62' . substr($waNumber, 1); }
                                            $waMessage = "Selamat Artikel anda sudah terpublikasi:\n\nKode artikel: *{$s->id_artikel}*\nNama Penulis: *{$s->nama_penulis}*\nLink Publikasi: {$s->link_publish}\n\nJangan lupa di referensikan ke teman2 nya.\n\nSALAM APJI";
                                            $waUrl = "https://wa.me/{$waNumber}?text=" . urlencode($waMessage);
                                        @endphp
                                        <a href="{{ $waUrl }}" target="_blank" class="btn btn-success btn-sm" style="padding: 2px 6px; font-size: 0.7rem;" title="Chat WhatsApp {{ $s->no_hp_penulis }}">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    @endif
                                    <div class="mt-1 d-flex align-items-center gap-1">
                                        @if($s->email_penulis)
                                            <a href="mailto:{{ $s->email_penulis }}" class="btn btn-outline-primary btn-sm" style="padding:2px 5px;font-size:0.65rem;" title="{{ $s->email_penulis }}">
                                                <i class="bi bi-envelope-fill"></i>
                                            </a>
                                        @endif
                                        <input type="text" class="inline-credential-input {{ $s->email_penulis ? 'has-value' : '' }}"
                                               value="{{ $s->email_penulis }}"
                                               placeholder="email@..."
                                               style="width:90px;"
                                               data-submission="{{ $s->id }}"
                                               data-field="email_penulis"
                                               onchange="quickUpdateCredential(this)"
                                               title="Email penulis">
                                    </div>
                                </td>
                                <!-- Author Access: PIC Marketing, Petugas Submit, Username, Password -->
                                <td class="grp-dark">
                                                                        <select class="inline-assign-select lazy-select {{ $s->marketing_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-type="marketing"
                                            data-model="marketing"
                                            data-selected="{{ $s->marketing_id }}"
                                            onchange="quickAssignMarketing(this)">
                                        <option value="">-- Pilih --</option>
                                        @if($s->marketing_id)
                                            <option value="{{ $s->marketing_id }}" selected>{{ $marketings->firstWhere('id', $s->marketing_id)?->name }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td>
                                    @if($s->petugasSubmit)
                                        {{ $s->petugasSubmit->name }}
                                    @elseif($s->marketing)
                                        <span class="text-success" title="Disubmit oleh Marketing">{{ $s->marketing->name }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td><code class="{{ $s->username_author ? 'copyable' : '' }}" title="Klik untuk copy">{{ $s->username_author ?? '-' }}</code></td>
                                <td><code class="{{ $s->password_author ? 'copyable' : '' }}" title="Klik untuk copy">{{ $s->password_author ?? '-' }}</code></td>
                                
                                <!-- Editor 1 -->
                                <td class="grp-info">
                                                                        <select class="inline-assign-select lazy-select {{ $s->petugas_editor1_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-type="editor1"
                                            data-model="pic"
                                            data-selected="{{ $s->petugas_editor1_id }}"
                                            onchange="quickAssign(this)">
                                        <option value="">-- Pilih --</option>
                                        @if($s->petugas_editor1_id)
                                            <option value="{{ $s->petugas_editor1_id }}" selected>{{ $pics->firstWhere('id', $s->petugas_editor1_id)?->name }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td>
                                    <div class="credential-group">
                                        <input type="text" class="inline-credential-input {{ $s->username_editor ? 'has-value' : '' }}" 
                                               value="{{ $s->username_editor }}" 
                                               placeholder="user"
                                               data-submission="{{ $s->id }}" 
                                               data-field="username_editor"
                                               onchange="quickUpdateCredential(this)">
                                        <span>/</span>
                                        <input type="text" class="inline-credential-input {{ $s->password_editor ? 'has-value' : '' }}" 
                                               value="{{ $s->password_editor }}" 
                                               placeholder="pass"
                                               data-submission="{{ $s->id }}" 
                                               data-field="password_editor"
                                               onchange="quickUpdateCredential(this)">
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-link p-0 border-0"
                                            onclick="quickToggleValid(this)"
                                            data-submission="{{ $s->id }}"
                                            data-field="editor1_valid"
                                            data-valid="{{ $s->editor1_valid ? '1' : '0' }}"
                                            title="Toggle Editor1 Valid">
                                        {!! $s->editor1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>

                                <!-- Author 1 -->
                                <td class="grp-warning">
                                                                        <select class="inline-assign-select lazy-select {{ $s->petugas_author1_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-type="author1"
                                            data-model="pic"
                                            data-selected="{{ $s->petugas_author1_id }}"
                                            onchange="quickAssign(this)">
                                        <option value="">-- Pilih --</option>
                                        @if($s->petugas_author1_id)
                                            <option value="{{ $s->petugas_author1_id }}" selected>{{ $pics->firstWhere('id', $s->petugas_author1_id)?->name }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-link p-0 border-0"
                                            onclick="quickToggleValid(this)"
                                            data-submission="{{ $s->id }}"
                                            data-field="author1_valid"
                                            data-valid="{{ $s->author1_valid ? '1' : '0' }}"
                                            title="Toggle Author1 Valid">
                                        {!! $s->author1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>

                                <!-- Editor 2 -->
                                <td class="grp-info">
                                                                        <select class="inline-assign-select lazy-select {{ $s->petugas_editor2_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-type="editor2"
                                            data-model="pic"
                                            data-selected="{{ $s->petugas_editor2_id }}"
                                            onchange="quickAssign(this)">
                                        <option value="">-- Pilih --</option>
                                        @if($s->petugas_editor2_id)
                                            <option value="{{ $s->petugas_editor2_id }}" selected>{{ $pics->firstWhere('id', $s->petugas_editor2_id)?->name }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-link p-0 border-0"
                                            onclick="quickToggleValid(this)"
                                            data-submission="{{ $s->id }}"
                                            data-field="editor2_valid"
                                            data-valid="{{ $s->editor2_valid ? '1' : '0' }}"
                                            title="Toggle Editor2 Valid">
                                        {!! $s->editor2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>

                                <!-- Reviewer 1 -->
                                <td class="grp-primary">
                                                                        <select class="inline-assign-select lazy-select {{ $s->petugas_reviewer1_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-type="reviewer1"
                                            data-model="pic"
                                            data-selected="{{ $s->petugas_reviewer1_id }}"
                                            onchange="quickAssign(this)">
                                        <option value="">-- Pilih --</option>
                                        @if($s->petugas_reviewer1_id)
                                            <option value="{{ $s->petugas_reviewer1_id }}" selected>{{ $pics->firstWhere('id', $s->petugas_reviewer1_id)?->name }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td>
                                    <div class="credential-group">
                                        <input type="text" class="inline-credential-input {{ $s->username_reviewer1 ? 'has-value' : '' }}" 
                                               value="{{ $s->username_reviewer1 }}" 
                                               placeholder="user"
                                               data-submission="{{ $s->id }}" 
                                               data-field="username_reviewer1"
                                               onchange="quickUpdateCredential(this)">
                                        <span>/</span>
                                        <input type="text" class="inline-credential-input {{ $s->password_reviewer1 ? 'has-value' : '' }}" 
                                               value="{{ $s->password_reviewer1 }}" 
                                               placeholder="pass"
                                               data-submission="{{ $s->id }}" 
                                               data-field="password_reviewer1"
                                               onchange="quickUpdateCredential(this)">
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="inline-credential-input {{ $s->catatan_reviewer1 ? 'has-value' : '' }}"
                                           value="{{ $s->catatan_reviewer1 }}"
                                           placeholder="catatan..."
                                           style="min-width:80px;"
                                           data-submission="{{ $s->id }}"
                                           data-field="catatan_reviewer1"
                                           onchange="quickUpdateCredential(this)">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-link p-0 border-0"
                                            onclick="quickToggleValid(this)"
                                            data-submission="{{ $s->id }}"
                                            data-field="reviewer1_valid"
                                            data-valid="{{ $s->reviewer1_valid ? '1' : '0' }}"
                                            title="Toggle Reviewer1 Valid">
                                        {!! $s->reviewer1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>
                                
                                <!-- Reviewer 2 -->
                                <td class="grp-primary">
                                                                        <select class="inline-assign-select lazy-select {{ $s->petugas_reviewer2_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-type="reviewer2"
                                            data-model="pic"
                                            data-selected="{{ $s->petugas_reviewer2_id }}"
                                            onchange="quickAssign(this)">
                                        <option value="">-- Pilih --</option>
                                        @if($s->petugas_reviewer2_id)
                                            <option value="{{ $s->petugas_reviewer2_id }}" selected>{{ $pics->firstWhere('id', $s->petugas_reviewer2_id)?->name }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td>
                                    <div class="credential-group">
                                        <input type="text" class="inline-credential-input {{ $s->username_reviewer2 ? 'has-value' : '' }}" 
                                               value="{{ $s->username_reviewer2 }}" 
                                               placeholder="user"
                                               data-submission="{{ $s->id }}" 
                                               data-field="username_reviewer2"
                                               onchange="quickUpdateCredential(this)">
                                        <span>/</span>
                                        <input type="text" class="inline-credential-input {{ $s->password_reviewer2 ? 'has-value' : '' }}" 
                                               value="{{ $s->password_reviewer2 }}" 
                                               placeholder="pass"
                                               data-submission="{{ $s->id }}" 
                                               data-field="password_reviewer2"
                                               onchange="quickUpdateCredential(this)">
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="inline-credential-input {{ $s->catatan_reviewer2 ? 'has-value' : '' }}"
                                           value="{{ $s->catatan_reviewer2 }}"
                                           placeholder="catatan..."
                                           style="min-width:80px;"
                                           data-submission="{{ $s->id }}"
                                           data-field="catatan_reviewer2"
                                           onchange="quickUpdateCredential(this)">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-link p-0 border-0"
                                            onclick="quickToggleValid(this)"
                                            data-submission="{{ $s->id }}"
                                            data-field="reviewer2_valid"
                                            data-valid="{{ $s->reviewer2_valid ? '1' : '0' }}"
                                            title="Toggle Reviewer2 Valid">
                                        {!! $s->reviewer2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>
                                
                                <!-- Editor 3 -->
                                <td class="grp-info">
                                                                        <select class="inline-assign-select lazy-select {{ $s->petugas_editor3_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-type="editor3"
                                            data-model="pic"
                                            data-selected="{{ $s->petugas_editor3_id }}"
                                            onchange="quickAssign(this)">
                                        <option value="">-- Pilih --</option>
                                        @if($s->petugas_editor3_id)
                                            <option value="{{ $s->petugas_editor3_id }}" selected>{{ $pics->firstWhere('id', $s->petugas_editor3_id)?->name }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-link p-0 border-0"
                                            onclick="quickToggleValid(this)"
                                            data-submission="{{ $s->id }}"
                                            data-field="editor3_valid"
                                            data-valid="{{ $s->editor3_valid ? '1' : '0' }}"
                                            title="Toggle Editor3 Valid">
                                        {!! $s->editor3_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>

                                <!-- Author 2 -->
                                <td class="grp-warning">
                                                                        <select class="inline-assign-select lazy-select {{ $s->petugas_author2_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-type="author2"
                                            data-model="pic"
                                            data-selected="{{ $s->petugas_author2_id }}"
                                            onchange="quickAssign(this)">
                                        <option value="">-- Pilih --</option>
                                        @if($s->petugas_author2_id)
                                            <option value="{{ $s->petugas_author2_id }}" selected>{{ $pics->firstWhere('id', $s->petugas_author2_id)?->name }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-link p-0 border-0"
                                            onclick="quickToggleValid(this)"
                                            data-submission="{{ $s->id }}"
                                            data-field="author2_valid"
                                            data-valid="{{ $s->author2_valid ? '1' : '0' }}"
                                            title="Toggle Author2 Valid">
                                        {!! $s->author2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>

                                <!-- Production -->
                                <td class="grp-success">
                                    <select class="inline-assign-select lazy-select {{ $s->petugas_production_id ? 'has-value' : '' }}"
                                            data-submission="{{ $s->id }}"
                                            data-type="production"
                                            data-model="pic"
                                            data-selected="{{ $s->petugas_production_id }}"
                                            onchange="quickAssign(this)">
                                        <option value="">-- Pilih --</option>
                                        @if($s->petugas_production_id)
                                            <option value="{{ $s->petugas_production_id }}" selected>{{ $pics->firstWhere('id', $s->petugas_production_id)?->name }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td title="Username Editor">
                                    <input type="text" class="inline-credential-input {{ $s->username_editor ? 'has-value' : '' }}"
                                           value="{{ $s->username_editor }}"
                                           placeholder="user"
                                           data-submission="{{ $s->id }}"
                                           data-field="username_editor"
                                           onchange="quickUpdateCredential(this)">
                                </td>
                                <td title="Password Editor">
                                    <input type="text" class="inline-credential-input {{ $s->password_editor ? 'has-value' : '' }}"
                                           value="{{ $s->password_editor }}"
                                           placeholder="pass"
                                           data-submission="{{ $s->id }}"
                                           data-field="password_editor"
                                           onchange="quickUpdateCredential(this)">
                                </td>
                                <td title="Link Publish">
                                    <div style="display:flex;align-items:center;gap:2px;">
                                        <input type="text" class="inline-credential-input {{ $s->link_publish ? 'has-value' : '' }}"
                                               value="{{ $s->link_publish }}"
                                               placeholder="https://..."
                                               style="min-width:90px;"
                                               data-submission="{{ $s->id }}"
                                               data-field="link_publish"
                                               onchange="quickUpdateCredential(this)">
                                        <button type="button" class="btn btn-link p-0 border-0" style="font-size:0.8rem;line-height:1;"
                                                onclick="var u=this.previousElementSibling.value.trim();if(u)window.open(u,'_blank');"
                                                title="Buka Link">
                                            <i class="bi bi-box-arrow-up-right text-info"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-link p-0 border-0"
                                            onclick="quickToggleValid(this)"
                                            data-submission="{{ $s->id }}"
                                            data-field="production_valid"
                                            data-valid="{{ $s->production_valid ? '1' : '0' }}"
                                            title="Toggle Production Valid">
                                        {!! $s->production_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>

                                <!-- Validator -->
                                <td class="td-validator grp-validator">
                                                                        <select class="inline-assign-select lazy-select {{ $s->petugas_validator_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-type="validator"
                                            data-model="pic"
                                            data-selected="{{ $s->petugas_validator_id }}"
                                            onchange="quickAssign(this)">
                                        <option value="">-- Pilih --</option>
                                        @if($s->petugas_validator_id)
                                            <option value="{{ $s->petugas_validator_id }}" selected>{{ $pics->firstWhere('id', $s->petugas_validator_id)?->name }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td class="td-validator">
                                    <input type="text" class="inline-credential-input {{ $s->catatan_validator ? 'has-value' : '' }}"
                                           value="{{ $s->catatan_validator }}"
                                           placeholder="catatan..."
                                           style="min-width:80px;"
                                           data-submission="{{ $s->id }}"
                                           data-field="catatan_validator"
                                           onchange="quickUpdateCredential(this)">
                                </td>
                                <td class="text-center td-validator">
                                    <button type="button" class="btn btn-link p-0 border-0"
                                            onclick="quickToggleValid(this)"
                                            data-submission="{{ $s->id }}"
                                            data-field="validator_valid"
                                            data-valid="{{ $s->validator_valid ? '1' : '0' }}"
                                            title="Toggle Validator Valid">
                                        {!! $s->validator_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="33" class="text-center text-muted py-4">
                                    Tidak ada data
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @include('partials.per-page-selector', ['paginator' => $submissions, 'default' => 50])
            </div>
        </div>
    </div>
</div>

<script>
// Quick Assign Marketing function
function quickAssignMarketing(selectEl) {
    const submissionId = selectEl.dataset.submission;
    const marketingId = selectEl.value;
    
    selectEl.classList.add('saving');
    
    fetch('{{ route("admin.submissions.quick-assign-marketing") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            submission_id: submissionId,
            marketing_id: marketingId || null
        })
    })
    .then(response => response.json())
    .then(data => {
        selectEl.classList.remove('saving');
        if (data.success) {
            if (marketingId) {
                selectEl.classList.add('has-value');
            } else {
                selectEl.classList.remove('has-value');
            }
            selectEl.style.boxShadow = '0 0 0 2px rgba(25, 135, 84, 0.5)';
            setTimeout(() => {
                selectEl.style.boxShadow = '';
            }, 1000);
        } else {
            alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
            location.reload();
        }
    })
    .catch(error => {
        selectEl.classList.remove('saving');
        console.error('Error:', error);
        alert('Terjadi kesalahan jaringan');
        location.reload();
    });
}

// Quick Assign function for inline dropdown
function quickAssign(selectEl) {
    const submissionId = selectEl.dataset.submission;
    const assignmentType = selectEl.dataset.type;
    const petugasId = selectEl.value;
    
    if (!petugasId) {
        // If cleared, still update to remove assignment
    }
    
    selectEl.classList.add('saving');
    
    fetch('{{ route("admin.submissions.quick-assign") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            submission_id: submissionId,
            assignment_type: assignmentType,
            petugas_id: petugasId || null
        })
    })
    .then(response => response.json())
    .then(data => {
        selectEl.classList.remove('saving');
        if (data.success) {
            if (petugasId) {
                selectEl.classList.add('has-value');
            } else {
                selectEl.classList.remove('has-value');
            }
            // Show brief success indicator
            selectEl.style.boxShadow = '0 0 0 2px rgba(25, 135, 84, 0.5)';
            setTimeout(() => {
                selectEl.style.boxShadow = '';
            }, 1000);
        } else {
            alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
            // Revert selection
            location.reload();
        }
    })
    .catch(error => {
        selectEl.classList.remove('saving');
        console.error('Error:', error);
        alert('Terjadi kesalahan jaringan');
        location.reload();
    });
}

// Quick Update Credential function for inline input
function quickUpdateCredential(inputEl) {
    const submissionId = inputEl.dataset.submission;
    const field = inputEl.dataset.field;
    const value = inputEl.value.trim();
    
    inputEl.classList.add('saving');
    
    fetch('{{ route("admin.submissions.quick-update-credential") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            submission_id: submissionId,
            field: field,
            value: value
        })
    })
    .then(response => response.json())
    .then(data => {
        inputEl.classList.remove('saving');
        if (data.success) {
            if (value) {
                inputEl.classList.add('has-value');
            } else {
                inputEl.classList.remove('has-value');
            }
            // Show brief success indicator
            inputEl.style.boxShadow = '0 0 0 2px rgba(25, 135, 84, 0.5)';
            setTimeout(() => {
                inputEl.style.boxShadow = '';
            }, 1000);
        } else {
            alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(error => {
        inputEl.classList.remove('saving');
        console.error('Error:', error);
        alert('Terjadi kesalahan jaringan');
    });
}

function quickToggleValid(btn) {
    const submissionId = btn.dataset.submission;
    const field = btn.dataset.field;
    const currentValid = btn.dataset.valid === '1';

    btn.disabled = true;

    fetch('{{ route("admin.submissions.toggle-valid-field") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ submission_id: submissionId, field: field })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            const newValid = !currentValid;
            btn.dataset.valid = newValid ? '1' : '0';
            btn.innerHTML = newValid
                ? '<i class="bi bi-check-circle-fill text-success"></i>'
                : '<i class="bi bi-circle text-muted"></i>';
        } else {
            alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(() => {
        btn.disabled = false;
        alert('Terjadi kesalahan jaringan');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.getElementById('monitoringScrollWrapper');
    const positionFill = document.getElementById('scrollPositionFill');
    const positionText = document.getElementById('scrollPositionText');
    const scrollLeftBtn = document.getElementById('scrollLeftBtn');
    const scrollRightBtn = document.getElementById('scrollRightBtn');
    const scrollStartBtn = document.getElementById('scrollStartBtn');
    const scrollEndBtn = document.getElementById('scrollEndBtn');
    
    // Peta data-target → id <th> group header
    const colIdMap = {
        'submit':     'colSubmit',
        'editor1':    'colEditor1',
        'author1':    'colAuthor1',
        'editor2':    'colEditor2',
        'reviewer1':  'colReviewer1',
        'reviewer2':  'colReviewer2',
        'editor3':    'colEditor3',
        'author2':    'colAuthor2',
        'production': 'colProduction',
        'validator':  'colValidator'
    };

    // Hitung lebar sticky area dari CSS (konstan, tidak tergantung scroll)
    function getStickyWidth() {
        var s2 = wrapper.querySelector('th.sticky-second');
        if (!s2) return 0;
        return parseFloat(getComputedStyle(s2).left) + s2.offsetWidth;
    }

    function scrollToGroup(target) {
        var th = document.getElementById(colIdMap[target]);
        if (!th) { wrapper.scrollTo({ left: 0, behavior: 'smooth' }); return; }
        var stickyWidth = getStickyWidth();
        var wRect = wrapper.getBoundingClientRect();
        var thRect = th.getBoundingClientRect();
        // wrapper.scrollLeft + (thRect.left - wRect.left) = posisi natural th dalam tabel
        var targetScroll = wrapper.scrollLeft + (thRect.left - wRect.left) - stickyWidth;
        wrapper.scrollTo({ left: Math.max(0, targetScroll), behavior: 'smooth' });
    }

    // Update scroll position indicator
    function updateScrollPosition() {
        const scrollLeft = wrapper.scrollLeft;
        const scrollWidth = wrapper.scrollWidth - wrapper.clientWidth;
        const progress = scrollWidth > 0 ? (scrollLeft / scrollWidth) * 100 : 0;
        positionFill.style.width = progress + '%';
        positionText.textContent = Math.round(progress) + '%';
        
        // Update button states
        scrollStartBtn.disabled = scrollLeft <= 0;
        scrollLeftBtn.disabled = scrollLeft <= 0;
        scrollRightBtn.disabled = scrollLeft >= scrollWidth;
        scrollEndBtn.disabled = scrollLeft >= scrollWidth;
        
        // Update quick nav active state
        document.querySelectorAll('.quick-nav-btn').forEach(btn => {
            btn.classList.remove('active');
        });
    }
    
    wrapper.addEventListener('scroll', updateScrollPosition);
    
    // Scroll amount
    const scrollAmount = 400;
    
    scrollLeftBtn.addEventListener('click', () => {
        wrapper.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    });
    
    scrollRightBtn.addEventListener('click', () => {
        wrapper.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    });
    
    scrollStartBtn.addEventListener('click', () => {
        wrapper.scrollTo({ left: 0, behavior: 'smooth' });
    });
    
    scrollEndBtn.addEventListener('click', () => {
        wrapper.scrollTo({ left: wrapper.scrollWidth, behavior: 'smooth' });
    });
    
    // Quick navigation — posisi dihitung dinamis dari DOM
    document.querySelectorAll('.quick-nav-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            scrollToGroup(this.dataset.target);
            document.querySelectorAll('.quick-nav-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Keyboard navigation
    wrapper.setAttribute('tabindex', '0');
    wrapper.addEventListener('keydown', function(e) {
        switch(e.key) {
            case 'ArrowLeft':
                wrapper.scrollBy({ left: -100, behavior: 'smooth' });
                break;
            case 'ArrowRight':
                wrapper.scrollBy({ left: 100, behavior: 'smooth' });
                break;
            case 'Home':
                wrapper.scrollTo({ left: 0, behavior: 'smooth' });
                break;
            case 'End':
                wrapper.scrollTo({ left: wrapper.scrollWidth, behavior: 'smooth' });
                break;
        }
    });

    // Initial state (drag-to-scroll handled globally by partials/drag-to-scroll.blade.php)
    updateScrollPosition();
    
    // ========== BULK ASSIGNMENT FUNCTIONALITY ==========
    const selectAll = document.getElementById('selectAll');
    const selectAllTable = document.getElementById('selectAllTable');
    const checkboxes = document.querySelectorAll('.submission-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const bulkEditorBtn = document.getElementById('bulkEditorBtn');
    const bulkAuthorBtn = document.getElementById('bulkAuthorBtn');
    const bulkReviewerBtn = document.getElementById('bulkReviewerBtn');
    const bulkProductionBtn = document.getElementById('bulkProductionBtn');
    const bulkValidatorBtn = document.getElementById('bulkValidatorBtn');
    
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.submission-checkbox:checked');
        const count = checked.length;
        selectedCount.textContent = count + ' dipilih';
        
        // Enable/disable bulk buttons
        bulkEditorBtn.disabled = count === 0;
        bulkAuthorBtn.disabled = count === 0;
        bulkReviewerBtn.disabled = count === 0;
        bulkProductionBtn.disabled = count === 0;
        if (bulkValidatorBtn) bulkValidatorBtn.disabled = count === 0;
        
        // Update select all checkboxes
        selectAll.checked = count === checkboxes.length && count > 0;
        selectAllTable.checked = count === checkboxes.length && count > 0;
        
        // Update hidden inputs in modals
        updateModalSubmissionIds();
    }
    
    function updateModalSubmissionIds() {
        const checked = document.querySelectorAll('.submission-checkbox:checked');
        const ids = Array.from(checked).map(cb => cb.value);
        
        document.querySelectorAll('.bulk-submission-ids').forEach(input => {
            input.value = JSON.stringify(ids);
        });
        
        // Update selected list preview
        const kodes = Array.from(checked).map(cb => cb.dataset.kode);
        document.querySelectorAll('.selected-submissions-preview').forEach(el => {
            if (kodes.length > 0) {
                el.innerHTML = kodes.map(k => '<span class="badge bg-secondary me-1">' + k + '</span>').join('');
            } else {
                el.innerHTML = '<span class="text-muted">Tidak ada yang dipilih</span>';
            }
        });
        
        // Update credentials table for Editor modal
        const editorCredentialsList = document.getElementById('editorCredentialsList');
        if (editorCredentialsList) {
            let html = '';
            Array.from(checked).forEach(cb => {
                const id = cb.value;
                const kode = cb.dataset.kode;
                const title = cb.dataset.title || '-';
                html += `<tr>
                    <td>
                        <span class="badge bg-secondary">${kode}</span>
                        <div class="small text-muted mt-1" style="max-width: 200px; white-space: normal;">${title}</div>
                    </td>
                    <td><input type="text" name="credentials[${id}][username]" class="form-control form-control-sm" placeholder="Username"></td>
                    <td><input type="text" name="credentials[${id}][password]" class="form-control form-control-sm" placeholder="Password"></td>
                </tr>`;
            });
            editorCredentialsList.innerHTML = html || '<tr><td colspan="3" class="text-center text-muted">Pilih submission terlebih dahulu</td></tr>';
        }
        
        // Update credentials table for Reviewer modal
        const reviewerCredentialsList = document.getElementById('reviewerCredentialsList');
        if (reviewerCredentialsList) {
            let html = '';
            Array.from(checked).forEach(cb => {
                const id = cb.value;
                const kode = cb.dataset.kode;
                const title = cb.dataset.title || '-';
                html += `<tr>
                    <td>
                        <span class="badge bg-secondary">${kode}</span>
                        <div class="small text-muted mt-1" style="max-width: 200px; white-space: normal;">${title}</div>
                    </td>
                    <td><input type="text" name="credentials[${id}][username]" class="form-control form-control-sm" placeholder="Username Reviewer"></td>
                    <td><input type="text" name="credentials[${id}][password]" class="form-control form-control-sm" placeholder="Password Reviewer"></td>
                </tr>`;
            });
            reviewerCredentialsList.innerHTML = html || '<tr><td colspan="3" class="text-center text-muted">Pilih submission terlebih dahulu</td></tr>';
        }
        
        // Update production selected count in modal
        const productionSelectedCount = document.getElementById('productionSelectedCount');
        if (productionSelectedCount) {
            productionSelectedCount.textContent = ids.length;
        }
        
        // Update validator selected count in modal
        const validatorSelectedCount = document.getElementById('validatorSelectedCount');
        if (validatorSelectedCount) {
            validatorSelectedCount.textContent = ids.length;
        }
    }
    
    // Select All (top card)
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        selectAllTable.checked = this.checked;
        updateSelectedCount();
    });
    
    // Select All (table header)
    selectAllTable.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        selectAll.checked = this.checked;
        updateSelectedCount();
    });
    
    // Individual checkboxes
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
});
</script>

<!-- Bulk Editor Assignment Modal -->
<div class="modal fade" id="bulkEditorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-people"></i> Penugasan Massal Editor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.submissions.bulk-assign-with-credentials') }}" method="POST">
                @csrf
                <input type="hidden" name="submission_ids" class="bulk-submission-ids">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipe Penugasan <span class="text-danger">*</span></label>
                            <select class="form-select" name="assignment_type" id="editorAssignmentType" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="editor1">Editor 1</option>
                                <option value="editor2">Editor 2</option>
                                <option value="editor3">Editor 3</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pilih Petugas <span class="text-danger">*</span></label>
                            <select class="form-select" name="petugas_id" required>
                                <option value="">-- Pilih Petugas --</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}">{{ $pic->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div id="editorCredentialsContainer">
                        <label class="form-label">Username & Password per Submission:</label>
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Kode Submit</th>
                                        <th>Username Editor</th>
                                        <th>Password Editor</th>
                                    </tr>
                                </thead>
                                <tbody id="editorCredentialsList">
                                    <!-- Will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">
                        <i class="bi bi-check-circle"></i> Tugaskan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Author Assignment Modal -->
<div class="modal fade" id="bulkAuthorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-person-check"></i> Penugasan Massal Author</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.submissions.bulk-assign') }}" method="POST">
                @csrf
                <input type="hidden" name="submission_ids" class="bulk-submission-ids">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Submissions yang dipilih:</label>
                        <div class="selected-submissions-preview border rounded p-2" style="max-height: 100px; overflow-y: auto;">
                            <span class="text-muted">Tidak ada yang dipilih</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipe Penugasan <span class="text-danger">*</span></label>
                        <select class="form-select" name="assignment_type" required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="author1">Author 1</option>
                            <option value="author2">Author 2</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih Petugas <span class="text-danger">*</span></label>
                        <select class="form-select" name="petugas_id" required>
                            <option value="">-- Pilih Petugas --</option>
                            @foreach($pics as $pic)
                                <option value="{{ $pic->id }}">{{ $pic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle"></i> Tugaskan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Reviewer Assignment Modal -->
<div class="modal fade" id="bulkReviewerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-journal-check"></i> Penugasan Massal Reviewer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.submissions.bulk-assign-with-credentials') }}" method="POST">
                @csrf
                <input type="hidden" name="submission_ids" class="bulk-submission-ids">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipe Penugasan <span class="text-danger">*</span></label>
                            <select class="form-select" name="assignment_type" id="reviewerAssignmentType" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="reviewer1">Reviewer 1</option>
                                <option value="reviewer2">Reviewer 2</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pilih Petugas <span class="text-danger">*</span></label>
                            <select class="form-select" name="petugas_id" required>
                                <option value="">-- Pilih Petugas --</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}">{{ $pic->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div id="reviewerCredentialsContainer">
                        <label class="form-label">Username & Password per Submission:</label>
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Kode Submit</th>
                                        <th>Username Reviewer</th>
                                        <th>Password Reviewer</th>
                                    </tr>
                                </thead>
                                <tbody id="reviewerCredentialsList">
                                    <!-- Will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Tugaskan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Production Assignment Modal -->
<div class="modal fade" id="bulkProductionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-gear"></i> Penugasan Massal Production</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.submissions.bulk-assign') }}" method="POST">
                @csrf
                <input type="hidden" name="submission_ids" class="bulk-submission-ids">
                <input type="hidden" name="assignment_type" value="production">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Anda akan menugaskan petugas Production untuk <strong id="productionSelectedCount">0</strong> submission yang dipilih.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih Petugas Production <span class="text-danger">*</span></label>
                        <select class="form-select" name="petugas_id" required>
                            <option value="">-- Pilih Petugas --</option>
                            @foreach($pics as $pic)
                                <option value="{{ $pic->id }}">{{ $pic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Tugaskan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Validator Assignment Modal -->
<div class="modal fade" id="bulkValidatorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background:#6f42c1;">
                <h5 class="modal-title"><i class="bi bi-shield-check"></i> Penugasan Massal Validator</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.submissions.bulk-assign') }}" method="POST">
                @csrf
                <input type="hidden" name="submission_ids" class="bulk-submission-ids">
                <input type="hidden" name="assignment_type" value="validator">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Anda akan menugaskan petugas Validator untuk <strong id="validatorSelectedCount">0</strong> submission yang dipilih.
                    </div>
                    <div class="alert" style="background:#f3e8ff; border-color:#6f42c1;">
                        <i class="bi bi-shield-check" style="color:#6f42c1;"></i>
                        <strong>Tugas Validator:</strong> Mengecek kesesuaian artikel yang sudah dipublish oleh tim Production. Jika sesuai → validasi OK. Jika ada revisi → kembalikan ke Production.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih Petugas Validator <span class="text-danger">*</span></label>
                        <select class="form-select" name="petugas_id" required>
                            <option value="">-- Pilih Petugas --</option>
                            @foreach($pics as $pic)
                                <option value="{{ $pic->id }}">{{ $pic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white" style="background:#6f42c1;">
                        <i class="bi bi-check-circle"></i> Tugaskan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-upload"></i> Import Data Submission</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.submissions.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Format file:</strong> Excel (.xlsx, .xls) atau CSV
                        <br>
                        <a href="{{ route('admin.submissions.template') }}" class="text-decoration-none">
                            <i class="bi bi-download"></i> Download Template
                        </a>
                    </div>
                    
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Pilih File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="importFile" name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Maksimal 50MB</div>
                    </div>
                    
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Perhatian:</strong>
                        <ul class="mb-0 ps-3">
                            <li>Data dengan Kode Submit yang sama akan diupdate</li>
                            <li>Pastikan format tanggal: YYYY-MM-DD</li>
                            <li>Kolom wajib: kode_submit, judul_artikel, nama_penulis</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">
                        <i class="bi bi-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const listPics = @json($pics);
    const listMarketings = @json($marketings);

    // Event delegation for mouseenter to lazy-load options
    document.addEventListener('mouseover', function(e) {
        if(e.target && e.target.classList.contains('lazy-select')) {
            let select = e.target;
            if(!select.dataset.loaded) {
                let model = select.dataset.model;
                let selectedVal = select.dataset.selected;
                let optionsData = model === 'marketing' ? listMarketings : listPics;
                
                // Add all options if not already present
                optionsData.forEach(item => {
                    if (item.id != selectedVal) { // skip already rendered selected
                        let opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.name;
                        select.appendChild(opt);
                    }
                });
                select.dataset.loaded = 'true';
            }
        }
    });
});

</script>

<script>
/* Force sub-header row colors — light tint + dark text, always readable */
document.addEventListener('DOMContentLoaded', function () {
    var colorMap = {
        'bg-dark':      { bg: '#e2e8f0', color: '#1e293b' },
        'bg-info':      { bg: '#bae6fd', color: '#0369a1' },
        'bg-warning':   { bg: '#fde68a', color: '#92400e' },
        'bg-primary':   { bg: '#c7d2fe', color: '#3730a3' },
        'bg-success':   { bg: '#bbf7d0', color: '#15803d' },
        'bg-validator': { bg: '#e9d5ff', color: '#7c3aed' }
    };
    document.querySelectorAll('.table-monitoring thead tr:nth-child(2) th').forEach(function (th) {
        var matched = false;
        for (var cls in colorMap) {
            if (th.classList.contains(cls)) {
                th.style.setProperty('background', colorMap[cls].bg, 'important');
                th.style.setProperty('color', colorMap[cls].color, 'important');
                th.style.setProperty('font-weight', '700', 'important');
                matched = true;
                break;
            }
        }
        if (!matched) {
            th.style.setProperty('background', '#f1f5f9', 'important');
            th.style.setProperty('color', '#334155', 'important');
            th.style.setProperty('font-weight', '700', 'important');
        }
    });
});
</script>

<script>
// Copy-on-click untuk code.copyable
document.addEventListener('click', function (e) {
    var el = e.target.closest('code.copyable');
    if (!el) return;
    var text = el.textContent.trim();
    if (!text || text === '-') return;
    navigator.clipboard.writeText(text).then(function () {
        el.classList.add('copied');
        var orig = el.title;
        el.title = 'Tersalin!';
        setTimeout(function () {
            el.classList.remove('copied');
            el.title = orig;
        }, 1200);
    });
});

// Drag-to-scroll monitoring: hanya dari area thead (header sticky)
// tbody tetap bebas untuk seleksi teks
(function () {
    var wrapper = document.querySelector('.monitoring-scroll-wrapper');
    if (!wrapper) return;
    var thead = wrapper.querySelector('thead');
    if (!thead) return;

    thead.style.cursor = 'grab';

    var dragging = false, startX = 0, startLeft = 0;

    thead.addEventListener('mousedown', function (e) {
        if (e.target.closest('select,button,a,input')) return;
        dragging  = true;
        startX    = e.pageX;
        startLeft = wrapper.scrollLeft;
        thead.style.cursor = 'grabbing';
        thead.style.userSelect = 'none';
        e.preventDefault();
    });

    document.addEventListener('mousemove', function (e) {
        if (!dragging) return;
        wrapper.scrollLeft = startLeft - (e.pageX - startX) * 1.5;
    });

    document.addEventListener('mouseup', function () {
        if (!dragging) return;
        dragging = false;
        thead.style.cursor = 'grab';
        thead.style.userSelect = '';
    });

    // Touch
    var tStartX = 0, tStartLeft = 0;
    wrapper.addEventListener('touchstart', function (e) {
        tStartX    = e.touches[0].pageX;
        tStartLeft = wrapper.scrollLeft;
    }, { passive: true });
    wrapper.addEventListener('touchmove', function (e) {
        wrapper.scrollLeft = tStartLeft - (e.touches[0].pageX - tStartX);
    }, { passive: true });
})();
</script>

@endsection
