@extends('pic.layouts.app')

@section('title', 'Monitoring Proses Review')
@section('page-title', 'Monitoring Proses Review')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('styles')
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

.table-monitoring {
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.8rem;
}

.table-monitoring thead th {
    position: sticky;
    top: 0;
    z-index: 20;
    background-color: #212529 !important;
    color: white !important;
    border: 1px solid #404040;
    padding: 8px 6px;
    white-space: nowrap;
}

.table-monitoring tbody td {
    border: 1px solid #dee2e6;
    padding: 6px;
    vertical-align: middle;
    min-width: 80px;
}

.table-monitoring tbody td code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.7rem;
    display: inline-block;
}

.table-monitoring tbody td input.form-control-sm {
    font-size: 0.7rem;
    padding: 2px 4px;
    height: auto;
}

.sticky-first {
    position: sticky;
    left: 0;
    z-index: 25;
    background-color: #212529 !important;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}

.sticky-second {
    position: sticky;
    left: 120px;
    z-index: 25;
    background-color: #212529 !important;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}

.table-monitoring tbody .sticky-first,
.table-monitoring tbody .sticky-second {
    background-color: white !important;
}

.my-task-row {
    background-color: #fff3cd !important;
}

.my-task-row .sticky-first,
.my-task-row .sticky-second {
    background-color: #fff3cd !important;
}

.scroll-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    margin-bottom: 10px;
}

.scroll-nav-btn {
    background: white;
    border: 1px solid #dee2e6;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.scroll-nav-btn:hover {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.quick-nav-btn {
    background: white;
    border: 1px solid #dee2e6;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.75rem;
    cursor: pointer;
    margin: 0 2px;
    transition: all 0.2s;
}

.quick-nav-btn:hover {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.scroll-position-indicator {
    flex: 1;
    max-width: 300px;
    margin: 0 15px;
}

.scroll-position-bar {
    height: 8px;
    background: #dee2e6;
    border-radius: 4px;
    overflow: hidden;
}

.scroll-position-fill {
    height: 100%;
    background: linear-gradient(90deg, #0d6efd, #0dcaf0);
    transition: width 0.1s;
}

.btn-validation {
    min-width: 35px;
    padding: 4px 8px;
    transition: all 0.2s ease;
}

.btn-validation:hover {
    transform: scale(1.1);
}

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
</style>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center py-3">
                <h3 class="mb-0">{{ $stats['new'] ?? 0 }}</h3>
                <small>New</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body text-center py-3">
                <h3 class="mb-0">{{ $stats['in_progress'] ?? 0 }}</h3>
                <small>In Progress</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center py-3">
                <h3 class="mb-0">{{ $stats['published'] ?? 0 }}</h3>
                <small>Published</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Jurnal</label>
                <select name="journal_id" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    @foreach($journals as $journal)
                        <option value="{{ $journal->id }}" {{ request('journal_id') == $journal->id ? 'selected' : '' }}>
                            {{ $journal->nama_jurnal }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="editor1_process" {{ request('status') == 'editor1_process' ? 'selected' : '' }}>Editor1 Process</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Cari</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari judul/penulis..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <div class="btn-group btn-group-sm w-100" role="group">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('pic.submissions.monitoring') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            </div>
        </form>
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
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-body p-0">
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
                            <td title="{{ $s->judul_artikel }}" style="max-width: 200px;">{{ Str::limit($s->judul_artikel, 30) }}</td>
                            <td class="text-center">
                                @if($s->link_artikel)
                                    <a href="{{ $s->link_artikel }}" target="_blank"><i class="bi bi-link-45deg"></i></a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ Str::limit($s->nama_penulis, 15) }}</td>
                            <td>{{ $s->no_hp_penulis ?? '-' }}</td>
                            <td>
                                @if($s->username_author)
                                    <code style="font-size: 0.7rem;">{{ $s->username_author }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($s->password_author)
                                    <code style="font-size: 0.7rem;">{{ $s->password_author }}</code>
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
                                            <small style="font-size: 0.6rem; color: #666;">user:</small>
                                            <input type="text" class="form-control form-control-sm" style="width: 70px; font-size: 0.7rem;" 
                                                   value="{{ $s->username_editor ?? '' }}" 
                                                   data-submission="{{ $s->id }}"
                                                   data-field="username_editor"
                                                   onchange="updateCredential(this)" 
                                                   placeholder="username">
                                        </div>
                                        <div class="d-flex gap-1 align-items-center">
                                            <small style="font-size: 0.6rem; color: #666;">pass:</small>
                                            <input type="text" class="form-control form-control-sm" style="width: 70px; font-size: 0.7rem;" 
                                                   value="{{ $s->password_editor ?? '' }}" 
                                                   data-submission="{{ $s->id }}"
                                                   data-field="password_editor"
                                                   onchange="updateCredential(this)" 
                                                   placeholder="password">
                                        </div>
                                    </div>
                                @else
                                    @if($s->username_editor || $s->password_editor)
                                        <div style="font-size: 0.7rem;">
                                            <div><small class="text-muted">user:</small> <code>{{ $s->username_editor ?? '-' }}</code></div>
                                            <div><small class="text-muted">pass:</small> <code>{{ $s->password_editor ?? '-' }}</code></div>
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
                            <td class="text-center">
                                @if($s->petugas_editor2_id == $currentPicId)
                                    <button type="button" class="btn btn-sm {{ $s->editor2_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                            onclick="toggleValid(this, {{ $s->id }}, 'editor2')"
                                            title="Klik untuk toggle validasi">
                                        <i class="bi {{ $s->editor2_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                    </button>
                                    <div class="mt-2 p-2 border rounded bg-light">
                                        <div class="mb-2">
                                            <label style="font-size:0.65rem; font-weight:bold; color:#0d6efd;">Reviewer 1:</label>
                                            <div class="d-flex gap-1 mt-1">
                                                <input type="text" class="form-control form-control-sm" style="width: 70px; font-size: 0.65rem;" 
                                                       value="{{ $s->username_reviewer1 ?? '' }}" 
                                                       data-submission="{{ $s->id }}"
                                                       data-field="username_reviewer1"
                                                       onchange="updateCredential(this)" 
                                                       placeholder="user">
                                                <input type="text" class="form-control form-control-sm" style="width: 70px; font-size: 0.65rem;" 
                                                       value="{{ $s->password_reviewer1 ?? '' }}" 
                                                       data-submission="{{ $s->id }}"
                                                       data-field="password_reviewer1"
                                                       onchange="updateCredential(this)" 
                                                       placeholder="pass">
                                            </div>
                                        </div>
                                        <div>
                                            <label style="font-size:0.65rem; font-weight:bold; color:#0d6efd;">Reviewer 2:</label>
                                            <div class="d-flex gap-1 mt-1">
                                                <input type="text" class="form-control form-control-sm" style="width: 70px; font-size: 0.65rem;" 
                                                       value="{{ $s->username_reviewer2 ?? '' }}" 
                                                       data-submission="{{ $s->id }}"
                                                       data-field="username_reviewer2"
                                                       onchange="updateCredential(this)" 
                                                       placeholder="user">
                                                <input type="text" class="form-control form-control-sm" style="width: 70px; font-size: 0.65rem;" 
                                                       value="{{ $s->password_reviewer2 ?? '' }}" 
                                                       data-submission="{{ $s->id }}"
                                                       data-field="password_reviewer2"
                                                       onchange="updateCredential(this)" 
                                                       placeholder="pass">
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {!! $s->editor2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                @endif
                            </td>
                            
                            <!-- Reviewer 1 -->
                            <td>
                                @if($s->petugas_reviewer1_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $s->petugasReviewer1?->name ?? '-' }}
                                @endif
                            </td>
                            <td>
                                @if($s->username_reviewer1 || $s->password_reviewer1)
                                    <div style="font-size: 0.7rem;">
                                        <div><small class="text-muted">user:</small> <code>{{ $s->username_reviewer1 ?? '-' }}</code></div>
                                        <div><small class="text-muted">pass:</small> <code>{{ $s->password_reviewer1 ?? '-' }}</code></div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td title="{{ $s->catatan_reviewer1 }}">{{ Str::limit($s->catatan_reviewer1, 15) ?? '-' }}</td>
                            <td class="text-center">
                                @if($s->petugas_reviewer1_id == $currentPicId)
                                    @if(!$s->editor2_valid)
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled onclick="alert('Ada tugas lain yang belum dikerjakan!')">
                                            <i class="bi bi-circle"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm {{ $s->reviewer1_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                onclick="toggleValid(this, {{ $s->id }}, 'reviewer1')"
                                                title="Klik untuk toggle validasi">
                                            <i class="bi {{ $s->reviewer1_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                        </button>
                                    @endif
                                @else
                                    {!! $s->reviewer1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
                                @endif
                            </td>
                            
                            <!-- Reviewer 2 -->
                            <td>
                                @if($s->petugas_reviewer2_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $s->petugasReviewer2?->name ?? '-' }}
                                @endif
                            </td>
                            <td>
                                @if($s->username_reviewer2 || $s->password_reviewer2)
                                    <div style="font-size: 0.7rem;">
                                        <div><small class="text-muted">user:</small> <code>{{ $s->username_reviewer2 ?? '-' }}</code></div>
                                        <div><small class="text-muted">pass:</small> <code>{{ $s->password_reviewer2 ?? '-' }}</code></div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td title="{{ $s->catatan_reviewer2 }}">{{ Str::limit($s->catatan_reviewer2, 15) ?? '-' }}</td>
                            <td class="text-center">
                                @if($s->petugas_reviewer2_id == $currentPicId)
                                    @if(!$s->reviewer1_valid)
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled onclick="alert('Ada tugas lain yang belum dikerjakan!')">
                                            <i class="bi bi-circle"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm {{ $s->reviewer2_valid ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                onclick="toggleValid(this, {{ $s->id }}, 'reviewer2')"
                                                title="Klik untuk toggle validasi">
                                            <i class="bi {{ $s->reviewer2_valid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                        </button>
                                    @endif
                                @else
                                    {!! $s->reviewer2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}
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
    </div>
</div>

<!-- Pagination -->
<div class="mt-3">
    {{ $submissions->links() }}
</div>

@endsection

@section('scripts')
<script>
// Scroll Navigation
document.addEventListener('DOMContentLoaded', function() {
    const scrollWrapper = document.getElementById('monitoringScrollWrapper');
    const scrollPositionFill = document.getElementById('scrollPositionFill');
    const scrollPositionText = document.getElementById('scrollPositionText');
    
    // Update scroll position indicator
    function updateScrollPosition() {
        if (scrollWrapper) {
            const scrollLeft = scrollWrapper.scrollLeft;
            const scrollWidth = scrollWrapper.scrollWidth - scrollWrapper.clientWidth;
            const percentage = scrollWidth > 0 ? (scrollLeft / scrollWidth) * 100 : 0;
            
            if (scrollPositionFill) scrollPositionFill.style.width = percentage + '%';
            if (scrollPositionText) scrollPositionText.textContent = Math.round(percentage) + '%';
        }
    }
    
    if (scrollWrapper) {
        scrollWrapper.addEventListener('scroll', updateScrollPosition);
    }
    
    // Scroll buttons
    document.getElementById('scrollStartBtn')?.addEventListener('click', function() {
        scrollWrapper.scrollTo({ left: 0, behavior: 'smooth' });
    });
    
    document.getElementById('scrollLeftBtn')?.addEventListener('click', function() {
        scrollWrapper.scrollBy({ left: -300, behavior: 'smooth' });
    });
    
    document.getElementById('scrollRightBtn')?.addEventListener('click', function() {
        scrollWrapper.scrollBy({ left: 300, behavior: 'smooth' });
    });
    
    document.getElementById('scrollEndBtn')?.addEventListener('click', function() {
        scrollWrapper.scrollTo({ left: scrollWrapper.scrollWidth, behavior: 'smooth' });
    });
    
    // Quick navigation buttons
    document.querySelectorAll('.quick-nav-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.dataset.target;
            const targetCol = document.getElementById('col' + target.charAt(0).toUpperCase() + target.slice(1));
            
            if (targetCol && scrollWrapper) {
                const targetLeft = targetCol.offsetLeft - 150;
                scrollWrapper.scrollTo({ left: targetLeft, behavior: 'smooth' });
            }
        });
    });
    
    // Initial position
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
