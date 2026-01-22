@extends('pic.layouts.app')

@section('title', 'Monitoring & Tugas Saya')
@section('page-title', 'Monitoring & Tugas Saya')

@section('sidebar-class', 'auto-collapse')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('styles')
<style>
/* ===== SCROLL WRAPPER ===== */
.monitoring-scroll-wrapper {
    overflow-x: auto;
    overflow-y: auto;
    max-height: 65vh;
    position: relative;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0 0 8px 8px;
}

/* Force horizontal scroll */
.monitoring-scroll-wrapper table {
    min-width: 2200px;
}

/* Custom Scrollbar - Mirip Admin */
.monitoring-scroll-wrapper::-webkit-scrollbar {
    height: 12px;
    width: 10px;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-track {
    background: #f1f3f4;
    border-radius: 6px;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb {
    background: #adb5bd;
    border-radius: 6px;
    border: 2px solid #f1f3f4;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: #6c757d;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-corner {
    background: #f1f3f4;
}

/* Firefox scrollbar */
.monitoring-scroll-wrapper {
    scrollbar-width: thin;
    scrollbar-color: #adb5bd #f1f3f4;
}

/* ===== SCROLL CONTROLS BAR ===== */
.scroll-control-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    gap: 15px;
}

.scroll-nav-group {
    display: flex;
    align-items: center;
    gap: 4px;
}

.scroll-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #ced4da;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.15s;
    color: #495057;
}

.scroll-btn:hover:not(:disabled) {
    background: #e9ecef;
    border-color: #adb5bd;
}

.scroll-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* Progress Bar */
.scroll-progress {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
}

.scroll-progress-bar {
    flex: 1;
    height: 6px;
    background: #dee2e6;
    border-radius: 3px;
    overflow: hidden;
}

.scroll-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 3px;
    transition: width 0.1s ease;
    width: 0%;
}

.scroll-progress-text {
    font-size: 0.75rem;
    color: #6c757d;
    min-width: 30px;
    text-align: right;
}

/* Quick Jump Buttons */
.quick-jump {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.quick-jump-label {
    font-size: 0.75rem;
    color: #6c757d;
    white-space: nowrap;
}

.jump-btn {
    padding: 4px 10px;
    font-size: 0.72rem;
    font-weight: 500;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.15s;
    color: #495057;
}

.jump-btn:hover {
    background: #667eea;
    border-color: #667eea;
    color: white;
}

.jump-btn.active {
    background: #667eea;
    border-color: #667eea;
    color: white;
}

/* ===== TABLE STYLING ===== */
.table-monitoring {
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.75rem;
    margin-bottom: 0;
}

.table-monitoring thead th {
    position: sticky;
    top: 0;
    z-index: 3;
    background: #343a40 !important;
    color: white !important;
    border: 1px solid #454d55;
    white-space: nowrap;
    padding: 6px 8px;
    font-size: 0.72rem;
    font-weight: 600;
}

.table-monitoring thead tr:nth-child(2) th {
    top: 32px;
}

/* Color coded headers */
.table-monitoring thead th.bg-info { background: #17a2b8 !important; }
.table-monitoring thead th.bg-warning { background: #ffc107 !important; color: #212529 !important; }
.table-monitoring thead th.bg-primary { background: #667eea !important; }
.table-monitoring thead th.bg-success { background: #28a745 !important; }
.table-monitoring thead th.bg-dark { background: #343a40 !important; }

.table-monitoring tbody td {
    white-space: nowrap;
    padding: 5px 8px;
    border: 1px solid #e9ecef;
    vertical-align: middle;
    font-size: 0.72rem;
}

.table-monitoring tbody tr:hover td {
    background-color: #e7f3ff !important;
}

.table-monitoring tbody tr:nth-child(even) td {
    background-color: #f8f9fa;
}

/* Sticky columns */
.table-monitoring th.sticky-first,
.table-monitoring td.sticky-first {
    position: sticky;
    left: 0;
    z-index: 2;
    background: #fff;
    min-width: 100px;
    box-shadow: 2px 0 4px rgba(0,0,0,0.08);
}

.table-monitoring thead th.sticky-first {
    z-index: 5;
    background: #343a40 !important;
}

.table-monitoring th.sticky-second,
.table-monitoring td.sticky-second {
    position: sticky;
    left: 100px;
    z-index: 2;
    background: #fff;
    min-width: 60px;
    box-shadow: 2px 0 4px rgba(0,0,0,0.08);
}

.table-monitoring thead th.sticky-second {
    z-index: 5;
    background: #343a40 !important;
}

.table-monitoring tbody tr:hover td.sticky-first,
.table-monitoring tbody tr:hover td.sticky-second {
    background-color: #e7f3ff !important;
}

.table-monitoring tbody tr:nth-child(even) td.sticky-first,
.table-monitoring tbody tr:nth-child(even) td.sticky-second {
    background-color: #f8f9fa;
}

/* My task highlight */
.my-task-row td {
    background-color: #fff3cd !important;
}

.my-task-row:hover td {
    background-color: #ffe69c !important;
}

.my-task-row .sticky-first,
.my-task-row .sticky-second {
    background-color: #fff3cd !important;
}

/* ===== CREDENTIALS & INPUTS ===== */
.inline-credential-input {
    font-size: 0.65rem;
    padding: 2px 4px;
    width: 60px;
    border: 1px solid #dee2e6;
    border-radius: 3px;
    background: #fff;
    font-family: 'Consolas', monospace;
}

.inline-credential-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
    outline: none;
}

/* Badge styling */
.table-monitoring .badge {
    font-size: 0.65rem;
    padding: 2px 6px;
}

/* Button styling */
.table-monitoring .btn-sm {
    padding: 3px 6px;
    font-size: 0.7rem;
}

.table-monitoring .btn-sm i {
    font-size: 0.8rem;
}

.table-monitoring td code {
    background-color: #fff3cd;
    padding: 1px 3px;
    border-radius: 3px;
    font-size: 0.65rem;
}

/* Column widths */
.table-monitoring td:nth-child(3) { max-width: 150px; overflow: hidden; text-overflow: ellipsis; }
.table-monitoring td:nth-child(5) { max-width: 100px; overflow: hidden; text-overflow: ellipsis; }
.table-monitoring td:nth-child(6) { max-width: 90px; }

.credential-input-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.credential-input-row {
    display: flex;
    align-items: center;
    gap: 4px;
}

.btn-validation {
    min-width: 32px;
    padding: 4px 8px;
    transition: all 0.15s;
}

.btn-validation:hover {
    transform: scale(1.05);
}
</style>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-0" style="font-size: 0.75rem;">Total Submit</h6>
                        <h3 class="card-title mb-0" style="font-size: 1.5rem;">{{ $stats['total'] ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-file-earmark-text fs-3 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-0" style="font-size: 0.75rem;">Baru</h6>
                        <h3 class="card-title mb-0" style="font-size: 1.5rem;">{{ $stats['new'] ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-clock fs-3 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-0" style="font-size: 0.75rem;">Dalam Proses</h6>
                        <h3 class="card-title mb-0" style="font-size: 1.5rem;">{{ $stats['in_progress'] ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-gear fs-3 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-0" style="font-size: 0.75rem;">Published</h6>
                        <h3 class="card-title mb-0" style="font-size: 1.5rem;">{{ $stats['published'] ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-check-circle fs-3 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@if(request('my_tasks') && isset($stats['urgent']) && $stats['urgent'] > 0)
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
        <div>
            <h5 class="alert-heading mb-1">
                <strong>{{ $stats['urgent'] }}</strong> Tugas Mendesak Memerlukan Perhatian Anda!
            </h5>
            <p class="mb-0 small">Tugas-tugas ini membutuhkan tindakan Anda segera untuk melanjutkan proses review.</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Filter Card -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <span style="font-size: 0.9rem;"><i class="bi bi-bar-chart"></i> Monitoring & Tugas Saya</span>
        <a href="{{ route('pic.submissions.index') }}" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 4px 8px;">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body py-2">
        <form method="GET" class="mb-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-0" style="font-size: 0.75rem;">&nbsp;</label>
                    <div class="form-check" style="padding-top: 6px;">
                        <input class="form-check-input" type="checkbox" name="my_tasks" id="myTasksFilter" value="1" {{ request('my_tasks') ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label" for="myTasksFilter" style="font-size: 0.8rem; font-weight: 600; color: #dc3545;">
                            <i class="bi bi-person-check-fill"></i> Hanya Tugas Saya
                        </label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="tanggal_dari" class="form-label small mb-0" style="font-size: 0.75rem;">Tanggal Dari</label>
                    <input type="date" class="form-control form-control-sm" id="tanggal_dari" name="tanggal_dari" value="{{ request('tanggal_dari') }}" style="font-size: 0.75rem; padding: 4px 8px;">
                </div>
                <div class="col-md-2">
                    <label for="tanggal_sampai" class="form-label small mb-0" style="font-size: 0.75rem;">Tanggal Sampai</label>
                    <input type="date" class="form-control form-control-sm" id="tanggal_sampai" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" style="font-size: 0.75rem; padding: 4px 8px;">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0" style="font-size: 0.75rem;">Jurnal</label>
                    <select name="journal_id" class="form-select form-select-sm" style="font-size: 0.75rem; padding: 4px 8px;">
                        <option value="">-- Semua --</option>
                        @foreach($journals as $journal)
                            <option value="{{ $journal->id }}" {{ request('journal_id') == $journal->id ? 'selected' : '' }}>
                                {{ Str::limit($journal->nama_jurnal, 20) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0" style="font-size: 0.75rem;">Status</label>
                    <select name="status" class="form-select form-select-sm" style="font-size: 0.75rem; padding: 4px 8px;">
                        <option value="">-- Semua --</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="editor1_process" {{ request('status') == 'editor1_process' ? 'selected' : '' }}>Editor1 Process</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0" style="font-size: 0.75rem;">Cari</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Kode/Judul/Penulis" value="{{ request('search') }}" style="font-size: 0.75rem; padding: 4px 8px;">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0" style="font-size: 0.75rem;">&nbsp;</label>
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <button type="submit" class="btn btn-primary" style="font-size: 0.75rem; padding: 4px 8px;">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="{{ route('pic.submissions.monitoring') }}" class="btn btn-outline-secondary" style="font-size: 0.75rem; padding: 4px 8px;">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- Scroll Control Bar -->
        <div class="scroll-control-bar">
            <div class="scroll-nav-group">
                <button type="button" class="scroll-btn" id="scrollStartBtn" title="Ke Awal">
                    <i class="bi bi-chevron-bar-left"></i>
                </button>
                <button type="button" class="scroll-btn" id="scrollLeftBtn" title="Scroll Kiri">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <div class="scroll-progress">
                    <div class="scroll-progress-bar">
                        <div class="scroll-progress-fill" id="scrollPositionFill"></div>
                    </div>
                    <span class="scroll-progress-text" id="scrollPositionText">0%</span>
                </div>
                <button type="button" class="scroll-btn" id="scrollRightBtn" title="Scroll Kanan">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <button type="button" class="scroll-btn" id="scrollEndBtn" title="Ke Akhir">
                    <i class="bi bi-chevron-bar-right"></i>
                </button>
            </div>
            <div class="quick-jump">
                <span class="quick-jump-label">Lompat ke:</span>
                <button type="button" class="jump-btn" data-target="submit">Submit</button>
                <button type="button" class="jump-btn" data-target="editor1">Editor1</button>
                <button type="button" class="jump-btn" data-target="author1">Author1</button>
                <button type="button" class="jump-btn" data-target="editor2">Editor2</button>
                <button type="button" class="jump-btn" data-target="editor3">Editor3</button>
                <button type="button" class="jump-btn" data-target="author2">Author2</button>
                <button type="button" class="jump-btn" data-target="production">Production</button>
            </div>
        </div>

        <!-- Data Table -->
        <div class="monitoring-scroll-wrapper" id="monitoringScrollWrapper">
            <table class="table table-monitoring table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th rowspan="2" class="align-middle sticky-first">Kode Submit</th>
                        <th rowspan="2" class="align-middle sticky-second">ID Artikel</th>
                        <th rowspan="2" class="align-middle">Judul</th>
                        <th rowspan="2" class="align-middle">Link</th>
                        <th rowspan="2" class="align-middle">Penulis</th>
                        <th rowspan="2" class="align-middle">No HP</th>
                        <th colspan="2" class="text-center">Author Access</th>
                        <th rowspan="2" class="align-middle">PIC Marketing</th>
                        <th rowspan="2" class="align-middle" id="colSubmit">Petugas Submit</th>
                        <th colspan="3" class="text-center bg-info" id="colEditor1">Editor 1</th>
                        <th colspan="2" class="text-center bg-warning text-dark" id="colAuthor1">Author 1</th>
                        <th colspan="3" class="text-center bg-info" id="colEditor2">Editor 2</th>
                        <th colspan="2" class="text-center bg-info" id="colEditor3">Editor 3</th>
                        <th colspan="2" class="text-center bg-warning text-dark" id="colAuthor2">Author 2</th>
                        <th colspan="3" class="text-center bg-success" id="colProduction">Production</th>
                    </tr>
                    <tr>
                        <!-- Author Access sub-headers -->
                        <th class="bg-dark">Username</th>
                        <th class="bg-dark">Password</th>
                        <!-- Editor 1 sub-headers (3 cols) -->
                        <th class="bg-info">Petugas</th>
                        <th class="bg-info">User/Pass</th>
                        <th class="bg-info">Valid</th>
                        <!-- Author 1 sub-headers (2 cols) -->
                        <th class="bg-warning">Petugas</th>
                        <th class="bg-warning">Valid</th>
                        <!-- Editor 2 sub-headers (3 cols) -->
                        <th class="bg-info">Petugas</th>
                        <th class="bg-info">R1/R2</th>
                        <th class="bg-info">Valid</th>
                        <!-- Editor 3 sub-headers (2 cols) -->
                        <th class="bg-info">Petugas</th>
                        <th class="bg-info">Valid</th>
                        <!-- Author 2 sub-headers (2 cols) -->
                        <th class="bg-warning">Petugas</th>
                        <th class="bg-warning">Valid</th>
                        <!-- Production sub-headers (3 cols) -->
                        <th class="bg-success">Petugas</th>
                        <th class="bg-success">Link Publish</th>
                        <th class="bg-success">Valid</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currentPicId = auth()->guard('pic')->id();
                    @endphp
                    @forelse($submissions as $s)
                        @php
                            $isMyTask = $s->created_by == $currentPicId
                                || $s->petugas_submit_id == $currentPicId
                                || $s->petugas_editor1_id == $currentPicId
                                || $s->petugas_author1_id == $currentPicId
                                || $s->petugas_editor2_id == $currentPicId
                                || $s->petugas_reviewer1_id == $currentPicId
                                || $s->petugas_reviewer2_id == $currentPicId
                                || $s->petugas_editor3_id == $currentPicId
                                || $s->petugas_author2_id == $currentPicId
                                || $s->petugas_production_id == $currentPicId;
                        @endphp
                        <tr class="{{ $isMyTask ? 'my-task-row' : '' }}">
                            <td class="sticky-first">
                                <a href="{{ route('pic.submissions.show', $s) }}" class="text-decoration-none" title="Lihat detail">
                                    <code class="text-primary">{{ $s->kode_submit }}</code>
                                </a>
                            </td>
                            <td class="sticky-second">{{ $s->id_artikel }}</td>
                            <td title="{{ $s->judul_artikel }}" style="max-width: 150px;">{{ Str::limit($s->judul_artikel, 20) }}</td>
                            <td class="text-center">
                                @if($s->link_artikel)
                                    <a href="{{ $s->link_artikel }}" target="_blank"><i class="bi bi-link-45deg"></i></a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ Str::limit($s->nama_penulis, 10) }}</td>
                            <td>{{ $s->no_hp_penulis ?? '-' }}</td>
                            <td>
                                @if($s->username_author)
                                    <code style="font-size: 0.6rem;">{{ $s->username_author }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($s->password_author)
                                    <code style="font-size: 0.6rem;">{{ $s->password_author }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($s->marketing)
                                    {{ $s->marketing->name }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($s->petugas_submit_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $s->petugasSubmit?->name ?? '-' }}
                                @endif
                            </td>
                            
                            <!-- Editor 1 -->
                            <td>
                                @if($s->petugas_editor1_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $s->petugasEditor1?->name ?? '-' }}
                                @endif
                            </td>
                            <td>
                                @if($s->petugas_editor1_id == $currentPicId)
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex gap-1 align-items-center">
                                            <small style="font-size: 0.5rem; color: #666; min-width: 22px;">u:</small>
                                            <input type="text" class="form-control form-control-sm" style="width: 50px; font-size: 0.6rem; padding: 1px 2px;" 
                                                   value="{{ $s->username_editor ?? '' }}" 
                                                   data-submission="{{ $s->id }}"
                                                   data-field="username_editor"
                                                   onchange="updateCredential(this)" 
                                                   placeholder="user">
                                        </div>
                                        <div class="d-flex gap-1 align-items-center">
                                            <small style="font-size: 0.5rem; color: #666; min-width: 22px;">p:</small>
                                            <input type="text" class="form-control form-control-sm" style="width: 50px; font-size: 0.6rem; padding: 1px 2px;" 
                                                   value="{{ $s->password_editor ?? '' }}" 
                                                   data-submission="{{ $s->id }}"
                                                   data-field="password_editor"
                                                   onchange="updateCredential(this)" 
                                                   placeholder="pass">
                                        </div>
                                    </div>
                                @else
                                    @if($s->username_editor || $s->password_editor)
                                        <div style="font-size: 0.6rem;">
                                            <div><small class="text-muted" style="font-size: 0.55rem;">u:</small> <code>{{ $s->username_editor ?? '-' }}</code></div>
                                            <div><small class="text-muted" style="font-size: 0.55rem;">p:</small> <code>{{ $s->password_editor ?? '-' }}</code></div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s->petugas_editor1_id == $currentPicId)
                                    <button type="button" class="btn btn-sm {{ $s->editor1_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                            onclick="toggleValid(this, {{ $s->id }}, 'editor1')"
                                            title="Klik untuk toggle validasi">
                                        <i class="bi {{ $s->editor1_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                    </button>
                                @else
                                    {!! $s->editor1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                @endif
                            </td>
                            
                            <!-- Author 1 -->
                            <td>
                                @if($s->petugas_author1_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $s->petugasAuthor1?->name ?? '-' }}
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s->petugas_author1_id == $currentPicId)
                                    <button type="button" class="btn btn-sm {{ $s->author1_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                            onclick="toggleValid(this, {{ $s->id }}, 'author1')"
                                            title="Klik untuk toggle validasi">
                                        <i class="bi {{ $s->author1_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                    </button>
                                @else
                                    {!! $s->author1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                @endif
                            </td>
                            
                            <!-- Editor 2 -->
                            <td>
                                @if($s->petugas_editor2_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $s->petugasEditor2?->name ?? '-' }}
                                @endif
                            </td>
                            <td>
                                @if($s->petugas_editor2_id == $currentPicId)
                                    <div class="p-1" style="font-size: 0.55rem;">
                                        <div class="mb-1">
                                            <label style="font-size:0.55rem; font-weight:bold; color:#0d6efd;">R1:</label>
                                            <div class="d-flex gap-1">
                                                <input type="text" class="form-control form-control-sm" style="width: 45px; font-size: 0.55rem; padding: 1px 2px;" 
                                                       value="{{ $s->username_reviewer1 ?? '' }}" 
                                                       data-submission="{{ $s->id }}"
                                                       data-field="username_reviewer1"
                                                       onchange="updateCredential(this)" 
                                                       placeholder="user">
                                                <input type="text" class="form-control form-control-sm" style="width: 45px; font-size: 0.55rem; padding: 1px 2px;" 
                                                       value="{{ $s->password_reviewer1 ?? '' }}" 
                                                       data-submission="{{ $s->id }}"
                                                       data-field="password_reviewer1"
                                                       onchange="updateCredential(this)" 
                                                       placeholder="pass">
                                            </div>
                                        </div>
                                        <div>
                                            <label style="font-size:0.55rem; font-weight:bold; color:#0d6efd;">R2:</label>
                                            <div class="d-flex gap-1">
                                                <input type="text" class="form-control form-control-sm" style="width: 45px; font-size: 0.55rem; padding: 1px 2px;" 
                                                       value="{{ $s->username_reviewer2 ?? '' }}" 
                                                       data-submission="{{ $s->id }}"
                                                       data-field="username_reviewer2"
                                                       onchange="updateCredential(this)" 
                                                       placeholder="user">
                                                <input type="text" class="form-control form-control-sm" style="width: 45px; font-size: 0.55rem; padding: 1px 2px;" 
                                                       value="{{ $s->password_reviewer2 ?? '' }}" 
                                                       data-submission="{{ $s->id }}"
                                                       data-field="password_reviewer2"
                                                       onchange="updateCredential(this)" 
                                                       placeholder="pass">
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    @if($s->username_reviewer1 || $s->username_reviewer2)
                                        <div style="font-size: 0.55rem;">
                                            @if($s->username_reviewer1)
                                                <div><strong>R1:</strong> {{ $s->username_reviewer1 }}</div>
                                            @endif
                                            @if($s->username_reviewer2)
                                                <div><strong>R2:</strong> {{ $s->username_reviewer2 }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s->petugas_editor2_id == $currentPicId)
                                    <button type="button" class="btn btn-sm {{ $s->editor2_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                            onclick="toggleValid(this, {{ $s->id }}, 'editor2')"
                                            title="Klik untuk toggle validasi">
                                        <i class="bi {{ $s->editor2_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                    </button>
                                @else
                                    {!! $s->editor2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                @endif
                            </td>
                            
                            <!-- Editor 3 -->
                            <td>
                                @if($s->petugas_editor3_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $s->petugasEditor3?->name ?? '-' }}
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s->petugas_editor3_id == $currentPicId)
                                    <button type="button" class="btn btn-sm {{ $s->editor3_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                            onclick="toggleValid(this, {{ $s->id }}, 'editor3')"
                                            title="Klik untuk toggle validasi">
                                        <i class="bi {{ $s->editor3_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                    </button>
                                @else
                                    {!! $s->editor3_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                @endif
                            </td>
                            
                            <!-- Author 2 -->
                            <td>
                                @if($s->petugas_author2_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $s->petugasAuthor2?->name ?? '-' }}
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s->petugas_author2_id == $currentPicId)
                                    <button type="button" class="btn btn-sm {{ $s->author2_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                            onclick="toggleValid(this, {{ $s->id }}, 'author2')"
                                            title="Klik untuk toggle validasi">
                                        <i class="bi {{ $s->author2_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                    </button>
                                @else
                                    {!! $s->author2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                @endif
                            </td>
                            
                            <!-- Production -->
                            <td>
                                @if($s->petugas_production_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $s->petugasProduction?->name ?? '-' }}
                                @endif
                            </td>
                            <td>
                                @if($s->link_publish)
                                    <a href="{{ $s->link_publish }}" target="_blank"><i class="bi bi-link-45deg"></i></a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s->petugas_production_id == $currentPicId)
                                    <button type="button" class="btn btn-sm {{ $s->production_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                            onclick="toggleValid(this, {{ $s->id }}, 'production')"
                                            title="Klik untuk toggle validasi">
                                        <i class="bi {{ $s->production_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                    </button>
                                @else
                                    {!! $s->production_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="32" class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.5;"></i>
                                <p class="mt-3 mb-0">Tidak ada data submission ditemukan</p>
                                <small class="text-muted">Silakan coba filter yang berbeda atau hubungi admin</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $submissions->links() }}
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Scroll Navigation
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.getElementById('monitoringScrollWrapper');
    const positionFill = document.getElementById('scrollPositionFill');
    const positionText = document.getElementById('scrollPositionText');
    const scrollLeftBtn = document.getElementById('scrollLeftBtn');
    const scrollRightBtn = document.getElementById('scrollRightBtn');
    const scrollStartBtn = document.getElementById('scrollStartBtn');
    const scrollEndBtn = document.getElementById('scrollEndBtn');
    
    // Calculate column positions dynamically
    const columnPositions = {};
    function calculateColumnPositions() {
        const targets = ['submit', 'editor1', 'author1', 'editor2', 'editor3', 'author2', 'production'];
        targets.forEach(target => {
            const colId = 'col' + target.charAt(0).toUpperCase() + target.slice(1);
            const col = document.getElementById(colId);
            if (col && wrapper) {
                // Get position relative to the scrollable wrapper
                const colRect = col.getBoundingClientRect();
                const wrapperRect = wrapper.getBoundingClientRect();
                columnPositions[target] = col.offsetLeft - 200; // Offset for better visibility
            }
        });
        
        // Fallback positions if elements not found
        if (Object.keys(columnPositions).length === 0) {
            columnPositions['submit'] = 0;
            columnPositions['editor1'] = 500;
            columnPositions['author1'] = 750;
            columnPositions['editor2'] = 900;
            columnPositions['editor3'] = 1100;
            columnPositions['author2'] = 1250;
            columnPositions['production'] = 1400;
        }
    }
    
    // Calculate on load with slight delay to ensure table is rendered
    setTimeout(() => {
        calculateColumnPositions();
    }, 100);
    
    // Update scroll position indicator
    function updateScrollPosition() {
        if (!wrapper) return;
        
        const scrollLeft = wrapper.scrollLeft;
        const scrollWidth = wrapper.scrollWidth - wrapper.clientWidth;
        const progress = scrollWidth > 0 ? (scrollLeft / scrollWidth) * 100 : 0;
        
        if (positionFill) positionFill.style.width = progress + '%';
        if (positionText) positionText.textContent = Math.round(progress) + '%';
        
        // Update button states
        if (scrollStartBtn) scrollStartBtn.disabled = scrollLeft <= 0;
        if (scrollLeftBtn) scrollLeftBtn.disabled = scrollLeft <= 0;
        if (scrollRightBtn) scrollRightBtn.disabled = scrollLeft >= scrollWidth;
        if (scrollEndBtn) scrollEndBtn.disabled = scrollLeft >= scrollWidth;
        
        // Update jump btn active state
        document.querySelectorAll('.jump-btn').forEach(btn => {
            btn.classList.remove('active');
        });
    }
    
    if (wrapper) {
        wrapper.addEventListener('scroll', updateScrollPosition);
    }
    
    // Scroll amount
    const scrollAmount = 400;
    
    // Scroll buttons
    if (scrollLeftBtn) {
        scrollLeftBtn.addEventListener('click', () => {
            wrapper.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
    }
    
    if (scrollRightBtn) {
        scrollRightBtn.addEventListener('click', () => {
            wrapper.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
    }
    
    if (scrollStartBtn) {
        scrollStartBtn.addEventListener('click', () => {
            wrapper.scrollTo({ left: 0, behavior: 'smooth' });
        });
    }
    
    if (scrollEndBtn) {
        scrollEndBtn.addEventListener('click', () => {
            wrapper.scrollTo({ left: wrapper.scrollWidth, behavior: 'smooth' });
        });
    }
    
    // Quick navigation - Jump buttons
    document.querySelectorAll('.jump-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.dataset.target;
            const position = columnPositions[target] || 0;
            
            if (wrapper) {
                wrapper.scrollTo({ left: position, behavior: 'smooth' });
                
                document.querySelectorAll('.jump-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });
    
    // Keyboard navigation
    if (wrapper) {
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
    }
    
    // Recalculate column positions on window resize
    window.addEventListener('resize', function() {
        calculateColumnPositions();
    });
    
    // Initial state
    updateScrollPosition();
});

// Update Credential Function
function updateCredential(element) {
    const submissionId = element.dataset.submission;
    const field = element.dataset.field;
    const value = element.value;
    
    // Validate input
    if (!value || value.trim() === '') {
        element.style.borderColor = '#dc3545';
        showToast('⚠ Credential tidak boleh kosong', 'warning');
        return;
    }
    
    // Show loading state
    element.disabled = true;
    element.style.opacity = '0.5';
    element.style.borderColor = '#0d6efd';
    
    fetch('{{ route("pic.submissions.update-credential") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            submission_id: submissionId,
            field: field,
            value: value
        })
    })
    .then(response => response.json())
    .then(data => {
        element.disabled = false;
        element.style.opacity = '1';
        
        if (data.success) {
            // Show success feedback
            element.style.borderColor = '#198754';
            element.style.backgroundColor = '#d1e7dd';
            showToast('✓ Credential berhasil diupdate', 'success');
            
            setTimeout(() => {
                element.style.borderColor = '';
                element.style.backgroundColor = '';
            }, 1500);
        } else {
            // Show error
            element.style.borderColor = '#dc3545';
            element.style.backgroundColor = '#f8d7da';
            showToast('⚠ ' + (data.message || 'Gagal update credential'), 'danger');
            
            setTimeout(() => {
                element.style.backgroundColor = '';
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        element.disabled = false;
        element.style.opacity = '1';
        element.style.borderColor = '#dc3545';
        element.style.backgroundColor = '#f8d7da';
        showToast('⚠ Terjadi kesalahan saat update credential', 'danger');
        
        setTimeout(() => {
            element.style.backgroundColor = '';
        }, 2000);
    });
}

// Toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'position-fixed top-0 end-0 p-3';
    toast.style.zIndex = 9999;
    
    const bgClass = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : type === 'warning' ? 'bg-warning' : 'bg-info';
    const icon = type === 'success' ? 'bi-check-circle-fill' : type === 'danger' ? 'bi-x-circle-fill' : type === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill';
    
    toast.innerHTML = `
        <div class="toast align-items-center text-white ${bgClass} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${icon} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.position-fixed').remove()" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => { 
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Toggle Valid Function
function toggleValid(button, submissionId, stage) {
    // Show loading
    const icon = button.querySelector('i');
    const originalClass = icon.className;
    icon.className = 'spinner-border spinner-border-sm';
    button.disabled = true;
    button.style.opacity = '0.6';
    
    fetch('{{ route("pic.submissions.toggle-valid") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            submission_id: submissionId,
            stage: stage
        })
    })
    .then(response => response.json())
    .then(data => {
        button.disabled = false;
        button.style.opacity = '1';
        
        if (data.success) {
            // Update button state
            if (data.valid) {
                button.classList.remove('btn-outline-secondary');
                button.classList.add('btn-success');
                icon.className = 'bi bi-check-circle-fill';
                showToast('✓ Validasi berhasil!', 'success');
            } else {
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-secondary');
                icon.className = 'bi bi-circle';
                showToast('✗ Validasi dibatalkan', 'warning');
            }
            
            // Add animation effect
            button.style.transform = 'scale(1.1)';
            setTimeout(() => { button.style.transform = 'scale(1)'; }, 200);
        } else {
            icon.className = originalClass;
            showToast('⚠ ' + (data.message || 'Gagal toggle validasi'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        icon.className = originalClass;
        button.disabled = false;
        button.style.opacity = '1';
        showToast('⚠ Terjadi kesalahan saat toggle validasi', 'danger');
    });
}
</script>
@endsection
