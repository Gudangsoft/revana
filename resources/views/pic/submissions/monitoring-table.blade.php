@extends('pic.layouts.app')

@section('title', 'Monitoring Proses Review')
@section('page-title', 'Monitoring Proses Review')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('styles')
<style>
    .monitoring-table-wrapper {
        overflow-x: auto;
        max-height: 70vh;
    }
    
    .table-monitoring {
        font-size: 0.85rem;
        white-space: nowrap;
    }
    
    .table-monitoring th {
        background-color: #2c3e50;
        color: white;
        position: sticky;
        top: 0;
        z-index: 10;
        font-weight: 600;
    }
    
    .table-monitoring td {
        vertical-align: middle;
    }
    
    .sticky-col {
        position: sticky;
        left: 0;
        background-color: white;
        z-index: 5;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    
    .sticky-col.header {
        background-color: #2c3e50;
        z-index: 11;
    }
    
    .pic-badge {
        background-color: #17a2b8;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.75rem;
        display: inline-block;
    }
    
    .status-badge {
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-submitted { background-color: #6c757d; color: white; }
    .status-editor1_process { background-color: #0dcaf0; color: black; }
    .status-published { background-color: #198754; color: white; }
    .status-new { background-color: #ffc107; color: black; }
    
    .checkpoint-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
    }
    
    .checkpoint-completed {
        background-color: #198754;
        color: white;
    }
    
    .checkpoint-pending {
        background-color: #e9ecef;
        color: #6c757d;
    }
    
    .checkpoint-mine {
        background-color: #0d6efd;
        color: white;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .my-task-row {
        background-color: #fff3cd;
    }
    
    .card-stats {
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    
    .card-stats:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .card-new { border-left-color: #6c757d; }
    .card-progress { border-left-color: #0dcaf0; }
    .card-done { border-left-color: #198754; }
    
    .quick-nav-pills {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .quick-nav-pills .nav-pill {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid #dee2e6;
        background: white;
    }
    
    .quick-nav-pills .nav-pill:hover {
        background: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }
</style>
@endsection

@section('content')

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card card-stats card-new">
            <div class="card-body text-center">
                <h2 class="mb-0 text-secondary">{{ $stats['new'] ?? 0 }}</h2>
                <small class="text-muted">New</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stats card-progress">
            <div class="card-body text-center">
                <h2 class="mb-0 text-info">{{ $stats['in_progress'] ?? 0 }}</h2>
                <small class="text-muted">In Progress</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stats card-done">
            <div class="card-body text-center">
                <h2 class="mb-0 text-success">{{ $stats['published'] ?? 0 }}</h2>
                <small class="text-muted">Published</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Jurnal</label>
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
                <label class="form-label small">Status</label>
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
                <label class="form-label small">Cari judul/penulis...</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('pic.submissions.monitoring') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Quick Navigation -->
<div class="quick-nav-pills mb-3">
    <span class="text-muted small">Lompat ke:</span>
    <span class="nav-pill" onclick="scrollToColumn('submit')">Submit</span>
    <span class="nav-pill" onclick="scrollToColumn('editor1')">Editor1</span>
    <span class="nav-pill" onclick="scrollToColumn('author1')">Author1</span>
    <span class="nav-pill" onclick="scrollToColumn('editor2')">Editor2</span>
    <span class="nav-pill" onclick="scrollToColumn('reviewer1')">Reviewer1</span>
    <span class="nav-pill" onclick="scrollToColumn('reviewer2')">Reviewer2</span>
    <span class="nav-pill" onclick="scrollToColumn('editor3')">Editor3</span>
    <span class="nav-pill" onclick="scrollToColumn('author2')">Author2</span>
    <span class="nav-pill" onclick="scrollToColumn('production')">Production</span>
</div>

<!-- Monitoring Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="monitoring-table-wrapper">
            <table class="table table-monitoring table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th class="sticky-col header">Kode Submit</th>
                        <th>ID Artikel</th>
                        <th>PIC Marketing</th>
                        <th id="col-submit">Petugas Submit</th>
                        <!-- Editor 1 -->
                        <th colspan="3" class="text-center bg-info text-dark" id="col-editor1">Editor 1</th>
                        <!-- Author 1 -->
                        <th colspan="3" class="text-center bg-warning text-dark" id="col-author1">Author 1</th>
                        <!-- Editor 2 -->
                        <th colspan="3" class="text-center bg-info text-dark" id="col-editor2">Editor 2</th>
                        <!-- Reviewer 1 -->
                        <th colspan="3" class="text-center bg-primary text-white" id="col-reviewer1">Reviewer 1</th>
                        <!-- Reviewer 2 -->
                        <th colspan="3" class="text-center bg-primary text-white" id="col-reviewer2">Reviewer 2</th>
                        <!-- Editor 3 -->
                        <th colspan="3" class="text-center bg-info text-dark" id="col-editor3">Editor 3</th>
                        <!-- Author 2 -->
                        <th colspan="3" class="text-center bg-warning text-dark" id="col-author2">Author 2</th>
                        <!-- Production -->
                        <th colspan="2" class="text-center bg-success text-white" id="col-production">Production</th>
                    </tr>
                    <tr>
                        <th class="sticky-col header">Status</th>
                        <th>Tanggal</th>
                        <th>-</th>
                        <th>-</th>
                        <!-- E1 -->
                        <th>Petugas</th>
                        <th>Valid</th>
                        <th>Petugas</th>
                        <!-- A1 -->
                        <th>Petugas</th>
                        <th>Valid</th>
                        <th>Petugas</th>
                        <!-- E2 -->
                        <th>Petugas</th>
                        <th>Valid</th>
                        <th>Petugas</th>
                        <!-- R1 -->
                        <th>Petugas</th>
                        <th>Valid</th>
                        <th>Petugas</th>
                        <!-- R2 -->
                        <th>Petugas</th>
                        <th>Valid</th>
                        <th>Petugas</th>
                        <!-- E3 -->
                        <th>Petugas</th>
                        <th>Valid</th>
                        <th>Petugas</th>
                        <!-- A2 -->
                        <th>Petugas</th>
                        <th>Valid</th>
                        <th>Petugas</th>
                        <!-- P -->
                        <th>Petugas</th>
                        <th>Valid</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currentPicId = auth()->guard('pic')->id();
                    @endphp
                    @forelse($submissions as $submission)
                        @php
                            $isMyTask = $submission->created_by == $currentPicId
                                || $submission->petugas_submit_id == $currentPicId
                                || $submission->petugas_editor1_id == $currentPicId
                                || $submission->petugas_author1_id == $currentPicId
                                || $submission->petugas_editor2_id == $currentPicId
                                || $submission->petugas_reviewer1_id == $currentPicId
                                || $submission->petugas_reviewer2_id == $currentPicId
                                || $submission->petugas_editor3_id == $currentPicId
                                || $submission->petugas_author2_id == $currentPicId
                                || $submission->petugas_production_id == $currentPicId;
                        @endphp
                        <tr class="{{ $isMyTask ? 'my-task-row' : '' }}">
                            <!-- Kode Submit (Sticky) -->
                            <td class="sticky-col">
                                <a href="{{ route('pic.submissions.show', $submission->id) }}" class="text-primary fw-bold">
                                    {{ $submission->kode_submit }}
                                </a>
                                <br>
                                <small class="text-muted">{{ $submission->judul_artikel }}</small>
                            </td>
                            
                            <!-- ID Artikel -->
                            <td>{{ $submission->id_artikel ?? '-' }}</td>
                            
                            <!-- PIC Marketing -->
                            <td>
                                <span class="pic-badge">{{ $submission->pic_marketing ?? 'Novalino Bagus' }}</span>
                            </td>
                            
                            <!-- Petugas Submit -->
                            <td>
                                @if($submission->petugas_submit_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $submission->petugasSubmit->name ?? '-' }}
                                @endif
                            </td>
                            
                            <!-- Editor 1 -->
                            <td>
                                @if($submission->petugas_editor1_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $submission->petugasEditor1->name ?? '-' }}
                                @endif
                            </td>
                            <td>
                                <span class="checkpoint-icon {{ $submission->editor1_valid ? 'checkpoint-completed' : 'checkpoint-pending' }}">
                                    <i class="bi {{ $submission->editor1_valid ? 'bi-check' : 'bi-circle' }}"></i>
                                </span>
                            </td>
                            <td>{{ $submission->petugasEditor1->name ?? '-' }}</td>
                            
                            <!-- Author 1 -->
                            <td>
                                @if($submission->petugas_author1_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $submission->petugasAuthor1->name ?? '-' }}
                                @endif
                            </td>
                            <td>
                                <span class="checkpoint-icon {{ $submission->author1_valid ? 'checkpoint-completed' : 'checkpoint-pending' }}">
                                    <i class="bi {{ $submission->author1_valid ? 'bi-check' : 'bi-circle' }}"></i>
                                </span>
                            </td>
                            <td>{{ $submission->petugasAuthor1->name ?? '-' }}</td>
                            
                            <!-- Editor 2 -->
                            <td>
                                @if($submission->petugas_editor2_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $submission->petugasEditor2->name ?? '-' }}
                                @endif
                            </td>
                            <td>
                                <span class="checkpoint-icon {{ $submission->editor2_valid ? 'checkpoint-completed' : 'checkpoint-pending' }}">
                                    <i class="bi {{ $submission->editor2_valid ? 'bi-check' : 'bi-circle' }}"></i>
                                </span>
                            </td>
                            <td>{{ $submission->petugasEditor2->name ?? '-' }}</td>
                            
                            <!-- Reviewer 1 -->
                            <td>
                                @if($submission->petugas_reviewer1_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $submission->petugasReviewer1->name ?? '-' }}
                                @endif
                            </td>
                            <td>
                                <span class="checkpoint-icon {{ $submission->reviewer1_valid ? 'checkpoint-completed' : 'checkpoint-pending' }}">
                                    <i class="bi {{ $submission->reviewer1_valid ? 'bi-check' : 'bi-circle' }}"></i>
                                </span>
                            </td>
                            <td>{{ $submission->petugasReviewer1->name ?? '-' }}</td>
                            
                            <!-- Reviewer 2 -->
                            <td>
                                @if($submission->petugas_reviewer2_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $submission->petugasReviewer2->name ?? '-' }}
                                @endif
                            </td>
                            <td>
                                <span class="checkpoint-icon {{ $submission->reviewer2_valid ? 'checkpoint-completed' : 'checkpoint-pending' }}">
                                    <i class="bi {{ $submission->reviewer2_valid ? 'bi-check' : 'bi-circle' }}"></i>
                                </span>
                            </td>
                            <td>{{ $submission->petugasReviewer2->name ?? '-' }}</td>
                            
                            <!-- Editor 3 -->
                            <td>
                                @if($submission->petugas_editor3_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $submission->petugasEditor3->name ?? '-' }}
                                @endif
                            </td>
                            <td>
                                <span class="checkpoint-icon {{ $submission->editor3_valid ? 'checkpoint-completed' : 'checkpoint-pending' }}">
                                    <i class="bi {{ $submission->editor3_valid ? 'bi-check' : 'bi-circle' }}"></i>
                                </span>
                            </td>
                            <td>{{ $submission->petugasEditor3->name ?? '-' }}</td>
                            
                            <!-- Author 2 -->
                            <td>
                                @if($submission->petugas_author2_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $submission->petugasAuthor2->name ?? '-' }}
                                @endif
                            </td>
                            <td>
                                <span class="checkpoint-icon {{ $submission->author2_valid ? 'checkpoint-completed' : 'checkpoint-pending' }}">
                                    <i class="bi {{ $submission->author2_valid ? 'bi-check' : 'bi-circle' }}"></i>
                                </span>
                            </td>
                            <td>{{ $submission->petugasAuthor2->name ?? '-' }}</td>
                            
                            <!-- Production -->
                            <td>
                                @if($submission->petugas_production_id == $currentPicId)
                                    <span class="badge bg-primary">Saya</span>
                                @else
                                    {{ $submission->petugasProduction->name ?? '-' }}
                                @endif
                            </td>
                            <td>
                                <span class="checkpoint-icon {{ $submission->production_valid ? 'checkpoint-completed' : 'checkpoint-pending' }}">
                                    <i class="bi {{ $submission->production_valid ? 'bi-check' : 'bi-circle' }}"></i>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="30" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Tidak ada data submission yang ditugaskan ke Anda</p>
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
function scrollToColumn(colName) {
    const wrapper = document.querySelector('.monitoring-table-wrapper');
    const col = document.getElementById('col-' + colName);
    if (col && wrapper) {
        const colPosition = col.offsetLeft - wrapper.offsetLeft;
        wrapper.scrollTo({
            left: colPosition - 100,
            behavior: 'smooth'
        });
    }
}
</script>
@endsection
