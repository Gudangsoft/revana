@extends('pic.layouts.app')

@section('title', 'Point Saya')
@section('page-title', 'Point Saya')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row">
    @if(session('success'))
    <div class="col-12 mb-3">
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
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
                        <h6 class="mb-0">Total Point</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['total_points'], 1) }}</h2>
                    </div>
                    <i class="bi bi-trophy fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Point Hari Ini</h6>
                        <h2 class="mb-0 fw-bold">+{{ number_format($stats['points_today'], 1) }}</h2>
                    </div>
                    <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Point Bulan Ini</h6>
                        <h2 class="mb-0 fw-bold">+{{ number_format($stats['points_this_month'], 1) }}</h2>
                    </div>
                    <i class="bi bi-calendar-month fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Tugas</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['total_tasks'], 0, ',', '.') }}</h2>
                    </div>
                    <i class="bi bi-list-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Point Configuration Info -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Konfigurasi Point
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        @foreach($stepConfig as $step => $config)
                        <tr>
                            <td>
                                <span class="badge bg-secondary">{{ $config['label'] }}</span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-success">+{{ $config['points'] }} point</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Points by Step Breakdown -->
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart"></i> Breakdown Point per Tugas
            </div>
            <div class="card-body">
                @if($pointsByStep->count() > 0)
                <div class="row">
                    @foreach($pointsByStep as $stepData)
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 text-center">
                            <small class="text-muted d-block">{{ \App\Models\PicPointHistory::getLabelForStep($stepData->step) }}</small>
                            <h4 class="mb-0 text-primary">{{ number_format($stepData->total, 2) }}</h4>
                            <small class="text-muted">{{ $stepData->count }} tugas</small>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Belum ada point yang diperoleh
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Filter & History -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history"></i> Riwayat Perolehan Point</span>
        <div class="d-flex gap-2 align-items-center">
            <form method="POST" action="{{ route('pic.points.sync') }}" class="d-inline" id="syncMyPointForm">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm" id="btnSyncMyPoint">
                    <i class="bi bi-arrow-repeat" id="syncMyIcon"></i>
                    <span id="syncMyText"> Refresh Point</span>
                </button>
            </form>
            @include('partials.column-toggle', ['tableId' => 'picPointsTable', 'columns' => ['Tanggal', 'Kode Submit', 'Tugas', 'Deskripsi', 'Point'], 'columnOffset' => 0])
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Cepat: Hari Ini/Minggu Ini/Bulan Ini/Tahun Ini -->
        @php
            $picPeriodOptions = [
                ''      => 'Semua',
                'today' => 'Hari Ini',
                'week'  => 'Minggu Ini',
                'month' => 'Bulan Ini',
                'year'  => 'Tahun Ini',
            ];
        @endphp
        <div class="btn-group mb-3 flex-wrap" role="group">
            @foreach($picPeriodOptions as $value => $label)
            <a href="{{ route('pic.points.index', array_merge(request()->except(['period', 'tanggal_dari', 'tanggal_sampai', 'page']), $value ? ['period' => $value] : [])) }}"
               class="btn btn-sm {{ request('period', '') === $value ? 'btn-primary' : 'btn-outline-primary' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        <!-- Filter Form -->
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <label class="form-label small">Dari Tanggal</label>
                <input type="date" class="form-control form-control-sm" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Sampai Tanggal</label>
                <input type="date" class="form-control form-control-sm" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Tipe Tugas</label>
                <select class="form-select form-select-sm" name="step">
                    <option value="">-- Semua --</option>
                    @foreach($stepConfig as $step => $config)
                    <option value="{{ $step }}" {{ request('step') == $step ? 'selected' : '' }}>{{ $config['label'] }}</option>
                    @endforeach
                    <option value="adjustment" {{ request('step') == 'adjustment' ? 'selected' : '' }}>Penyesuaian Point</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm me-2">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('pic.points.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>

        <!-- Total Hasil Filter -->
        <div class="alert alert-light border d-flex flex-wrap gap-3 align-items-center mb-3">
            <span><i class="bi bi-list-check"></i> Total Tugas: <strong>{{ number_format($filteredTotals->total_tasks, 0, ',', '.') }}</strong></span>
            <span><i class="bi bi-trophy"></i> Total Point: <strong class="{{ $filteredTotals->total_points >= 0 ? 'text-success' : 'text-danger' }}">{{ $filteredTotals->total_points >= 0 ? '+' : '' }}{{ number_format($filteredTotals->total_points, 1) }}</strong></span>
        </div>

        <!-- History Table -->
        <div class="table-responsive">
            <table class="table table-hover table-striped" id="picPointsTable">
                <thead class="table-dark">
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode Submit</th>
                        <th>Tugas</th>
                        <th>Deskripsi</th>
                        <th class="text-end">Point</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pointHistories as $history)
                    <tr>
                        <td>
                            <small>{{ $history->created_at->format('d M Y') }}</small><br>
                            <small class="text-muted">{{ $history->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            @if($history->submission)
                            <code>{{ $history->submission->kode_submit }}</code>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ \App\Models\PicPointHistory::getLabelForStep($history->step) }}</span>
                        </td>
                        <td>{{ $history->description ?? '-' }}</td>
                        <td class="text-end">
                            <span class="badge {{ $history->points_earned >= 0 ? 'bg-success' : 'bg-danger' }} fs-6">
                                {{ $history->points_earned >= 0 ? '+' : '' }}{{ number_format($history->points_earned, 2) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Belum ada riwayat point
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @include('partials.per-page-selector', ['paginator' => $pointHistories])
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto refresh every 60 seconds
    setTimeout(function() {
        location.reload();
    }, 60000);

    // Refresh button loading state
    document.getElementById('syncMyPointForm').addEventListener('submit', function() {
        var btn  = document.getElementById('btnSyncMyPoint');
        var icon = document.getElementById('syncMyIcon');
        var text = document.getElementById('syncMyText');
        btn.disabled = true;
        icon.classList.add('spin-icon');
        text.textContent = ' Menyinkronkan...';
    });
</script>
<style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .spin-icon { display: inline-block; animation: spin 0.8s linear infinite; }
</style>
@endsection
