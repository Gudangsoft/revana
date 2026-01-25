@extends('layouts.app')

@section('title', 'Monitoring Fasttrack - ' . $appSettings['app_name'])
@section('page-title', 'Monitoring Fasttrack')

@section('sidebar')
    @include('admin.partials.sidebar')
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

/* Inline credential input */
.credential-group {
    display: flex;
    gap: 2px;
    align-items: center;
}

/* Inline assignment select styles */
.inline-assign-select {
    min-width: 100px;
    max-width: 130px;
    padding: 2px 4px;
    font-size: 0.7rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    background-color: #fff;
}

.inline-assign-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.inline-assign-select.saving {
    opacity: 0.7;
    pointer-events: none;
}

.inline-assign-select.success {
    border-color: #28a745;
    background-color: #d4edda;
}

.inline-assign-select.error {
    border-color: #dc3545;
    background-color: #f8d7da;
}

/* Inline credential input styles */
.inline-credential-input {
    width: 60px;
    padding: 2px 4px;
    font-size: 0.7rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
}

.inline-credential-input:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.inline-credential-input.success {
    border-color: #28a745;
    background-color: #d4edda;
}

.inline-credential-input.error {
    border-color: #dc3545;
    background-color: #f8d7da;
}

.table-monitoring {
    border-collapse: collapse;
    border-spacing: 0;
    font-size: 0.8rem;
}

.table-monitoring thead th {
    position: sticky;
    top: 0;
    z-index: 3;
    background: #212529 !important;
    color: white !important;
    border: 1px solid #212529 !important;
    white-space: nowrap;
    padding: 6px 8px;
}

.table-monitoring thead tr:nth-child(2) th {
    top: 32px;
    background: #343a40 !important;
    color: white !important;
    border: 1px solid #343a40 !important;
}

/* Override Bootstrap bg-* classes in header */
.table-monitoring thead th.bg-info,
.table-monitoring thead th.bg-warning,
.table-monitoring thead th.bg-primary,
.table-monitoring thead th.bg-success,
.table-monitoring thead th.bg-dark {
    color: white !important;
    border-color: #212529 !important;
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
    background: linear-gradient(90deg, #ffc107, #fd7e14);
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
    background: #ffc107;
    color: #000;
    border-color: #ffc107;
}
</style>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Total Fasttrack</h6>
                        <h2 class="card-title mb-0">{{ $submissions->total() }}</h2>
                    </div>
                    <i class="bi bi-lightning-charge fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Published</h6>
                        <h2 class="card-title mb-0">{{ $submissions->total() }}</h2>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Proses Langsung</h6>
                        <h2 class="card-title mb-0">100%</h2>
                    </div>
                    <i class="bi bi-speedometer2 fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-lightning-charge text-warning"></i> Monitoring Fasttrack (Proses Cepat)</span>
                <div class="btn-group">
                    <a href="{{ route('admin.fasttrack.create') }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-plus-circle"></i> Input Fasttrack
                    </a>
                    <a href="{{ route('admin.submissions.monitoring') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Ke Normal
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Info Box -->
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-lightning-charge"></i> <strong>Fasttrack</strong> = Artikel sudah publish, langsung tercatat tanpa proses workflow. 
                    Kolom proses dapat dikosongkan.
                </div>

                <!-- Filter -->
                <form action="{{ route('admin.fasttrack.index') }}" method="GET" class="mb-3">
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
                            <label for="search" class="form-label small mb-1">Cari</label>
                            <input type="text" class="form-control form-control-sm" name="search" placeholder="Kode/Judul/Penulis..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.fasttrack.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
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

                <!-- Table -->
                <div class="monitoring-scroll-wrapper" id="monitoringScrollWrapper">
                    <table class="table table-monitoring table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th rowspan="2" class="sticky-first text-center" style="min-width: 120px;">Kode Submit</th>
                                <th rowspan="2" class="sticky-second">ID Artikel</th>
                                <th rowspan="2">Judul</th>
                                <th rowspan="2">Link</th>
                                <th rowspan="2">Penulis</th>
                                <th rowspan="2">No HP</th>
                                <!-- Author Access / Submit -->
                                <th colspan="4" class="text-center bg-info" data-section="submit">Author Access</th>
                                <!-- Editor 1 -->
                                <th colspan="3" class="text-center bg-warning" data-section="editor1">Editor 1</th>
                                <!-- Author 1 -->
                                <th colspan="2" class="text-center bg-info" data-section="author1">Author 1</th>
                                <!-- Editor 2 -->
                                <th colspan="2" class="text-center bg-warning" data-section="editor2">Editor 2</th>
                                <!-- Reviewer 1 -->
                                <th colspan="4" class="text-center bg-primary" data-section="reviewer1">Reviewer 1</th>
                                <!-- Reviewer 2 -->
                                <th colspan="4" class="text-center bg-primary" data-section="reviewer2">Reviewer 2</th>
                                <!-- Editor 3 -->
                                <th colspan="2" class="text-center bg-warning" data-section="editor3">Editor 3</th>
                                <!-- Author 2 -->
                                <th colspan="2" class="text-center bg-info" data-section="author2">Author 2</th>
                                <!-- Production -->
                                <th colspan="3" class="text-center bg-success" data-section="production">Production</th>
                                <th rowspan="2" class="text-center">Aksi</th>
                            </tr>
                            <tr>
                                <!-- Author Access sub-headers -->
                                <th class="bg-info">PIC Marketing</th>
                                <th class="bg-info">Petugas Submit</th>
                                <th class="bg-info">Username</th>
                                <th class="bg-info">Password</th>
                                <!-- Editor 1 sub-headers -->
                                <th class="bg-warning">Petugas</th>
                                <th class="bg-warning">User/Pass</th>
                                <th class="bg-warning">Valid</th>
                                <!-- Author 1 sub-headers -->
                                <th class="bg-info">Petugas</th>
                                <th class="bg-info">Valid</th>
                                <!-- Editor 2 sub-headers -->
                                <th class="bg-warning">Petugas</th>
                                <th class="bg-warning">Valid</th>
                                <!-- Reviewer 1 sub-headers -->
                                <th class="bg-primary">Petugas</th>
                                <th class="bg-primary">User/Pass</th>
                                <th class="bg-primary">Catatan</th>
                                <th class="bg-primary">Valid</th>
                                <!-- Reviewer 2 sub-headers -->
                                <th class="bg-primary">Petugas</th>
                                <th class="bg-primary">User/Pass</th>
                                <th class="bg-primary">Catatan</th>
                                <th class="bg-primary">Valid</th>
                                <!-- Editor 3 sub-headers -->
                                <th class="bg-warning">Petugas</th>
                                <th class="bg-warning">Valid</th>
                                <!-- Author 2 sub-headers -->
                                <th class="bg-info">Petugas</th>
                                <th class="bg-info">Valid</th>
                                <!-- Production sub-headers -->
                                <th class="bg-success">Petugas</th>
                                <th class="bg-success">Link Publish</th>
                                <th class="bg-success">Valid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $s)
                            <tr>
                                <td class="sticky-first">
                                    <a href="{{ route('admin.fasttrack.show', $s) }}" class="text-decoration-none" title="Klik untuk detail">
                                        <code class="text-warning">{{ $s->kode_submit }}</code>
                                    </a>
                                    <span class="badge bg-warning text-dark ms-1"><i class="bi bi-lightning-charge"></i> FT</span>
                                    <br><span class="badge bg-success mt-1"><i class="bi bi-check-circle-fill"></i> SELESAI</span>
                                </td>
                                <td class="sticky-second">{{ $s->id_artikel ?? '-' }}</td>
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
                                            $waMessage = "Selamat Artikel anda sudah terpublikasi:\n\n";
                                            $waMessage .= "Kode artikel: *{$s->id_artikel}*\n";
                                            $waMessage .= "Nama Penulis: *{$s->nama_penulis}*\n";
                                            $waMessage .= "Link Publikasi: {$s->link_publish}\n\n";
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
                                
                                <!-- Author Access: PIC Marketing, Petugas Submit, Username, Password -->
                                <td>
                                    <select class="inline-assign-select" 
                                            onchange="quickAssignMarketing(this, {{ $s->id }})"
                                            data-original="{{ $s->marketing_id }}">
                                        <option value="">-- Pilih --</option>
                                        @foreach($marketings as $m)
                                            <option value="{{ $m->id }}" {{ $s->marketing_id == $m->id ? 'selected' : '' }}>
                                                {{ $m->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="inline-assign-select" 
                                            onchange="quickAssign(this, {{ $s->id }}, 'petugas_submit_id')"
                                            data-original="{{ $s->petugas_submit_id }}">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $p)
                                            <option value="{{ $p->id }}" {{ $s->petugas_submit_id == $p->id ? 'selected' : '' }}>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><code>{{ $s->username_author ?? '-' }}</code></td>
                                <td><code>{{ $s->password_author ?? '-' }}</code></td>
                                
                                <!-- Editor 1: Petugas, User/Pass, Valid -->
                                <td>
                                    <select class="inline-assign-select" 
                                            onchange="quickAssign(this, {{ $s->id }}, 'petugas_editor1_id')"
                                            data-original="{{ $s->petugas_editor1_id }}">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $p)
                                            <option value="{{ $p->id }}" {{ $s->petugas_editor1_id == $p->id ? 'selected' : '' }}>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="credential-group">
                                        <input type="text" 
                                               class="inline-credential-input" 
                                               value="{{ $s->username_editor }}" 
                                               placeholder="user"
                                               onblur="quickUpdateCredential(this, {{ $s->id }}, 'username_editor')">
                                        <span>/</span>
                                        <input type="text" 
                                               class="inline-credential-input" 
                                               value="{{ $s->password_editor }}" 
                                               placeholder="pass"
                                               onblur="quickUpdateCredential(this, {{ $s->id }}, 'password_editor')">
                                    </div>
                                </td>
                                <td class="text-center">{!! $s->editor1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Author 1: Petugas, Valid -->
                                <td>
                                    <select class="inline-assign-select" 
                                            onchange="quickAssign(this, {{ $s->id }}, 'petugas_author1_id')"
                                            data-original="{{ $s->petugas_author1_id }}">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $p)
                                            <option value="{{ $p->id }}" {{ $s->petugas_author1_id == $p->id ? 'selected' : '' }}>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">{!! $s->author1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Editor 2: Petugas, Valid -->
                                <td>
                                    <select class="inline-assign-select" 
                                            onchange="quickAssign(this, {{ $s->id }}, 'petugas_editor2_id')"
                                            data-original="{{ $s->petugas_editor2_id }}">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $p)
                                            <option value="{{ $p->id }}" {{ $s->petugas_editor2_id == $p->id ? 'selected' : '' }}>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">{!! $s->editor2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Reviewer 1: Petugas, User/Pass, Catatan, Valid -->
                                <td>
                                    <select class="inline-assign-select" 
                                            onchange="quickAssign(this, {{ $s->id }}, 'petugas_reviewer1_id')"
                                            data-original="{{ $s->petugas_reviewer1_id }}">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $p)
                                            <option value="{{ $p->id }}" {{ $s->petugas_reviewer1_id == $p->id ? 'selected' : '' }}>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="credential-group">
                                        <input type="text" 
                                               class="inline-credential-input" 
                                               value="{{ $s->username_reviewer1 }}" 
                                               placeholder="user"
                                               onblur="quickUpdateCredential(this, {{ $s->id }}, 'username_reviewer1')">
                                        <span>/</span>
                                        <input type="text" 
                                               class="inline-credential-input" 
                                               value="{{ $s->password_reviewer1 }}" 
                                               placeholder="pass"
                                               onblur="quickUpdateCredential(this, {{ $s->id }}, 'password_reviewer1')">
                                    </div>
                                </td>
                                <td title="{{ $s->catatan_reviewer1 }}">{{ Str::limit($s->catatan_reviewer1, 15) ?? '-' }}</td>
                                <td class="text-center">{!! $s->reviewer1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Reviewer 2: Petugas, User/Pass, Catatan, Valid -->
                                <td>
                                    <select class="inline-assign-select" 
                                            onchange="quickAssign(this, {{ $s->id }}, 'petugas_reviewer2_id')"
                                            data-original="{{ $s->petugas_reviewer2_id }}">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $p)
                                            <option value="{{ $p->id }}" {{ $s->petugas_reviewer2_id == $p->id ? 'selected' : '' }}>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="credential-group">
                                        <input type="text" 
                                               class="inline-credential-input" 
                                               value="{{ $s->username_reviewer2 }}" 
                                               placeholder="user"
                                               onblur="quickUpdateCredential(this, {{ $s->id }}, 'username_reviewer2')">
                                        <span>/</span>
                                        <input type="text" 
                                               class="inline-credential-input" 
                                               value="{{ $s->password_reviewer2 }}" 
                                               placeholder="pass"
                                               onblur="quickUpdateCredential(this, {{ $s->id }}, 'password_reviewer2')">
                                    </div>
                                </td>
                                <td title="{{ $s->catatan_reviewer2 }}">{{ Str::limit($s->catatan_reviewer2, 15) ?? '-' }}</td>
                                <td class="text-center">{!! $s->reviewer2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Editor 3: Petugas, Valid -->
                                <td>
                                    <select class="inline-assign-select" 
                                            onchange="quickAssign(this, {{ $s->id }}, 'petugas_editor3_id')"
                                            data-original="{{ $s->petugas_editor3_id }}">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $p)
                                            <option value="{{ $p->id }}" {{ $s->petugas_editor3_id == $p->id ? 'selected' : '' }}>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">{!! $s->editor3_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Author 2: Petugas, Valid -->
                                <td>
                                    <select class="inline-assign-select" 
                                            onchange="quickAssign(this, {{ $s->id }}, 'petugas_author2_id')"
                                            data-original="{{ $s->petugas_author2_id }}">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $p)
                                            <option value="{{ $p->id }}" {{ $s->petugas_author2_id == $p->id ? 'selected' : '' }}>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">{!! $s->author2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Production: Petugas, Link Publish, Valid -->
                                <td>
                                    <select class="inline-assign-select" 
                                            onchange="quickAssign(this, {{ $s->id }}, 'petugas_production_id')"
                                            data-original="{{ $s->petugas_production_id }}">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $p)
                                            <option value="{{ $p->id }}" {{ $s->petugas_production_id == $p->id ? 'selected' : '' }}>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    @if($s->link_publish)
                                        <a href="{{ $s->link_publish }}" target="_blank" class="btn btn-sm btn-success" style="padding: 2px 6px; font-size: 0.7rem;">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center"><i class="bi bi-check-circle-fill text-success"></i></td>
                                
                                <!-- Aksi -->
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.fasttrack.show', $s) }}" class="btn btn-info" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.fasttrack.edit', $s) }}" class="btn btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.fasttrack.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus fasttrack ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="32" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-6"></i>
                                    <p class="mt-2">Belum ada data fasttrack</p>
                                    <a href="{{ route('admin.fasttrack.create') }}" class="btn btn-warning">
                                        <i class="bi bi-plus-circle"></i> Input Fasttrack Pertama
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $submissions->withQueryString()->links() }}
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

// AJAX Quick Assignment Functions
function quickAssignMarketing(select, submissionId) {
    const marketingId = select.value;
    const originalValue = select.dataset.original;
    
    select.classList.add('saving');
    
    fetch('/admin/submissions/quick-assign-marketing', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            submission_id: submissionId,
            marketing_id: marketingId || null
        })
    })
    .then(response => response.json())
    .then(data => {
        select.classList.remove('saving');
        if (data.success) {
            select.classList.add('success');
            select.dataset.original = marketingId;
            setTimeout(() => select.classList.remove('success'), 2000);
        } else {
            select.classList.add('error');
            select.value = originalValue;
            setTimeout(() => select.classList.remove('error'), 2000);
            alert('Gagal: ' + (data.message || 'Error'));
        }
    })
    .catch(error => {
        select.classList.remove('saving');
        select.classList.add('error');
        select.value = originalValue;
        setTimeout(() => select.classList.remove('error'), 2000);
        console.error('Error:', error);
    });
}

function quickAssign(select, submissionId, field) {
    const picId = select.value;
    const originalValue = select.dataset.original;
    
    // Map field to assignment_type
    const fieldMap = {
        'petugas_submit_id': 'submit',
        'petugas_editor1_id': 'editor1',
        'petugas_editor2_id': 'editor2',
        'petugas_editor3_id': 'editor3',
        'petugas_author1_id': 'author1',
        'petugas_author2_id': 'author2',
        'petugas_reviewer1_id': 'reviewer1',
        'petugas_reviewer2_id': 'reviewer2',
        'petugas_production_id': 'production',
    };
    
    const assignmentType = fieldMap[field] || field;
    
    select.classList.add('saving');
    
    fetch('/admin/submissions/quick-assign', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            submission_id: submissionId,
            assignment_type: assignmentType,
            petugas_id: picId || null
        })
    })
    .then(response => response.json())
    .then(data => {
        select.classList.remove('saving');
        if (data.success) {
            select.classList.add('success');
            select.dataset.original = picId;
            setTimeout(() => select.classList.remove('success'), 2000);
        } else {
            select.classList.add('error');
            select.value = originalValue;
            setTimeout(() => select.classList.remove('error'), 2000);
            alert('Gagal: ' + (data.message || 'Error'));
        }
    })
    .catch(error => {
        select.classList.remove('saving');
        select.classList.add('error');
        select.value = originalValue;
        setTimeout(() => select.classList.remove('error'), 2000);
        console.error('Error:', error);
    });
}

function quickUpdateCredential(input, submissionId, field) {
    const value = input.value;
    const originalValue = input.dataset.original || '';
    
    // Skip if no change
    if (value === originalValue) return;
    
    input.dataset.original = value;
    
    fetch('/admin/submissions/quick-update-credential', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            submission_id: submissionId,
            field: field,
            value: value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.classList.add('success');
            setTimeout(() => input.classList.remove('success'), 2000);
        } else {
            input.classList.add('error');
            setTimeout(() => input.classList.remove('error'), 2000);
            alert('Gagal: ' + (data.message || 'Error'));
        }
    })
    .catch(error => {
        input.classList.add('error');
        setTimeout(() => input.classList.remove('error'), 2000);
        console.error('Error:', error);
    });
}
</script>
@endsection
