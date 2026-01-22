@extends('pic.layouts.app')

@section('title', 'Monitoring & Tugas Saya')
@section('page-title', 'Monitoring & Tugas Saya')

@section('sidebar-class', 'auto-collapse')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<style>
/* Sticky Table Styles for Monitoring */
.monitoring-scroll-wrapper {
    overflow-x: auto;
    overflow-y: visible;
    max-height: 70vh;
    scrollbar-width: thin;
    scrollbar-color: #6c757d #dee2e6;
}

.monitoring-scroll-wrapper::-webkit-scrollbar {
    height: 14px;
    width: 14px;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-track {
    background: #dee2e6;
    border-radius: 7px;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #6c757d, #495057);
    border-radius: 7px;
    border: 2px solid #dee2e6;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #495057, #343a40);
}

.monitoring-scroll-wrapper::-webkit-scrollbar-corner {
    background: #dee2e6;
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
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.8rem;
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
.table-monitoring th.sticky-first,
.table-monitoring td.sticky-first {
    position: sticky;
    left: 0;
    z-index: 2;
    background: #fff;
    min-width: 120px;
    box-shadow: 3px 0 6px -3px rgba(0,0,0,0.15);
}

.table-monitoring thead th.sticky-first {
    z-index: 5;
    background: #212529 !important;
}

/* Sticky second column (ID Artikel) */
.table-monitoring th.sticky-second,
.table-monitoring td.sticky-second {
    position: sticky;
    left: 120px;
    z-index: 2;
    background: #fff;
    min-width: 100px;
    box-shadow: 3px 0 6px -3px rgba(0,0,0,0.15);
}

.table-monitoring thead th.sticky-second {
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

.table-monitoring tbody tr:hover td.sticky-first,
.table-monitoring tbody tr:hover td.sticky-second {
    background-color: #e8f4fd !important;
}

/* Alternating row colors */
.table-monitoring tbody tr:nth-child(even) td {
    background-color: #f8f9fa;
}

.table-monitoring tbody tr:nth-child(even) td.sticky-first,
.table-monitoring tbody tr:nth-child(even) td.sticky-second {
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
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="myTasksFilter" name="my_tasks" value="1" {{ request('my_tasks') ? 'checked' : '' }} onchange="this.form.submit()" form="filterForm">
                    <label class="form-check-label" for="myTasksFilter">
                        <strong>Tugas Saya</strong>
                    </label>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <form action="{{ route('pic.submissions.monitoring') }}" method="GET" class="mb-4" id="filterForm">
                    <input type="hidden" name="my_tasks" value="{{ request('my_tasks') ? '1' : '' }}" id="myTasksHidden">
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
                                <th colspan="2" class="text-center bg-info" id="colEditor2">Editor 2</th>
                                <th colspan="4" class="text-center bg-primary" id="colReviewer1">Reviewer 1</th>
                                <th colspan="4" class="text-center bg-primary" id="colReviewer2">Reviewer 2</th>
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
                                <!-- Production sub-headers (3 cols) -->
                                <th class="bg-success">Petugas</th>
                                <th class="bg-success">Link Publish</th>
                                <th class="bg-success">Valid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $s)
                            <tr>
                                <td class="sticky-first">
                                    <a href="{{ route('pic.submissions.show', $s) }}" class="text-decoration-none" title="Klik untuk detail">
                                        <code class="text-primary">{{ $s->kode_submit }}</code>
                                    </a>
                                </td>
                                <td class="sticky-second">{{ $s->id_artikel }}</td>
                                <td title="{{ $s->judul_artikel }}">{{ Str::limit($s->judul_artikel, 25) }}</td>
                                <td class="text-center">
                                    @if($s->link_artikel)
                                        <a href="{{ $s->link_artikel }}" target="_blank"><i class="bi bi-link-45deg"></i></a>
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
                                <td>
                                    <select class="inline-assign-select {{ $s->marketing_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="marketing_id"
                                            onchange="updatePetugas(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($marketings as $mkt)
                                            <option value="{{ $mkt->id }}" {{ $s->marketing_id == $mkt->id ? 'selected' : '' }}>{{ $mkt->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="inline-assign-select {{ $s->petugas_submit_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="petugas_submit_id"
                                            onchange="updatePetugas(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $pic)
                                            <option value="{{ $pic->id }}" {{ $s->petugas_submit_id == $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                
                                <!-- Editor 1 -->
                                <td>
                                    <select class="inline-assign-select {{ $s->petugas_editor1_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="petugas_editor1_id"
                                            onchange="updatePetugas(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $pic)
                                            <option value="{{ $pic->id }}" {{ $s->petugas_editor1_id == $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="credential-group">
                                        <input type="text" class="inline-credential-input {{ $s->username_editor ? 'has-value' : '' }}" 
                                               value="{{ $s->username_editor }}" 
                                               placeholder="user"
                                               data-submission="{{ $s->id }}" 
                                               data-field="username_editor"
                                               onchange="updateCredential(this)">
                                        <span>/</span>
                                        <input type="text" class="inline-credential-input {{ $s->password_editor ? 'has-value' : '' }}" 
                                               value="{{ $s->password_editor }}" 
                                               placeholder="pass"
                                               data-submission="{{ $s->id }}" 
                                               data-field="password_editor"
                                               onchange="updateCredential(this)">
                                    </div>
                                </td>
                                <td class="text-center">{!! $s->editor1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Author 1 -->
                                <td>
                                    <select class="inline-assign-select {{ $s->petugas_author1_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="petugas_author1_id"
                                            onchange="updatePetugas(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $pic)
                                            <option value="{{ $pic->id }}" {{ $s->petugas_author1_id == $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">{!! $s->author1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Editor 2 -->
                                <td>
                                    <select class="inline-assign-select {{ $s->petugas_editor2_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="petugas_editor2_id"
                                            onchange="updatePetugas(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $pic)
                                            <option value="{{ $pic->id }}" {{ $s->petugas_editor2_id == $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">{!! $s->editor2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Reviewer 1 -->
                                <td>
                                    <select class="inline-assign-select {{ $s->petugas_reviewer1_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="petugas_reviewer1_id"
                                            onchange="updatePetugas(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $pic)
                                            <option value="{{ $pic->id }}" {{ $s->petugas_reviewer1_id == $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="credential-group">
                                        <input type="text" class="inline-credential-input {{ $s->username_reviewer1 ? 'has-value' : '' }}" 
                                               value="{{ $s->username_reviewer1 }}" 
                                               placeholder="user"
                                               data-submission="{{ $s->id }}" 
                                               data-field="username_reviewer1"
                                               onchange="updateCredential(this)">
                                        <span>/</span>
                                        <input type="text" class="inline-credential-input {{ $s->password_reviewer1 ? 'has-value' : '' }}" 
                                               value="{{ $s->password_reviewer1 }}" 
                                               placeholder="pass"
                                               data-submission="{{ $s->id }}" 
                                               data-field="password_reviewer1"
                                               onchange="updateCredential(this)">
                                    </div>
                                </td>
                                <td title="{{ $s->catatan_reviewer1 }}">{{ Str::limit($s->catatan_reviewer1, 15) ?? '-' }}</td>
                                <td class="text-center">{!! $s->reviewer1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Reviewer 2 -->
                                <td>
                                    <select class="inline-assign-select {{ $s->petugas_reviewer2_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="petugas_reviewer2_id"
                                            onchange="updatePetugas(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $pic)
                                            <option value="{{ $pic->id }}" {{ $s->petugas_reviewer2_id == $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="credential-group">
                                        <input type="text" class="inline-credential-input {{ $s->username_reviewer2 ? 'has-value' : '' }}" 
                                               value="{{ $s->username_reviewer2 }}" 
                                               placeholder="user"
                                               data-submission="{{ $s->id }}" 
                                               data-field="username_reviewer2"
                                               onchange="updateCredential(this)">
                                        <span>/</span>
                                        <input type="text" class="inline-credential-input {{ $s->password_reviewer2 ? 'has-value' : '' }}" 
                                               value="{{ $s->password_reviewer2 }}" 
                                               placeholder="pass"
                                               data-submission="{{ $s->id }}" 
                                               data-field="password_reviewer2"
                                               onchange="updateCredential(this)">
                                    </div>
                                </td>
                                <td title="{{ $s->catatan_reviewer2 }}">{{ Str::limit($s->catatan_reviewer2, 15) ?? '-' }}</td>
                                <td class="text-center">{!! $s->reviewer2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Editor 3 -->
                                <td>
                                    <select class="inline-assign-select {{ $s->petugas_editor3_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="petugas_editor3_id"
                                            onchange="updatePetugas(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $pic)
                                            <option value="{{ $pic->id }}" {{ $s->petugas_editor3_id == $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">{!! $s->editor3_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Author 2 -->
                                <td>
                                    <select class="inline-assign-select {{ $s->petugas_author2_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="petugas_author2_id"
                                            onchange="updatePetugas(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $pic)
                                            <option value="{{ $pic->id }}" {{ $s->petugas_author2_id == $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">{!! $s->author2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Production -->
                                <td>
                                    <select class="inline-assign-select {{ $s->petugas_production_id ? 'has-value' : '' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-field="petugas_production_id"
                                            onchange="updatePetugas(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $pic)
                                            <option value="{{ $pic->id }}" {{ $s->petugas_production_id == $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    @if($s->link_publish)
                                        <a href="{{ $s->link_publish }}" target="_blank"><i class="bi bi-link-45deg"></i></a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">{!! $s->production_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="30" class="text-center text-muted py-4">
                                    Tidak ada data
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
// Update Petugas function for inline dropdown
function updatePetugas(selectEl) {
    const submissionId = selectEl.dataset.submission;
    const field = selectEl.dataset.field;
    const value = selectEl.value;
    
    selectEl.classList.add('saving');
    
    fetch('{{ route("pic.submissions.update-petugas") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            submission_id: submissionId,
            field: field,
            value: value || null
        })
    })
    .then(response => response.json())
    .then(data => {
        selectEl.classList.remove('saving');
        if (data.success) {
            if (value) {
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

// Update Credential function for inline input
function updateCredential(inputEl) {
    const submissionId = inputEl.dataset.submission;
    const field = inputEl.dataset.field;
    const value = inputEl.value.trim();
    
    inputEl.classList.add('saving');
    
    fetch('{{ route("pic.submissions.update-credential") }}', {
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
    
    // My Tasks filter checkbox
    const myTasksFilter = document.getElementById('myTasksFilter');
    const myTasksHidden = document.getElementById('myTasksHidden');
    
    if (myTasksFilter) {
        myTasksFilter.addEventListener('change', function() {
            myTasksHidden.value = this.checked ? '1' : '';
            document.getElementById('filterForm').submit();
        });
    }
});
</script>
@endsection
