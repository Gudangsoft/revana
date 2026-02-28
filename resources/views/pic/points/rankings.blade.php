@extends('pic.layouts.app')

@section('title', 'Peringkat Point')
@section('page-title', 'Peringkat Point')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
{{-- My Rank Banner --}}
@if($currentPicRank)
<div class="alert alert-info mb-4">
    <div class="d-flex align-items-center">
        <i class="bi bi-award-fill me-3" style="font-size: 2rem;"></i>
        <div>
            <h5 class="mb-1">Peringkat Anda: <strong>#{{ $currentPicRank }}</strong> dari {{ $activePicCount }} PIC</h5>
            <p class="mb-0">Total Point: <strong>{{ number_format($currentPic->total_points ?? 0) }}</strong></p>
        </div>
    </div>
</div>
@endif

{{-- Statistics Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Total Point PIC</h6>
                        <h3 class="mb-0">{{ number_format($totalPicPoints) }}</h3>
                    </div>
                    <i class="bi bi-trophy-fill" style="font-size: 2.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Total Point Marketing</h6>
                        <h3 class="mb-0">{{ number_format($totalMarketingPoints) }}</h3>
                    </div>
                    <i class="bi bi-trophy-fill" style="font-size: 2.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">PIC Aktif</h6>
                        <h3 class="mb-0">{{ number_format($activePicCount) }}</h3>
                    </div>
                    <i class="bi bi-person-badge-fill" style="font-size: 2.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Marketing Aktif</h6>
                        <h3 class="mb-0">{{ number_format($activeMarketingCount) }}</h3>
                    </div>
                    <i class="bi bi-megaphone-fill" style="font-size: 2.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- PIC Point Ranking --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-trophy-fill"></i> Peringkat Point PIC
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="text-center" style="width: 70px;">Rank</th>
                                <th>Nama PIC</th>
                                <th class="text-center">Total Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($picRankings as $pic)
                            <tr class="{{ $pic->id == $currentPic->id ? 'table-info' : ($pic->rank <= 3 ? 'table-warning' : '') }}">
                                <td class="text-center">
                                    @if($pic->rank == 1)
                                        <span class="badge bg-warning text-dark" style="font-size: 1.1rem;">
                                            <i class="bi bi-trophy-fill"></i> 1
                                        </span>
                                    @elseif($pic->rank == 2)
                                        <span class="badge bg-secondary" style="font-size: 1.1rem;">
                                            <i class="bi bi-award-fill"></i> 2
                                        </span>
                                    @elseif($pic->rank == 3)
                                        <span class="badge bg-danger" style="font-size: 1.1rem;">
                                            <i class="bi bi-award"></i> 3
                                        </span>
                                    @else
                                        <span class="text-muted fw-bold">{{ $pic->rank }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary me-2">
                                            {{ strtoupper(substr($pic->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong>{{ $pic->name }}</strong>
                                            @if($pic->id == $currentPic->id)
                                                <span class="badge bg-info ms-1">Anda</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary" style="font-size: 1rem;">
                                        {{ number_format($pic->total_points ?? 0) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0">Belum ada data PIC</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-muted">
                Total {{ $picRankings->count() }} PIC aktif
            </div>
        </div>
    </div>

    {{-- Marketing Point Ranking --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <i class="bi bi-trophy-fill"></i> Peringkat Point Marketing
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="text-center" style="width: 70px;">Rank</th>
                                <th>Nama Marketing</th>
                                <th class="text-center">Total Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($marketingRankings as $marketing)
                            <tr class="{{ $marketing->rank <= 3 ? 'table-success' : '' }}">
                                <td class="text-center">
                                    @if($marketing->rank == 1)
                                        <span class="badge bg-warning text-dark" style="font-size: 1.1rem;">
                                            <i class="bi bi-trophy-fill"></i> 1
                                        </span>
                                    @elseif($marketing->rank == 2)
                                        <span class="badge bg-secondary" style="font-size: 1.1rem;">
                                            <i class="bi bi-award-fill"></i> 2
                                        </span>
                                    @elseif($marketing->rank == 3)
                                        <span class="badge bg-danger" style="font-size: 1.1rem;">
                                            <i class="bi bi-award"></i> 3
                                        </span>
                                    @else
                                        <span class="text-muted fw-bold">{{ $marketing->rank }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-success me-2">
                                            {{ strtoupper(substr($marketing->name, 0, 1)) }}
                                        </div>
                                        <strong>{{ $marketing->name }}</strong>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success" style="font-size: 1rem;">
                                        {{ number_format($marketing->total_points ?? 0) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0">Belum ada data Marketing</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-muted">
                Total {{ $marketingRankings->count() }} Marketing aktif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1rem;
    flex-shrink: 0;
}

.table-responsive {
    scrollbar-width: thin;
}

.table-responsive::-webkit-scrollbar {
    width: 6px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 1;
}
</style>
@endpush
