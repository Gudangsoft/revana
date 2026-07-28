@extends('marketing.layouts.app')

@section('title', 'Dashboard Marketing')

@section('content')
@if(session('motivational_message'))
<div class="alert alert-dismissible fade show border-0 mb-3" role="alert"
     style="background: linear-gradient(135deg,#f59e0b,#ef4444); color:#fff;">
    <div class="d-flex align-items-center gap-2">
        <span class="fs-5">{{ session('motivational_message') }}</span>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
@endif
@include('partials.birthday-notification', [
    'wishRoute' => route('marketing.birthday.wish'),
])

@include('partials.incomplete-profile-alert', [
    'profileUser'  => $marketing,
    'profileRoute' => route('marketing.profile.edit'),
])

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-speedometer2"></i> Dashboard Marketing
    </h4>
    <div class="text-muted">
        <i class="bi bi-person-circle"></i> {{ $marketing->name }}
    </div>
</div>

<!-- Welcome Card -->
<div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
    <div class="card-body text-white py-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="mb-2">Selamat Datang, {{ $marketing->name }}!</h3>
                <p class="mb-0 opacity-75">Pantau performa artikel Anda dan dapatkan point dari setiap submit yang berhasil.</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="display-3 mb-2 opacity-75">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <h2 class="mb-0 fw-bold">{{ number_format($stats['total_points'], 2) }}</h2>
                <small class="opacity-75">Total Point</small>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 opacity-75 small">Total Artikel</p>
                        <h2 class="mb-0 fw-bold">{{ $stats['total_submissions'] }}</h2>
                    </div>
                    <div class="bg-white bg-opacity-25 p-2 rounded">
                        <i class="bi bi-file-earmark-text fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 opacity-75 small">Baru Submit</p>
                        <h2 class="mb-0 fw-bold">{{ $stats['submitted'] }}</h2>
                    </div>
                    <div class="bg-white bg-opacity-25 p-2 rounded">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 opacity-75 small">Dalam Proses</p>
                        <h2 class="mb-0 fw-bold">{{ $stats['in_process'] }}</h2>
                    </div>
                    <div class="bg-white bg-opacity-25 p-2 rounded">
                        <i class="bi bi-gear-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 opacity-75 small">Published</p>
                        <h2 class="mb-0 fw-bold">{{ $stats['published'] }}</h2>
                    </div>
                    <div class="bg-white bg-opacity-25 p-2 rounded">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Overview -->
@if($stats['total_submissions'] > 0)
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0"><i class="bi bi-bar-chart-fill"></i> Progress Artikel</h5>
    </div>
    <div class="card-body">
        @php
            $total = $stats['total_submissions'];
            $submittedPct = $total > 0 ? round(($stats['submitted'] / $total) * 100) : 0;
            $processPct = $total > 0 ? round(($stats['in_process'] / $total) * 100) : 0;
            $publishedPct = $total > 0 ? round(($stats['published'] / $total) * 100) : 0;
            $rejectedPct = $total > 0 ? round(($stats['rejected'] / $total) * 100) : 0;
        @endphp
        
        <div class="progress mb-3" style="height: 35px; border-radius: 10px;">
            @if($submittedPct > 0)
            <div class="progress-bar" role="progressbar" style="width: {{ $submittedPct }}%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);" 
                 title="Baru Submit: {{ $stats['submitted'] }}">
                @if($submittedPct > 10) <strong>{{ $stats['submitted'] }}</strong> @endif
            </div>
            @endif
            @if($processPct > 0)
            <div class="progress-bar" role="progressbar" style="width: {{ $processPct }}%; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"
                 title="Dalam Proses: {{ $stats['in_process'] }}">
                @if($processPct > 10) <strong>{{ $stats['in_process'] }}</strong> @endif
            </div>
            @endif
            @if($publishedPct > 0)
            <div class="progress-bar" role="progressbar" style="width: {{ $publishedPct }}%; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);"
                 title="Published: {{ $stats['published'] }}">
                @if($publishedPct > 10) <strong>{{ $stats['published'] }}</strong> @endif
            </div>
            @endif
            @if($rejectedPct > 0)
            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $rejectedPct }}%"
                 title="Rejected: {{ $stats['rejected'] }}">
                @if($rejectedPct > 10) <strong>{{ $stats['rejected'] }}</strong> @endif
            </div>
            @endif
        </div>
        
        <div class="row text-center g-3">
            <div class="col-3">
                <div class="p-2 rounded" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="text-white fw-bold fs-4">{{ $stats['submitted'] }}</div>
                    <small class="text-white opacity-75">Submitted</small>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 rounded" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="text-white fw-bold fs-4">{{ $stats['in_process'] }}</div>
                    <small class="text-white opacity-75">Proses</small>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 rounded" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <div class="text-white fw-bold fs-4">{{ $stats['published'] }}</div>
                    <small class="text-white opacity-75">Published</small>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 rounded bg-danger">
                    <div class="text-white fw-bold fs-4">{{ $stats['rejected'] }}</div>
                    <small class="text-white opacity-75">Rejected</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-3">
    <!-- Recent Submissions with Progress -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text-fill"></i> Artikel Terbaru</h5>
                <a href="{{ route('marketing.submissions') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-list-ul"></i> Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                @if($submissions->count() > 0)
                @include('partials.column-toggle', ['tableId' => 'mktDashTable', 'columns' => ['Kode', 'Judul Artikel', 'Jurnal', 'Status', 'Progress'], 'columnOffset' => 0])
                <div class="table-responsive">
                    <table id="mktDashTable" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Kode</th>
                                <th>Judul Artikel</th>
                                <th>Jurnal</th>
                                <th class="text-center">Status</th>
                                <th style="width: 140px;">Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($submissions->take(5) as $submission)
                            <tr>
                                <td class="px-3">
                                    <code class="badge bg-light text-dark">{{ $submission->kode_submit }}</code>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ Str::limit($submission->judul_artikel, 35) }}</div>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3"></i> {{ $submission->tanggal_submit?->format('d M Y') }}
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($submission->journalSlot?->journalMaster?->nama_jurnal ?? '-', 20) }}</small>
                                </td>
                                <td class="text-center">
                                    <x-submission-status :submission="$submission" size="small" />
                                </td>
                                <td>
                                    <x-submission-progress :submission="$submission" :height="10" />
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3 mb-0">Belum ada artikel yang disubmit</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Recent Points -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-trophy-fill text-warning"></i> Riwayat Point</h5>
                <a href="{{ route('marketing.points') }}" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-clock-history"></i> Semua
                </a>
            </div>
            <div class="card-body">
                <!-- Point Summary -->
                <div class="alert mb-3 border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="d-flex justify-content-between align-items-center text-white">
                        <div>
                            <i class="bi bi-coin"></i> <strong>Total Point</strong>
                        </div>
                        <div class="fs-3 fw-bold">{{ number_format($stats['total_points'], 2) }}</div>
                    </div>
                </div>
                
                @if($pointHistories->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($pointHistories->take(8) as $history)
                    <div class="list-group-item px-0 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    @if($history->points_earned >= 0)
                                    <span class="badge bg-success">+{{ number_format($history->points_earned, 2) }}</span>
                                    @else
                                    <span class="badge bg-danger">{{ number_format($history->points_earned, 2) }}</span>
                                    @endif
                                    <small class="text-muted">{{ $history->created_at->diffForHumans() }}</small>
                                </div>
                                <small class="text-muted d-block">{{ Str::limit($history->description, 40) }}</small>
                                @if($history->submission)
                                <small class="text-primary d-block">
                                    <i class="bi bi-file-text"></i> {{ Str::limit($history->submission->judul_artikel, 35) }}
                                </small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-star text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3 mb-0">Belum ada point</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Point Rankings Section --}}
<div class="row g-3 mt-3">
    {{-- Marketing Point Ranking --}}
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy-fill"></i> Peringkat Point Marketing</span>
                <a href="{{ route('marketing.points.rankings') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-bar-chart-fill"></i> Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                @if($topMarketings->isEmpty() || $topMarketings->sum('total_points') == 0)
                <div class="text-center text-muted py-5">
                    <i class="bi bi-hourglass-split" style="font-size: 2.5rem; opacity:.4;"></i>
                    <p class="mb-0 mt-2">Belum ada peringkat</p>
                    <small>Selesaikan tugas untuk mendapatkan point</small>
                </div>
                @else
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
                            @foreach($topMarketings->filter(fn($m) => ($m->total_points ?? 0) > 0) as $index => $mkt)
                            <tr class="{{ $mkt->id == $marketing->id ? 'table-success' : '' }}">
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
                                        @if($mkt->id == $marketing->id)
                                            <span class="badge bg-success ms-1">Anda</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success" style="font-size: 1rem;">
                                        {{ number_format($mkt->total_points ?? 0, 2) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- PIC Point Ranking --}}
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy-fill"></i> Peringkat Point PIC</span>
                <a href="{{ route('marketing.points.rankings') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-bar-chart-fill"></i> Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                @if($topPics->isEmpty() || $topPics->sum('total_points') == 0)
                <div class="text-center text-muted py-5">
                    <i class="bi bi-hourglass-split" style="font-size: 2.5rem; opacity:.4;"></i>
                    <p class="mb-0 mt-2">Belum ada peringkat</p>
                    <small>Point akan muncul setelah PIC menyelesaikan tugas</small>
                </div>
                @else
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
                            @foreach($topPics->filter(fn($p) => ($p->total_points ?? 0) > 0) as $index => $pic)
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
                                        <div class="avatar-circle bg-primary me-2">
                                            {{ strtoupper(substr($pic->name, 0, 1)) }}
                                        </div>
                                        <strong>{{ $pic->name }}</strong>
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
