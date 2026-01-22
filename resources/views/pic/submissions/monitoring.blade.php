@extends('pic.layouts.app')

@section('title', 'Tugas Saya')
@section('page-title', 'Tugas Saya')

@section('sidebar-class', 'auto-collapse')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<style>
/* Sticky Table Styles for Monitoring */
.monitoring-scroll-wrapper {
    overflow-x: scroll !important;
    overflow-y: auto;
    max-height: calc(100vh - 350px);
    min-height: 400px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    position: relative;
    /* Force scrollbar to always show */
    -ms-overflow-style: scrollbar;
}

/* Custom scrollbar for Webkit browsers (Chrome, Edge, Safari) */
.monitoring-scroll-wrapper::-webkit-scrollbar {
    height: 20px !important;
    width: 14px;
    background: #dee2e6;
    display: block !important;
    visibility: visible !important;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-track {
    background: linear-gradient(180deg, #e9ecef 0%, #dee2e6 100%);
    border-radius: 0;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.15);
    border-top: 1px solid #ced4da;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #0d6efd, #0b5ed7);
    border-radius: 10px;
    border: 4px solid #dee2e6;
    min-width: 100px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #0b5ed7, #0a58ca);
    cursor: grab;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb:active {
    cursor: grabbing;
}

/* Firefox scrollbar */
@supports (-moz-appearance:none) {
    .monitoring-scroll-wrapper {
        scrollbar-width: auto;
        scrollbar-color: #0d6efd #dee2e6;
    }
}

.monitoring-scroll-wrapper::-webkit-scrollbar-corner {
    background: #dee2e6;
}

/* Credential display */
.credential-group {
    display: flex;
    gap: 2px;
    align-items: center;
}

.credential-group code {
    font-size: 0.7rem;
    padding: 2px 4px;
    background: #f8f9fa;
    border-radius: 3px;
}

/* Validation checkbox button */
.validation-btn {
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 4px;
    transition: all 0.2s;
    border: none;
    background: transparent;
}
.validation-btn:hover {
    background: #e9ecef;
    transform: scale(1.2);
}
.validation-btn.saving {
    opacity: 0.5;
    pointer-events: none;
}
.validation-btn i {
    font-size: 1rem;
}

.table-monitoring {
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.8rem;
    min-width: 2500px; /* Force horizontal scroll */
    width: max-content;
}

.table-monitoring thead th {
    position: sticky;
    top: 0;
    z-index: 3;
    background: #212529 !important;
    color: white !important;
    border: 1px solid #343a40;
    white-space: nowrap;
    padding: 6px 8px;
}

.table-monitoring thead tr:nth-child(2) th {
    top: 38px;
    background: #343a40 !important;
    color: white !important;
}

/* Override Bootstrap bg-* classes in header to ensure white text */
.table-monitoring thead th.bg-info,
.table-monitoring thead th.bg-warning,
.table-monitoring thead th.bg-primary,
.table-monitoring thead th.bg-success {
    color: white !important;
}

.table-monitoring thead th.text-dark {
    color: white !important;
}

/* Sticky first column (Kode Submit) */
.table-monitoring th.sticky-col-1,
.table-monitoring td.sticky-col-1 {
    position: sticky;
    left: 0;
    z-index: 2;
    background: #fff;
    min-width: 120px;
    max-width: 120px;
}

.table-monitoring thead th.sticky-col-1 {
    z-index: 5;
    background: #212529 !important;
}

/* Sticky second column (ID Artikel) */
.table-monitoring th.sticky-col-2,
.table-monitoring td.sticky-col-2 {
    position: sticky;
    left: 120px;
    z-index: 2;
    background: #fff;
    min-width: 80px;
    max-width: 80px;
}

.table-monitoring thead th.sticky-col-2 {
    z-index: 5;
    background: #212529 !important;
}

/* Sticky third column (Judul) */
.table-monitoring th.sticky-col-3,
.table-monitoring td.sticky-col-3 {
    position: sticky;
    left: 200px;
    z-index: 2;
    background: #fff;
    min-width: 150px;
    max-width: 150px;
}

.table-monitoring thead th.sticky-col-3 {
    z-index: 5;
    background: #212529 !important;
}

/* Sticky fourth column (Link) */
.table-monitoring th.sticky-col-4,
.table-monitoring td.sticky-col-4 {
    position: sticky;
    left: 350px;
    z-index: 2;
    background: #fff;
    min-width: 50px;
    max-width: 50px;
}

.table-monitoring thead th.sticky-col-4 {
    z-index: 5;
    background: #212529 !important;
}

/* Sticky fifth column (Penulis) */
.table-monitoring th.sticky-col-5,
.table-monitoring td.sticky-col-5 {
    position: sticky;
    left: 400px;
    z-index: 2;
    background: #fff;
    min-width: 100px;
    max-width: 100px;
}

.table-monitoring thead th.sticky-col-5 {
    z-index: 5;
    background: #212529 !important;
}

/* Sticky sixth column (No HP) - last sticky with shadow */
.table-monitoring th.sticky-col-6,
.table-monitoring td.sticky-col-6 {
    position: sticky;
    left: 500px;
    z-index: 2;
    background: #fff;
    min-width: 60px;
    max-width: 60px;
    box-shadow: 3px 0 6px -3px rgba(0,0,0,0.2);
}

.table-monitoring thead th.sticky-col-6 {
    z-index: 5;
    background: #212529 !important;
}

.table-monitoring tbody td {
    white-space: nowrap;
    padding: 5px 8px;
    border: 1px solid #dee2e6;
}

.table-monitoring tbody tr:hover td {
    background-color: #e8f4fd !important;
}

.table-monitoring tbody tr:hover td.sticky-col-1,
.table-monitoring tbody tr:hover td.sticky-col-2,
.table-monitoring tbody tr:hover td.sticky-col-3,
.table-monitoring tbody tr:hover td.sticky-col-4,
.table-monitoring tbody tr:hover td.sticky-col-5,
.table-monitoring tbody tr:hover td.sticky-col-6 {
    background-color: #e8f4fd !important;
}

/* Alternating row colors */
.table-monitoring tbody tr:nth-child(even) td {
    background-color: #f8f9fa;
}

.table-monitoring tbody tr:nth-child(even) td.sticky-col-1,
.table-monitoring tbody tr:nth-child(even) td.sticky-col-2,
.table-monitoring tbody tr:nth-child(even) td.sticky-col-3,
.table-monitoring tbody tr:nth-child(even) td.sticky-col-4,
.table-monitoring tbody tr:nth-child(even) td.sticky-col-5,
.table-monitoring tbody tr:nth-child(even) td.sticky-col-6 {
    background-color: #f8f9fa;
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
</style>

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
                        <h6 class="card-subtitle mb-1">Baru</h6>
                        <h2 class="card-title mb-0">{{ $stats['new'] }}</h2>
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
                        <h2 class="card-title mb-0">{{ $stats['in_progress'] }}</h2>
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
                <span><i class="bi bi-bar-chart"></i> Monitoring Proses Submit</span>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <form action="{{ route('pic.submissions.monitoring') }}" method="GET" class="mb-4" id="filterForm">
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
                            <label for="journal_id" class="form-label small mb-1">Jurnal</label>
                            <select class="form-select form-select-sm" id="journal_id" name="journal_id">
                                <option value="">-- Semua --</option>
                                @foreach($journals as $journal)
                                    <option value="{{ $journal->id }}" {{ request('journal_id') == $journal->id ? 'selected' : '' }}>
                                        {{ Str::limit($journal->nama_jurnal, 20) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label small mb-1">Status</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="">-- Semua --</option>
                                <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                                <option value="EDITOR1" {{ request('status') == 'EDITOR1' ? 'selected' : '' }}>Editor 1</option>
                                <option value="AUTHOR1" {{ request('status') == 'AUTHOR1' ? 'selected' : '' }}>Author 1</option>
                                <option value="EDITOR2" {{ request('status') == 'EDITOR2' ? 'selected' : '' }}>Editor 2</option>
                                <option value="REVIEWER1" {{ request('status') == 'REVIEWER1' ? 'selected' : '' }}>Reviewer 1</option>
                                <option value="REVIEWER2" {{ request('status') == 'REVIEWER2' ? 'selected' : '' }}>Reviewer 2</option>
                                <option value="EDITOR3" {{ request('status') == 'EDITOR3' ? 'selected' : '' }}>Editor 3</option>
                                <option value="AUTHOR2" {{ request('status') == 'AUTHOR2' ? 'selected' : '' }}>Author 2</option>
                                <option value="PRODUCTION" {{ request('status') == 'PRODUCTION' ? 'selected' : '' }}>Production</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>
                        <div class="col-md-4">
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
                    </div>
                </div>

                <!-- Data Table with Full Process Columns -->
                <div class="monitoring-scroll-wrapper" id="monitoringScrollWrapper">
                    <table class="table table-monitoring table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th rowspan="2" class="align-middle sticky-col-1">Kode Submit</th>
                                <th rowspan="2" class="align-middle sticky-col-2">ID Artikel</th>
                                <th rowspan="2" class="align-middle sticky-col-3">Judul</th>
                                <th rowspan="2" class="align-middle sticky-col-4">Link</th>
                                <th rowspan="2" class="align-middle sticky-col-5">Penulis</th>
                                <th rowspan="2" class="align-middle sticky-col-6">No HP</th>
                                <th colspan="2" class="text-center">Author Access</th>
                                <th rowspan="2" class="align-middle">PIC Marketing</th>
                                <th rowspan="2" class="align-middle" id="colSubmit">Petugas Submit</th>
                                <th colspan="3" class="text-center bg-info" id="colEditor1">Editor 1</th>
                                <th colspan="2" class="text-center bg-warning text-dark" id="colAuthor1">Author 1</th>
                                <th colspan="2" class="text-center bg-info" id="colEditor2">Editor 2</th>
                                <th colspan="3" class="text-center bg-primary" id="colReviewer1">Reviewer 1</th>
                                <th colspan="3" class="text-center bg-primary" id="colReviewer2">Reviewer 2</th>
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
                                <!-- Editor 2 sub-headers (2 cols) -->
                                <th class="bg-info">Petugas</th>
                                <th class="bg-info">Valid</th>
                                <!-- Reviewer 1 sub-headers (3 cols) -->
                                <th class="bg-primary">Petugas</th>
                                <th class="bg-primary">User/Pass</th>
                                <th class="bg-primary">Valid</th>
                                <!-- Reviewer 2 sub-headers (3 cols) -->
                                <th class="bg-primary">Petugas</th>
                                <th class="bg-primary">User/Pass</th>
                                <th class="bg-primary">Valid</th>
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
                            @forelse($submissions as $s)
                            <tr>
                                <td class="sticky-col-1">
                                    <a href="{{ route('pic.submissions.show', $s) }}" class="text-decoration-none" title="Klik untuk detail">
                                        <code class="text-primary">{{ $s->kode_submit }}</code>
                                    </a>
                                </td>
                                <td class="sticky-col-2">{{ $s->id_artikel }}</td>
                                <td class="sticky-col-3" title="{{ $s->judul_artikel }}">{{ Str::limit($s->judul_artikel, 20) }}</td>
                                <td class="sticky-col-4 text-center">
                                    @if($s->link_artikel)
                                        <a href="{{ $s->link_artikel }}" target="_blank"><i class="bi bi-link-45deg"></i></a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="sticky-col-5">{{ Str::limit($s->nama_penulis, 12) }}</td>
                                <td class="sticky-col-6">
                                    @if($s->no_hp_penulis)
                                        @php
                                            $waNumber = preg_replace('/[^0-9]/', '', $s->no_hp_penulis);
                                            if (substr($waNumber, 0, 1) === '0') {
                                                $waNumber = '62' . substr($waNumber, 1);
                                            }
                                            $waMessage = "Salam sejahtera untuk author bernama *{$s->nama_penulis}*\n\n";
                                            $waMessage .= "Dengan kode submit: *{$s->kode_submit}*\n";
                                            $waMessage .= "Link artikel: {$s->link_artikel}\n";
                                            $waMessage .= "Kode LOA: *{$s->kode_loa}*\n\n";
                                            $waMessage .= "User: `{$s->username_author}`\n";
                                            $waMessage .= "Pass: `{$s->password_author}`\n\n";
                                            $waMessage .= "Sedang dalam proses *{$s->status}*";
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
                                <td>{{ $s->petugasEditor1?->name ?? '-' }}</td>
                                <td>
                                    <div class="credential-group">
                                        <code>{{ $s->username_editor ?? '-' }}</code>
                                        <span>/</span>
                                        <code>{{ $s->password_editor ?? '-' }}</code>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="validation-btn" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="editor1_valid"
                                            data-current="{{ $s->editor1_valid ? '1' : '0' }}"
                                            onclick="toggleValidation(this)">
                                        {!! $s->editor1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>
                                
                                <!-- Author 1 -->
                                <td>{{ $s->petugasAuthor1?->name ?? '-' }}</td>
                                <td class="text-center">
                                    <button type="button" class="validation-btn" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="author1_valid"
                                            data-current="{{ $s->author1_valid ? '1' : '0' }}"
                                            onclick="toggleValidation(this)">
                                        {!! $s->author1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>
                                
                                <!-- Editor 2 -->
                                <td>{{ $s->petugasEditor2?->name ?? '-' }}</td>
                                <td class="text-center">
                                    <button type="button" class="validation-btn" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="editor2_valid"
                                            data-current="{{ $s->editor2_valid ? '1' : '0' }}"
                                            onclick="toggleValidation(this)">
                                        {!! $s->editor2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>
                                
                                <!-- Reviewer 1 -->
                                <td>{{ $s->petugasReviewer1?->name ?? '-' }}</td>
                                <td>
                                    <div class="credential-group">
                                        <code>{{ $s->username_reviewer1 ?? '-' }}</code>
                                        <span>/</span>
                                        <code>{{ $s->password_reviewer1 ?? '-' }}</code>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="validation-btn" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="reviewer1_valid"
                                            data-current="{{ $s->reviewer1_valid ? '1' : '0' }}"
                                            onclick="toggleValidation(this)">
                                        {!! $s->reviewer1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>
                                
                                <!-- Reviewer 2 -->
                                <td>{{ $s->petugasReviewer2?->name ?? '-' }}</td>
                                <td>
                                    <div class="credential-group">
                                        <code>{{ $s->username_reviewer2 ?? '-' }}</code>
                                        <span>/</span>
                                        <code>{{ $s->password_reviewer2 ?? '-' }}</code>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="validation-btn" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="reviewer2_valid"
                                            data-current="{{ $s->reviewer2_valid ? '1' : '0' }}"
                                            onclick="toggleValidation(this)">
                                        {!! $s->reviewer2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>
                                
                                <!-- Editor 3 -->
                                <td>{{ $s->petugasEditor3?->name ?? '-' }}</td>
                                <td class="text-center">
                                    <button type="button" class="validation-btn" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="editor3_valid"
                                            data-current="{{ $s->editor3_valid ? '1' : '0' }}"
                                            onclick="toggleValidation(this)">
                                        {!! $s->editor3_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>
                                
                                <!-- Author 2 -->
                                <td>{{ $s->petugasAuthor2?->name ?? '-' }}</td>
                                <td class="text-center">
                                    <button type="button" class="validation-btn" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="author2_valid"
                                            data-current="{{ $s->author2_valid ? '1' : '0' }}"
                                            onclick="toggleValidation(this)">
                                        {!! $s->author2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>
                                
                                <!-- Production -->
                                <td>{{ $s->petugasProduction?->name ?? '-' }}</td>
                                <td>
                                    @if($s->link_publish)
                                        <a href="{{ $s->link_publish }}" target="_blank"><i class="bi bi-link-45deg"></i></a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="validation-btn" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="production_valid"
                                            data-current="{{ $s->production_valid ? '1' : '0' }}"
                                            onclick="toggleValidation(this)">
                                        {!! $s->production_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="28" class="text-center text-muted py-4">
                                    Tidak ada data tugas yang ditugaskan kepada Anda
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
    </div>
</div>

<script>
// Toggle Validation function
function toggleValidation(btn) {
    const submissionId = btn.dataset.submission;
    const field = btn.dataset.field;
    const currentValue = btn.dataset.current === '1';
    
    // Convert field name to stage (e.g., 'editor1_valid' -> 'editor1')
    const stage = field.replace('_valid', '');
    
    btn.classList.add('saving');
    
    fetch('{{ route("pic.submissions.toggle-valid") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            submission_id: submissionId,
            stage: stage
        })
    })
    .then(response => response.json())
    .then(data => {
        btn.classList.remove('saving');
        if (data.success) {
            const newValue = data.is_valid;
            btn.dataset.current = newValue ? '1' : '0';
            if (newValue) {
                btn.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
            } else {
                btn.innerHTML = '<i class="bi bi-circle text-muted"></i>';
            }
        } else {
            alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(error => {
        btn.classList.remove('saving');
        console.error('Error:', error);
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
        'production': 2150
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
    
    // Initial state
    updateScrollPosition();
});
</script>
@endsection
