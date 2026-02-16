@extends('pic.layouts.app')

@section('title', 'Monitoring Proses Review')
@section('page-title', 'Monitoring Proses Review')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('styles')
<style>
    .progress-card {
        border-left: 4px solid;
        transition: all 0.3s;
    }
    .progress-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .progress-card.submitted { border-left-color: #6c757d; }
    .progress-card.in_review { border-left-color: #0dcaf0; }
    .progress-card.revision { border-left-color: #ffc107; }
    .progress-card.accepted { border-left-color: #198754; }
    .progress-card.published { border-left-color: #0d6efd; }
    .progress-card.rejected { border-left-color: #dc3545; }
    
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 15px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #6c757d;
    }
    .timeline-item.active::before {
        background: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
    }
    .timeline-item.completed::before {
        background: #198754;
    }
</style>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['new'] ?? 0 }}</h3>
                <small>New</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['in_progress'] ?? 0 }}</h3>
                <small>In Progress</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['published'] ?? 0 }}</h3>
                <small>Published</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="journal_id" class="form-select">
                    <option value="">Semua Jurnal</option>
                    @foreach($journals as $journal)
                        <option value="{{ $journal->id }}" {{ request('journal_id') == $journal->id ? 'selected' : '' }}>
                            {{ $journal->nama_jurnal }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>Submitted</option>
                    <option value="EDITOR1" {{ request('status') == 'EDITOR1' ? 'selected' : '' }}>Editor 1</option>
                    <option value="AUTHOR1" {{ request('status') == 'AUTHOR1' ? 'selected' : '' }}>Author 1</option>
                    <option value="EDITOR2" {{ request('status') == 'EDITOR2' ? 'selected' : '' }}>Editor 2</option>
                    <option value="REVIEWER1" {{ request('status') == 'REVIEWER1' ? 'selected' : '' }}>Reviewer 1</option>
                    <option value="REVIEWER2" {{ request('status') == 'REVIEWER2' ? 'selected' : '' }}>Reviewer 2</option>
                    <option value="EDITOR3" {{ request('status') == 'EDITOR3' ? 'selected' : '' }}>Editor 3</option>
                    <option value="AUTHOR2" {{ request('status') == 'AUTHOR2' ? 'selected' : '' }}>Author 2</option>
                    <option value="PRODUCTION" {{ request('status') == 'PRODUCTION' ? 'selected' : '' }}>Production</option>
                    <option value="PUBLISHED" {{ request('status') == 'PUBLISHED' ? 'selected' : '' }}>Published</option>
                    <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Cari judul/penulis..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('pic.submissions.monitoring') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Submission Cards -->
<div class="row">
    @forelse($submissions as $submission)
    <div class="col-md-6 mb-3">
        <div class="card progress-card {{ $submission->status }}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <code>{{ $submission->kode_submit }}</code>
                </span>
                @php
                    $statusColors = [
                        'new' => 'secondary',
                        'in_progress' => 'info',
                        'published' => 'success',
                    ];
                @endphp
                <span class="badge bg-{{ $statusColors[$submission->status] ?? 'secondary' }} status-badge">
                    {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                </span>
            </div>
            <div class="card-body">
                <h6 class="card-title text-primary mb-2">{{ Str::limit($submission->judul_artikel, 60) }}</h6>
                <p class="card-text small mb-2">
                    <i class="bi bi-person"></i> {{ $submission->nama_penulis }}<br>
                    <i class="bi bi-journal"></i> 
                    @if($submission->journalSlot && $submission->journalSlot->journalMaster)
                        {{ $submission->journalSlot->journalMaster->nama_jurnal }}
                    @else
                        -
                    @endif
                </p>
                
                <!-- Workflow Progress -->
                <div class="row text-center small mt-3">
                    <div class="col">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->editor1_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 25px; height: 25px;">
                            <i class="bi bi-check text-white" style="font-size: 0.7rem;"></i>
                        </div>
                        <div class="text-muted" style="font-size: 0.65rem;">E1</div>
                    </div>
                    <div class="col">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->author1_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 25px; height: 25px;">
                            <i class="bi bi-check text-white" style="font-size: 0.7rem;"></i>
                        </div>
                        <div class="text-muted" style="font-size: 0.65rem;">A1</div>
                    </div>
                    <div class="col">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->editor2_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 25px; height: 25px;">
                            <i class="bi bi-check text-white" style="font-size: 0.7rem;"></i>
                        </div>
                        <div class="text-muted" style="font-size: 0.65rem;">E2</div>
                    </div>
                    <div class="col">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->reviewer1_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 25px; height: 25px;">
                            <i class="bi bi-check text-white" style="font-size: 0.7rem;"></i>
                        </div>
                        <div class="text-muted" style="font-size: 0.65rem;">R1</div>
                    </div>
                    <div class="col">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->reviewer2_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 25px; height: 25px;">
                            <i class="bi bi-check text-white" style="font-size: 0.7rem;"></i>
                        </div>
                        <div class="text-muted" style="font-size: 0.65rem;">R2</div>
                    </div>
                    <div class="col">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->editor3_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 25px; height: 25px;">
                            <i class="bi bi-check text-white" style="font-size: 0.7rem;"></i>
                        </div>
                        <div class="text-muted" style="font-size: 0.65rem;">E3</div>
                    </div>
                    <div class="col">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->author2_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 25px; height: 25px;">
                            <i class="bi bi-check text-white" style="font-size: 0.7rem;"></i>
                        </div>
                        <div class="text-muted" style="font-size: 0.65rem;">A2</div>
                    </div>
                    <div class="col">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->production_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 25px; height: 25px;">
                            <i class="bi bi-check text-white" style="font-size: 0.7rem;"></i>
                        </div>
                        <div class="text-muted" style="font-size: 0.65rem;">P</div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">
                        <i class="bi bi-calendar"></i> {{ $submission->tanggal_submit ? \Carbon\Carbon::parse($submission->tanggal_submit)->format('d/m/Y') : $submission->created_at->format('d/m/Y') }}
                    </small>
                    <a href="{{ route('pic.submissions.show', $submission) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> Detail
                    </a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle"></i> Belum ada data submission untuk ditampilkan
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $submissions->appends(request()->query())->links() }}
</div>
@endsection
