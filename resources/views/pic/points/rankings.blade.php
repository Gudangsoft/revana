@extends('pic.layouts.app')

@section('title', 'Peringkat Point')
@section('page-title', 'Peringkat Point')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
{{-- My Rank Banner --}}
@if($currentPicRank && $totalPicPoints > 0)
<div class="card border-info mb-4">
    <div class="card-header bg-info text-white">
        <i class="bi bi-person-badge-fill me-2"></i> Posisi Peringkat Saya
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-3 text-center border-end">
                <div class="display-4 fw-bold text-info">#{{ $currentPicRank }}</div>
                <small class="text-muted">dari {{ $activePicCount }} PIC</small>
            </div>
            <div class="col-md-3 text-center border-end">
                <div class="h4 mb-0 text-primary">{{ number_format($currentPic->total_points ?? 0, 2) }}</div>
                <small class="text-muted">Total Point</small>
            </div>
            <div class="col-md-3 text-center border-end">
                <div class="h4 mb-0 {{ $topPercentage <= 25 ? 'text-success' : ($topPercentage <= 50 ? 'text-warning' : 'text-secondary') }}">
                    Top {{ $topPercentage }}%
                </div>
                <small class="text-muted">Persentase Posisi</small>
            </div>
            <div class="col-md-3 text-center">
                @if($currentPicRank == 1)
                    <div class="h5 mb-0 text-warning">
                        <i class="bi bi-trophy-fill"></i> Peringkat 1
                    </div>
                    <small class="text-muted">Anda yang terbaik!</small>
                @elseif($pointsToNextRank > 0)
                    <div class="h4 mb-0 text-danger">+{{ number_format($pointsToNextRank, 2) }}</div>
                    <small class="text-muted">Point ke peringkat {{ $currentPicRank - 1 }}</small>
                @else
                    <div class="h5 mb-0 text-success">
                        <i class="bi bi-check-circle-fill"></i> Sama dengan di atas
                    </div>
                    <small class="text-muted">Point sama dengan rank {{ $currentPicRank - 1 }}</small>
                @endif
            </div>
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
                        <h3 class="mb-0">{{ number_format($totalPicPoints, 2) }}</h3>
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
                        <h3 class="mb-0">{{ number_format($totalMarketingPoints, 2) }}</h3>
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
                @if($totalPicPoints <= 0)
                <div class="text-center text-muted py-5">
                    <i class="bi bi-hourglass-split" style="font-size: 3rem; opacity:.35;"></i>
                    <p class="mb-0 mt-2 fw-semibold">Belum ada peringkat</p>
                    <small>Selesaikan tugas untuk mendapatkan point pertama</small>
                </div>
                @else
                @php $rankedPic = $picRankings->filter(fn($p) => ($p->total_points ?? 0) > 0)->values(); @endphp
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
                            @foreach($rankedPic as $i => $pic)
                            <tr class="{{ $pic->id == $currentPic->id ? 'table-info' : ($i < 3 ? 'table-warning' : '') }}">
                                <td class="text-center">
                                    @if($i == 0)
                                        <span class="badge bg-warning text-dark" style="font-size: 1.1rem;">
                                            <i class="bi bi-trophy-fill"></i> 1
                                        </span>
                                    @elseif($i == 1)
                                        <span class="badge bg-secondary" style="font-size: 1.1rem;">
                                            <i class="bi bi-award-fill"></i> 2
                                        </span>
                                    @elseif($i == 2)
                                        <span class="badge bg-danger" style="font-size: 1.1rem;">
                                            <i class="bi bi-award"></i> 3
                                        </span>
                                    @else
                                        <span class="text-muted fw-bold">{{ $i + 1 }}</span>
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
                                        {{ number_format($pic->total_points ?? 0, 2) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            <div class="card-footer text-muted">
                @if($totalPicPoints > 0) Total {{ $rankedPic->count() }} PIC dengan point
                @else {{ $picRankings->count() }} PIC aktif — belum ada point @endif
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
                @if($totalMarketingPoints <= 0)
                <div class="text-center text-muted py-5">
                    <i class="bi bi-hourglass-split" style="font-size: 3rem; opacity:.35;"></i>
                    <p class="mb-0 mt-2 fw-semibold">Belum ada peringkat</p>
                    <small>Point akan muncul setelah Marketing menyelesaikan tugas</small>
                </div>
                @else
                @php $rankedMktPic = $marketingRankings->filter(fn($m) => ($m->total_points ?? 0) > 0)->values(); @endphp
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
                            @foreach($rankedMktPic as $i => $marketing)
                            <tr class="{{ $i < 3 ? 'table-success' : '' }}">
                                <td class="text-center">
                                    @if($i == 0)
                                        <span class="badge bg-warning text-dark" style="font-size: 1.1rem;">
                                            <i class="bi bi-trophy-fill"></i> 1
                                        </span>
                                    @elseif($i == 1)
                                        <span class="badge bg-secondary" style="font-size: 1.1rem;">
                                            <i class="bi bi-award-fill"></i> 2
                                        </span>
                                    @elseif($i == 2)
                                        <span class="badge bg-danger" style="font-size: 1.1rem;">
                                            <i class="bi bi-award"></i> 3
                                        </span>
                                    @else
                                        <span class="text-muted fw-bold">{{ $i + 1 }}</span>
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
                                        {{ number_format($marketing->total_points ?? 0, 2) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            <div class="card-footer text-muted">
                @if($totalMarketingPoints > 0) Total {{ $rankedMktPic->count() }} Marketing dengan point
                @else {{ $marketingRankings->count() }} Marketing aktif — belum ada point @endif
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
