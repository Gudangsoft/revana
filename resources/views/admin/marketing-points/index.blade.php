@extends('layouts.app')

@section('title', 'Laporan Point Marketing - ' . $appSettings['app_name'])
@section('page-title', 'Laporan Point Marketing')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    @if(session('success'))
    <div class="col-md-12 mb-3">
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Marketing Aktif</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($totalMarketings) }}</h2>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Point</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($totalPoints) }}</h2>
                    </div>
                    <i class="bi bi-trophy fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Submission</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($totalSubmissions) }}</h2>
                    </div>
                    <i class="bi bi-file-earmark-text fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Top Bulan Ini</h6>
                        <h5 class="mb-0 fw-bold">
                            {{ $topPerformerThisMonth?->name ?? 'N/A' }}
                        </h5>
                        @if($topPerformerThisMonth)
                        <small>{{ number_format($topPerformerThisMonth->points_this_month ?? 0) }} point</small>
                        @endif
                    </div>
                    <i class="bi bi-star fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Leaderboard -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy"></i> Leaderboard Marketing</span>
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" 
                           placeholder="Cari nama/email..." value="{{ request('search') }}" style="width: 200px;">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    @if(request('search'))
                    <a href="{{ route('admin.marketing-points.index') }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                    @endif
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="60">Rank</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th class="text-center">Total Submission</th>
                                <th class="text-center">Total Point</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($marketings as $index => $marketing)
                            <tr>
                                <td>
                                    @php
                                        $rank = ($marketings->currentPage() - 1) * $marketings->perPage() + $index + 1;
                                    @endphp
                                    @if($rank == 1)
                                        <span class="badge bg-warning text-dark"><i class="bi bi-trophy"></i> 1</span>
                                    @elseif($rank == 2)
                                        <span class="badge bg-secondary"><i class="bi bi-trophy"></i> 2</span>
                                    @elseif($rank == 3)
                                        <span class="badge bg-danger"><i class="bi bi-trophy"></i> 3</span>
                                    @else
                                        <span class="text-muted">{{ $rank }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $marketing->name }}</strong>
                                </td>
                                <td>{{ $marketing->email ?? '-' }}</td>
                                <td>{{ $marketing->phone ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $marketing->submissions->count() }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success fs-6">{{ number_format($marketing->total_points ?? 0) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.marketing-points.show', $marketing) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Tidak ada data marketing
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($marketings->hasPages())
            <div class="card-footer">
                {{ $marketings->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto refresh every 30 seconds
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>
@endsection
