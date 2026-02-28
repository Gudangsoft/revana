@extends('pic.layouts.app')

@section('title', 'Dashboard PIC Author')
@section('page-title', 'Dashboard PIC Author')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Sync Point Reminder --}}
@include('partials.sync-point-reminder', [
    'reminderId' => 'pic_sync_point',
    'syncRoute'  => route('pic.points.sync'),
    'syncLabel'  => 'Sinkronkan Point Saya Sekarang',
    'syncMethod' => 'POST',
])

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-info-circle"></i> Selamat Datang, {{ auth()->guard('pic')->user()->name }}</h5>
                <p class="card-text">Anda login sebagai <strong>PIC Author</strong>. Gunakan dashboard ini untuk mengelola tugas dan monitoring artikel.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-speedometer2"></i> Menu Utama
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="{{ route('pic.submissions.monitoring') }}" class="text-decoration-none">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="bi bi-list-check text-primary" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Monitoring & Tugas</h5>
                                    <p class="card-text text-muted">Lihat dan kelola tugas yang ditugaskan</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('pic.journals.index') }}" class="text-decoration-none">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="bi bi-journal-text text-success" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Data Jurnal</h5>
                                    <p class="card-text text-muted">Kelola data jurnal</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('pic.points.index') }}" class="text-decoration-none">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <i class="bi bi-trophy-fill text-warning" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Point Saya</h5>
                                    <p class="card-text text-muted">Lihat perolehan point</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('pic.submissions.index') }}" class="text-decoration-none">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <i class="bi bi-journal-bookmark text-info" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Kelola Jurnal</h5>
                                    <p class="card-text text-muted">Kelola submission jurnal normal</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('pic.fasttrack.index') }}" class="text-decoration-none">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <i class="bi bi-lightning-charge text-warning" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Kelola Jurnal FS</h5>
                                    <p class="card-text text-muted">Kelola submission jurnal fasttrack</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('pic.journal-slots.index') }}" class="text-decoration-none">
                            <div class="card h-100 border-danger">
                                <div class="card-body text-center">
                                    <i class="bi bi-rocket text-danger" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Data Jurnal FS</h5>
                                    <p class="card-text text-muted">Kelola data jurnal fasttrack</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('pic.points.rankings') }}" class="text-decoration-none">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="bi bi-bar-chart-fill text-success" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Peringkat Point</h5>
                                    <p class="card-text text-muted">Lihat peringkat point PIC & Marketing</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Point Rankings Section --}}
<div class="row mt-4">
    {{-- PIC Point Ranking --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy-fill"></i> Peringkat Point PIC</span>
                <a href="{{ route('pic.points.rankings') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-bar-chart-fill"></i> Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">Rank</th>
                                <th>Nama PIC</th>
                                <th class="text-center">Total Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPics as $index => $pic)
                            <tr class="{{ $pic->id == auth()->guard('pic')->user()->id ? 'table-info' : '' }}">
                                <td class="text-center">
                                    @if($index == 0)
                                        <span class="badge bg-warning text-dark" style="font-size: 1rem;">
                                            <i class="bi bi-trophy-fill"></i> 1
                                        </span>
                                    @elseif($index == 1)
                                        <span class="badge bg-secondary" style="font-size: 1rem;">
                                            <i class="bi bi-award-fill"></i> 2
                                        </span>
                                    @elseif($index == 2)
                                        <span class="badge bg-danger" style="font-size: 1rem;">
                                            <i class="bi bi-award"></i> 3
                                        </span>
                                    @else
                                        <span class="text-muted">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary me-2">
                                            {{ strtoupper(substr($pic->name, 0, 1)) }}
                                        </div>
                                        <strong>{{ $pic->name }}</strong>
                                        @if($pic->id == auth()->guard('pic')->user()->id)
                                            <span class="badge bg-info ms-1">Anda</span>
                                        @endif
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
        </div>
    </div>

    {{-- Marketing Point Ranking --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy-fill"></i> Peringkat Point Marketing</span>
                <a href="{{ route('pic.points.rankings') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-bar-chart-fill"></i> Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">Rank</th>
                                <th>Nama Marketing</th>
                                <th class="text-center">Total Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topMarketings as $index => $mkt)
                            <tr>
                                <td class="text-center">
                                    @if($index == 0)
                                        <span class="badge bg-warning text-dark" style="font-size: 1rem;">
                                            <i class="bi bi-trophy-fill"></i> 1
                                        </span>
                                    @elseif($index == 1)
                                        <span class="badge bg-secondary" style="font-size: 1rem;">
                                            <i class="bi bi-award-fill"></i> 2
                                        </span>
                                    @elseif($index == 2)
                                        <span class="badge bg-danger" style="font-size: 1rem;">
                                            <i class="bi bi-award"></i> 3
                                        </span>
                                    @else
                                        <span class="text-muted">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-success me-2">
                                            {{ strtoupper(substr($mkt->name, 0, 1)) }}
                                        </div>
                                        <strong>{{ $mkt->name }}</strong>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success" style="font-size: 1rem;">
                                        {{ number_format($mkt->total_points ?? 0) }}
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
        </div>
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
