@extends('layouts.app')

@section('title', 'Catatan Kinerja Harian PIC')
@section('page-title', 'Catatan Kinerja Harian PIC')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filter --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.laporan-harian.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">PIC</label>
                    <select name="pic_id" class="form-select form-select-sm">
                        <option value="">Semua PIC</option>
                        @foreach($pics as $pic)
                        <option value="{{ $pic->id }}" {{ $picId == $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Dari Tanggal</label>
                    <input type="date" name="dari_tanggal" class="form-control form-control-sm" value="{{ $dariTanggal }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="sampai_tanggal" class="form-control form-control-sm" value="{{ $sampaiTanggal }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="belum_divalidasi" id="chkBelum" value="1"
                               {{ $belumDivalidasi ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="chkBelum">Belum Divalidasi</label>
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.laporan-harian.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    <a href="{{ route('admin.laporan-harian.rekap') }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-calendar3 me-1"></i>Rekap
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary cards --}}
    @php
        $totalPicHari   = $laporan->total();
        $avgCapaian     = $laporan->count() > 0 ? round($laporan->avg('avg_capaian')) : 0;
        $totalValidated = $laporan->filter(fn($l) => $l->total_validated > 0 && $l->total_validated >= $l->total_kegiatan)->count();
        $totalBelum     = $totalPicHari - $totalValidated;
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-people-fill fs-2 opacity-75"></i>
                    <div>
                        <div class="fs-4 fw-bold">{{ $totalPicHari }}</div>
                        <div class="small opacity-75">Total PIC (per Hari)</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success text-white shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-percent fs-2 opacity-75"></i>
                    <div>
                        <div class="fs-4 fw-bold">{{ $avgCapaian }}%</div>
                        <div class="small opacity-75">Rata-rata Capaian</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info text-white shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-patch-check-fill fs-2 opacity-75"></i>
                    <div>
                        <div class="fs-4 fw-bold">{{ $totalValidated }}</div>
                        <div class="small opacity-75">Sudah Divalidasi</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning text-dark shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-hourglass-split fs-2 opacity-75"></i>
                    <div>
                        <div class="fs-4 fw-bold">{{ $totalBelum }}</div>
                        <div class="small opacity-75">Belum Divalidasi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart tren capaian --}}
    @if($chartData->count() > 1)
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="bi bi-graph-up me-2 text-primary"></i><strong>Tren Rata-rata Capaian Harian</strong></span>
            <span class="text-muted small">{{ $dariTanggal }} s.d. {{ $sampaiTanggal }}</span>
        </div>
        <div class="card-body py-2">
            <canvas id="chartCapaian" height="80"></canvas>
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-table me-2"></i><strong>Daftar Catatan Kinerja Harian</strong></span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary">{{ $laporan->total() }} data</span>
                <a href="{{ route('admin.laporan-harian.export') }}?{{ http_build_query(['pic_id' => $picId, 'dari_tanggal' => $dariTanggal, 'sampai_tanggal' => $sampaiTanggal]) }}"
                   class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download me-1"></i>Export CSV
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:120px">Tanggal</th>
                        <th style="width:180px">PIC</th>
                        <th style="width:80px" class="text-center">Kegiatan</th>
                        <th style="width:260px">Ringkasan Kegiatan</th>
                        <th style="width:90px" class="text-center">Capaian</th>
                        <th style="width:160px">Status Validasi</th>
                        <th style="width:70px" class="text-center">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporan as $item)
                    @php
                        $tanggalCarbon = \Carbon\Carbon::parse($item->tanggal);
                        $isAllValid    = $item->total_validated > 0 && $item->total_validated >= $item->total_kegiatan;
                        $isSomeValid   = $item->total_validated > 0 && !$isAllValid;
                    @endphp
                    <tr>
                        <td class="small text-nowrap">
                            {{ $tanggalCarbon->locale('id')->translatedFormat('d M Y') }}
                            @if($tanggalCarbon->isToday())
                                <br><span class="badge bg-success">Hari ini</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $item->pic->name ?? '-' }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill">{{ $item->total_kegiatan }}</span>
                        </td>
                        <td class="small" style="max-width:280px;">
                            @foreach(explode('||', $item->ringkasan_kegiatan ?? '') as $idx => $judul)
                            @if($judul)
                            <div class="d-flex align-items-start gap-1 {{ $idx > 0 ? 'mt-1' : '' }}">
                                <span class="badge bg-light text-dark border flex-shrink-0" style="font-size:0.65rem;">{{ $idx+1 }}</span>
                                <span class="text-truncate" style="max-width:240px;" title="{{ $judul }}">{{ Str::limit($judul, 60) }}</span>
                            </div>
                            @endif
                            @endforeach
                        </td>
                        <td class="text-center">
                            @php $c = $item->avg_capaian; @endphp
                            <span class="badge {{ $c >= 80 ? 'bg-success' : ($c >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                {{ $c }}%
                            </span>
                        </td>
                        <td>
                            @if($isAllValid)
                                <span class="badge bg-success">
                                    <i class="bi bi-patch-check-fill me-1"></i>Semua Valid
                                </span>
                                @if($item->last_validated_at)
                                <div class="small text-muted mt-1" style="font-size:0.72rem;">
                                    <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($item->last_validated_at)->format('d/m/Y H:i') }}
                                </div>
                                @endif
                                @if($item->last_validated_by && $validators->has($item->last_validated_by))
                                <div class="small text-muted" style="font-size:0.72rem;">
                                    <i class="bi bi-person-check me-1"></i>{{ $validators[$item->last_validated_by] }}
                                </div>
                                @endif
                            @elseif($isSomeValid)
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-patch-check me-1"></i>Sebagian ({{ $item->total_validated }}/{{ $item->total_kegiatan }})
                                </span>
                            @else
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-hourglass-split me-1"></i>Belum
                                </span>
                            @endif
                            @if($item->catatan_admin)
                                <i class="bi bi-chat-text-fill text-info ms-1" title="{{ $item->catatan_admin }}" style="cursor:help;"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.laporan-harian.show', [$item->pic_id, \Carbon\Carbon::parse($item->tanggal)->toDateString()]) }}"
                               class="btn btn-outline-primary btn-sm" title="Lihat & Validasi">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>Tidak ada data laporan untuk filter ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($laporan->hasPages())
        <div class="card-footer">
            {{ $laporan->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection

@if($chartData->count() > 1)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const labels = @json($chartData->pluck('tanggal')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m')));
    const values = @json($chartData->pluck('avg_capaian'));
    const ctx = document.getElementById('chartCapaian');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Rata-rata Capaian (%)',
                data: values,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.08)',
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#6366f1',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0, max: 100, ticks: { callback: v => v + '%' } },
                x: { grid: { display: false } }
            }
        }
    });
})();
</script>
@endpush
@endif
