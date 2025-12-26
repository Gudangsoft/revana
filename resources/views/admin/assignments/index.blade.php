@extends('layouts.app')

@section('title', 'Review Assignments - REVANA')
@section('page-title', 'Daftar Review Assignment')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.journals.index') }}" class="nav-link">
        <i class="bi bi-journal-text"></i> Jurnal
    </a>
    <a href="{{ route('admin.assignments.index') }}" class="nav-link active">
        <i class="bi bi-clipboard-check"></i> Review Assignments
    </a>
    <a href="{{ route('admin.reviewers.index') }}" class="nav-link">
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
    <a href="{{ route('admin.rewards.index') }}" class="nav-link">
        <i class="bi bi-trophy"></i> Reward Management
    </a>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col">
        <a href="{{ route('admin.assignments.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Assign Reviewer Baru
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card">
    <div class="card-header">
        <i class="bi bi-list-ul"></i> Semua Review Assignments
    </div>
    <div class="card-body">
        @if($assignments->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada assignment yang dibuat.
            <a href="{{ route('admin.assignments.create') }}">Buat assignment pertama</a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="hide-mobile">#</th>
                        <th>Jurnal</th>
                        <th class="hide-mobile">Reviewer</th>
                        <th>Status</th>
                        <th class="hide-mobile">Assigned By</th>
                        <th class="hide-mobile">Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignments as $assignment)
                    <tr>
                        <td class="hide-mobile">{{ $assignment->id }}</td>
                        <td>
                            <div class="fw-bold">{{ Str::limit($assignment->journal->title, 40) }}</div>
                            <small class="text-muted d-block d-md-inline">
                                <span class="badge bg-secondary">{{ $assignment->journal->accreditation }}</span>
                                {{ $assignment->journal->points }} pts
                            </small>
                            <div class="d-md-none mt-1">
                                <small class="text-muted"><i class="bi bi-person"></i> {{ $assignment->reviewer->name }}</small>
                            </div>
                        </td>
                        <td class="hide-mobile">
                            <div>{{ $assignment->reviewer->name }}</div>
                            <small class="text-muted">{{ $assignment->reviewer->email }}</small>
                        </td>
                        <td>
                            @if($assignment->status === 'pending')
                                <span class="badge bg-warning">
                                    <i class="bi bi-clock"></i> Pending
                                </span>
                            @elseif($assignment->status === 'accepted')
                                <span class="badge bg-info">
                                    <i class="bi bi-check"></i> Accepted
                                </span>
                            @elseif($assignment->status === 'rejected')
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle"></i> Rejected
                                </span>
                            @elseif($assignment->status === 'submitted')
                                <span class="badge bg-primary">
                                    <i class="bi bi-send"></i> Submitted
                                </span>
                            @elseif($assignment->status === 'approved')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Approved
                                </span>
                            @endif
                        </td>
                        <td class="hide-mobile">
                            <small>{{ $assignment->assignedBy->name }}</small>
                        </td>
                        <td class="hide-mobile">
                            <small>{{ $assignment->created_at->format('d M Y') }}</small>
                            <br>
                            <small class="text-muted">{{ $assignment->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.assignments.show', $assignment) }}" 
                                   class="btn btn-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($assignment->status === 'pending')
                                <form action="{{ route('admin.assignments.destroy', $assignment) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus assignment ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $assignments->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Stats Summary -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-muted">Total</h5>
                <h2>{{ $assignments->total() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-warning">Pending</h5>
                <h2>{{ $assignments->where('status', 'pending')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-info">Accepted</h5>
                <h2>{{ $assignments->where('status', 'accepted')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-success">Completed</h5>
                <h2>{{ $assignments->where('status', 'approved')->count() }}</h2>
            </div>
        </div>
    </div>
</div>
@endsection
