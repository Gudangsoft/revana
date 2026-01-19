@extends('layouts.app')

@section('title', 'Monitoring Proses - ' . $appSettings['app_name'])
@section('page-title', 'Monitoring Proses Submit')

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
                <span><i class="bi bi-bar-chart"></i> Monitoring Proses Submit (Filter Tanggal)</span>
                <a href="{{ route('admin.submissions.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <form action="{{ route('admin.submissions.monitoring') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label for="tanggal_dari" class="form-label">Tanggal Dari</label>
                            <input type="date" class="form-control" id="tanggal_dari" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="tanggal_sampai" class="form-label">Tanggal Sampai</label>
                            <input type="date" class="form-control" id="tanggal_sampai" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="journal_master_id" class="form-label">Jurnal</label>
                            <select class="form-select" id="journal_master_id" name="journal_master_id">
                                <option value="">-- Semua Jurnal --</option>
                                @foreach($journals as $journal)
                                    <option value="{{ $journal->id }}" {{ request('journal_master_id') == $journal->id ? 'selected' : '' }}>
                                        {{ $journal->nama_jurnal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">-- Semua --</option>
                                @foreach($statusOptions as $key => $value)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.submissions.monitoring') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
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
                                <th rowspan="2" class="align-middle text-center" style="width: 40px; min-width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="selectAllTable">
                                </th>
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
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input submission-checkbox" value="{{ $s->id }}" data-kode="{{ $s->kode_submit }}">
                                </td>
                                <td class="sticky-first">
                                    <a href="{{ route('admin.submissions.process', $s) }}" class="text-decoration-none" title="Klik untuk proses">
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
                                <td>{{ $s->no_hp_penulis ?? '-' }}</td>
                                <td><code>{{ $s->username_author ?? '-' }}</code></td>
                                <td><code>{{ $s->password_author ?? '-' }}</code></td>
                                <td>{{ $s->pic_marketing ?? '-' }}</td>
                                <td>{{ $s->petugasSubmit?->name ?? '-' }}</td>
                                
                                <!-- Editor 1 -->
                                <td>{{ $s->petugasEditor1?->name ?? '-' }}</td>
                                <td><small><code>{{ $s->username_editor ?? '-' }}</code>/<code>{{ $s->password_editor ?? '-' }}</code></small></td>
                                <td class="text-center">{!! $s->editor1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Author 1 -->
                                <td>{{ $s->petugasAuthor1?->name ?? '-' }}</td>
                                <td class="text-center">{!! $s->author1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Editor 2 -->
                                <td>{{ $s->petugasEditor2?->name ?? '-' }}</td>
                                <td class="text-center">{!! $s->editor2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Reviewer 1 -->
                                <td>{{ $s->petugasReviewer1?->name ?? '-' }}</td>
                                <td><small><code>{{ $s->username_reviewer1 ?? '-' }}</code>/<code>{{ $s->password_reviewer1 ?? '-' }}</code></small></td>
                                <td title="{{ $s->catatan_reviewer1 }}">{{ Str::limit($s->catatan_reviewer1, 15) ?? '-' }}</td>
                                <td class="text-center">{!! $s->reviewer1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Reviewer 2 -->
                                <td>{{ $s->petugasReviewer2?->name ?? '-' }}</td>
                                <td><small><code>{{ $s->username_reviewer2 ?? '-' }}</code>/<code>{{ $s->password_reviewer2 ?? '-' }}</code></small></td>
                                <td title="{{ $s->catatan_reviewer2 }}">{{ Str::limit($s->catatan_reviewer2, 15) ?? '-' }}</td>
                                <td class="text-center">{!! $s->reviewer2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Editor 3 -->
                                <td>{{ $s->petugasEditor3?->name ?? '-' }}</td>
                                <td class="text-center">{!! $s->editor3_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Author 2 -->
                                <td>{{ $s->petugasAuthor2?->name ?? '-' }}</td>
                                <td class="text-center">{!! $s->author2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Production -->
                                <td>{{ $s->petugasProduction?->name ?? '-' }}</td>
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
    
    // ========== BULK ASSIGNMENT FUNCTIONALITY ==========
    const selectAll = document.getElementById('selectAll');
    const selectAllTable = document.getElementById('selectAllTable');
    const checkboxes = document.querySelectorAll('.submission-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const bulkEditorBtn = document.getElementById('bulkEditorBtn');
    const bulkAuthorBtn = document.getElementById('bulkAuthorBtn');
    const bulkReviewerBtn = document.getElementById('bulkReviewerBtn');
    
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.submission-checkbox:checked');
        const count = checked.length;
        selectedCount.textContent = count + ' dipilih';
        
        // Enable/disable bulk buttons
        bulkEditorBtn.disabled = count === 0;
        bulkAuthorBtn.disabled = count === 0;
        bulkReviewerBtn.disabled = count === 0;
        
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-people"></i> Penugasan Massal Editor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                            <option value="editor1">Editor 1</option>
                            <option value="editor2">Editor 2</option>
                            <option value="editor3">Editor 3</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih Petugas <span class="text-danger">*</span></label>
                        <select class="form-select" name="petugas_id" required>
                            <option value="">-- Pilih Petugas --</option>
                            @foreach(\App\Models\User::where('role', 'admin')->orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="row" id="editorCredentials" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Username Editor</label>
                                <input type="text" class="form-control" name="username_editor" placeholder="Username (opsional)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password Editor</label>
                                <input type="text" class="form-control" name="password_editor" placeholder="Password (opsional)">
                            </div>
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
                            @foreach(\App\Models\User::where('role', 'admin')->orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-journal-check"></i> Penugasan Massal Reviewer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                            <option value="reviewer1">Reviewer 1</option>
                            <option value="reviewer2">Reviewer 2</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih Petugas <span class="text-danger">*</span></label>
                        <select class="form-select" name="petugas_id" required>
                            <option value="">-- Pilih Petugas --</option>
                            @foreach(\App\Models\User::where('role', 'admin')->orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Username Reviewer</label>
                                <input type="text" class="form-control" name="username_reviewer" placeholder="Username (opsional)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password Reviewer</label>
                                <input type="text" class="form-control" name="password_reviewer" placeholder="Password (opsional)">
                            </div>
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

<script>
// Show editor credentials for Editor 1
document.querySelector('#bulkEditorModal select[name="assignment_type"]').addEventListener('change', function() {
    document.getElementById('editorCredentials').style.display = this.value === 'editor1' ? 'flex' : 'none';
});
</script>
@endsection
