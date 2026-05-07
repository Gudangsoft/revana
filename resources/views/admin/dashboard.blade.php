@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Dashboard Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@if(session('motivational_message'))
<div class="alert alert-dismissible fade show border-0 mb-3" role="alert"
     style="background: linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff;">
    <div class="d-flex align-items-center gap-2">
        <span class="fs-5">{{ session('motivational_message') }}</span>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
@endif
<!-- Notification Alert for Submitted Reviews -->
@if($submittedReviews > 0)
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-bell-fill me-2" style="font-size: 1.5rem;"></i>
        <div>
            <strong>Review Selesai Dikerjakan!</strong>
            <br>
            Ada <strong>{{ $submittedReviews }}</strong> review yang telah diselesaikan reviewer dan menunggu validasi Anda.
            <a href="{{ route('admin.assignments.index') }}" class="alert-link">Lihat Review</a>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- <!-- Notification Alert for Pending Redemptions -->
@if($pendingRedemptions > 0)
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-gift-fill me-2" style="font-size: 1.5rem;"></i>
        <div>
            <strong>Penukaran Reward Menunggu!</strong>
            <br>
            Ada <strong>{{ $pendingRedemptions }}</strong> penukaran reward yang menunggu persetujuan Anda.
            <a href="{{ route('admin.redemptions.index') }}" class="alert-link">Lihat Redemptions</a>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif --}}

<!-- Notification Alert for Pending Review Requests -->
@if($pendingReviewRequests > 0)
<div class="alert alert-primary alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-file-earmark-text-fill me-2" style="font-size: 1.5rem;"></i>
        <div>
            <strong>Permintaan Review Baru!</strong>
            <br>
            Ada <strong>{{ $pendingReviewRequests }}</strong> permintaan review dari reviewer yang menunggu persetujuan Anda.
            <a href="{{ route('admin.review-requests.index', ['status' => 'pending']) }}" class="alert-link">Lihat Permintaan</a>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Sync Point Reminder --}}
@include('partials.sync-point-reminder', [
    'reminderId' => 'admin_sync_point',
    'syncRoute'  => route('admin.pic-points.index'),
    'syncLabel'  => 'Buka Halaman Sinkronisasi Point PIC',
    'syncMethod' => 'GET',
])

<!-- Stats Cards -->
<div class="row">
    <div class="col-md-3">
        <div class="card stats-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Jurnal</h6>
                        <h2 class="mb-0">{{ $totalJournals }}</h2>
                    </div>
                    <div class="text-primary" style="font-size: 2.5rem;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Reviewers</h6>
                        <h2 class="mb-0">{{ $totalReviewers }}</h2>
                    </div>
                    <div class="text-success" style="font-size: 2.5rem;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Submissions</h6>
                        <h2 class="mb-0">{{ $totalSubmissions }}</h2>
                    </div>
                    <div class="text-info" style="font-size: 2.5rem;">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Perlu Review</h6>
                        <h2 class="mb-0">{{ $pendingSubmissions }}</h2>
                    </div>
                    <div class="text-warning" style="font-size: 2.5rem;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="text-primary">{{ $approvedSubmissions }}</h5>
                <small class="text-muted">Disetujui</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="text-info">{{ $inProgressSubmissions }}</h5>
                <small class="text-muted">Sedang Diproses</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="text-success">{{ $regularSubmissions }}</h5>
                <small class="text-muted">Normal</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="text-warning">{{ $fasttrackSubmissions }}</h5>
                <small class="text-muted">Fasttrack</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mt-4">
    {{-- Tren Submission Bulanan --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-graph-up-arrow text-primary"></i> Tren Submission {{ date('Y') }}</span>
                <small class="text-muted">Total · Published · Rejected</small>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="110"></canvas>
            </div>
        </div>
    </div>

    {{-- Conversion Rate Donut --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <span class="fw-semibold"><i class="bi bi-pie-chart-fill text-success"></i> Status Overview</span>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <canvas id="statusDonut" style="max-height:180px;max-width:180px;"></canvas>
                <div class="mt-3 w-100">
                    @php
                        $total = $approvedSubmissions + $rejectedSubmissions + $inProgressSubmissions + $pendingSubmissions;
                        $pct = fn($v) => $total > 0 ? round($v / $total * 100) : 0;
                    @endphp
                    <div class="d-flex justify-content-between mb-1">
                        <span><span class="badge bg-success">Published</span></span>
                        <span class="fw-bold">{{ $approvedSubmissions }} <small class="text-muted">({{ $pct($approvedSubmissions) }}%)</small></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span><span class="badge bg-danger">Rejected</span></span>
                        <span class="fw-bold">{{ $rejectedSubmissions }} <small class="text-muted">({{ $pct($rejectedSubmissions) }}%)</small></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span><span class="badge bg-primary">In Progress</span></span>
                        <span class="fw-bold">{{ $inProgressSubmissions }} <small class="text-muted">({{ $pct($inProgressSubmissions) }}%)</small></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><span class="badge bg-warning text-dark">Submitted</span></span>
                        <span class="fw-bold">{{ $pendingSubmissions }} <small class="text-muted">({{ $pct($pendingSubmissions) }}%)</small></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels    = @json($chartLabels);
    const totals    = @json($chartTotals);
    const published = @json($chartPublished);
    const rejected  = @json($chartRejected);

    // Trend chart
    new Chart(document.getElementById('trendChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Total Submission',
                    data: totals,
                    backgroundColor: 'rgba(99,102,241,.15)',
                    borderColor: 'rgba(99,102,241,1)',
                    borderWidth: 2,
                    borderRadius: 4,
                    type: 'bar',
                    order: 2,
                },
                {
                    label: 'Published',
                    data: published,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22,163,74,.12)',
                    borderWidth: 2,
                    fill: true,
                    tension: .4,
                    type: 'line',
                    order: 1,
                },
                {
                    label: 'Rejected',
                    data: rejected,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: .4,
                    type: 'line',
                    order: 0,
                },
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,.05)' } },
                x: { grid: { display: false } },
            }
        }
    });

    // Status donut
    new Chart(document.getElementById('statusDonut'), {
        type: 'doughnut',
        data: {
            labels: ['Published', 'Rejected', 'In Progress', 'Submitted'],
            datasets: [{
                data: [{{ $approvedSubmissions }}, {{ $rejectedSubmissions }}, {{ $inProgressSubmissions }}, {{ $pendingSubmissions }}],
                backgroundColor: ['#16a34a','#ef4444','#3b82f6','#f59e0b'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: {
                    label: ctx => ` ${ctx.label}: ${ctx.raw}`
                }}
            }
        }
    });
})();
</script>
@endpush

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <i class="bi bi-lightning-charge-fill text-warning"></i> <span class="fw-semibold">Quick Actions</span>
            </div>
            <div class="card-body d-flex flex-wrap gap-2">
                <a href="{{ route('admin.journals.index') }}" class="btn btn-primary">
                    <i class="bi bi-journal-bookmark-fill"></i> Jurnal Normal
                </a>
                <a href="{{ route('admin.submissions.index') }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-text-fill"></i> Kelola Submissions
                </a>
                <a href="{{ route('admin.submissions.index', ['program' => 'bkd']) }}" class="btn" style="background:#0891b2;color:#fff;">
                    <i class="bi bi-briefcase-fill"></i> Submission BKD
                    <span class="badge bg-white text-dark ms-1">{{ $bkdStats['total'] }}</span>
                </a>
                <a href="{{ route('admin.submissions.index', ['program' => 'jafa']) }}" class="btn" style="background:#7c3aed;color:#fff;">
                    <i class="bi bi-mortarboard-fill"></i> Submission JAFA
                    <span class="badge bg-white text-dark ms-1">{{ $jafaStats['total'] }}</span>
                </a>
                <a href="{{ route('admin.fasttrack.index') }}" class="btn btn-warning text-dark">
                    <i class="bi bi-lightning-charge-fill"></i> Fasttrack Jurnal
                </a>
                <a href="{{ route('admin.laporan-kinerja.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-graph-up-arrow"></i> Laporan Kinerja
                </a>
                <a href="{{ route('admin.sms-gateway.index') }}" class="btn btn-outline-success">
                    <i class="bi bi-whatsapp"></i> WA Gateway
                </a>
            </div>
        </div>
    </div>
</div>

<!-- BKD & JAFA Stats -->
<div class="row mt-4 g-3">
    {{-- BKD --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0891b2 !important;">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-semibold" style="color:#0891b2;"><i class="bi bi-briefcase-fill"></i> Program BKD</span>
                <a href="{{ route('admin.submissions.index', ['program' => 'bkd']) }}" class="btn btn-sm btn-outline-secondary">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body">
                <div class="row text-center g-2">
                    <div class="col-3">
                        <div class="p-2 rounded" style="background:#e0f2fe;">
                            <div class="fw-bold fs-5" style="color:#0891b2;">{{ $bkdStats['total'] }}</div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded" style="background:#fef9c3;">
                            <div class="fw-bold fs-5 text-warning">{{ $bkdStats['pending'] }}</div>
                            <small class="text-muted">Antrian</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded" style="background:#dcfce7;">
                            <div class="fw-bold fs-5 text-success">{{ $bkdStats['published'] }}</div>
                            <small class="text-muted">Published</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded" style="background:#fee2e2;">
                            <div class="fw-bold fs-5 text-danger">{{ $bkdStats['rejected'] }}</div>
                            <small class="text-muted">Ditolak</small>
                        </div>
                    </div>
                </div>
                @if($bkdStats['total'] > 0)
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:.75rem;">
                        <span class="text-muted">Progress Published</span>
                        <span class="fw-semibold">{{ round($bkdStats['published'] / $bkdStats['total'] * 100) }}%</span>
                    </div>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar bg-success" style="width:{{ round($bkdStats['published'] / $bkdStats['total'] * 100) }}%"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    {{-- JAFA --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #7c3aed !important;">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-semibold" style="color:#7c3aed;"><i class="bi bi-mortarboard-fill"></i> Program JAFA</span>
                <a href="{{ route('admin.submissions.index', ['program' => 'jafa']) }}" class="btn btn-sm btn-outline-secondary">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body">
                <div class="row text-center g-2">
                    <div class="col-3">
                        <div class="p-2 rounded" style="background:#ede9fe;">
                            <div class="fw-bold fs-5" style="color:#7c3aed;">{{ $jafaStats['total'] }}</div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded" style="background:#fef9c3;">
                            <div class="fw-bold fs-5 text-warning">{{ $jafaStats['pending'] }}</div>
                            <small class="text-muted">Antrian</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded" style="background:#dcfce7;">
                            <div class="fw-bold fs-5 text-success">{{ $jafaStats['published'] }}</div>
                            <small class="text-muted">Published</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded" style="background:#fee2e2;">
                            <div class="fw-bold fs-5 text-danger">{{ $jafaStats['rejected'] }}</div>
                            <small class="text-muted">Ditolak</small>
                        </div>
                    </div>
                </div>
                @if($jafaStats['total'] > 0)
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:.75rem;">
                        <span class="text-muted">Progress Published</span>
                        <span class="fw-semibold">{{ round($jafaStats['published'] / $jafaStats['total'] * 100) }}%</span>
                    </div>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar" style="width:{{ round($jafaStats['published'] / $jafaStats['total'] * 100) }}%;background:#7c3aed;"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Submissions Disetujui -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    <i class="bi bi-patch-check-fill text-success"></i> Submissions yang Sudah Disetujui
                    <span class="badge bg-success ms-1">{{ $totalCompletedReviews }}</span>
                </span>
                <div class="d-flex gap-2">
                    <small class="text-muted align-self-center">10 terbaru</small>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.submissions.index', ['status' => 'PUBLISHED']) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-arrow-right-circle"></i> Lihat Semua
                        </a>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="bi bi-file-earmark-excel"></i> Export
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;">#</th>
                                <th style="width:160px;">Kode Submit</th>
                                <th>Judul Artikel</th>
                                <th>Jurnal</th>
                                <th>Penulis</th>
                                <th style="width:100px;">Selesai</th>
                                <th style="width:60px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($completedReviews as $s)
                            @php
                                $prog = strtoupper($s->program_type ?? '');
                                $progColor = match($s->program_type) {
                                    'bkd'  => '#0891b2',
                                    'jafa' => '#7c3aed',
                                    default => '#6b7280',
                                };
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td>
                                    <code class="text-success fw-bold" style="font-size:.8rem;">{{ $s->kode_submit ?? '-' }}</code>
                                    @if($prog)
                                    <br><span class="badge rounded-pill" style="background:{{ $progColor }};font-size:.65rem;">{{ $prog }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="max-width:280px;">{{ Str::limit($s->judul_artikel ?? '-', 55) }}</div>
                                    <small class="text-muted">{{ Str::limit($s->nama_penulis ?? '', 30) }}</small>
                                </td>
                                <td><small>{{ $s->journalSlot?->journalMaster?->nama_jurnal ?? '-' }}</small></td>
                                <td><small>{{ $s->marketing?->name ?? '-' }}</small></td>
                                <td><small class="text-muted">{{ $s->updated_at?->format('d M Y') ?? '-' }}</small></td>
                                <td>
                                    <a href="{{ route('admin.submissions.show', $s) }}" class="btn btn-xs btn-outline-primary" style="padding:.15rem .4rem;font-size:.75rem;">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size:2rem;"></i>
                                    <p class="mb-0 mt-1">Belum ada artikel yang disetujui</p>
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

<!-- Point Rankings -->
@include('admin.partials.point-rankings')

<!-- Submissions Terbaru -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    <i class="bi bi-clock-history text-primary"></i> Submissions Terbaru
                    <small class="text-muted fw-normal">(15 terbaru)</small>
                </span>
                <a href="{{ route('admin.submissions.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-list-ul"></i> Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:160px;">Kode Submit</th>
                                <th>Judul Artikel</th>
                                <th>Jurnal</th>
                                <th style="width:130px;">Marketing / PIC</th>
                                <th style="width:130px;">Status</th>
                                <th style="width:100px;">Tanggal</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSubmissions as $s)
                            @php
                                $progColor = match($s->program_type) {
                                    'bkd'  => '#0891b2',
                                    'jafa' => '#7c3aed',
                                    default => '#6b7280',
                                };
                                $prog = strtoupper($s->program_type ?? 'SUB');
                                if ($s->process_type === 'fasttrack') $prog = 'FT';
                            @endphp
                            <tr>
                                <td>
                                    <code class="fw-bold" style="color:{{ $progColor }};font-size:.8rem;">{{ $s->kode_submit }}</code>
                                    <br>
                                    <span class="badge rounded-pill" style="background:{{ $progColor }};font-size:.62rem;">{{ $prog }}</span>
                                </td>
                                <td>
                                    <div style="max-width:260px;" class="fw-semibold">{{ Str::limit($s->judul_artikel ?? '-', 50) }}</div>
                                    <small class="text-muted"><i class="bi bi-person"></i> {{ Str::limit($s->nama_penulis ?? '', 28) }}</small>
                                </td>
                                <td><small>{{ $s->journalSlot?->journalMaster?->nama_jurnal ?? '-' }}</small></td>
                                <td>
                                    <small class="text-muted">
                                        @if($s->marketing) <i class="bi bi-megaphone-fill"></i> {{ $s->marketing->name }}<br> @endif
                                        @if($s->petugasSubmit) <i class="bi bi-person-badge"></i> {{ $s->petugasSubmit->name }} @endif
                                    </small>
                                </td>
                                <td>
                                    <span class="badge {{ $s->status_badge_class }}" style="font-size:.7rem;">
                                        {{ $s->status_label }}
                                    </span>
                                </td>
                                <td><small class="text-muted">{{ $s->tanggal_submit ? \Carbon\Carbon::parse($s->tanggal_submit)->format('d M Y') : $s->created_at->format('d M Y') }}</small></td>
                                <td>
                                    <a href="{{ route('admin.submissions.show', $s) }}" class="btn btn-outline-primary" style="padding:.15rem .4rem;font-size:.75rem;">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size:2rem;"></i>
                                    <p class="mb-0 mt-1">Belum ada submission</p>
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

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="bi bi-file-earmark-excel"></i> Export Laporan ke Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.export.completed.reviews') }}" method="GET">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Export semua jurnal yang telah selesai direview dan disetujui. Anda bisa filter berdasarkan tanggal atau export semua data.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai (Opsional)</label>
                        <input type="date" class="form-control" name="start_date">
                        <small class="text-muted">Kosongkan untuk export semua data</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Akhir (Opsional)</label>
                        <input type="date" class="form-control" name="end_date">
                        <small class="text-muted">Kosongkan untuk export semua data</small>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <strong>Data yang akan diexport:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Judul Artikel & Link Submit</li>
                            <li>Bahasa & Deadline</li>
                            <li>Data Reviewer & Institusi</li>
                            <li>Hasil Review (Link Google Drive)</li>
                            <li>Tanggal-tanggal penting</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-download"></i> Download Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
