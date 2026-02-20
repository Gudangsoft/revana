@extends('layouts.app')

@section('title', 'Detail Point ' . $marketing->name . ' - ' . $appSettings['app_name'])
@section('page-title', 'Detail Point Marketing')

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

    <!-- Marketing Info Card -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person"></i> Informasi Marketing
            </div>
            <div class="card-body text-center">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                     style="width: 80px; height: 80px; font-size: 2rem;">
                    {{ strtoupper(substr($marketing->name, 0, 1)) }}
                </div>
                <h4 class="mb-1">{{ $marketing->name }}</h4>
                <p class="text-muted mb-2">{{ $marketing->email ?? '-' }}</p>
                <p class="text-muted mb-3">{{ $marketing->phone ?? '-' }}</p>
                
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="mb-0 text-success">{{ number_format($stats['total_points']) }}</h4>
                        <small class="text-muted">Total Point</small>
                    </div>
                    <div class="col-4">
                        <h4 class="mb-0 text-info">{{ number_format($stats['total_submissions']) }}</h4>
                        <small class="text-muted">Submission</small>
                    </div>
                    <div class="col-4">
                        <h4 class="mb-0 text-warning">{{ number_format($stats['points_this_month']) }}</h4>
                        <small class="text-muted">Bulan Ini</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Adjust Points -->
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-plus-circle"></i> Penyesuaian Point
            </div>
            <div class="card-body">
                <form action="{{ route('admin.marketing-points.adjust', $marketing) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Point</label>
                        <input type="number" name="points" class="form-control" required 
                               placeholder="Contoh: 10 atau -5">
                        <small class="text-muted">Gunakan angka negatif untuk mengurangi</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan</label>
                        <input type="text" name="reason" class="form-control" required
                               placeholder="Alasan penyesuaian">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check"></i> Simpan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Point History -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock-history"></i> Riwayat Point</span>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.marketing-points.export', array_merge(['marketing' => $marketing->id], request()->only(['tanggal_dari', 'tanggal_sampai']))) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.marketing-points.index') }}" class="btn btn-sm btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="date" name="tanggal_dari" class="form-control form-control-sm" 
                               value="{{ request('tanggal_dari') }}" placeholder="Dari tanggal">
                    </div>
                    <div class="col-md-4">
                        <input type="date" name="tanggal_sampai" class="form-control form-control-sm" 
                               value="{{ request('tanggal_sampai') }}" placeholder="Sampai tanggal">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.marketing-points.show', $marketing) }}" class="btn btn-sm btn-secondary">
                            <i class="bi bi-x"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Submission</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Point</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pointHistories as $history)
                        <tr>
                            <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($history->submission)
                                <a href="{{ route('admin.submissions.show', $history->submission) }}">
                                    {{ $history->submission->title }}
                                </a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $history->description }}</td>
                            <td class="text-center">
                                @if($history->points_earned >= 0)
                                <span class="badge bg-success">+{{ $history->points_earned }}</span>
                                @else
                                <span class="badge bg-danger">{{ $history->points_earned }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada riwayat point
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pointHistories->hasPages())
            <div class="card-footer">
                {{ $pointHistories->links() }}
            </div>
            @endif
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
