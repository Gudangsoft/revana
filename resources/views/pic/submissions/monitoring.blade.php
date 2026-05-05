@extends('pic.layouts.app')

@section('title', 'Monitoring' . (request('program') ? ' ' . strtoupper(request('program')) : ''))
@section('page-title', 'Monitoring' . (request('program') ? ' ' . strtoupper(request('program')) : ''))

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<style>
/* Override content width for this page */
.content {
    max-width: 100vw;
    overflow-x: hidden;
}

/* Sticky Table Styles for Monitoring */
.monitoring-scroll-wrapper {
    overflow-x: auto;
    overflow-y: auto;
    max-height: calc(100vh - 400px);
    min-height: 300px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    scrollbar-width: thin;
    scrollbar-color: #6c757d #dee2e6;
}

.monitoring-scroll-wrapper::-webkit-scrollbar {
    height: 12px;
    width: 12px;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 6px;
    border: 2px solid #f1f1f1;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-corner {
    background: #dee2e6;
}

/* Editable credential inputs */
.editable-credential {
    display: flex;
    gap: 4px;
    align-items: center;
}

.editable-credential input {
    transition: border-color 0.2s;
}

.editable-credential input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Validation toggle button */
.validation-toggle {
    padding: 4px 8px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
    border-width: 1px;
}

.validation-toggle:hover {
    transform: scale(1.1);
}

.validation-toggle.btn-success {
    background-color: #198754;
    border-color: #198754;
    color: white;
}

.validation-toggle.btn-outline-secondary {
    background-color: transparent;
    border-color: #6c757d;
    color: #6c757d;
}

.validation-toggle.btn-outline-secondary:hover {
    background-color: #6c757d;
    color: white;
}

/* Validation checkbox button */
.validation-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.table-monitoring {
    border-collapse: collapse;
    font-size: 0.8rem;
    margin: 0;
    border-spacing: 0;
}

.table-monitoring thead th {
    position: sticky;
    top: 0;
    z-index: 20;
    font-size: 0.7rem;
    padding: 4px;
    border: 1px solid #dee2e6;
    white-space: nowrap;
    background-color: #212529 !important;
    color: #fff !important;
    line-height: 1;
    vertical-align: middle;
    height: 32px;
}

.table-monitoring thead tr:nth-child(2) th {
    position: sticky;
    top: 32px;
    z-index: 20;
    line-height: 1;
    vertical-align: middle;
    height: 32px;
}

.table-monitoring thead th.bg-info {
    background-color: #0dcaf0 !important;
    color: #000 !important;
    z-index: 20 !important;
    position: sticky !important;
}

.table-monitoring thead th.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
    z-index: 20 !important;
    position: sticky !important;
}

.table-monitoring thead th.bg-primary {
    background-color: #0d6efd !important;
    color: #fff !important;
    z-index: 20 !important;
    position: sticky !important;
}

.table-monitoring thead th.bg-success {
    background-color: #198754 !important;
    color: #fff !important;
    z-index: 20 !important;
    position: sticky !important;
}

.table-monitoring thead th.bg-validator {
    background-color: #20c997 !important;
    color: #fff !important;
    z-index: 20 !important;
    position: sticky !important;
}

.table-monitoring thead th.bg-dark {
    background-color: #212529 !important;
    color: #fff !important;
    z-index: 20 !important;
    position: sticky !important;
}

.table-monitoring thead th.text-dark {
    color: #000 !important;
}

/* Sticky first column */
.table-monitoring th.sticky-first,
.table-monitoring td.sticky-first {
    position: sticky;
    left: 0;
    z-index: 3;
    background: #fff;
    min-width: 110px;
    box-shadow: 2px 0 4px -2px rgba(0,0,0,0.1);
}

.table-monitoring thead th.sticky-first {
    z-index: 21;
    background-color: #212529 !important;
    color: #fff !important;
}

/* Sticky second column */
.table-monitoring th.sticky-second,
.table-monitoring td.sticky-second {
    position: sticky;
    left: 110px;
    z-index: 3;
    background: #fff;
    min-width: 90px;
    box-shadow: 2px 0 4px -2px rgba(0,0,0,0.1);
}

.table-monitoring thead th.sticky-second {
    z-index: 21;
    background-color: #212529 !important;
    color: #fff !important;
}

/* Non-sticky columns */
.table-monitoring td:not(.sticky-first):not(.sticky-second),
.table-monitoring th:not(.sticky-first):not(.sticky-second) {
    z-index: 1;
    position: relative;
}

.table-monitoring tbody td {
    padding: 4px;
    border: 1px solid #dee2e6;
    white-space: nowrap;
    line-height: 1;
    vertical-align: middle;
    height: 30px;
}

.table-monitoring tbody tr:hover td {
    background-color: #f1f3f5;
}

.table-monitoring tbody tr:hover td.sticky-first,
.table-monitoring tbody tr:hover td.sticky-second {
    background-color: #e9ecef;
}

.table-monitoring tbody tr:nth-child(even) td {
    background-color: #f9fafb;
}

.table-monitoring tbody tr:nth-child(even) td.sticky-first,
.table-monitoring tbody tr:nth-child(even) td.sticky-second {
    background-color: #f9fafb;
}

/* Highlight for current user's assigned tasks */
.table-monitoring tbody tr.my-task td {
    background-color: #fff3cd;
}

.table-monitoring tbody tr.my-task td.sticky-first,
.table-monitoring tbody tr.my-task td.sticky-second {
    background-color: #fff3cd;
}

.table-monitoring tbody tr.my-task:hover td {
    background-color: #ffe69c;
}

.table-monitoring tbody tr.my-task:hover td.sticky-first,
.table-monitoring tbody tr.my-task:hover td.sticky-second {
    background-color: #ffe69c;
}

/* Highlight individual cells for assigned tasks */
.table-monitoring tbody td.my-task {
    background-color: #fff3cd !important;
}

.table-monitoring tbody tr:hover td.my-task {
    background-color: #ffe69c !important;
}

/* Editable credential styling */
.editable-credential {
    display: flex;
    gap: 4px;
    align-items: center;
    min-width: 200px;
}

.editable-credential input {
    font-family: 'Courier New', monospace;
}

.editable-credential input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Validation toggle button */
.validation-toggle {
    min-width: 36px;
    padding: 4px 8px;
    transition: all 0.2s;
}

.validation-toggle:hover {
    transform: scale(1.1);
}

.task-indicator {
    display: inline-block;
    margin-right: 4px;
    color: #ffc107;
}

/* Scroll controls */
.scroll-controls {
    margin-bottom: 10px;
    padding: 8px 12px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 6px;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    position: relative;
    z-index: 20;
}

.scroll-nav-btn {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
    z-index: 21;
}

.scroll-nav-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #0b5ed7, #0a58ca);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

.scroll-nav-btn:disabled {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    cursor: not-allowed;
    opacity: 0.5;
}

.scroll-position-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    max-width: 300px;
}

.scroll-position-bar {
    width: 200px;
    height: 8px;
    background: #dee2e6;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.scroll-position-fill {
    height: 100%;
    background: linear-gradient(90deg, #198754, #20c997);
    transition: width 0.2s ease;
}

.quick-nav {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}

.quick-nav-btn {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    border: none;
    padding: 5px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.75rem;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
    z-index: 21;
}

.quick-nav-btn:hover {
    background: linear-gradient(135deg, #5a6268, #495057);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.quick-nav-btn.active {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
}

/* Summary cards */
.summary-cards {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.summary-card {
    flex: 0 0 auto;
    min-width: 120px;
    max-width: 180px;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    padding: 12px 15px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.summary-card h6 {
    font-size: 0.7rem;
    color: #6c757d;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.summary-card .value {
    font-size: 1.75rem;
    font-weight: bold;
    color: #212529;
}

.summary-card.my-tasks {
    border-left: 4px solid #ffc107;
}

.summary-card.all-tasks {
    border-left: 4px solid #0d6efd;
}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <span><i class="bi bi-clipboard-data"></i> Monitoring Proses Submit{{ request('program') ? ' ' . strtoupper(request('program')) : '' }}</span>
                    @if(!in_array(request('program'), ['bkd', 'jafa']))
                    <a href="{{ route('pic.fasttrack.monitoring') }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-lightning"></i> Lihat Fasttrack
                    </a>
                    @endif
                </div>
                @if(!in_array(request('program'), ['bkd', 'jafa']))
                <div class="alert alert-info mb-0 py-2 px-3" style="font-size: 0.875rem;">
                    <i class="bi bi-info-circle"></i>
                    Halaman ini menampilkan data <strong>submissions normal</strong> saja.
                </div>
                @endif
            </div>
            <div class="card-body">
                <!-- Summary Cards -->
                <div class="summary-cards mb-3">
                    <div class="summary-card my-tasks">
                        <h6>Tugas Saya</h6>
                        <div class="value">{{ $myTaskCount }}</div>
                    </div>
                    <div class="summary-card all-tasks">
                        <h6>Total Submit</h6>
                        <div class="value">{{ $totalSubmissions }}</div>
                    </div>
                </div>
                    <!-- Filter Form -->
                <form action="{{ route('pic.submissions.monitoring') }}" method="GET" class="mb-3" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label for="tanggal_dari" class="form-label small mb-1">Tanggal Dari</label>
                            <input type="date" class="form-control form-control-sm" style="width: 150px;" id="tanggal_dari" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
                        </div>
                        <div class="col-auto">
                            <label for="tanggal_sampai" class="form-label small mb-1">Tanggal Sampai</label>
                            <input type="date" class="form-control form-control-sm" style="width: 150px;" id="tanggal_sampai" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
                        </div>
                        <div class="col-auto">
                            <label for="journal_id" class="form-label small mb-1">Jurnal</label>
                            <select class="form-select form-select-sm" style="width: 180px;" id="journal_id" name="journal_id">
                                <option value="">-- Semua --</option>
                                @foreach($journals as $journal)
                                    <option value="{{ $journal->id }}" {{ request('journal_id') == $journal->id ? 'selected' : '' }}>
                                        {{ Str::limit($journal->nama_jurnal, 20) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="status" class="form-label small mb-1">Status</label>
                            <select class="form-select form-select-sm" style="width: 130px;" id="status" name="status">
                                <option value="">-- Semua --</option>
                                <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>Submitted</option>
                                <option value="EDITOR1" {{ request('status') == 'EDITOR1' ? 'selected' : '' }}>Editor 1</option>
                                <option value="AUTHOR1" {{ request('status') == 'AUTHOR1' ? 'selected' : '' }}>Author 1</option>
                                <option value="EDITOR2" {{ request('status') == 'EDITOR2' ? 'selected' : '' }}>Editor 2</option>
                                <option value="REVIEWER1" {{ request('status') == 'REVIEWER1' ? 'selected' : '' }}>Reviewer 1</option>
                                <option value="REVIEWER2" {{ request('status') == 'REVIEWER2' ? 'selected' : '' }}>Reviewer 2</option>
                                <option value="EDITOR3" {{ request('status') == 'EDITOR3' ? 'selected' : '' }}>Editor 3</option>
                                <option value="AUTHOR2" {{ request('status') == 'AUTHOR2' ? 'selected' : '' }}>Author 2</option>
                                <option value="PRODUCTION" {{ request('status') == 'PRODUCTION' ? 'selected' : '' }}>Production</option>
                                <option value="VALIDATOR" {{ request('status') == 'VALIDATOR' ? 'selected' : '' }}>Validasi</option>
                                <option value="PUBLISHED" {{ request('status') == 'PUBLISHED' ? 'selected' : '' }}>Published</option>
                                <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                                <a href="{{ route('pic.submissions.monitoring') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Important Info Alert -->
                <div class="alert alert-info d-flex align-items-start mb-3" style="font-size: 0.85rem;">
                    <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                    <div>
                        <strong>Info Penting:</strong>
                        <ul class="mb-0 mt-1" style="font-size: 0.8rem;">
                            <li><strong>Link Publish</strong> hanya bisa diedit jika validasi Production <strong>belum dicentang</strong>.</li>
                            <li>Jika hendak mengubah Link Publish, <strong>matikan validasi Production</strong> terlebih dahulu dengan klik tombol centang hijau.</li>
                            <li>Setelah selesai edit, centang kembali validasi Production untuk mengunci data.</li>
                        </ul>
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
                        <button type="button" class="quick-nav-btn" data-target="validator">Validasi</button>
                    </div>
                </div>

                <!-- Submissions Monitoring Table -->
                <div class="monitoring-scroll-wrapper" id="monitoringScrollWrapper">
                    <table class="table table-monitoring table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2" class="text-center">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th rowspan="2" class="sticky-first">Kode Submit</th>
                                <th rowspan="2" class="sticky-second">ID Artikel</th>
                                <th rowspan="2">Judul</th>
                                <th rowspan="2">Volume</th>
                                <th rowspan="2">Link</th>
                                <th rowspan="2">Penulis</th>
                                <th rowspan="2">No HP</th>
                                <th colspan="2" class="text-center bg-dark text-white" id="colAuthorAccess">Author Access</th>
                                <th rowspan="2">PIC Marketing</th>
                                <th rowspan="2" id="colSubmit">Petugas Submit</th>
                                <th colspan="3" class="text-center bg-info text-dark" id="colEditor1">Editor 1</th>
                                <th colspan="2" class="text-center bg-warning text-dark" id="colAuthor1">Author 1</th>
                                <th colspan="4" class="text-center bg-info text-dark" id="colEditor2">Editor 2</th>
                                <th colspan="2" class="text-center bg-primary text-white" id="colReviewer1">Reviewer 1</th>
                                <th colspan="2" class="text-center bg-primary text-white" id="colReviewer2">Reviewer 2</th>
                                <th colspan="2" class="text-center bg-info text-dark" id="colEditor3">Editor 3</th>
                                <th colspan="2" class="text-center bg-warning text-dark" id="colAuthor2">Author 2</th>
                                <th colspan="3" class="text-center bg-success text-white" id="colProduction">Production</th>
                                <th colspan="2" class="text-center bg-validator" id="colValidator">Validasi</th>
                            </tr>
                            <tr>
                                <!-- Author Access sub-headers -->
                                <th class="bg-dark text-white">Username</th>
                                <th class="bg-dark text-white">Password</th>
                                <!-- Editor 1 sub-headers (3 cols) -->
                                <th class="bg-info text-dark">Petugas</th>
                                <th class="bg-info text-dark">User/Pass</th>
                                <th class="bg-info text-dark">Valid</th>
                                <!-- Author 1 sub-headers (2 cols) -->
                                <th class="bg-warning text-dark">Petugas</th>
                                <th class="bg-warning text-dark">Valid</th>
                                <!-- Editor 2 sub-headers (5 cols) -->
                                <th class="bg-info text-dark">Petugas</th>
                                <th class="bg-info text-dark">User/Pass R1</th>
                                <th class="bg-info text-dark">User/Pass R2</th>
                                <th class="bg-info text-dark">Valid</th>
                                <!-- Reviewer 1 sub-headers (2 cols) -->
                                <th class="bg-primary text-white">Petugas</th>
                                <th class="bg-primary text-white">Valid</th>
                                <!-- Reviewer 2 sub-headers (2 cols) -->
                                <th class="bg-primary text-white">Petugas</th>
                                <th class="bg-primary text-white">Valid</th>
                                <!-- Editor 3 sub-headers (2 cols) -->
                                <th class="bg-info text-dark">Petugas</th>
                                <th class="bg-info text-dark">Valid</th>
                                <!-- Author 2 sub-headers (2 cols) -->
                                <th class="bg-warning text-dark">Petugas</th>
                                <th class="bg-warning text-dark">Valid</th>
                                <!-- Production sub-headers (3 cols) -->
                                <th class="bg-success text-white">Petugas</th>
                                <th class="bg-success text-white">Link Publish</th>
                                <th class="bg-success text-white">Valid</th>
                                <!-- Validator sub-headers (2 cols) -->
                                <th class="bg-validator">Petugas</th>
                                <th class="bg-validator">Valid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $s)
                            @php
                                $picId = auth()->guard('pic')->id();
                            @endphp
                            <tr class="{{ 
                                $s->petugas_editor1_id == $picId || 
                                $s->petugas_author1_id == $picId || 
                                $s->petugas_editor2_id == $picId || 
                                $s->petugas_reviewer1_id == $picId || 
                                $s->petugas_reviewer2_id == $picId || 
                                $s->petugas_editor3_id == $picId || 
                                $s->petugas_author2_id == $picId || 
                                $s->petugas_production_id == $picId ||
                                $s->petugas_validator_id == $picId 
                                ? 'my-task' : '' }}"
                                data-submission-id="{{ $s->id }}"
                                data-editor1-valid="{{ $s->editor1_valid ? '1' : '0' }}"
                                data-author1-valid="{{ $s->author1_valid ? '1' : '0' }}"
                                data-editor2-valid="{{ $s->editor2_valid ? '1' : '0' }}"
                                data-reviewer1-valid="{{ $s->reviewer1_valid ? '1' : '0' }}"
                                data-reviewer2-valid="{{ $s->reviewer2_valid ? '1' : '0' }}"
                                data-editor3-valid="{{ $s->editor3_valid ? '1' : '0' }}"
                                data-author2-valid="{{ $s->author2_valid ? '1' : '0' }}"
                                data-production-valid="{{ $s->production_valid ? '1' : '0' }}"
                                data-validator-valid="{{ $s->validator_valid ? '1' : '0' }}"
                                data-has-link-publish="{{ $s->link_publish ? '1' : '0' }}">
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input submission-checkbox" value="{{ $s->id }}" data-kode="{{ $s->kode_submit }}" data-title="{{ Str::limit($s->judul_artikel, 40) }}">
                                </td>
                                <td class="sticky-first">
                                    @if($s->petugas_editor1_id == $picId || 
                                        $s->petugas_author1_id == $picId || 
                                        $s->petugas_editor2_id == $picId || 
                                        $s->petugas_reviewer1_id == $picId || 
                                        $s->petugas_reviewer2_id == $picId || 
                                        $s->petugas_editor3_id == $picId || 
                                        $s->petugas_author2_id == $picId || 
                                        $s->petugas_production_id == $picId ||
                                        $s->petugas_validator_id == $picId)
                                        <span class="task-indicator" title="Tugas Anda">
                                            <i class="bi bi-star-fill"></i>
                                        </span>
                                    @endif
                                    <a href="{{ route('pic.submissions.process', $s) }}" class="text-decoration-none" title="Klik untuk proses">
                                        <code class="text-primary">{{ $s->kode_submit }}</code>
                                    </a>
                                    @if($s->journalSlot)
                                        <br><small class="text-muted" style="font-size: 0.65rem; line-height: 1.2;" title="{{ $s->journalSlot->journalMaster?->nama_jurnal ?? '-' }} - Vol.{{ $s->journalSlot->volume }} No.{{ $s->journalSlot->nomor }}">{{ Str::limit($s->journalSlot->journalMaster?->nama_jurnal ?? '-', 20) }}<br>Vol.{{ $s->journalSlot->volume }} No.{{ $s->journalSlot->nomor }}</small>
                                    @endif
                                </td>
                                <td class="sticky-second">{{ $s->id_artikel }}</td>
                                <td title="{{ $s->judul_artikel }}">{{ Str::limit($s->judul_artikel, 30) }}</td>
                                <td title="{{ $s->journalSlot?->display_name }}">{{ $s->journalSlot ? 'Vol.' . $s->journalSlot->volume . ' No.' . $s->journalSlot->nomor : '-' }}</td>
                                <td class="text-center">
                                    @if($s->link_artikel)
                                        <a href="{{ $s->link_artikel }}" target="_blank" title="Buka Link Artikel">
                                            <i class="bi bi-link-45deg"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ Str::limit($s->nama_penulis, 15) }}</td>
                                <td>
                                    @if($s->no_hp_penulis)
                                        @php
                                            $waNumber = preg_replace('/[^0-9]/', '', $s->no_hp_penulis);
                                            if (substr($waNumber, 0, 1) === '0') {
                                                $waNumber = '62' . substr($waNumber, 1);
                                            }
                                            $waMessage = "Selamat Artikel anda sudah terpublikasi:\n\n";
                                            $waMessage .= "Kode artikel: *{$s->id_artikel}*\n";
                                            $waMessage .= "Nama Penulis: *{$s->nama_penulis}*\n";
                                            $waMessage .= "Link Publikasi: {$s->link_publikasi}\n\n";
                                            $waMessage .= "Jangan lupa di referensikan ke teman2 nya.\n\n";
                                            $waMessage .= "SALAM APJI";
                                            $waUrl = "https://wa.me/{$waNumber}?text=" . urlencode($waMessage);
                                        @endphp
                                        <a href="{{ $waUrl }}" target="_blank" class="btn btn-success btn-sm" style="padding: 2px 6px; font-size: 0.7rem;" title="Chat WhatsApp {{ $s->no_hp_penulis }}">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td><code>{{ $s->username_author ?? '-' }}</code></td>
                                <td><code>{{ $s->password_author ?? '-' }}</code></td>
                                <td>{{ $s->marketing?->name ?? '-' }}</td>
                                <td>{{ $s->petugasSubmit?->name ?? '-' }}</td>
                                
                                <!-- Editor 1 -->
                                <td class="{{ $s->petugas_editor1_id == $picId ? 'my-task' : '' }}">
                                    {{ $s->petugasEditor1?->name ?? '-' }}
                                    @if($s->petugas_editor1_id == $picId)
                                        <i class="bi bi-star-fill text-warning" title="Tugas Anda"></i>
                                    @endif
                                </td>
                                <td class="{{ $s->petugas_editor1_id == $picId ? 'my-task' : '' }}">
                                    @if($s->petugas_editor1_id == $picId)
                                        <div class="editable-credential" data-submission="{{ $s->id }}" data-field-user="username_editor" data-field-pass="password_editor">
                                            <input type="text" class="form-control form-control-sm d-inline-block" style="width: 45%; font-size: 0.7rem;" value="{{ $s->username_editor }}" placeholder="Username">
                                            <span>/</span>
                                            <input type="text" class="form-control form-control-sm d-inline-block" style="width: 45%; font-size: 0.7rem;" value="{{ $s->password_editor }}" placeholder="Password">
                                        </div>
                                    @else
                                        @if($s->username_editor || $s->password_editor)
                                            <div class="credential-group">
                                                <code>{{ $s->username_editor ?? '-' }}</code>
                                                <span>/</span>
                                                <code>{{ $s->password_editor ?? '-' }}</code>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center {{ $s->petugas_editor1_id == $picId ? 'my-task' : '' }}">
                                    @if($s->petugas_editor1_id == $picId)
                                        {{-- Editor1 is first stage, always enabled --}}
                                        <button type="button" class="btn btn-sm validation-toggle {{ $s->editor1_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                data-submission="{{ $s->id }}" data-field="editor1_valid" data-current="{{ $s->editor1_valid ? '1' : '0' }}"
                                                data-stage-index="0">
                                            <i class="bi {{ $s->editor1_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                        </button>
                                    @else
                                        {!! $s->editor1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    @endif
                                </td>
                                
                                <!-- Author 1 -->
                                <td class="{{ $s->petugas_author1_id == auth()->id() ? 'my-task' : '' }}">
                                    {{ $s->petugasAuthor1?->name ?? '-' }}
                                    @if($s->petugas_author1_id == auth()->id())
                                        <i class="bi bi-star-fill text-warning" title="Tugas Anda"></i>
                                    @endif
                                </td>
                                <td class="text-center {{ $s->petugas_author1_id == $picId ? 'my-task' : '' }}">
                                    @if($s->petugas_author1_id == $picId)
                                        <button type="button" class="btn btn-sm validation-toggle {{ $s->author1_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                data-submission="{{ $s->id }}" data-field="author1_valid" data-current="{{ $s->author1_valid ? '1' : '0' }}"
                                                data-stage-index="1">
                                            <i class="bi {{ $s->author1_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                        </button>
                                    @else
                                        {!! $s->author1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    @endif
                                </td>
                                
                                <!-- Editor 2 -->
                                <td class="{{ $s->petugas_editor2_id == $picId ? 'my-task' : '' }}">
                                    {{ $s->petugasEditor2?->name ?? '-' }}
                                    @if($s->petugas_editor2_id == $picId)
                                        <i class="bi bi-star-fill text-warning" title="Tugas Anda"></i>
                                    @endif
                                </td>
                                <td class="{{ $s->petugas_editor2_id == $picId ? 'my-task' : '' }}">
                                    @if($s->petugas_editor2_id == $picId)
                                        <div class="editable-credential" data-submission="{{ $s->id }}" data-field-user="username_reviewer1" data-field-pass="password_reviewer1">
                                            <input type="text" class="form-control form-control-sm d-inline-block" style="width: 45%; font-size: 0.7rem;" value="{{ $s->username_reviewer1 }}" placeholder="Username">
                                            <span>/</span>
                                            <input type="text" class="form-control form-control-sm d-inline-block" style="width: 45%; font-size: 0.7rem;" value="{{ $s->password_reviewer1 }}" placeholder="Password">
                                        </div>
                                    @else
                                        @if($s->username_reviewer1 || $s->password_reviewer1)
                                            <div class="credential-group">
                                                <code>{{ $s->username_reviewer1 ?? '-' }}</code>
                                                <span>/</span>
                                                <code>{{ $s->password_reviewer1 ?? '-' }}</code>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    @endif
                                </td>
                                <td class="{{ $s->petugas_editor2_id == $picId ? 'my-task' : '' }}">
                                    @if($s->petugas_editor2_id == $picId)
                                        <div class="editable-credential" data-submission="{{ $s->id }}" data-field-user="username_reviewer2" data-field-pass="password_reviewer2">
                                            <input type="text" class="form-control form-control-sm d-inline-block" style="width: 45%; font-size: 0.7rem;" value="{{ $s->username_reviewer2 }}" placeholder="Username">
                                            <span>/</span>
                                            <input type="text" class="form-control form-control-sm d-inline-block" style="width: 45%; font-size: 0.7rem;" value="{{ $s->password_reviewer2 }}" placeholder="Password">
                                        </div>
                                    @else
                                        @if($s->username_reviewer2 || $s->password_reviewer2)
                                            <div class="credential-group">
                                                <code>{{ $s->username_reviewer2 ?? '-' }}</code>
                                                <span>/</span>
                                                <code>{{ $s->password_reviewer2 ?? '-' }}</code>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center {{ $s->petugas_editor2_id == $picId ? 'my-task' : '' }}">
                                    @if($s->petugas_editor2_id == $picId)
                                        <button type="button" class="btn btn-sm validation-toggle {{ $s->editor2_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                data-submission="{{ $s->id }}" data-field="editor2_valid" data-current="{{ $s->editor2_valid ? '1' : '0' }}"
                                                data-stage-index="2">
                                            <i class="bi {{ $s->editor2_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                        </button>
                                    @else
                                        {!! $s->editor2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    @endif
                                </td>
                                
                                <!-- Reviewer 1 -->
                                <td class="{{ $s->petugas_reviewer1_id == $picId ? 'my-task' : '' }}">
                                    {{ $s->petugasReviewer1?->name ?? '-' }}
                                    @if($s->petugas_reviewer1_id == $picId)
                                        <i class="bi bi-star-fill text-warning" title="Tugas Anda"></i>
                                    @endif
                                </td>
                                <td class="text-center {{ $s->petugas_reviewer1_id == $picId ? 'my-task' : '' }}">
                                    @if($s->petugas_reviewer1_id == $picId)
                                        <button type="button" class="btn btn-sm validation-toggle {{ $s->reviewer1_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                data-submission="{{ $s->id }}" data-field="reviewer1_valid" data-current="{{ $s->reviewer1_valid ? '1' : '0' }}"
                                                data-stage-index="3">
                                            <i class="bi {{ $s->reviewer1_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                        </button>
                                    @else
                                        {!! $s->reviewer1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    @endif
                                </td>
                                
                                <!-- Reviewer 2 -->
                                <td class="{{ $s->petugas_reviewer2_id == $picId ? 'my-task' : '' }}">
                                    {{ $s->petugasReviewer2?->name ?? '-' }}
                                    @if($s->petugas_reviewer2_id == $picId)
                                        <i class="bi bi-star-fill text-warning" title="Tugas Anda"></i>
                                    @endif
                                </td>
                                <td class="text-center {{ $s->petugas_reviewer2_id == $picId ? 'my-task' : '' }}">
                                    @if($s->petugas_reviewer2_id == $picId)
                                        <button type="button" class="btn btn-sm validation-toggle {{ $s->reviewer2_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                data-submission="{{ $s->id }}" data-field="reviewer2_valid" data-current="{{ $s->reviewer2_valid ? '1' : '0' }}"
                                                data-stage-index="4">
                                            <i class="bi {{ $s->reviewer2_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                        </button>
                                    @else
                                        {!! $s->reviewer2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    @endif
                                </td>
                                
                                <!-- Editor 3 -->
                                <td class="{{ $s->petugas_editor3_id == auth()->id() ? 'my-task' : '' }}">
                                    {{ $s->petugasEditor3?->name ?? '-' }}
                                    @if($s->petugas_editor3_id == auth()->id())
                                        <i class="bi bi-star-fill text-warning" title="Tugas Anda"></i>
                                    @endif
                                </td>
                                <td class="text-center {{ $s->petugas_editor3_id == $picId ? 'my-task' : '' }}">
                                    @if($s->petugas_editor3_id == $picId)
                                        <button type="button" class="btn btn-sm validation-toggle {{ $s->editor3_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                data-submission="{{ $s->id }}" data-field="editor3_valid" data-current="{{ $s->editor3_valid ? '1' : '0' }}"
                                                data-stage-index="5">
                                            <i class="bi {{ $s->editor3_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                        </button>
                                    @else
                                        {!! $s->editor3_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    @endif
                                </td>
                                
                                <!-- Author 2 -->
                                <td class="{{ $s->petugas_author2_id == $picId ? 'my-task' : '' }}">
                                    {{ $s->petugasAuthor2?->name ?? '-' }}
                                    @if($s->petugas_author2_id == $picId)
                                        <i class="bi bi-star-fill text-warning" title="Tugas Anda"></i>
                                    @endif
                                </td>
                                <td class="text-center {{ $s->petugas_author2_id == $picId ? 'my-task' : '' }}">
                                    @if($s->petugas_author2_id == $picId)
                                        <button type="button" class="btn btn-sm validation-toggle {{ $s->author2_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                data-submission="{{ $s->id }}" data-field="author2_valid" data-current="{{ $s->author2_valid ? '1' : '0' }}"
                                                data-stage-index="6">
                                            <i class="bi {{ $s->author2_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                        </button>
                                    @else
                                        {!! $s->author2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    @endif
                                </td>
                                
                                <!-- Production -->
                                <td class="{{ $s->petugas_production_id == $picId ? 'my-task' : '' }}">
                                    {{ $s->petugasProduction?->name ?? '-' }}
                                    @if($s->petugas_production_id == $picId)
                                        <i class="bi bi-star-fill text-warning" title="Tugas Anda"></i>
                                    @endif
                                </td>
                                <td class="{{ $s->petugas_production_id == $picId ? 'my-task' : '' }}">
                                    @if($s->petugas_production_id == $picId)
                                        <input type="text" class="form-control form-control-sm {{ $s->production_valid ? 'bg-light' : '' }}" style="font-size: 0.7rem; min-width: 150px;" 
                                               value="{{ $s->link_publish }}" placeholder="Link Publish" 
                                               data-submission="{{ $s->id }}" data-field="link_publish"
                                               {{ $s->production_valid ? 'readonly' : '' }}
                                               title="{{ $s->production_valid ? 'Link terkunci. Matikan validasi untuk mengedit.' : 'Masukkan link publish' }}">
                                    @else
                                        @if($s->link_publish)
                                            <a href="{{ $s->link_publish }}" target="_blank" title="Buka Link Publish">
                                                <i class="bi bi-link-45deg"></i>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center {{ $s->petugas_production_id == $picId ? 'my-task' : '' }}">
                                    @if($s->petugas_production_id == $picId)
                                        <button type="button" class="btn btn-sm validation-toggle {{ $s->production_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                data-submission="{{ $s->id }}" data-field="production_valid" data-current="{{ $s->production_valid ? '1' : '0' }}"
                                                data-stage-index="7">
                                            <i class="bi {{ $s->production_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                        </button>
                                    @else
                                        {!! $s->production_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    @endif
                                </td>
                                
                                <!-- Validator -->
                                <td class="{{ $s->petugas_validator_id == $picId ? 'my-task' : '' }}">
                                    {{ $s->petugasValidator?->name ?? '-' }}
                                    @if($s->petugas_validator_id == $picId)
                                        <i class="bi bi-star-fill text-warning" title="Tugas Anda"></i>
                                    @endif
                                </td>
                                <td class="text-center {{ $s->petugas_validator_id == $picId ? 'my-task' : '' }}">
                                    @if($s->petugas_validator_id == $picId)
                                        <button type="button" class="btn btn-sm validation-toggle {{ $s->validator_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                data-submission="{{ $s->id }}" data-field="validator_valid" data-current="{{ $s->validator_valid ? '1' : '0' }}"
                                                data-stage-index="8">
                                            <i class="bi {{ $s->validator_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                        </button>
                                    @else
                                        {!! $s->validator_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="29" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mt-2">Tidak ada data submissions</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @include('partials.per-page-selector', ['paginator' => $submissions])
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.getElementById('monitoringScrollWrapper');
    const positionFill = document.getElementById('scrollPositionFill');
    const positionText = document.getElementById('scrollPositionText');
    const scrollLeftBtn = document.getElementById('scrollLeftBtn');
    const scrollRightBtn = document.getElementById('scrollRightBtn');
    const scrollStartBtn = document.getElementById('scrollStartBtn');
    const scrollEndBtn = document.getElementById('scrollEndBtn');
    
    // Column positions for quick navigation
    const columnPositions = {
        'submit': 0,
        'editor1': 600,
        'author1': 850,
        'editor2': 1000,
        'reviewer1': 1150,
        'reviewer2': 1500,
        'editor3': 1850,
        'author2': 2000,
        'production': 2150,
        'validator': 2300
    };
    
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
    
    // Quick navigation
    document.querySelectorAll('.quick-nav-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.dataset.target;
            const position = columnPositions[target] || 0;
            
            wrapper.scrollTo({ left: position, behavior: 'smooth' });
            
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
                e.preventDefault();
                break;
            case 'ArrowRight':
                wrapper.scrollBy({ left: 100, behavior: 'smooth' });
                e.preventDefault();
                break;
            case 'Home':
                wrapper.scrollTo({ left: 0, behavior: 'smooth' });
                e.preventDefault();
                break;
            case 'End':
                wrapper.scrollTo({ left: wrapper.scrollWidth, behavior: 'smooth' });
                e.preventDefault();
                break;
        }
    });
    
    // Horizontal scroll with Shift + Mouse Wheel
    wrapper.addEventListener('wheel', function(e) {
        if (e.shiftKey) {
            e.preventDefault();
            wrapper.scrollLeft += e.deltaY;
        }
    }, { passive: false });
    
    // Initial state
    updateScrollPosition();
    
    // Checkbox functionality
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.submission-checkbox');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }
    
    if (checkboxes.length > 0) {
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
                if (selectAll) selectAll.checked = allChecked;
            });
        });
    }

    // Inline credential editing
    document.querySelectorAll('.editable-credential input').forEach(input => {
        input.addEventListener('blur', function() {
            const container = this.closest('.editable-credential');
            const submissionId = container.dataset.submission;
            const inputs = container.querySelectorAll('input');
            const username = inputs[0].value;
            const password = inputs[1].value;
            const fieldUser = container.dataset.fieldUser;
            const fieldPass = container.dataset.fieldPass;

            // Save both username and password
            saveCredential(submissionId, fieldUser, username);
            saveCredential(submissionId, fieldPass, password);
        });
    });

    // Link publish editing
    document.querySelectorAll('input[data-field="link_publish"]').forEach(input => {
        input.addEventListener('blur', function() {
            // Check if input is readonly (locked because of validation)
            if (this.hasAttribute('readonly')) {
                alert('Link publish terkunci karena sudah divalidasi. Matikan validasi Production terlebih dahulu.');
                return;
            }
            
            const submissionId = this.dataset.submission;
            const value = this.value;
            saveCredential(submissionId, 'link_publish', value);
        });
    });

    // Validation toggle
    document.querySelectorAll('.validation-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const submissionId = this.dataset.submission;
            const field = this.dataset.field;
            const current = this.dataset.current === '1';
            const newValue = !current;
            
            // Check if previous stages are valid (sequential validation)
            // EXCEPTION 1: Reviewer 1 and Reviewer 2 can work in parallel
            // EXCEPTION 2: Editor 3 and Author 2 are OPTIONAL (can be skipped)
            const row = this.closest('tr');
            const stageOrder = ['editor1_valid', 'author1_valid', 'editor2_valid', 'reviewer1_valid', 'reviewer2_valid', 'editor3_valid', 'author2_valid', 'production_valid', 'validator_valid'];
            const currentStageIndex = stageOrder.indexOf(field);
            
            // Check all previous stages
            for (let i = 0; i < currentStageIndex; i++) {
                const previousStage = stageOrder[i];
                
                // SPECIAL CASE 1: Reviewer 1 and Reviewer 2 work in parallel
                // If current stage is reviewer2, skip reviewer1 validation check
                if (field === 'reviewer2_valid' && previousStage === 'reviewer1_valid') {
                    continue;
                }
                // If current stage is reviewer1, skip reviewer2 validation check (shouldn't happen but for safety)
                if (field === 'reviewer1_valid' && previousStage === 'reviewer2_valid') {
                    continue;
                }
                
                // SPECIAL CASE 2: Editor 3 and Author 2 are OPTIONAL
                // Production can skip editor3 and author2 validation
                if (field === 'production_valid') {
                    if (previousStage === 'editor3_valid' || previousStage === 'author2_valid') {
                        continue;
                    }
                }

                // Author 2 can skip Editor 3 validation
                if (field === 'author2_valid' && previousStage === 'editor3_valid') {
                    continue;
                }

                // SPECIAL CASE 3: Validator - Editor 3 and Author 2 tidak wajib selesai
                if (field === 'validator_valid') {
                    if (previousStage === 'editor3_valid' || previousStage === 'author2_valid') {
                        continue;
                    }
                }

                // Convert snake_case to camelCase for dataset access: editor1_valid -> editor1Valid
                const dataAttr = previousStage.replace(/_([a-z])/g, (match, letter) => letter.toUpperCase());
                const previousValid = row.dataset[dataAttr] === '1';

                if (!previousValid) {
                    const stageNames = {
                        'editor1_valid': 'Editor 1',
                        'author1_valid': 'Author 1',
                        'editor2_valid': 'Editor 2',
                        'reviewer1_valid': 'Reviewer 1',
                        'reviewer2_valid': 'Reviewer 2',
                        'editor3_valid': 'Editor 3',
                        'author2_valid': 'Author 2',
                        'production_valid': 'Production',
                        'validator_valid': 'Validator'
                    };

                    alert('Proses sebelumnya (' + stageNames[previousStage] + ') belum valid. Harap tunggu validasi dari tahap sebelumnya.');
                    return;
                }
            }

            // SPECIAL VALIDATION: Editor 3 requires BOTH Reviewer 1 AND Reviewer 2 to be completed
            if (field === 'editor3_valid') {
                const reviewer1Valid = row.dataset.reviewer1Valid === '1';
                const reviewer2Valid = row.dataset.reviewer2Valid === '1';

                if (!reviewer1Valid || !reviewer2Valid) {
                    alert('Editor 3 hanya bisa diproses setelah Reviewer 1 DAN Reviewer 2 selesai.');
                    return;
                }
            }

            // SPECIAL VALIDATION: Production requires BOTH Reviewer 1 AND Reviewer 2 (minimum)
            if (field === 'production_valid') {
                const reviewer1Valid = row.dataset.reviewer1Valid === '1';
                const reviewer2Valid = row.dataset.reviewer2Valid === '1';

                if (!reviewer1Valid || !reviewer2Valid) {
                    alert('Production minimal memerlukan Reviewer 1 DAN Reviewer 2 selesai. Editor 3 dan Author 2 bersifat opsional.');
                    return;
                }
            }

            // SPECIAL VALIDATION: Validator wajib ada link publikasi
            if (field === 'validator_valid' && newValue) {
                const hasLink = row.dataset.hasLinkPublish === '1';
                // Cek juga input langsung kalau ada (production PIC)
                const linkInput = row.querySelector('input[data-field="link_publish"]');
                const linkFilled = hasLink || (linkInput && linkInput.value.trim() !== '');
                if (!linkFilled) {
                    alert('Link publikasi harus diisi terlebih dahulu sebelum melakukan validasi akhir.');
                    return;
                }
            }

            fetch('{{ route("pic.submissions.toggle-validation") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    submission_id: submissionId,
                    field: field,
                    value: newValue
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button appearance
                    this.dataset.current = newValue ? '1' : '0';
                    this.className = newValue ? 'btn btn-sm validation-toggle btn-success' : 'btn btn-sm validation-toggle btn-outline-secondary';
                    this.querySelector('i').className = newValue ? 'bi bi-check-circle-fill' : 'bi bi-circle';
                    
                    // Update row data attribute (convert snake_case to camelCase)
                    const dataAttr = field.replace(/_([a-z])/g, (match, letter) => letter.toUpperCase());
                    row.dataset[dataAttr] = newValue ? '1' : '0';
                    
                    // Toggle link_publish input readonly status when production_valid is toggled
                    if (field === 'production_valid') {
                        const linkInput = row.querySelector('input[data-field="link_publish"]');
                        if (linkInput) {
                            if (newValue) {
                                linkInput.setAttribute('readonly', true);
                                linkInput.classList.add('bg-light');
                                linkInput.title = 'Link terkunci. Matikan validasi untuk mengedit.';
                            } else {
                                linkInput.removeAttribute('readonly');
                                linkInput.classList.remove('bg-light');
                                linkInput.title = 'Masukkan link publish';
                            }
                        }
                    }
                } else {
                    alert('Gagal update validasi: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat update validasi');
            });
        });
    });

    function saveCredential(submissionId, field, value) {
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
            if (!data.success) {
                alert('Gagal menyimpan: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan');
        });
    }
});
</script>
@endsection
