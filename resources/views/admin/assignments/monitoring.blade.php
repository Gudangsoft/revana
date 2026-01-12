@extends('layouts.app')

@section('title', ' - Monitoring Review')
@section('page-title', 'Monitoring Review')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-graph-up"></i> Monitoring Review</h2>
        <div>
            <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">
                <i class="bi bi-list"></i> List View
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="monitoringTable">
                    <thead class="table-light">
                        <tr>
                            <th width="3%">No</th>
                            <th width="8%">Kode</th>
                            <th width="20%">Judul Artikel</th>
                            <th width="10%">Jurnal</th>
                            <th width="15%">Reviewers</th>
                            <th width="12%">Progress Review</th>
                            <th width="10%">Status</th>
                            <th width="8%">Deadline</th>
                            <th width="7%">Assigned</th>
                            <th width="7%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $index => $assignment)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td><strong>{{ $assignment->article_code }}</strong></td>
                            <td>
                                <div class="text-truncate" style="max-width: 250px;" title="{{ $assignment->article_title }}">
                                    {{ $assignment->article_title }}
                                </div>
                            </td>
                            <td>
                                @if($assignment->journal)
                                    <span class="badge bg-info">{{ $assignment->journal->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $reviewers = [];
                                    if($assignment->reviewer) $reviewers[] = $assignment->reviewer;
                                    if($assignment->reviewer2) $reviewers[] = $assignment->reviewer2;
                                    if($assignment->reviewer3) $reviewers[] = $assignment->reviewer3;
                                    if($assignment->reviewer4) $reviewers[] = $assignment->reviewer4;
                                    if($assignment->reviewer5) $reviewers[] = $assignment->reviewer5;
                                @endphp
                                @if(count($reviewers) > 0)
                                    <small>
                                        @foreach($reviewers as $idx => $rev)
                                            <div class="mb-1">
                                                <span class="badge bg-secondary">R{{ $idx + 1 }}</span>
                                                {{ $rev->name }}
                                            </div>
                                        @endforeach
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $totalReviewers = count($reviewers);
                                    $submittedReviews = $assignment->reviewResults->count();
                                    $progressPercent = $totalReviewers > 0 ? ($submittedReviews / $totalReviewers) * 100 : 0;
                                @endphp
                                <div class="mb-1">
                                    <small><strong>{{ $submittedReviews }}/{{ $totalReviewers }}</strong> Submitted</small>
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $progressPercent == 100 ? 'bg-success' : ($progressPercent > 0 ? 'bg-warning' : 'bg-secondary') }}" 
                                         role="progressbar" 
                                         style="width: {{ $progressPercent }}%"
                                         aria-valuenow="{{ $progressPercent }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ number_format($progressPercent, 0) }}%
                                    </div>
                                </div>
                                @if($submittedReviews > 0)
                                    <small class="text-muted">
                                        @foreach($assignment->reviewResults as $result)
                                            @if($result->reviewer)
                                                <div>✓ {{ $result->reviewer->name }}</div>
                                            @endif
                                        @endforeach
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($assignment->status === 'PENDING')
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-clock"></i> Pending
                                    </span>
                                @elseif($assignment->status === 'ON_PROGRESS')
                                    <span class="badge bg-warning">
                                        <i class="bi bi-hourglass-split"></i> On Progress
                                    </span>
                                @elseif($assignment->status === 'SUBMITTED')
                                    <span class="badge bg-info">
                                        <i class="bi bi-check"></i> Submitted
                                    </span>
                                @elseif($assignment->status === 'REVISION')
                                    <span class="badge bg-danger">
                                        <i class="bi bi-arrow-repeat"></i> Revision
                                    </span>
                                @elseif($assignment->status === 'APPROVED')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Approved
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($assignment->deadline)
                                    @php
                                        $deadline = \Carbon\Carbon::parse($assignment->deadline);
                                        $now = \Carbon\Carbon::now();
                                        $daysRemaining = $now->diffInDays($deadline, false);
                                    @endphp
                                    <div>
                                        <small>{{ $deadline->format('d M Y') }}</small>
                                    </div>
                                    @if($assignment->status !== 'APPROVED')
                                        @if($daysRemaining < 0)
                                            <span class="badge bg-danger">Overdue</span>
                                        @elseif($daysRemaining <= 3)
                                            <span class="badge bg-warning">{{ abs($daysRemaining) }}d left</span>
                                        @else
                                            <span class="badge bg-success">{{ $daysRemaining }}d left</span>
                                        @endif
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $assignment->created_at->format('d/m/Y') }}</small>
                            </td>
                            <td>
                                <a href="{{ route('admin.assignments.show', $assignment) }}" 
                                   class="btn btn-sm btn-primary" 
                                   title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2">Belum ada review assignment</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>{{ $assignments->where('status', 'PENDING')->count() }}</h5>
                    <small>Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>{{ $assignments->where('status', 'ON_PROGRESS')->count() }}</h5>
                    <small>On Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>{{ $assignments->where('status', 'SUBMITTED')->count() }}</h5>
                    <small>Submitted</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>{{ $assignments->where('status', 'APPROVED')->count() }}</h5>
                    <small>Approved</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table td {
        vertical-align: middle;
    }
    .progress {
        background-color: #e9ecef;
    }
</style>

<script>
    // Auto refresh every 30 seconds
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>
@endsection
