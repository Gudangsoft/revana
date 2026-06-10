@extends('layouts.app')

@section('title', 'Rekap Bulanan Catatan Kinerja')
@section('page-title', 'Rekap Bulanan Catatan Kinerja Harian')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">

    <div class="mb-3">
        <a href="{{ route('admin.laporan-harian.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
        </a>
    </div>

    {{-- Filter --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.laporan-harian.rekap') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Bulan</label>
                    <input type="month" name="bulan" class="form-control form-control-sm" value="{{ $bulan }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">PIC</label>
                    <select name="pic_id" class="form-select form-select-sm">
                        <option value="">Semua PIC</option>
                        @foreach($pics as $pic)
                        <option value="{{ $pic->id }}" {{ $picId == $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search me-1"></i>Tampilkan
                    </button>
                    <a href="{{ route('admin.laporan-harian.rekap.export') }}?{{ http_build_query(request()->only(['bulan','pic_id'])) }}"
                       class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                    </a>
                    <a href="{{ route('admin.laporan-harian.export') }}?bulan={{ $bulan }}&pic_id={{ $picId }}&dari_tanggal={{ \Carbon\Carbon::parse($bulan)->startOfMonth()->toDateString() }}&sampai_tanggal={{ \Carbon\Carbon::parse($bulan)->endOfMonth()->toDateString() }}"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-filetype-csv me-1"></i>Export CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    @php $bulanLabel = \Carbon\Carbon::parse($bulan)->locale('id')->translatedFormat('F Y'); @endphp

    {{-- Summary header --}}
    @if($rekap->count() > 0)
    @php
        $totalKegiatan  = $rekap->sum('total_kegiatan');
        $avgCapaianAll  = round($rekap->avg('avg_capaian'));
        $totalValidated = $rekap->sum('total_validated');
        $pctValid       = $totalKegiatan > 0 ? round($totalValidated / $totalKegiatan * 100) : 0;
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold">{{ $rekap->count() }}</div>
                    <div class="small opacity-75">PIC Aktif</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-indigo text-white shadow-sm" style="background:#6366f1;">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold">{{ $totalKegiatan }}</div>
                    <div class="small opacity-75">Total Kegiatan</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success text-white shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold">{{ $avgCapaianAll }}%</div>
                    <div class="small opacity-75">Rata-rata Capaian</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info text-white shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold">{{ $pctValid }}%</div>
                    <div class="small opacity-75">% Tervalidasi</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Chart tren harian --}}
    @if($chartData->count() > 1)
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <i class="bi bi-graph-up me-2 text-primary"></i><strong>Tren Rata-rata Capaian — {{ $bulanLabel }}</strong>
        </div>
        <div class="card-body py-2">
            <canvas id="chartRekap" height="80"></canvas>
        </div>
    </div>
    @endif

    {{-- Tabel rekap per PIC --}}
    <div class="card shadow-sm">
        <div class="card-header">
            <i class="bi bi-table me-2"></i>
            <strong>Rekap Per PIC — {{ $bulanLabel }}</strong>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px">#</th>
                        <th>PIC</th>
                        <th style="width:110px" class="text-center">Hari Aktif</th>
                        <th style="width:120px" class="text-center">Total Kegiatan</th>
                        <th style="width:130px" class="text-center">Rata-rata Capaian</th>
                        <th style="width:130px" class="text-center">Tervalidasi</th>
                        <th style="width:110px" class="text-center">% Validasi</th>
                        <th style="width:70px" class="text-center">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekap as $i => $row)
                    @php
                        $pct = $row->total_kegiatan > 0 ? round($row->total_validated / $row->total_kegiatan * 100) : 0;
                        $c   = $row->avg_capaian;
                    @endphp
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $row->pic->name ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $row->total_hari }} hari</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $row->total_kegiatan }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $c >= 80 ? 'bg-success' : ($c >= 50 ? 'bg-warning text-dark' : 'bg-danger') }} fs-6">
                                {{ $c }}%
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success">{{ $row->total_validated }}</span>
                            <span class="text-muted small"> / {{ $row->total_kegiatan }}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:6px;">
                                    <div class="progress-bar {{ $pct === 100 ? 'bg-success' : 'bg-primary' }}" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="small text-muted" style="min-width:32px;">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.laporan-harian.index') }}?pic_id={{ $row->pic_id }}&dari_tanggal={{ \Carbon\Carbon::parse($bulan)->startOfMonth()->toDateString() }}&sampai_tanggal={{ \Carbon\Carbon::parse($bulan)->endOfMonth()->toDateString() }}"
                               class="btn btn-outline-primary btn-sm" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>Tidak ada data untuk bulan ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
    const ctx = document.getElementById('chartRekap');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Rata-rata Capaian (%)',
                data: values,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.08)',
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#10b981',
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
