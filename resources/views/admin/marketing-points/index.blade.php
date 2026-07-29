@extends('layouts.app')

@section('title', 'Laporan Point Marketing - ' . $appSettings['app_name'])
@section('page-title', 'Laporan Point Marketing')

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
                        <h6 class="mb-0">Total Marketing Aktif</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($totalMarketings) }}</h2>
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
                        <h2 class="mb-0 fw-bold">{{ number_format($totalPoints, 2) }}</h2>
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
                        <h6 class="mb-0">Total Submission</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($totalSubmissions) }}</h2>
                    </div>
                    <i class="bi bi-file-earmark-text fs-1 opacity-50"></i>
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
                        <small>{{ number_format($topPerformerThisMonth->points_this_month ?? 0, 2) }} point</small>
                        @endif
                    </div>
                    <i class="bi bi-star fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Leaderboard -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy"></i> Leaderboard Marketing</span>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('admin.marketing-points.export-leaderboard') }}{{ request('search') ? '?search=' . urlencode(request('search')) : '' }}" class="btn btn-sm btn-info">
                        <i class="bi bi-file-excel"></i> Export Excel
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#resetAllMarketingPointsModal">
                        <i class="bi bi-trash3"></i> Reset Semua Point
                    </button>
                    @include('partials.column-toggle', ['tableId' => 'dataTable', 'columns' => ['Nama', 'Email', 'Phone', 'Total Submission', 'Total Point', 'Aksi'], 'columnOffset' => 1])
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" 
                           placeholder="Cari nama/email..." value="{{ request('search') }}" style="width: 200px;">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    @if(request('search'))
                    <a href="{{ route('admin.marketing-points.index') }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                    @endif
                </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="dataTable">
                        <thead class="table-light">
                            <tr>
                                <th width="60">Rank</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th class="text-center">Total Submission</th>
                                <th class="text-center">Total Point</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($marketings as $index => $marketing)
                            <tr>
                                <td>
                                    @php
                                        $rank = ($marketings->currentPage() - 1) * $marketings->perPage() + $index + 1;
                                    @endphp
                                    @if($rank == 1)
                                        <span class="badge bg-warning text-dark"><i class="bi bi-trophy"></i> 1</span>
                                    @elseif($rank == 2)
                                        <span class="badge bg-secondary"><i class="bi bi-trophy"></i> 2</span>
                                    @elseif($rank == 3)
                                        <span class="badge bg-danger"><i class="bi bi-trophy"></i> 3</span>
                                    @else
                                        <span class="text-muted">{{ $rank }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $marketing->name }}</strong>
                                </td>
                                <td>{{ $marketing->email ?? '-' }}</td>
                                <td>{{ $marketing->phone ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $marketing->submissions_count ?? $marketing->submissions->count() }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success fs-6">{{ number_format($marketing->total_points ?? 0, 2) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.marketing-points.show', $marketing) }}"
                                           class="btn btn-sm btn-outline-primary" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-warning" title="Sesuaikan Point"
                                            onclick="openAdjustModal({{ $marketing->id }}, '{{ addslashes($marketing->name) }}')">
                                            <i class="bi bi-sliders"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Tidak ada data marketing
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @include('partials.per-page-selector', ['paginator' => $marketings])
        </div>
    </div>
</div>

{{-- Adjust Point Modal --}}
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-sliders"></i> Sesuaikan Point — <span id="adjustModalName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="adjustForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Penyesuaian Point</label>
                        <input type="number" name="points" class="form-control" placeholder="Positif = tambah, negatif = kurangi" required>
                        <div class="form-text">Contoh: <code>5</code> untuk tambah 5 point, <code>-3</code> untuk kurangi 3 point</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alasan</label>
                        <input type="text" name="reason" class="form-control" maxlength="255" placeholder="Misal: bonus kinerja bulan ini" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="bi bi-check-circle"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Reset Semua Point Marketing --}}
<div class="modal fade" id="resetAllMarketingPointsModal" tabindex="-1" aria-labelledby="resetAllMarketingPointsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="resetAllMarketingPointsModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Reset Semua Point Marketing
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.marketing-points.reset-all') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>⚠ Peringatan Keras!</strong><br>
                        Tindakan ini akan:
                        <ul class="mb-0 mt-1">
                            <li>Menghapus <strong>SEMUA riwayat point</strong> dari semua Marketing secara permanen</li>
                            <li>Mengeset <strong>total_points = 0</strong> untuk semua Marketing</li>
                            <li><strong>Tidak bisa dibatalkan</strong> setelah dikonfirmasi</li>
                        </ul>
                    </div>
                    <p class="mb-1">Total sekarang: <strong class="text-success">{{ number_format($totalPoints, 2) }} point</strong> dari <strong>{{ number_format($totalMarketings) }} Marketing</strong> dan <strong>{{ number_format($totalHistories) }} riwayat</strong>.</p>
                    <p class="mb-3">Ketik <strong>RESET</strong> (huruf kapital) untuk mengkonfirmasi:</p>
                    <input type="text" name="konfirmasi" class="form-control @error('konfirmasi') is-invalid @enderror"
                           placeholder="Ketik RESET di sini..." autocomplete="off" required>
                    @error('konfirmasi')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3-fill me-1"></i>Ya, Reset Semua Point
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    setTimeout(function() { location.reload(); }, 30000);

    function openAdjustModal(id, name) {
        document.getElementById('adjustModalName').textContent = name;
        document.getElementById('adjustForm').action = '{{ url("/admin/marketing-points") }}/' + id + '/adjust';
        new bootstrap.Modal(document.getElementById('adjustModal')).show();
    }
</script>
@endsection
