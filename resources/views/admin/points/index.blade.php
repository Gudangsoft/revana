@extends('layouts.app')

@section('title', 'Point Management - REVANA')
@section('page-title', 'Manajemen Point')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.journals.index') }}" class="nav-link">
        <i class="bi bi-journal-text"></i> Jurnal
    </a>
    <a href="{{ route('admin.assignments.index') }}" class="nav-link">
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
    <a href="{{ route('admin.points.index') }}" class="nav-link active">
        <i class="bi bi-coin"></i> Point Management
    </a>
    <a href="{{ route('admin.rewards.index') }}" class="nav-link">
        <i class="bi bi-trophy"></i> Reward Management
    </a>
    <a href="{{ route('admin.marketings.index') }}" class="nav-link">
        <i class="bi bi-megaphone"></i> Marketing
    </a>
    <a href="{{ route('admin.pics.index') }}" class="nav-link">
        <i class="bi bi-person-badge"></i> PIC
    </a>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col">
        <a href="{{ route('admin.points.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Tambah/Kurangi Point
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
        <i class="bi bi-clock-history"></i> Riwayat Point
    </div>
    <div class="card-body">
        @if($pointHistories->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada riwayat point.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Reviewer</th>
                        <th>Tipe</th>
                        <th>Deskripsi</th>
                        <th>Points</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pointHistories as $history)
                    <tr>
                        <td>{{ $history->id }}</td>
                        <td>
                            {{ $history->created_at->format('d M Y H:i') }}
                            <br>
                            <small class="text-muted">{{ $history->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $history->user->name }}</div>
                            <small class="text-muted">{{ $history->user->email }}</small>
                        </td>
                        <td>
                            @if($history->type === 'EARNED')
                                <span class="badge bg-success">
                                    <i class="bi bi-arrow-up-circle"></i> Earned
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-arrow-down-circle"></i> Redeemed
                                </span>
                            @endif
                        </td>
                        <td>{{ $history->description }}</td>
                        <td>
                            @if($history->type === 'EARNED')
                                <span class="text-success fw-bold">+{{ number_format($history->points) }}</span>
                            @else
                                <span class="text-danger fw-bold">-{{ number_format($history->points) }}</span>
                            @endif
                        </td>
                        <td>
                            @if(!$history->review_assignment_id && !$history->reward_redemption_id)
                            <form action="{{ route('admin.points.destroy', $history) }}" method="POST" 
                                  onsubmit="return confirm('Hapus point history ini? Points akan dikembalikan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @else
                                <small class="text-muted">Auto</small>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $pointHistories->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

