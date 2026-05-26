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

        <!-- Rekap Akreditasi -->
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-bar-chart-fill"></i> Rekap Artikel
                @if(request('tanggal_dari') || request('tanggal_sampai'))
                <small class="text-muted ms-1">
                    ({{ request('tanggal_dari') ? \Carbon\Carbon::parse(request('tanggal_dari'))->format('d/m/Y') : '...' }}
                    – {{ request('tanggal_sampai') ? \Carbon\Carbon::parse(request('tanggal_sampai'))->format('d/m/Y') : '...' }})
                </small>
                @else
                <small class="text-muted ms-1">(Semua)</small>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="px-3 py-2 border-bottom bg-light d-flex justify-content-between">
                    <span class="fw-semibold text-dark">Total Artikel</span>
                    <span class="fw-bold text-primary">{{ number_format($recapTotal['count']) }}</span>
                </div>
                @if($recapByAccreditation->isEmpty())
                <div class="px-3 py-3 text-center text-muted small">
                    <i class="bi bi-journal-x"></i> Belum ada data jurnal
                </div>
                @else
                <ul class="list-group list-group-flush">
                    @foreach($recapByAccreditation as $accred => $data)
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <div>
                            <span class="badge rounded-pill
                                @if(str_contains(strtolower($accred), 'sinta 1') || str_contains(strtolower($accred), 'sinta1')) bg-danger
                                @elseif(str_contains(strtolower($accred), 'sinta 2') || str_contains(strtolower($accred), 'sinta2')) bg-warning text-dark
                                @elseif(str_contains(strtolower($accred), 'sinta 3') || str_contains(strtolower($accred), 'sinta3')) bg-info text-dark
                                @elseif(str_contains(strtolower($accred), 'sinta 4') || str_contains(strtolower($accred), 'sinta4')) bg-success
                                @elseif(str_contains(strtolower($accred), 'sinta 5') || str_contains(strtolower($accred), 'sinta5')) bg-secondary
                                @elseif(str_contains(strtolower($accred), 'sinta 6') || str_contains(strtolower($accred), 'sinta6')) bg-secondary
                                @else bg-dark @endif
                            ">{{ $accred }}</span>
                        </div>
                        <span class="fw-semibold">{{ $data['count'] }} artikel</span>
                    </li>
                    @endforeach
                </ul>
                @endif
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
                        @include('partials.column-toggle', ['tableId' => 'dataTable', 'columns' => ['Tanggal', 'Submission', 'Nama Jurnal', 'Akreditasi', 'Deskripsi', 'Point']])
                        <a href="{{ route('admin.marketing-points.export', array_merge(['marketing' => $marketing->id], request()->only(['tanggal_dari', 'tanggal_sampai', 'process_type']))) }}" class="btn btn-sm btn-success">
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
                    <div class="col-md-3">
                        <input type="date" name="tanggal_dari" class="form-control form-control-sm" 
                               value="{{ request('tanggal_dari') }}" placeholder="Dari tanggal">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="tanggal_sampai" class="form-control form-control-sm" 
                               value="{{ request('tanggal_sampai') }}" placeholder="Sampai tanggal">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="process_type">
                            <option value="all" {{ request('process_type', 'all') == 'all' ? 'selected' : '' }}>Semua Jalur</option>
                            <option value="normal" {{ request('process_type') == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="fasttrack" {{ request('process_type') == 'fasttrack' ? 'selected' : '' }}>Fasttrack</option>
                        </select>
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
                <table class="table table-hover mb-0" id="dataTable">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Submission</th>
                            <th>Nama Jurnal</th>
                            <th>Akreditasi</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Point</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pointHistories as $history)
                        @php
                            $journal = $history->submission?->journalSlot?->journalMaster;
                        @endphp
                        <tr>
                            <td class="text-nowrap">{{ $history->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($history->submission)
                                <a href="{{ route('admin.submissions.show', $history->submission) }}" class="text-decoration-none">
                                    <small>{{ $history->submission->kode_submit ?? $history->submission->title }}</small>
                                </a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($journal)
                                <small>{{ $journal->nama_jurnal }}</small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if($journal && $journal->accreditation)
                                <span class="badge rounded-pill
                                    @if(str_contains(strtolower($journal->accreditation), 'sinta 1') || str_contains(strtolower($journal->accreditation), 'sinta1')) bg-danger
                                    @elseif(str_contains(strtolower($journal->accreditation), 'sinta 2') || str_contains(strtolower($journal->accreditation), 'sinta2')) bg-warning text-dark
                                    @elseif(str_contains(strtolower($journal->accreditation), 'sinta 3') || str_contains(strtolower($journal->accreditation), 'sinta3')) bg-info text-dark
                                    @elseif(str_contains(strtolower($journal->accreditation), 'sinta 4') || str_contains(strtolower($journal->accreditation), 'sinta4')) bg-success
                                    @else bg-secondary @endif
                                ">{{ $journal->accreditation }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><small>{{ $history->description }}</small></td>
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
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada riwayat point
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('partials.per-page-selector', ['paginator' => $pointHistories])
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
