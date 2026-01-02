@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Detail Review Artikel')

@section('sidebar')
    <a href="{{ route('monitoring.index') }}" class="nav-link">
        <i class="bi bi-arrow-left"></i> Kembali ke Monitoring
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Journal Info Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-journal-text"></i> Informasi Artikel</h5>
            </div>
            <div class="card-body">
                <h4 class="mb-3">{{ $journal->title }}</h4>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" style="width: 150px;">Akreditasi:</td>
                                <td><span class="badge bg-primary">{{ $journal->accreditation }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Points:</td>
                                <td><strong>{{ $journal->points }}</strong> points</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Penerbit:</td>
                                <td>{{ $journal->publisher ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Marketing:</td>
                                <td>{{ $journal->marketing ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" style="width: 150px;">PIC:</td>
                                <td>{{ $journal->pic ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Username Author:</td>
                                <td>{{ $journal->author_username ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Password Author:</td>
                                <td>{{ $journal->author_password ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Dibuat oleh:</td>
                                <td>{{ $journal->creator->name ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Link Jurnal:</strong><br>
                    <a href="{{ $journal->link }}" target="_blank" class="text-decoration-none">
                        <i class="bi bi-link-45deg"></i> {{ $journal->link }}
                    </a>
                </div>

                @if($journal->turnitin_link)
                <div>
                    <strong>Link Turnitin:</strong><br>
                    <a href="{{ $journal->turnitin_link }}" target="_blank" class="text-decoration-none">
                        <i class="bi bi-link-45deg"></i> {{ $journal->turnitin_link }}
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Review Assignments Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Status Review per Reviewer</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0">Reviewer</th>
                                <th class="border-0">Status Assignment</th>
                                <th class="border-0">Status Review</th>
                                <th class="border-0">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journal->assignments as $assignment)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle fs-3 text-muted me-2"></i>
                                        <div>
                                            <strong>{{ $assignment->reviewer->name }}</strong><br>
                                            <small class="text-muted">{{ $assignment->reviewer->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($assignment->status === 'PENDING')
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock"></i> Pending
                                        </span>
                                    @elseif(in_array($assignment->status, ['ACCEPTED', 'ON_PROGRESS']))
                                        <span class="badge bg-info">
                                            <i class="bi bi-hourglass-split"></i> In Progress
                                        </span>
                                    @elseif($assignment->status === 'SUBMITTED')
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-check"></i> Submitted
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Completed
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($assignment->result)
                                        @if($assignment->status === 'SUBMITTED')
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-hourglass"></i> Menunggu Approval
                                            </span>
                                        @elseif($assignment->status === 'APPROVED')
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle-fill"></i> Approved
                                            </span>
                                        @elseif($assignment->status === 'REVISION')
                                            <span class="badge bg-danger">
                                                <i class="bi bi-arrow-repeat"></i> Revision
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        Assigned: {{ $assignment->created_at->format('d M Y H:i') }}<br>
                                        @if($assignment->result)
                                            Submitted: {{ $assignment->result->created_at->format('d M Y H:i') }}
                                        @endif
                                    </small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada reviewer yang ditugaskan
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Progress Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-graph-up"></i> Progress Review</h6>
            </div>
            <div class="card-body text-center">
                @php
                    $totalAssignments = $journal->assignments->count();
                    $completedAssignments = $journal->assignments->filter(function($a) {
                        return $a->status === 'APPROVED';
                    })->count();
                    $percentage = $totalAssignments > 0 ? round(($completedAssignments / $totalAssignments) * 100) : 0;
                @endphp
                
                <div class="position-relative d-inline-block mb-3">
                    <svg width="150" height="150" viewBox="0 0 150 150">
                        <circle cx="75" cy="75" r="65" fill="none" stroke="#e9ecef" stroke-width="12"/>
                        <circle cx="75" cy="75" r="65" fill="none" 
                            stroke="@if($percentage == 100) #28a745 @elseif($percentage > 50) #17a2b8 @else #ffc107 @endif" 
                            stroke-width="12" 
                            stroke-dasharray="{{ 2 * 3.14159 * 65 }}" 
                            stroke-dashoffset="{{ 2 * 3.14159 * 65 * (1 - $percentage / 100) }}"
                            transform="rotate(-90 75 75)"
                            stroke-linecap="round"/>
                    </svg>
                    <div class="position-absolute top-50 start-50 translate-middle">
                        <h2 class="mb-0">{{ $percentage }}%</h2>
                    </div>
                </div>
                
                <h5 class="mb-1">{{ $completedAssignments }} dari {{ $totalAssignments }}</h5>
                <p class="text-muted mb-0">Review Selesai</p>
            </div>
        </div>

        <!-- Timeline Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Timeline</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item mb-3">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <small class="text-muted">{{ $journal->created_at->format('d M Y H:i') }}</small>
                            <p class="mb-0"><strong>Artikel dibuat</strong></p>
                        </div>
                    </div>
                    
                    @foreach($journal->assignments as $assignment)
                    <div class="timeline-item mb-3">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <small class="text-muted">{{ $assignment->created_at->format('d M Y H:i') }}</small>
                            <p class="mb-0">Ditugaskan ke <strong>{{ $assignment->reviewer->name }}</strong></p>
                        </div>
                    </div>
                    
                    @if($assignment->result)
                    <div class="timeline-item mb-3">
                        <div class="timeline-marker 
                            @if($assignment->status === 'APPROVED') bg-success
                            @elseif($assignment->status === 'REVISION') bg-danger
                            @else bg-secondary
                            @endif">
                        </div>
                        <div class="timeline-content">
                            <small class="text-muted">{{ $assignment->result->created_at->format('d M Y H:i') }}</small>
                            <p class="mb-0">
                                Review 
                                @if($assignment->status === 'APPROVED')
                                    <strong class="text-success">Approved</strong>
                                @elseif($assignment->status === 'REVISION')
                                    <strong class="text-danger">Revision</strong>
                                @else
                                    <strong>Submitted</strong>
                                @endif
                                oleh {{ $assignment->reviewer->name }}
                            </p>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    position: absolute;
    left: -27px;
    top: 4px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    padding-left: 10px;
}
</style>
@endsection
