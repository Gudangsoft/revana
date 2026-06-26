@extends('layouts.app')

@section('title', 'Dashboard PIC Reviewer - ' . $appSettings['app_name'])
@section('page-title', 'Dashboard PIC Reviewer')

@section('sidebar')
    @include('admin.partials.sidebar-pic-reviewer')
@endsection

@section('content')
@php $s = $stats; @endphp

{{-- Selamat datang --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-clipboard2-pulse-fill text-primary me-2"></i>
            Selamat datang, {{ Auth::user()->name }}
        </h4>
        <small class="text-muted">Panel Manajemen Reviewer — {{ now()->translatedFormat('l, d F Y') }}</small>
    </div>
    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-6">
        <i class="bi bi-shield-check me-1"></i> PIC Reviewer
    </span>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('info'))
<div class="alert alert-info alert-dismissible fade show">
    <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ── Stat Cards ── --}}
<div class="row g-3 mb-4">
    {{-- Reviewer --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #6366f1 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:52px;height:52px;background:rgba(99,102,241,.12);">
                    <i class="bi bi-people-fill fs-4" style="color:#6366f1;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ number_format($s['totalReviewers']) }}</div>
                    <div class="text-muted small">Total Reviewer</div>
                    <div class="text-success small mt-1">
                        <i class="bi bi-circle-fill" style="font-size:8px;"></i>
                        {{ $s['activeReviewers'] }} aktif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Review --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #f59e0b !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:52px;height:52px;background:rgba(245,158,11,.12);">
                    <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1 text-warning">{{ number_format($s['pendingReview']) }}</div>
                    <div class="text-muted small">Menunggu Review</div>
                    <a href="{{ route('admin.assignments.index') }}" class="small text-warning text-decoration-none">
                        Lihat semua →
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Selesai Bulan Ini --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #10b981 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:52px;height:52px;background:rgba(16,185,129,.12);">
                    <i class="bi bi-check2-circle fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1 text-success">{{ number_format($s['completedThisMonth']) }}</div>
                    <div class="text-muted small">Selesai Bulan Ini</div>
                    <div class="text-muted small mt-1">{{ now()->format('F Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Permintaan Review --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #f43f5e !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:52px;height:52px;background:rgba(244,63,94,.12);">
                    <i class="bi bi-envelope-open-fill fs-4" style="color:#f43f5e;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1" style="color:#f43f5e;">{{ number_format($pendingReviewRequests) }}</div>
                    <div class="text-muted small">Permintaan Review</div>
                    @feature('review_requests')
                    <a href="{{ route('admin.review-requests.index') }}" class="small text-decoration-none" style="color:#f43f5e;">
                        Proses sekarang →
                    </a>
                    @endfeature
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Distribusi Submission & Menu Cepat ── --}}
<div class="row g-3 mb-4">
    {{-- Distribusi per Tipe --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>Distribusi Submission
                </h6>
            </div>
            <div class="card-body">
                @php
                    $types = [
                        ['label' => 'Normal',     'count' => $s['normalCount'],    'color' => '#c084fc', 'url' => route('admin.submissions.index')],
                        ['label' => 'Fasttrack',  'count' => $s['fastrackCount'],  'color' => '#f59e0b', 'url' => route('admin.fasttrack-management.submissions.index')],
                        ['label' => 'BKD',        'count' => $s['bkdCount'],       'color' => '#38bdf8', 'url' => route('admin.submissions.index', ['program' => 'bkd'])],
                        ['label' => 'JAFA',       'count' => $s['jafaCount'],      'color' => '#4ade80', 'url' => route('admin.submissions.index', ['program' => 'jafa'])],
                    ];
                    $total = max($s['totalSubmissions'], 1);
                @endphp

                @foreach($types as $t)
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <a href="{{ $t['url'] }}" class="text-decoration-none fw-semibold" style="color:{{ $t['color'] }};">
                            {{ $t['label'] }}
                        </a>
                        <span class="badge rounded-pill" style="background:{{ $t['color'] }};">
                            {{ number_format($t['count']) }}
                        </span>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar" role="progressbar"
                             style="width:{{ round($t['count'] / $total * 100) }}%; background:{{ $t['color'] }};">
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="text-muted small mt-3 text-end">
                    Total: {{ number_format($s['totalSubmissions']) }} submission
                </div>
            </div>
        </div>
    </div>

    {{-- Menu Akses Cepat --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-grid-fill text-primary me-2"></i>Akses Cepat
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @php
                        $menus = [
                            ['icon' => 'bi-clipboard2-check-fill', 'color' => '#86efac', 'bg' => 'rgba(134,239,172,.15)',
                             'label' => 'Penugasan Review',    'url' => route('admin.assignments.index')],
                            ['icon' => 'bi-people-fill',           'color' => '#67e8f9', 'bg' => 'rgba(103,232,249,.15)',
                             'label' => 'Daftar Reviewer',     'url' => route('admin.reviewers.index')],
                            ['icon' => 'bi-envelope-open-fill',    'color' => '#fcd34d', 'bg' => 'rgba(252,211,77,.15)',
                             'label' => 'Permintaan Review',   'url' => route('admin.review-requests.index'),
                             'badge' => $pendingReviewRequests],
                            ['icon' => 'bi-trophy-fill',           'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,.15)',
                             'label' => 'Papan Peringkat',     'url' => route('admin.leaderboard.index')],
                            ['icon' => 'bi-database-fill',         'color' => '#818cf8', 'bg' => 'rgba(129,140,248,.15)',
                             'label' => 'Master Jurnal',       'url' => route('admin.journal-masters.index')],
                            ['icon' => 'bi-calendar3',             'color' => '#818cf8', 'bg' => 'rgba(129,140,248,.15)',
                             'label' => 'Slot Jurnal',         'url' => route('admin.journal-slots.index')],
                            ['icon' => 'bi-activity',              'color' => '#34d399', 'bg' => 'rgba(52,211,153,.15)',
                             'label' => 'Monitoring Review',   'url' => route('admin.monitoring')],
                            ['icon' => 'bi-kanban-fill',           'color' => '#c084fc', 'bg' => 'rgba(192,132,252,.15)',
                             'label' => 'Monitoring Normal',   'url' => route('admin.submissions.monitoring')],
                        ];
                    @endphp

                    @foreach($menus as $m)
                    <div class="col-6 col-md-3">
                        <a href="{{ $m['url'] }}" class="card text-center text-decoration-none border-0 p-2 h-100"
                           style="background:{{ $m['bg'] }}; transition:transform .15s, box-shadow .15s;"
                           onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 6px 20px rgba(0,0,0,.1)';"
                           onmouseout="this.style.transform='';this.style.boxShadow='';">
                            <div class="position-relative d-inline-block">
                                <i class="bi {{ $m['icon'] }} fs-3 mb-1 d-block" style="color:{{ $m['color'] }};"></i>
                                @if(!empty($m['badge']) && $m['badge'] > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                      style="font-size:0.65rem;">{{ $m['badge'] }}</span>
                                @endif
                            </div>
                            <div class="small fw-semibold" style="color:#374151; font-size:0.78rem;">{{ $m['label'] }}</div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Penugasan Review Menunggu ── --}}
@if($recentPending->count())
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-hourglass-split text-warning me-2"></i>Penugasan Menunggu Konfirmasi
        </h6>
        <a href="{{ route('admin.assignments.index') }}" class="btn btn-sm btn-outline-primary">
            Lihat Semua
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Judul Artikel</th>
                        <th>Jurnal</th>
                        <th>Reviewer</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPending as $assignment)
                    <tr>
                        <td class="small">{{ Str::limit($assignment->article_title, 50) }}</td>
                        <td class="small text-muted">{{ Str::limit($assignment->journal?->nama_jurnal ?? '—', 30) }}</td>
                        <td class="small">{{ $assignment->reviewer?->name ?? '—' }}</td>
                        <td class="small">
                            @if($assignment->deadline)
                                {{ $assignment->deadline->format('d M Y') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="badge bg-warning text-dark">PENDING</span></td>
                        <td>
                            <a href="{{ route('admin.assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection
