@extends('layouts.app')

@section('title', 'Detail Point PIC - ' . $pic->name . ' - ' . $appSettings['app_name'])
@section('page-title', 'Detail Point PIC')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    @if(session('success'))
    <div class="col-md-12 mb-3">
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    <!-- Header -->
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">{{ $pic->name }}</h4>
                        <span class="badge bg-info">{{ $pic->role }}</span>
                        <span class="text-muted ms-2">{{ $pic->username }}</span>
                    </div>
                    <a href="{{ route('admin.pic-points.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Point</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['total_points']) }}</h2>
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
                        <h2 class="mb-0 fw-bold">+{{ number_format($stats['points_today']) }}</h2>
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
                        <h2 class="mb-0 fw-bold">+{{ number_format($stats['points_this_month']) }}</h2>
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
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['total_tasks']) }}</h2>
                    </div>
                    <i class="bi bi-list-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Points by Step Breakdown -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart"></i> Breakdown Point per Tugas
            </div>
            <div class="card-body">
                @if($pointsByStep->count() > 0)
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        @foreach($pointsByStep as $stepData)
                        <tr>
                            <td>
                                <span class="badge bg-secondary">{{ \App\Models\PicPointHistory::getLabelForStep($stepData->step) }}</span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-success">{{ number_format($stepData->total) }}</span>
                                <small class="text-muted">({{ $stepData->count }}x)</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Belum ada point yang diperoleh
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- History Table -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history"></i> Riwayat Perolehan Point</span>
                <a href="{{ route('admin.pic-points.export-show', array_merge(['pic' => $pic->id], request()->only(['tanggal_dari', 'tanggal_sampai', 'step']))) }}" class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </a>
            </div>
            <div class="card-body">
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
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.pic-points.show', $pic) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
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
                                    <a href="{{ route('admin.submissions.show', $history->submission) }}">
                                        <code>{{ $history->submission->kode_submit }}</code>
                                    </a>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $history->step === 'adjustment' ? 'bg-warning text-dark' : 'bg-info' }}">
                                        {{ \App\Models\PicPointHistory::getLabelForStep($history->step) }}
                                    </span>
                                </td>
                                <td>{{ $history->description ?? '-' }}</td>
                                <td class="text-end">
                                    @if($history->points_earned > 0)
                                        <span class="badge bg-success fs-6">+{{ $history->points_earned }}</span>
                                    @else
                                        <span class="badge bg-danger fs-6">{{ $history->points_earned }}</span>
                                    @endif
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
                <div class="d-flex justify-content-center">
                    {{ $pointHistories->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto refresh every 30 seconds
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>
@endsection
