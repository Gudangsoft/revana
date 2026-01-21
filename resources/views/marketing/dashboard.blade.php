@extends('marketing.layouts.app')

@section('title', 'Dashboard')

@section('content')
<h4 class="mb-4">
    <i class="bi bi-speedometer2"></i> Dashboard Marketing
</h4>

<!-- Welcome Card -->
<div class="card mb-4 bg-gradient" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
    <div class="card-body text-white">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3>Selamat Datang, {{ $marketing->name }}!</h3>
                <p class="mb-0">Pantau performa artikel Anda dan dapatkan point dari setiap submit yang berhasil.</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="display-4">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <h2 class="mb-0">{{ number_format($marketing->total_points ?? 0) }} Point</h2>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Total Artikel</h6>
                        <h2 class="mb-0">{{ $stats['total_submissions'] }}</h2>
                    </div>
                    <i class="bi bi-file-earmark-text" style="font-size: 2.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Baru Submit</h6>
                        <h2 class="mb-0">{{ $stats['submitted'] }}</h2>
                    </div>
                    <i class="bi bi-hourglass-split" style="font-size: 2.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Dalam Proses</h6>
                        <h2 class="mb-0">{{ $stats['in_process'] }}</h2>
                    </div>
                    <i class="bi bi-gear" style="font-size: 2.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Published</h6>
                        <h2 class="mb-0">{{ $stats['published'] }}</h2>
                    </div>
                    <i class="bi bi-check-circle" style="font-size: 2.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Overview -->
@if($stats['total_submissions'] > 0)
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-bar-chart"></i> Progress Artikel
    </div>
    <div class="card-body">
        @php
            $total = $stats['total_submissions'];
            $submittedPct = $total > 0 ? round(($stats['submitted'] / $total) * 100) : 0;
            $processPct = $total > 0 ? round(($stats['in_process'] / $total) * 100) : 0;
            $publishedPct = $total > 0 ? round(($stats['published'] / $total) * 100) : 0;
            $rejectedPct = $total > 0 ? round(($stats['rejected'] / $total) * 100) : 0;
        @endphp
        
        <div class="progress mb-3" style="height: 30px;">
            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $submittedPct }}%" 
                 title="Baru Submit: {{ $stats['submitted'] }}">
                @if($submittedPct > 10) Submitted ({{ $stats['submitted'] }}) @endif
            </div>
            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $processPct }}%"
                 title="Dalam Proses: {{ $stats['in_process'] }}">
                @if($processPct > 10) Proses ({{ $stats['in_process'] }}) @endif
            </div>
            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $publishedPct }}%"
                 title="Published: {{ $stats['published'] }}">
                @if($publishedPct > 10) Published ({{ $stats['published'] }}) @endif
            </div>
            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $rejectedPct }}%"
                 title="Rejected: {{ $stats['rejected'] }}">
                @if($rejectedPct > 10) Rejected ({{ $stats['rejected'] }}) @endif
            </div>
        </div>
        
        <div class="row text-center">
            <div class="col-md-3">
                <span class="badge bg-warning text-dark">Submitted</span>
                <div class="fw-bold">{{ $stats['submitted'] }} artikel</div>
            </div>
            <div class="col-md-3">
                <span class="badge bg-info">Dalam Proses</span>
                <div class="fw-bold">{{ $stats['in_process'] }} artikel</div>
            </div>
            <div class="col-md-3">
                <span class="badge bg-success">Published</span>
                <div class="fw-bold">{{ $stats['published'] }} artikel</div>
            </div>
            <div class="col-md-3">
                <span class="badge bg-danger">Rejected</span>
                <div class="fw-bold">{{ $stats['rejected'] }} artikel</div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- Recent Submissions with Progress -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text"></i> Artikel Terbaru & Progress</span>
                <a href="{{ route('marketing.submissions') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @if($submissions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Judul</th>
                                <th>Jurnal</th>
                                <th>Status</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($submissions->take(5) as $submission)
                            @php
                                // Calculate progress percentage based on status
                                $statusProgress = [
                                    'SUBMITTED' => 10,
                                    'REVIEW_ASSIGNED' => 20,
                                    'UNDER_REVIEW' => 30,
                                    'REVISION_REQUIRED' => 40,
                                    'REVISED' => 50,
                                    'EDITING' => 60,
                                    'EDITING_SUBMITTED' => 65,
                                    'EDITING_COMPLETED' => 70,
                                    'LAYOUT' => 75,
                                    'LAYOUT_SUBMITTED' => 80,
                                    'LAYOUT_COMPLETED' => 85,
                                    'PRODUCTION' => 90,
                                    'PRODUCTION_SUBMITTED' => 95,
                                    'PUBLISHED' => 100,
                                    'REJECTED' => 0,
                                ];
                                $progress = $statusProgress[$submission->status] ?? 15;
                                $progressColor = $submission->status == 'PUBLISHED' ? 'success' : 
                                               ($submission->status == 'REJECTED' ? 'danger' : 
                                               ($progress < 50 ? 'warning' : 'info'));
                            @endphp
                            <tr>
                                <td><code class="small">{{ $submission->kode_submit }}</code></td>
                                <td>
                                    <div class="fw-bold">{{ Str::limit($submission->judul_artikel, 25) }}</div>
                                    <small class="text-muted">{{ $submission->tanggal_submit?->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <small>{{ $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-' }}</small>
                                </td>
                                <td>
                                    @php
                                        $badgeColor = match($submission->status) {
                                            'SUBMITTED' => 'secondary',
                                            'REVIEW_ASSIGNED', 'UNDER_REVIEW' => 'primary',
                                            'REVISION_REQUIRED', 'REVISED' => 'warning',
                                            'EDITING', 'EDITING_SUBMITTED', 'EDITING_COMPLETED' => 'info',
                                            'LAYOUT', 'LAYOUT_SUBMITTED', 'LAYOUT_COMPLETED' => 'info',
                                            'PRODUCTION', 'PRODUCTION_SUBMITTED' => 'dark',
                                            'PUBLISHED' => 'success',
                                            'REJECTED' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }} small">{{ str_replace('_', ' ', $submission->status) }}</span>
                                </td>
                                <td style="width: 120px;">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $progressColor }}" role="progressbar" 
                                             style="width: {{ $progress }}%" title="{{ $progress }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $progress }}%</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <p>Belum ada artikel yang disubmit</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Recent Points -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy"></i> Riwayat Point</span>
                <a href="{{ route('marketing.points') }}" class="btn btn-sm btn-outline-warning">Lihat Semua</a>
            </div>
            <div class="card-body">
                <!-- Point Summary -->
                <div class="alert alert-success py-2 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-coin"></i> Total Point Anda</span>
                        <span class="fw-bold fs-5">{{ number_format($marketing->total_points ?? 0) }}</span>
                    </div>
                </div>
                
                @if($pointHistories->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($pointHistories as $history)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            @if($history->points_earned >= 0)
                            <div class="fw-bold text-success">+{{ $history->points_earned }} point</div>
                            @else
                            <div class="fw-bold text-danger">{{ $history->points_earned }} point</div>
                            @endif
                            <small class="text-muted">{{ Str::limit($history->description, 35) }}</small>
                            @if($history->submission)
                            <br><small class="text-primary">{{ Str::limit($history->submission->judul_artikel, 30) }}</small>
                            @endif
                        </div>
                        <small class="text-muted">{{ $history->created_at->format('d M') }}</small>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-star" style="font-size: 3rem;"></i>
                    <p>Belum ada point yang didapatkan</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
