@extends('layouts.app')

@section('title', 'Detail Reviewer - REVANA')
@section('page-title', 'Detail Reviewer')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.journals.index') }}" class="nav-link">
        <i class="bi bi-journal-text"></i> Journals
    </a>
    <a href="{{ route('admin.assignments.index') }}" class="nav-link">
        <i class="bi bi-clipboard-check"></i> Review Assignments
    </a>
    <a href="{{ route('admin.reviewers.index') }}" class="nav-link active">
        <i class="bi bi-people"></i> Reviewers
    </a>
    <a href="{{ route('admin.leaderboard.index') }}" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="{{ route('admin.redemptions.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
    <a href="{{ route('admin.points.index') }}" class="nav-link">
        <i class="bi bi-coin"></i> Point Management
    </a>
@endsection

@section('content')
<!-- Back Button -->
<div class="mb-3">
    <a href="{{ route('admin.reviewers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<!-- Profile Card -->
<div class="row">
    <div class="col-md-12">
        <div class="card" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important; border: none;">
            <div class="card-body" style="color: white !important;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 style="color: white !important;"><i class="bi bi-person-circle"></i> {{ $reviewer->name }}</h3>
                        <p class="mb-2" style="color: rgba(255,255,255,0.9) !important;">{{ $reviewer->email }}</p>
                        <div class="d-flex gap-3 mt-3">
                            <div>
                                <h4 class="mb-0" style="color: white !important;">{{ $reviewer->total_points }}</h4>
                                <small style="color: rgba(255,255,255,0.8) !important;">Total Points</small>
                            </div>
                            <div>
                                <h4 class="mb-0" style="color: white !important;">{{ $reviewer->available_points }}</h4>
                                <small style="color: rgba(255,255,255,0.8) !important;">Available Points</small>
                            </div>
                            <div>
                                <h4 class="mb-0" style="color: white !important;">{{ $reviewer->completed_reviews }}</h4>
                                <small style="color: rgba(255,255,255,0.8) !important;">Completed Reviews</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        @foreach($reviewer->badges as $badge)
                        <span class="badge bg-warning text-dark me-1" style="font-size: 1.2rem;" title="{{ $badge->description }}">
                            {{ $badge->icon }} {{ $badge->name }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Assignments -->
<div class="row mt-4">
    <!-- Statistics Cards -->
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-warning">Pending</h5>
                <h2>{{ $reviewer->reviewAssignments->where('status', 'pending')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-info">In Progress</h5>
                <h2>{{ $reviewer->reviewAssignments->whereIn('status', ['accepted', 'submitted'])->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-success">Completed</h5>
                <h2>{{ $reviewer->reviewAssignments->where('status', 'approved')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-danger">Rejected</h5>
                <h2>{{ $reviewer->reviewAssignments->where('status', 'rejected')->count() }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clipboard-check"></i> Riwayat Review Assignments
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Jurnal</th>
                                <th>Akreditasi</th>
                                <th>Points</th>
                                <th>Status</th>
                                <th>Tanggal Assignment</th>
                                <th>Tanggal Selesai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviewer->reviewAssignments as $assignment)
                            <tr>
                                <td>
                                    <strong>{{ Str::limit($assignment->journal->title, 50) }}</strong>
                                    @if($assignment->reviewResult)
                                        <br><small class="text-muted"><i class="bi bi-file-text"></i> Review submitted</small>
                                    @endif
                                </td>
                                <td>{{ $assignment->journal->accreditation }}</td>
                                <td><span class="badge bg-info">{{ $assignment->journal->points }} pts</span></td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'accepted' => 'info',
                                            'rejected' => 'danger',
                                            'submitted' => 'success',
                                            'approved' => 'success',
                                        ];
                                        $color = $statusColors[$assignment->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ ucfirst($assignment->status) }}</span>
                                </td>
                                <td>{{ $assignment->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    @if($assignment->approved_at)
                                        {{ $assignment->approved_at->format('d M Y H:i') }}
                                    @elseif($assignment->submitted_at)
                                        {{ $assignment->submitted_at->format('d M Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.assignments.show', $assignment) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada review assignment</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Point History -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Riwayat Points
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th>Deskripsi</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviewer->pointHistories()->latest()->take(20)->get() as $history)
                            <tr>
                                <td>{{ $history->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    @if($history->type == 'EARNED')
                                        <span class="badge bg-success">Earned</span>
                                    @else
                                        <span class="badge bg-danger">Redeemed</span>
                                    @endif
                                </td>
                                <td>{{ $history->description }}</td>
                                <td>
                                    @if($history->type == 'EARNED')
                                        <span class="text-success">+{{ $history->points }}</span>
                                    @else
                                        <span class="text-danger">-{{ $history->points }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada riwayat points</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
