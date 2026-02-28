@extends('layouts.app')

@section('title', 'Laporan Tim Submit Terbanyak')
@section('page-title', 'Laporan Tim Submit Terbanyak')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row mb-4">
    <!-- Stats Cards -->
    <div class="col-md-4 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Submission</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['total_submissions']) }}</h2>
                        <small>Artikel disubmit</small>
                    </div>
                    <i class="bi bi-send-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total PIC Submit</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['total_pic_submit']) }}</h2>
                        <small>PIC yang melakukan submit</small>
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
                        <h6 class="mb-0">Top Submitter</h6>
                        <h2 class="mb-0 fw-bold">{{ $stats['top_submitter'] ? $stats['top_submitter']->pic_name : '-' }}</h2>
                        <small>{{ $stats['top_submitter'] ? number_format($stats['top_submitter']->total_submit) . ' submission' : '' }}</small>
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
                <a href="{{ route('admin.team-submit-report') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
                <a href="{{ route('admin.team-submit-report', array_merge(request()->query(), ['export' => 'pdf'])) }}" class="btn btn-danger">
                    <i class="bi bi-file-pdf"></i> Export PDF
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Ranking Table -->
<div class="card">
    <div class="card-header bg-success text-white">
        <i class="bi bi-trophy-fill"></i> Peringkat Tim Submit Terbanyak
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 70px;">Rank</th>
                        <th>Nama PIC</th>
                        <th class="text-center">Total Submit</th>
                        <th class="text-center">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($picSubmits as $item)
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
                                <div class="avatar-circle bg-success me-2">
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
                            <span class="badge bg-success" style="font-size: 1rem;">
                                {{ number_format($item->total_submit) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $percentage = $stats['total_submissions'] > 0 ? ($item->total_submit / $stats['total_submissions']) * 100 : 0;
                            @endphp
                            <div class="progress" style="height: 20px; min-width: 100px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $percentage }}%">
                                    {{ number_format($percentage, 1) }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mb-0">Belum ada data submission</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer text-muted">
        Total {{ $picSubmits->count() }} PIC yang melakukan submit
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
