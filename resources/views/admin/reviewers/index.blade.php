@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Data Reviewer')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stats-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Reviewer</h6>
                        <h2 class="mb-0">{{ $reviewers->total() }}</h2>
                    </div>
                    <div class="text-primary" style="font-size: 2.5rem;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Reviews</h6>
                        <h2 class="mb-0">{{ $reviewers->sum('completed_reviews') }}</h2>
                    </div>
                    <div class="text-success" style="font-size: 2.5rem;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Points</h6>
                        <h2 class="mb-0">{{ $reviewers->sum('total_points') }}</h2>
                    </div>
                    <div class="text-warning" style="font-size: 2.5rem;">
                        <i class="bi bi-coin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reviewer List -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people"></i> Daftar Reviewer</span>
                <div>
                    <span class="badge bg-primary">Total: {{ $reviewers->total() }} Reviewer</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Total Points</th>
                                <th>Available Points</th>
                                <th>Completed Reviews</th>
                                <th>Active Tasks</th>
                                <th>Badges</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviewers as $reviewer)
                            <tr>
                                <td>
                                    <strong>{{ $reviewer->name }}</strong>
                                </td>
                                <td>{{ $reviewer->email }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $reviewer->total_points }} pts</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ $reviewer->available_points }} pts</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $reviewer->completed_reviews }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $reviewer->review_assignments_count }}</span>
                                </td>
                                <td>
                                    @foreach($reviewer->badges as $badge)
                                    <span class="badge bg-secondary" title="{{ $badge->description }}">
                                        {{ $badge->icon }}
                                    </span>
                                    @endforeach
                                    @if($reviewer->badges->count() == 0)
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.reviewers.show', $reviewer) }}" class="btn btn-sm btn-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.reviewers.edit', $reviewer) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada data reviewer</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($reviewers->hasPages())
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        @foreach(range(1, $reviewers->lastPage()) as $page)
                            <li class="page-item {{ $page == $reviewers->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $reviewers->url($page) }}">{{ $page }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

