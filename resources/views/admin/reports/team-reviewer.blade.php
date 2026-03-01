@extends('layouts.app')

@section('title', 'Laporan Tim Reviewer Terbanyak')
@section('page-title', 'Laporan Tim Reviewer Terbanyak')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row mb-4">
    <!-- Stats Cards -->
    <div class="col-md-4 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Review</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['total_reviews']) }}</h2>
                        <small>Tugas review selesai</small>
                    </div>
                    <i class="bi bi-person-check-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Reviewer</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['total_reviewers']) }}</h2>
                        <small>PIC yang melakukan review</small>
                    </div>
                    <i class="bi bi-people-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Top Reviewer</h6>
                        <h2 class="mb-0 fw-bold">{{ $stats['top_reviewer'] ? $stats['top_reviewer']->pic_name : '-' }}</h2>
                        <small>{{ $stats['top_reviewer'] ? number_format($stats['top_reviewer']->total_review) . ' review' : '' }}</small>
                    </div>
                    <i class="bi bi-trophy-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-funnel"></i> Filter Laporan
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('admin.team-reviewer-report') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
                <a href="{{ route('admin.team-reviewer-report', array_merge(request()->query(), ['export' => 'pdf'])) }}" class="btn btn-danger">
                    <i class="bi bi-file-pdf"></i> Export PDF
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Ranking Table -->
<div class="card">
    <div class="card-header bg-info text-white">
        <i class="bi bi-trophy-fill"></i> Peringkat Tim Reviewer Terbanyak
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 70px;">Rank</th>
                        <th>Nama PIC</th>
                        <th class="text-center">Reviewer 1</th>
                        <th class="text-center">Reviewer 2</th>
                        <th class="text-center">Total Review</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviewerStats as $item)
                    <tr class="{{ $item->rank <= 3 ? 'table-warning' : '' }}">
                        <td class="text-center">
                            @if($item->rank == 1)
                                <span class="badge bg-warning text-dark" style="font-size: 1.1rem;">
                                    <i class="bi bi-trophy-fill"></i> 1
                                </span>
                            @elseif($item->rank == 2)
                                <span class="badge bg-secondary" style="font-size: 1.1rem;">
                                    <i class="bi bi-award-fill"></i> 2
                                </span>
                            @elseif($item->rank == 3)
                                <span class="badge bg-danger" style="font-size: 1.1rem;">
                                    <i class="bi bi-award"></i> 3
                                </span>
                            @else
                                <span class="text-muted fw-bold">{{ $item->rank }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-info me-2">
                                    {{ strtoupper(substr($item->pic_name, 0, 1)) }}
                                </div>
                                <div>
                                    <strong>{{ $item->pic_name }}</strong>
                                    @if($item->pic && !$item->pic->is_active)
                                        <span class="badge bg-secondary ms-1">Nonaktif</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ number_format($item->reviewer1_count) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ number_format($item->reviewer2_count) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info" style="font-size: 1rem;">
                                {{ number_format($item->total_review) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mb-0">Belum ada data review</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer text-muted">
        Total {{ $reviewerStats->count() }} PIC yang melakukan review
    </div>
</div>

<style>
.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.9rem;
    flex-shrink: 0;
}
</style>
@endsection
