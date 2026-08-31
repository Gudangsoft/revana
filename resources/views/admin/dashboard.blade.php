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
@include('partials.birthday-notification', [
    'wishRoute' => Route::has('admin.birthday.wish') ? route('admin.birthday.wish') : (Route::has('birthday.wish') ? route('birthday.wish') : '#'),
])

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
<div class="row mt-4 g-3">
    <div class="col-md-2 col-4">
        <div class="card">
            <div class="card-body text-center py-3">
                <h5 class="text-primary mb-1">{{ $approvedSubmissions }}</h5>
                <small class="text-muted">Disetujui</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-4">
        <div class="card">
            <div class="card-body text-center py-3">
                <h5 class="text-info mb-1">{{ $inProgressSubmissions }}</h5>
                <small class="text-muted">Diproses</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-4">
        <div class="card">
            <div class="card-body text-center py-3">
                <h5 class="text-success mb-1">{{ $regularSubmissions }}</h5>
                <small class="text-muted">Normal</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-4">
        <div class="card">
            <div class="card-body text-center py-3">
                <h5 class="text-warning mb-1">{{ $fasttrackSubmissions }}</h5>
                <small class="text-muted">Fasttrack</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-4">
        <div class="card">
            <div class="card-body text-center py-3">
                <h5 class="mb-1" style="color:#0891b2;">{{ $bkdStats['total'] }}</h5>
                <small class="text-muted">BKD</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-4">
        <div class="card">
            <div class="card-body text-center py-3">
                <h5 class="text-purple mb-1" style="color:#7c3aed;">{{ $jafaStats['total'] }}</h5>
                <small class="text-muted">JAFA</small>
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

{{-- Monitoring Tren Submission — total submission saja, filter granularitas
     per tahun/bulan/hari (terpisah dari chart "Tren Submission {tahun}" di atas
     yang tetap Total+Published+Rejected khusus tahun berjalan). --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="fw-semibold"><i class="bi bi-bar-chart-steps text-primary"></i> Monitoring Tren Submission</span>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select id="trendKategoriSelect" class="form-select form-select-sm" style="width:auto;">
                        @foreach($trendCategoryOptions as $val => $label)
                        <option value="{{ $val }}" {{ $val === 'semua' ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select id="trendPeriodSelect" class="form-select form-select-sm" style="width:auto;">
                        <option value="year">Per Tahun</option>
                        <option value="month" selected>Per Bulan</option>
                        <option value="day">Per Hari</option>
                    </select>
                    <select id="trendYearSelect" class="form-select form-select-sm" style="width:auto;">
                        @foreach($submissionYears as $y)
                        <option value="{{ $y }}" {{ (int) $y === (int) date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                    <select id="trendMonthSelect" class="form-select form-select-sm" style="width:auto; display:none;">
                        @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $mi => $ml)
                        <option value="{{ $mi + 1 }}" {{ ($mi + 1) === (int) date('n') ? 'selected' : '' }}>{{ $ml }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div id="trendMonitoringMeta" class="mb-2 small text-muted">&nbsp;</div>
                <canvas id="submissionTrendChart" height="90"></canvas>
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

// ── Monitoring Tren Submission (total saja, filter per tahun/bulan/hari) ──
(function () {
    const kategoriSel = document.getElementById('trendKategoriSelect');
    const periodSel = document.getElementById('trendPeriodSelect');
    const yearSel   = document.getElementById('trendYearSelect');
    const monthSel  = document.getElementById('trendMonthSelect');
    const metaEl    = document.getElementById('trendMonitoringMeta');
    const canvas    = document.getElementById('submissionTrendChart');
    if (!periodSel || !canvas) return;

    // Rincian per kategori (Normal/Fasttrack/BKD/JAFA) utk titik data yang lagi
    // di-hover — diisi ulang tiap loadTrend(), dibaca oleh tooltip callback
    // afterBody di bawah supaya tetap tampil rincian lengkap apa pun kategori
    // yang sedang dipilih di dropdown.
    let trendBreakdown = {};

    const trendChart2 = new Chart(canvas, {
        type: 'bar',
        data: { labels: [], datasets: [{
            label: 'Total Submission',
            data: [],
            backgroundColor: 'rgba(99,102,241,.6)',
            borderColor: 'rgba(99,102,241,1)',
            borderWidth: 1.5,
            borderRadius: 4,
        }] },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index', intersect: false,
                    callbacks: {
                        afterBody: function (items) {
                            // Rincian cuma relevan kalau lagi lihat "Semua" — kalau kategori
                            // spesifik (mis. Normal) sudah dipilih, baris total di atas
                            // SUDAH menampilkan angka itu, jadi rincian jadi redundan.
                            if (!items.length || kategoriSel.value !== 'semua') return [];
                            const idx = items[0].dataIndex;
                            return Object.keys(trendBreakdown).map(function (label) {
                                return label + ': ' + trendBreakdown[label][idx];
                            });
                        }
                    }
                },
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,.05)' } },
                x: { grid: { display: false } },
            }
        }
    });

    function toggleControls() {
        const p = periodSel.value;
        yearSel.style.display  = (p === 'year') ? 'none' : '';
        monthSel.style.display = (p === 'day') ? '' : 'none';
    }

    function loadTrend() {
        const params = new URLSearchParams({
            kategori: kategoriSel.value,
            period: periodSel.value,
            year: yearSel.value,
            month: monthSel.value,
        });
        fetch('{{ route("admin.dashboard.submission-trend") }}?' + params.toString())
            .then(r => r.json())
            .then(json => {
                trendChart2.data.labels = json.labels;
                trendChart2.data.datasets[0].data = json.data;
                trendChart2.data.datasets[0].label = 'Total Submission (' + json.kategori_label + ')';
                trendBreakdown = json.breakdown || {};
                trendChart2.update();
                metaEl.textContent = 'Total pada periode ini: ' + Number(json.total).toLocaleString('id-ID') + ' submission';
            })
            .catch(() => { metaEl.textContent = 'Gagal memuat data tren.'; });
    }

    kategoriSel.addEventListener('change', loadTrend);
    periodSel.addEventListener('change', function () { toggleControls(); loadTrend(); });
    yearSel.addEventListener('change', loadTrend);
    monthSel.addEventListener('change', loadTrend);

    toggleControls();
    loadTrend();
})();
</script>
@endpush

<!-- Analytics Row: Top Reviewers + Avg Completion -->
<div class="row mt-4 g-3">
    {{-- Top 5 Reviewer --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-trophy-fill text-warning"></i> Top 5 Reviewer</span>
                <a href="{{ route('admin.leaderboard.index') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
            </div>
            <div class="card-body">
                @if($topReviewers->isEmpty())
                    <p class="text-muted text-center small py-3">Belum ada data reviewer</p>
                @else
                <canvas id="topReviewersChart" height="120"></canvas>
                @endif
            </div>
        </div>
    </div>

    {{-- Avg Completion + Audit Log Shortcut --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3" style="border-left:4px solid #6366f1!important;">
            <div class="card-body text-center py-4">
                <div style="font-size:2.8rem;font-weight:700;color:#6366f1;line-height:1;">
                    {{ $avgCompletionDays > 0 ? $avgCompletionDays : '–' }}
                </div>
                <div class="text-muted mt-1" style="font-size:.85rem;">hari rata-rata penyelesaian</div>
                <div class="text-muted" style="font-size:.75rem;">(submission → published)</div>
            </div>
        </div>
        <div class="card border-0 shadow-sm" style="border-left:4px solid #4ade80!important;">
            <div class="card-body py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-semibold" style="font-size:.9rem;">Audit Log</div>
                        <small class="text-muted">Lacak semua aksi kritis admin</small>
                    </div>
                    <a href="{{ route('admin.activity-logs.index') }}"
                       class="btn btn-sm btn-outline-success">
                        <i class="bi bi-shield-check"></i> Buka
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if($topReviewers->isNotEmpty())
<script>
(function () {
    new Chart(document.getElementById('topReviewersChart'), {
        type: 'bar',
        data: {
            labels: @json($topReviewers->pluck('name')),
            datasets: [{
                label: 'Total Poin',
                data: @json($topReviewers->pluck('total_points')),
                backgroundColor: ['#6366f1','#8b5cf6','#a78bfa','#c4b5fd','#ddd6fe'],
                borderRadius: 6,
                borderWidth: 0,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.raw} poin` } },
            },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' } },
                y: { grid: { display: false }, ticks: { font: { size: 12 } } },
            }
        }
    });
})();
</script>
@endif
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

<!-- Submissions Terbaru -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
            <div class="card-header border-0 d-flex justify-content-between align-items-center py-3 px-4"
                 style="background:linear-gradient(135deg,#3b82f6,#6366f1);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:32px;height:32px;background:rgba(255,255,255,.2);">
                        <i class="bi bi-clock-history text-white" style="font-size:.9rem;"></i>
                    </div>
                    <span class="fw-semibold text-white fs-6">Submissions Terbaru</span>
                    <span class="badge text-dark" style="background:rgba(255,255,255,.25);font-size:.72rem;">15 terbaru</span>
                </div>
                <a href="{{ route('admin.submissions.index') }}" class="btn btn-sm text-white"
                   style="background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);border-radius:8px;">
                    <i class="bi bi-list-ul"></i> Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" style="font-size:.85rem;">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="px-3 py-2 text-muted fw-semibold" style="width:150px;font-size:.75rem;letter-spacing:.04em;">KODE</th>
                                <th class="py-2 text-muted fw-semibold" style="font-size:.75rem;letter-spacing:.04em;">JUDUL ARTIKEL</th>
                                <th class="py-2 text-muted fw-semibold" style="font-size:.75rem;letter-spacing:.04em;">JURNAL</th>
                                <th class="py-2 text-muted fw-semibold" style="width:130px;font-size:.75rem;letter-spacing:.04em;">MARKETING / PIC</th>
                                <th class="py-2 text-muted fw-semibold" style="width:120px;font-size:.75rem;letter-spacing:.04em;">STATUS</th>
                                <th class="py-2 text-muted fw-semibold" style="width:95px;font-size:.75rem;letter-spacing:.04em;">TANGGAL</th>
                                <th class="py-2" style="width:44px;"></th>
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
                            <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;"
                                onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                                <td class="px-3">
                                    <a href="{{ route('admin.submissions.show', $s) }}" class="text-decoration-none">
                                        <code class="fw-bold" style="color:{{ $progColor }};font-size:.78rem;">{{ $s->kode_submit }}</code>
                                    </a>
                                    <br>
                                    <span class="badge rounded-pill" style="background:{{ $progColor }}22;color:{{ $progColor }};border:1px solid {{ $progColor }}55;font-size:.6rem;font-weight:600;">{{ $prog }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark" style="max-width:260px;line-height:1.3;">{{ Str::limit($s->judul_artikel ?? '-', 50) }}</div>
                                    <small class="text-muted"><i class="bi bi-person-fill" style="font-size:.65rem;"></i> {{ Str::limit($s->nama_penulis ?? '', 30) }}</small>
                                </td>
                                <td>
                                    <small class="text-secondary" style="line-height:1.3;display:block;max-width:160px;">{{ Str::limit($s->journalSlot?->journalMaster?->nama_jurnal ?? '-', 35) }}</small>
                                </td>
                                <td>
                                    <div style="font-size:.75rem;line-height:1.5;">
                                        @if($s->marketing)
                                        <div class="text-muted"><i class="bi bi-megaphone-fill text-primary" style="font-size:.65rem;"></i> {{ Str::limit($s->marketing->name, 18) }}</div>
                                        @endif
                                        @if($s->petugasSubmit)
                                        <div class="text-muted"><i class="bi bi-person-badge-fill text-success" style="font-size:.65rem;"></i> {{ Str::limit($s->petugasSubmit->name, 18) }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $s->status_badge_class }}" style="font-size:.68rem;padding:.3em .55em;">
                                        {{ $s->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted" style="font-size:.75rem;">
                                        {{ $s->tanggal_submit ? \Carbon\Carbon::parse($s->tanggal_submit)->format('d M Y') : $s->created_at->format('d M Y') }}
                                    </small>
                                </td>
                                <td class="pe-3">
                                    <a href="{{ route('admin.submissions.show', $s) }}"
                                       class="btn btn-sm d-flex align-items-center justify-content-center"
                                       style="width:28px;height:28px;padding:0;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;color:#3b82f6;">
                                        <i class="bi bi-eye" style="font-size:.75rem;"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox" style="font-size:2.5rem;opacity:.3;"></i>
                                    <p class="mb-0 mt-2 small">Belum ada submission</p>
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

<!-- Submissions Disetujui -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
            <div class="card-header border-0 d-flex justify-content-between align-items-center py-3 px-4"
                 style="background:linear-gradient(135deg,#10b981,#059669);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:32px;height:32px;background:rgba(255,255,255,.2);">
                        <i class="bi bi-patch-check-fill text-white" style="font-size:.9rem;"></i>
                    </div>
                    <span class="fw-semibold text-white fs-6">Submissions Disetujui</span>
                    <span class="badge text-dark" style="background:rgba(255,255,255,.25);font-size:.72rem;">{{ $totalCompletedReviews }} total</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.submissions.index', ['status' => 'PUBLISHED']) }}"
                       class="btn btn-sm text-white"
                       style="background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);border-radius:8px;">
                        <i class="bi bi-arrow-right-circle"></i> Lihat Semua
                    </a>
                    <button class="btn btn-sm text-white" data-bs-toggle="modal" data-bs-target="#exportModal"
                            style="background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);border-radius:8px;">
                        <i class="bi bi-file-earmark-excel"></i> Export
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" style="font-size:.85rem;">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="px-3 py-2 text-muted fw-semibold" style="width:40px;font-size:.75rem;letter-spacing:.04em;">#</th>
                                <th class="py-2 text-muted fw-semibold" style="width:150px;font-size:.75rem;letter-spacing:.04em;">KODE</th>
                                <th class="py-2 text-muted fw-semibold" style="font-size:.75rem;letter-spacing:.04em;">JUDUL ARTIKEL</th>
                                <th class="py-2 text-muted fw-semibold" style="font-size:.75rem;letter-spacing:.04em;">JURNAL</th>
                                <th class="py-2 text-muted fw-semibold" style="width:120px;font-size:.75rem;letter-spacing:.04em;">MARKETING</th>
                                <th class="py-2 text-muted fw-semibold" style="width:95px;font-size:.75rem;letter-spacing:.04em;">SELESAI</th>
                                <th class="py-2" style="width:44px;"></th>
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
                            <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;"
                                onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background=''">
                                <td class="px-3 text-muted" style="font-size:.75rem;">{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('admin.submissions.show', $s) }}" class="text-decoration-none">
                                        <code class="fw-bold text-success" style="font-size:.78rem;">{{ $s->kode_submit ?? '-' }}</code>
                                    </a>
                                    @if($prog)
                                    <br><span class="badge rounded-pill" style="background:{{ $progColor }}22;color:{{ $progColor }};border:1px solid {{ $progColor }}55;font-size:.6rem;font-weight:600;">{{ $prog }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark" style="max-width:280px;line-height:1.3;">{{ Str::limit($s->judul_artikel ?? '-', 55) }}</div>
                                    <small class="text-muted"><i class="bi bi-person-fill" style="font-size:.65rem;"></i> {{ Str::limit($s->nama_penulis ?? '', 30) }}</small>
                                </td>
                                <td><small class="text-secondary" style="font-size:.78rem;">{{ Str::limit($s->journalSlot?->journalMaster?->nama_jurnal ?? '-', 35) }}</small></td>
                                <td><small class="text-muted" style="font-size:.78rem;">{{ $s->marketing?->name ?? '-' }}</small></td>
                                <td>
                                    <small class="text-muted" style="font-size:.75rem;">{{ $s->updated_at?->format('d M Y') ?? '-' }}</small>
                                </td>
                                <td class="pe-3">
                                    <a href="{{ route('admin.submissions.show', $s) }}"
                                       class="btn btn-sm d-flex align-items-center justify-content-center"
                                       style="width:28px;height:28px;padding:0;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;color:#10b981;">
                                        <i class="bi bi-eye" style="font-size:.75rem;"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox" style="font-size:2.5rem;opacity:.3;"></i>
                                    <p class="mb-0 mt-2 small">Belum ada artikel yang disetujui</p>
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
