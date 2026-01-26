@extends('pic.layouts.app')

@section('title', 'Data Fasttrack')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
/* Override layout untuk halaman ini agar scroll di dalam tabel */
html, body {
    overflow: hidden !important;
    height: 100vh;
}

.main-container {
    height: calc(100vh - 56px) !important;
    overflow: hidden !important;
}

.content {
    max-width: 100vw;
    overflow: hidden !important;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.card {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 0;
}

.card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 0;
}

/* Sticky Table Styles for Monitoring */
.monitoring-scroll-wrapper {
    flex: 1;
    overflow-x: auto;
    overflow-y: auto;
    min-height: 200px;
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

.table-monitoring thead th.bg-dark {
    background-color: #212529 !important;
    color: #fff !important;
    z-index: 20 !important;
    position: sticky !important;
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
    background: linear-gradient(90deg, #ffc107, #fd7e14);
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
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: #000;
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
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

.summary-card.fasttrack {
    border-left: 4px solid #ffc107;
}

.summary-card.published {
    border-left: 4px solid #198754;
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-lightning-charge text-warning"></i> Data Fasttrack</span>
                <a href="{{ route('pic.fasttrack.create') }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-plus-circle"></i> Input Fasttrack
                </a>
            </div>
            <div class="card-body">
                <!-- Summary Cards -->
                <div class="summary-cards mb-3">
                    <div class="summary-card fasttrack">
                        <h6>Total Fasttrack</h6>
                        <div class="value">{{ $submissions->total() }}</div>
                    </div>
                    <div class="summary-card published">
                        <h6>Published</h6>
                        <div class="value">{{ $submissions->total() }}</div>
                    </div>
                </div>

                <!-- Filter Form -->
                <form action="{{ route('pic.fasttrack.index') }}" method="GET" class="mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label small mb-1">Tanggal Dari</label>
                            <input type="date" name="tanggal_dari" class="form-control form-control-sm" style="width: 150px;" value="{{ request('tanggal_dari') }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label small mb-1">Tanggal Sampai</label>
                            <input type="date" name="tanggal_sampai" class="form-control form-control-sm" style="width: 150px;" value="{{ request('tanggal_sampai') }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label small mb-1">Cari</label>
                            <input type="text" name="search" class="form-control form-control-sm" style="width: 200px;" placeholder="Kode/Judul/Penulis..." value="{{ request('search') }}">
                        </div>
                        <div class="col-auto">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                                <a href="{{ route('pic.fasttrack.index') }}" class="btn btn-outline-secondary">
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

                <!-- Fasttrack Monitoring Table -->
                <div class="monitoring-scroll-wrapper" id="monitoringScrollWrapper">
                    <table class="table table-monitoring table-bordered mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="sticky-first">Kode Submit</th>
                                <th rowspan="2" class="sticky-second">ID Artikel</th>
                                <th rowspan="2">Judul</th>
                                <th rowspan="2">Link</th>
                                <th rowspan="2">Penulis</th>
                                <th rowspan="2">No HP</th>
                                <th colspan="4" class="text-center bg-dark text-white" id="colSubmit" data-section="submit">Author Access</th>
                                <th colspan="3" class="text-center bg-info text-dark" id="colEditor1" data-section="editor1">Editor 1</th>
                                <th colspan="2" class="text-center bg-warning text-dark" id="colAuthor1" data-section="author1">Author 1</th>
                                <th colspan="2" class="text-center bg-info text-dark" id="colEditor2" data-section="editor2">Editor 2</th>
                                <th colspan="4" class="text-center bg-primary text-white" id="colReviewer1" data-section="reviewer1">Reviewer 1</th>
                                <th colspan="4" class="text-center bg-primary text-white" id="colReviewer2" data-section="reviewer2">Reviewer 2</th>
                                <th colspan="2" class="text-center bg-info text-dark" id="colEditor3" data-section="editor3">Editor 3</th>
                                <th colspan="2" class="text-center bg-warning text-dark" id="colAuthor2" data-section="author2">Author 2</th>
                                <th colspan="3" class="text-center bg-success text-white" id="colProduction" data-section="production">Production</th>
                                <th rowspan="2">Aksi</th>
                            </tr>
                            <tr>
                                <!-- Author Access sub-headers -->
                                <th class="bg-dark text-white">Marketing</th>
                                <th class="bg-dark text-white">Petugas</th>
                                <th class="bg-dark text-white">User</th>
                                <th class="bg-dark text-white">Pass</th>
                                <!-- Editor 1 sub-headers -->
                                <th class="bg-info text-dark">Petugas</th>
                                <th class="bg-info text-dark">User/Pass</th>
                                <th class="bg-info text-dark">Valid</th>
                                <!-- Author 1 sub-headers -->
                                <th class="bg-warning text-dark">Petugas</th>
                                <th class="bg-warning text-dark">Valid</th>
                                <!-- Editor 2 sub-headers -->
                                <th class="bg-info text-dark">Petugas</th>
                                <th class="bg-info text-dark">Valid</th>
                                <!-- Reviewer 1 sub-headers -->
                                <th class="bg-primary text-white">Petugas</th>
                                <th class="bg-primary text-white">User/Pass</th>
                                <th class="bg-primary text-white">Catatan</th>
                                <th class="bg-primary text-white">Valid</th>
                                <!-- Reviewer 2 sub-headers -->
                                <th class="bg-primary text-white">Petugas</th>
                                <th class="bg-primary text-white">User/Pass</th>
                                <th class="bg-primary text-white">Catatan</th>
                                <th class="bg-primary text-white">Valid</th>
                                <!-- Editor 3 sub-headers -->
                                <th class="bg-info text-dark">Petugas</th>
                                <th class="bg-info text-dark">Valid</th>
                                <!-- Author 2 sub-headers -->
                                <th class="bg-warning text-dark">Petugas</th>
                                <th class="bg-warning text-dark">Valid</th>
                                <!-- Production sub-headers -->
                                <th class="bg-success text-white">Petugas</th>
                                <th class="bg-success text-white">Link</th>
                                <th class="bg-success text-white">Valid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $s)
                            <tr>
                                <td class="sticky-first">
                                    <a href="{{ route('pic.fasttrack.show', $s) }}" class="text-decoration-none">
                                        <code class="text-warning">{{ $s->kode_submit }}</code>
                                    </a>
                                    <span class="badge bg-warning text-dark ms-1"><i class="bi bi-lightning-charge"></i> FT</span>
                                    <br><span class="badge bg-success mt-1"><i class="bi bi-check-circle-fill"></i> SELESAI</span>
                                </td>
                                <td class="sticky-second">{{ $s->id_artikel ?? '-' }}</td>
                                <td title="{{ $s->judul_artikel }}">{{ Str::limit($s->judul_artikel, 20) }}</td>
                                <td class="text-center">
                                    @if($s->link_artikel)
                                        <a href="{{ $s->link_artikel }}" target="_blank"><i class="bi bi-link-45deg"></i></a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ Str::limit($s->nama_penulis, 12) }}</td>
                                <td>
                                    @if($s->no_hp_penulis)
                                        @php
                                            $waNumber = preg_replace('/[^0-9]/', '', $s->no_hp_penulis);
                                            if (substr($waNumber, 0, 1) === '0') {
                                                $waNumber = '62' . substr($waNumber, 1);
                                            }
                                            $waUrl = "https://wa.me/{$waNumber}";
                                        @endphp
                                        <a href="{{ $waUrl }}" target="_blank" class="btn btn-success btn-sm" style="padding: 2px 6px; font-size: 0.7rem;">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                
                                <!-- Author Access -->
                                <td>{{ $s->marketing->name ?? '-' }}</td>
                                <td>{{ $s->petugasSubmit->name ?? ($s->marketing->name ?? '-') }}</td>
                                <td><code>{{ $s->username_author ?? '-' }}</code></td>
                                <td><code>{{ $s->password_author ?? '-' }}</code></td>
                                
                                <!-- Editor 1 -->
                                <td>{{ $s->petugasEditor1->name ?? '-' }}</td>
                                <td>@if($s->username_editor)<code>{{ $s->username_editor }}/{{ $s->password_editor ?? '-' }}</code>@else - @endif</td>
                                <td class="text-center">
                                    @if($s->petugas_editor1_id == $picId)
                                    <i class="bi {{ $s->editor1_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'editor1_valid', {{ $s->editor1_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->editor1_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <!-- Author 1 -->
                                <td>{{ $s->petugasAuthor1->name ?? '-' }}</td>
                                <td class="text-center">
                                    @if($s->petugas_author1_id == $picId)
                                    <i class="bi {{ $s->author1_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'author1_valid', {{ $s->author1_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->author1_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <!-- Editor 2 -->
                                <td>{{ $s->petugasEditor2->name ?? '-' }}</td>
                                <td class="text-center">
                                    @if($s->petugas_editor2_id == $picId)
                                    <i class="bi {{ $s->editor2_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'editor2_valid', {{ $s->editor2_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->editor2_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <!-- Reviewer 1 -->
                                <td>{{ $s->petugasReviewer1->name ?? '-' }}</td>
                                <td>@if($s->username_reviewer1)<code>{{ $s->username_reviewer1 }}/{{ $s->password_reviewer1 ?? '-' }}</code>@else - @endif</td>
                                <td title="{{ $s->catatan_reviewer1 }}">{{ Str::limit($s->catatan_reviewer1, 10) ?? '-' }}</td>
                                <td class="text-center">
                                    @if($s->petugas_reviewer1_id == $picId)
                                    <i class="bi {{ $s->reviewer1_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'reviewer1_valid', {{ $s->reviewer1_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->reviewer1_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <!-- Reviewer 2 -->
                                <td>{{ $s->petugasReviewer2->name ?? '-' }}</td>
                                <td>@if($s->username_reviewer2)<code>{{ $s->username_reviewer2 }}/{{ $s->password_reviewer2 ?? '-' }}</code>@else - @endif</td>
                                <td title="{{ $s->catatan_reviewer2 }}">{{ Str::limit($s->catatan_reviewer2, 10) ?? '-' }}</td>
                                <td class="text-center">
                                    @if($s->petugas_reviewer2_id == $picId)
                                    <i class="bi {{ $s->reviewer2_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'reviewer2_valid', {{ $s->reviewer2_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->reviewer2_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <!-- Editor 3 -->
                                <td>{{ $s->petugasEditor3->name ?? '-' }}</td>
                                <td class="text-center">
                                    @if($s->petugas_editor3_id == $picId)
                                    <i class="bi {{ $s->editor3_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'editor3_valid', {{ $s->editor3_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->editor3_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <!-- Author 2 -->
                                <td>{{ $s->petugasAuthor2->name ?? '-' }}</td>
                                <td class="text-center">
                                    @if($s->petugas_author2_id == $picId)
                                    <i class="bi {{ $s->author2_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'author2_valid', {{ $s->author2_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->author2_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <!-- Production -->
                                <td>{{ $s->petugasProduction->name ?? ($s->petugasSubmit->name ?? ($s->marketing->name ?? '-')) }}</td>
                                <td>
                                    @if($s->link_publish)
                                        <a href="{{ $s->link_publish }}" target="_blank" class="btn btn-sm btn-success" style="padding: 2px 6px; font-size: 0.7rem;">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($s->petugas_production_id == $picId)
                                    <i class="bi {{ $s->production_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'production_valid', {{ $s->production_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->production_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <td class="text-center">
                                    <a href="{{ route('pic.fasttrack.show', $s) }}" class="btn btn-info btn-sm" style="padding: 2px 6px; font-size: 0.7rem;">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="32" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p class="mb-0 mt-2">Belum ada data fasttrack</p>
                                    <a href="{{ route('pic.fasttrack.create') }}" class="btn btn-warning btn-sm mt-2">
                                        <i class="bi bi-plus-circle"></i> Input Fasttrack
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
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.getElementById('monitoringScrollWrapper');
    const positionFill = document.getElementById('scrollPositionFill');
    const positionText = document.getElementById('scrollPositionText');
    const scrollLeftBtn = document.getElementById('scrollLeftBtn');
    const scrollRightBtn = document.getElementById('scrollRightBtn');
    const scrollStartBtn = document.getElementById('scrollStartBtn');
    const scrollEndBtn = document.getElementById('scrollEndBtn');
    
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
    
    function updateScrollPosition() {
        const scrollLeft = wrapper.scrollLeft;
        const scrollWidth = wrapper.scrollWidth - wrapper.clientWidth;
        const progress = scrollWidth > 0 ? (scrollLeft / scrollWidth) * 100 : 0;
        positionFill.style.width = progress + '%';
        positionText.textContent = Math.round(progress) + '%';
        
        scrollStartBtn.disabled = scrollLeft <= 0;
        scrollLeftBtn.disabled = scrollLeft <= 0;
        scrollRightBtn.disabled = scrollLeft >= scrollWidth;
        scrollEndBtn.disabled = scrollLeft >= scrollWidth;
        
        document.querySelectorAll('.quick-nav-btn').forEach(btn => {
            btn.classList.remove('active');
        });
    }
    
    wrapper.addEventListener('scroll', updateScrollPosition);
    
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
    
    document.querySelectorAll('.quick-nav-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.dataset.target;
            const position = columnPositions[target] || 0;
            
            wrapper.scrollTo({ left: position, behavior: 'smooth' });
            
            document.querySelectorAll('.quick-nav-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
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
    
    updateScrollPosition();
});

// Toggle Valid Function
function toggleValid(icon, submissionId, field, currentValue) {
    const stage = field.replace('_valid', '');
    
    icon.style.opacity = '0.5';
    
    fetch('/pic/submissions/toggle-valid', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            submission_id: submissionId,
            stage: stage
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        icon.style.opacity = '1';
        if (data.success) {
            const isValid = data.is_valid;
            if (isValid) {
                icon.classList.remove('bi-circle', 'text-muted');
                icon.classList.add('bi-check-circle-fill', 'text-success');
            } else {
                icon.classList.remove('bi-check-circle-fill', 'text-success');
                icon.classList.add('bi-circle', 'text-muted');
            }
            icon.setAttribute('onclick', `toggleValid(this, ${submissionId}, '${field}', ${isValid})`);
        } else {
            alert('Gagal: ' + (data.message || 'Error'));
        }
    })
    .catch(error => {
        icon.style.opacity = '1';
        console.error('Error:', error);
        alert('Error: ' + error.message);
    });
}
</script>
@endsection
