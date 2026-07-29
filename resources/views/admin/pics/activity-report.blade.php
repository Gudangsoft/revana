@extends('layouts.app')

@section('title', 'Laporan Aktivitas PIC')
@section('page-title', 'Laporan Aktivitas PIC')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row mb-4">
    <!-- Stats Cards -->
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total PIC</h6>
                        <h2 class="mb-0 fw-bold">{{ $stats['total_pics'] }}</h2>
                        <small>{{ $stats['active_pics'] }} aktif</small>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Point</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['total_points_given'], 2) }}</h2>
                        <small>Point diberikan</small>
                    </div>
                    <i class="bi bi-trophy fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Tugas</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['total_tasks_completed']) }}</h2>
                        <small>Tugas diselesaikan</small>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Rata-rata</h6>
                        <h2 class="mb-0 fw-bold">{{ $stats['total_pics'] > 0 ? number_format($stats['total_points_given'] / $stats['total_pics'], 0) : 0 }}</h2>
                        <small>Point per PIC</small>
                    </div>
                    <i class="bi bi-graph-up fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-funnel"></i> Filter Laporan
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">PIC</label>
                <select class="form-select" name="pic_id">
                    <option value="">-- Semua PIC --</option>
                    @foreach($allPics as $picOption)
                        <option value="{{ $picOption->id }}" {{ request('pic_id') == $picOption->id ? 'selected' : '' }}>
                            {{ $picOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('admin.pics.activity-report') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
            <div class="col-md-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="show_inactive" id="show_inactive" {{ request('show_inactive') ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_inactive">
                        Tampilkan PIC yang tidak aktif
                    </label>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- PIC Activity Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bar-chart"></i> Laporan Aktivitas Per PIC</span>
        <div>
            <a href="{{ route('admin.pics.activity-report.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-sm btn-success">
                <i class="bi bi-file-excel"></i> Export Excel
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped" id="activityTable">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama PIC</th>
                        <th>Status</th>
                        <th class="text-center">Total Point</th>
                        <th class="text-center">Tugas Selesai</th>
                        <th>Breakdown Per Pekerjaan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pics as $index => $picData)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $picData->name }}</strong><br>
                            <small class="text-muted">{{ $picData->email }}</small>
                        </td>
                        <td>
                            @if($picData->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <h5 class="mb-0">
                                <span class="badge bg-primary">{{ number_format($picData->filtered_points) }}</span>
                            </h5>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">{{ number_format($picData->filtered_tasks) }}</span>
                        </td>
                        <td>
                            @if($picData->step_breakdown->count() > 0)
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($picData->step_breakdown as $step)
                                        @php
                                            $stepLabel = \App\Models\PicPointHistory::getLabelForStep($step->step);
                                        @endphp
                                        <span class="badge bg-secondary" title="{{ $stepLabel }}: {{ $step->count }} tugas, {{ $step->total }} point">
                                            {{ $stepLabel }}: {{ $step->total }}pt ({{ $step->count }}x)
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">Belum ada aktivitas</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.pic-points.show', $picData->id) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Tidak ada data PIC
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
