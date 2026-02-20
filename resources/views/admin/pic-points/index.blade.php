@extends('layouts.app')

@section('title', 'Laporan Point PIC - ' . $appSettings['app_name'])
@section('page-title', 'Laporan Point PIC')

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

    <!-- Stats Cards -->
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total PIC Aktif</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($totalPics) }}</h2>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Point</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($totalPoints) }}</h2>
                    </div>
                    <i class="bi bi-trophy fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Tugas Selesai</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($totalTasks) }}</h2>
                    </div>
                    <i class="bi bi-list-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Top Bulan Ini</h6>
                        <h5 class="mb-0 fw-bold">
                            {{ $topPerformerThisMonth?->name ?? 'N/A' }}
                        </h5>
                        @if($topPerformerThisMonth)
                        <small>{{ number_format($topPerformerThisMonth->points_this_month ?? 0) }} point</small>
                        @endif
                    </div>
                    <i class="bi bi-star fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Points by Step -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-bar-chart"></i> Distribusi Point per Tugas
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
                                <small class="text-muted">({{ $stepData->count }} tugas)</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="text-center text-muted py-3">
                    Belum ada data
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Leaderboard Table -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy"></i> Leaderboard PIC</span>
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('admin.pic-points.sync-all') }}" class="d-inline" onsubmit="return confirm('Sinkronkan semua point PIC dari riwayat point?')">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="bi bi-arrow-repeat"></i> Sinkronkan Point
                        </button>
                    </form>
                    <a href="{{ route('admin.pic-points.export') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-download"></i> Export Excel
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Search -->
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-8">
                        <input type="text" class="form-control form-control-sm" name="search" placeholder="Cari nama, username, email..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        <a href="{{ route('admin.pic-points.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width: 60px;">Rank</th>
                                <th>Nama</th>
                                <th class="text-center">Belum Selesai</th>
                                <th class="text-end">Total Point</th>
                                <th class="text-end">Bulan Ini</th>
                                <th class="text-end">Tugas</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rank = ($pics->currentPage() - 1) * $pics->perPage(); @endphp
                            @forelse($pics as $pic)
                            @php $rank++; @endphp
                            <tr>
                                <td class="text-center">
                                    @if($rank == 1)
                                        <span class="badge bg-warning text-dark fs-6"><i class="bi bi-trophy"></i> 1</span>
                                    @elseif($rank == 2)
                                        <span class="badge bg-secondary fs-6"><i class="bi bi-award"></i> 2</span>
                                    @elseif($rank == 3)
                                        <span class="badge bg-danger fs-6"><i class="bi bi-award"></i> 3</span>
                                    @else
                                        <span class="text-muted">{{ $rank }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $pic->name }}</strong><br>
                                    <small class="text-muted">{{ $pic->username }}</small>
                                </td>
                                <td class="text-center">
                                    @if($pic->pending_tasks_count > 0)
                                        <span class="badge bg-danger fs-6">{{ $pic->pending_tasks_count }}</span>
                                    @else
                                        <span class="badge bg-success">0</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-success fs-5">{{ number_format($pic->total_points) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-primary">+{{ number_format($pic->points_this_month) }}</span>
                                </td>
                                <td class="text-end">{{ number_format($pic->total_tasks_completed) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.pic-points.show', $pic) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#adjustModal{{ $pic->id }}" title="Adjust Point">
                                        <i class="bi bi-plus-slash-minus"></i>
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- Adjust Modal -->
                            <div class="modal fade" id="adjustModal{{ $pic->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.pic-points.adjust', $pic) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Adjust Point - {{ $pic->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Point Saat Ini</label>
                                                    <input type="text" class="form-control" value="{{ number_format($pic->total_points) }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Penyesuaian <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" name="adjustment" required placeholder="Contoh: 10 atau -5">
                                                    <small class="text-muted">Gunakan angka negatif untuk mengurangi point</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Alasan <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="reason" required placeholder="Masukkan alasan penyesuaian">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="bi bi-check-circle"></i> Adjust Point
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
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
                
                @include('partials.per-page-selector', ['paginator' => $pics])
            </div>
        </div>
    </div>
</div>

<!-- Point Configuration Info -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-gear"></i> Konfigurasi Point per Tugas
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($stepConfig as $step => $config)
            <div class="col-md-2 mb-3">
                <div class="border rounded p-3 text-center">
                    <span class="badge bg-secondary mb-2">{{ $config['label'] }}</span>
                    <h4 class="mb-0 text-success">+{{ $config['points'] }}</h4>
                    <small class="text-muted">point</small>
                </div>
            </div>
            @endforeach
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
