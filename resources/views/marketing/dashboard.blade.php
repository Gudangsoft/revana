@extends('marketing.layouts.app')

@section('title', 'Dashboard Marketing')

@section('content')
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
                <h2 class="mb-0 fw-bold">{{ number_format($marketing->total_points ?? 0) }}</h2>
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
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
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
                            @php
                                // Calculate progress percentage based on status
                                $statusProgress = [
                                    'SUBMITTED' => 10,
                                    'EDITOR1_PROCESS' => 20,
                                    'AUTHOR1_PROCESS' => 30,
                                    'EDITOR2_PROCESS' => 40,
                                    'REVIEWER1_PROCESS' => 50,
                                    'REVIEWER2_PROCESS' => 60,
                                    'EDITOR3_PROCESS' => 70,
                                    'AUTHOR2_PROCESS' => 80,
                                    'PRODUCTION_PROCESS' => 90,
                                    'PUBLISHED' => 100,
                                    'REJECTED' => 0,
                                ];
                                $progress = $statusProgress[$submission->status] ?? 15;
                                $progressColor = $submission->status == 'PUBLISHED' ? 'success' : 
                                               ($submission->status == 'REJECTED' ? 'danger' : 
                                               ($progress < 50 ? 'warning' : 'info'));
                            @endphp
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
                                    @php
                                        $badgeColor = match($submission->status) {
                                            'SUBMITTED' => 'secondary',
                                            'EDITOR1_PROCESS', 'AUTHOR1_PROCESS' => 'info',
                                            'EDITOR2_PROCESS' => 'primary',
                                            'REVIEWER1_PROCESS', 'REVIEWER2_PROCESS' => 'warning',
                                            'EDITOR3_PROCESS', 'AUTHOR2_PROCESS' => 'info',
                                            'PRODUCTION_PROCESS' => 'dark',
                                            'PUBLISHED' => 'success',
                                            'REJECTED' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }} small">{{ str_replace('_', ' ', $submission->status) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 10px;">
                                            <div class="progress-bar bg-{{ $progressColor }}" role="progressbar" 
                                                 style="width: {{ $progress }}%" title="{{ $progress }}%"></div>
                                        </div>
                                        <small class="text-muted" style="min-width: 35px;">{{ $progress }}%</small>
                                    </div>
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
                        <div class="fs-3 fw-bold">{{ number_format($marketing->total_points ?? 0) }}</div>
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
                                    <span class="badge bg-success">+{{ $history->points_earned }}</span>
                                    @else
                                    <span class="badge bg-danger">{{ $history->points_earned }}</span>
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
@endsection
