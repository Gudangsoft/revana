@extends('marketing.layouts.app')

@section('title', 'Point Saya')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-trophy"></i> Point Saya
    </h4>
    <button class="btn btn-outline-primary btn-sm" onclick="location.reload()">
        <i class="bi bi-arrow-clockwise"></i> Refresh
    </button>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
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
    
    <div class="col-md-4 mb-3">
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
    
    <div class="col-md-4 mb-3">
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
</div>

<!-- Point Info -->
<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle"></i>
    <strong>Sistem Point Marketing:</strong> Setiap artikel yang berhasil disubmit akan memberikan <strong>+{{ \App\Models\TaskPointSetting::getMarketingPoints('submit') }} point</strong>.
</div>

@php
    $mktPeriodOptions = [
        ''      => 'Semua',
        'today' => 'Hari Ini',
        'week'  => 'Minggu Ini',
        'month' => 'Bulan Ini',
        'year'  => 'Tahun Ini',
    ];
@endphp

<!-- Point History -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-clock-history"></i> Riwayat Perolehan Point
    </div>
    <div class="card-body">
        <div class="btn-group mb-3 flex-wrap" role="group">
            @foreach($mktPeriodOptions as $value => $label)
            <a href="{{ route('marketing.points', array_merge(request()->except(['period', 'page']), $value ? ['period' => $value] : [])) }}"
               class="btn btn-sm {{ request('period', '') === $value ? 'btn-primary' : 'btn-outline-primary' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        @if($pointHistories->count() > 0)
        @include('partials.column-toggle', ['tableId' => 'mktPointsTable', 'columns' => ['Tanggal', 'Point', 'Keterangan', 'Artikel'], 'columnOffset' => 0])
        <div class="table-responsive">
            <table id="mktPointsTable" class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Point</th>
                        <th>Keterangan</th>
                        <th>Artikel</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pointHistories as $history)
                    <tr>
                        <td>{{ $history->created_at->format('d M Y H:i') }}</td>
                        <td>
                            <span class="badge bg-success fs-6">+{{ $history->points_earned }}</span>
                        </td>
                        <td>{{ $history->description }}</td>
                        <td>
                            @if($history->submission)
                            <code>{{ $history->submission->kode_submit }}</code>
                            <br>
                            <small class="text-muted">{{ Str::limit($history->submission->judul_artikel, 30) }}</small>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @include('partials.per-page-selector', ['paginator' => $pointHistories])
        @else
        <div class="text-center text-muted py-5">
            <i class="bi bi-star" style="font-size: 4rem;"></i>
            <h5 class="mt-3">Belum Ada Point</h5>
            <p>Point akan bertambah ketika artikel Anda berhasil disubmit.</p>
        </div>
        @endif
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
