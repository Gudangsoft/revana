@extends('marketing.layouts.app')

@section('title', 'Monitoring Artikel')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-file-earmark-text"></i> Artikel Saya
    </h4>
    <div>
        <a href="{{ route('marketing.submissions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Submit Artikel Baru
        </a>
        <span class="badge bg-secondary fs-6 ms-2">Total: {{ $submissions->total() }} artikel</span>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" 
                       placeholder="Cari kode/judul/penulis..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>Submitted</option>
                    <option value="UNDER_REVIEW" {{ request('status') == 'UNDER_REVIEW' ? 'selected' : '' }}>Under Review</option>
                    <option value="EDITING" {{ request('status') == 'EDITING' ? 'selected' : '' }}>Editing</option>
                    <option value="LAYOUT" {{ request('status') == 'LAYOUT' ? 'selected' : '' }}>Layout</option>
                    <option value="PRODUCTION" {{ request('status') == 'PRODUCTION' ? 'selected' : '' }}>Production</option>
                    <option value="PUBLISHED" {{ request('status') == 'PUBLISHED' ? 'selected' : '' }}>Published</option>
                    <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('marketing.submissions') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

@if($submissions->count() > 0)
<!-- Submissions Cards -->
@foreach($submissions as $submission)
@php
    // Define workflow steps
    $workflowSteps = [
        'SUBMITTED' => ['label' => 'Submitted', 'icon' => 'bi-upload', 'order' => 1],
        'REVIEW_ASSIGNED' => ['label' => 'Review Assigned', 'icon' => 'bi-person-check', 'order' => 2],
        'UNDER_REVIEW' => ['label' => 'Under Review', 'icon' => 'bi-search', 'order' => 3],
        'REVISION_REQUIRED' => ['label' => 'Revision Required', 'icon' => 'bi-pencil-square', 'order' => 4],
        'REVISED' => ['label' => 'Revised', 'icon' => 'bi-check2-circle', 'order' => 5],
        'EDITING' => ['label' => 'Editing', 'icon' => 'bi-pen', 'order' => 6],
        'EDITING_SUBMITTED' => ['label' => 'Editing Submitted', 'icon' => 'bi-pen-fill', 'order' => 7],
        'EDITING_COMPLETED' => ['label' => 'Editing Done', 'icon' => 'bi-check-circle', 'order' => 8],
        'LAYOUT' => ['label' => 'Layout', 'icon' => 'bi-layout-text-window', 'order' => 9],
        'LAYOUT_SUBMITTED' => ['label' => 'Layout Submitted', 'icon' => 'bi-layout-text-window-reverse', 'order' => 10],
        'LAYOUT_COMPLETED' => ['label' => 'Layout Done', 'icon' => 'bi-check-circle', 'order' => 11],
        'PRODUCTION' => ['label' => 'Production', 'icon' => 'bi-gear-wide-connected', 'order' => 12],
        'PRODUCTION_SUBMITTED' => ['label' => 'Production Submitted', 'icon' => 'bi-gear-fill', 'order' => 13],
        'PUBLISHED' => ['label' => 'Published', 'icon' => 'bi-check-all', 'order' => 14],
        'REJECTED' => ['label' => 'Rejected', 'icon' => 'bi-x-circle', 'order' => 0],
    ];
    
    $currentStep = $workflowSteps[$submission->status] ?? ['label' => $submission->status, 'icon' => 'bi-question', 'order' => 0];
    $currentOrder = $currentStep['order'];
    
    // Calculate progress percentage
    $maxOrder = 14;
    $progressPct = $submission->status == 'REJECTED' ? 0 : round(($currentOrder / $maxOrder) * 100);
    
    // Determine card border color
    $borderColor = match(true) {
        $submission->status == 'PUBLISHED' => 'success',
        $submission->status == 'REJECTED' => 'danger',
        $currentOrder >= 9 => 'info',
        $currentOrder >= 6 => 'primary',
        default => 'warning'
    };
    
    // Simplified steps for display (group similar steps)
    $displaySteps = [
        ['key' => 'SUBMITTED', 'label' => 'Submit', 'statuses' => ['SUBMITTED']],
        ['key' => 'REVIEW', 'label' => 'Review', 'statuses' => ['REVIEW_ASSIGNED', 'UNDER_REVIEW', 'REVISION_REQUIRED', 'REVISED']],
        ['key' => 'EDITING', 'label' => 'Editing', 'statuses' => ['EDITING', 'EDITING_SUBMITTED', 'EDITING_COMPLETED']],
        ['key' => 'LAYOUT', 'label' => 'Layout', 'statuses' => ['LAYOUT', 'LAYOUT_SUBMITTED', 'LAYOUT_COMPLETED']],
        ['key' => 'PRODUCTION', 'label' => 'Production', 'statuses' => ['PRODUCTION', 'PRODUCTION_SUBMITTED']],
        ['key' => 'PUBLISHED', 'label' => 'Published', 'statuses' => ['PUBLISHED']],
    ];
@endphp
<div class="card mb-3 border-{{ $borderColor }}">
    <div class="card-header bg-{{ $borderColor }} bg-opacity-10 d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('marketing.submissions.show', $submission) }}" class="text-decoration-none text-dark">
                <code class="bg-white px-2 py-1 rounded me-2">{{ $submission->kode_submit }}</code>
                <strong>{{ Str::limit($submission->judul_artikel, 50) }}</strong>
            </a>
        </div>
        <div>
            <a href="{{ route('marketing.submissions.show', $submission) }}" class="btn btn-sm btn-outline-primary me-2">
                <i class="bi bi-eye"></i> Detail
            </a>
            <span class="badge bg-{{ $borderColor }} fs-6">
                <i class="bi {{ $currentStep['icon'] }}"></i> {{ $currentStep['label'] }}
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <small class="text-muted">Jurnal</small>
                <div class="fw-bold">{{ $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Penulis</small>
                <div class="fw-bold">{{ $submission->nama_penulis ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Tanggal Submit</small>
                <div class="fw-bold">{{ $submission->tanggal_submit?->format('d M Y') ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Progress</small>
                <div class="fw-bold text-{{ $borderColor }}">{{ $progressPct }}%</div>
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="progress mb-3" style="height: 8px;">
            <div class="progress-bar bg-{{ $borderColor }}" role="progressbar" 
                 style="width: {{ $progressPct }}%"></div>
        </div>
        
        <!-- Workflow Steps -->
        @if($submission->status != 'REJECTED')
        <div class="d-flex justify-content-between text-center">
            @foreach($displaySteps as $step)
            @php
                $isActive = in_array($submission->status, $step['statuses']);
                $isPassed = false;
                
                // Check if this step is passed
                foreach($step['statuses'] as $st) {
                    if(isset($workflowSteps[$st]) && isset($workflowSteps[$submission->status])) {
                        if($workflowSteps[$submission->status]['order'] > $workflowSteps[$st]['order']) {
                            $isPassed = true;
                            break;
                        }
                    }
                }
            @endphp
            <div class="flex-fill">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center 
                     {{ $isPassed ? 'bg-success' : ($isActive ? 'bg-primary' : 'bg-secondary bg-opacity-25') }} 
                     text-white" style="width: 32px; height: 32px;">
                    @if($isPassed)
                    <i class="bi bi-check"></i>
                    @elseif($isActive)
                    <i class="bi bi-circle-fill small"></i>
                    @else
                    <i class="bi bi-circle small text-muted"></i>
                    @endif
                </div>
                <div class="small mt-1 {{ $isActive ? 'fw-bold text-primary' : ($isPassed ? 'text-success' : 'text-muted') }}">
                    {{ $step['label'] }}
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="alert alert-danger mb-0 py-2">
            <i class="bi bi-x-circle"></i> Artikel ini ditolak
            @if($submission->catatan)
            <br><small>Catatan: {{ $submission->catatan }}</small>
            @endif
        </div>
        @endif
    </div>
</div>
@endforeach

<!-- Pagination -->
<div class="d-flex justify-content-center mt-3">
    {{ $submissions->links() }}
</div>

@else
<div class="card">
    <div class="card-body">
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox" style="font-size: 4rem;"></i>
            <h5 class="mt-3">Belum Ada Artikel</h5>
            <p>Anda belum memiliki artikel yang disubmit.</p>
        </div>
    </div>
</div>
@endif
@endsection
