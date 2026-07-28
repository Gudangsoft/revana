@extends('layouts.app')

@section('title', 'Laporan Kinerja - ' . $appSettings['app_name'])
@section('page-title', 'Laporan Kinerja')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
<div class="row g-3">

    {{-- Filter --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <form action="{{ route('admin.laporan-kinerja.index') }}" method="GET" class="row g-2 align-items-end">
                    {{-- Filter Bulanan --}}
                    <div class="col-auto">
                        <label class="form-label mb-1 small fw-semibold text-muted">
                            Bulan
                            <i class="bi bi-info-circle text-muted"
                               title="Periode kinerja memakai cutoff 26–25, bukan kalender 1–31. Contoh: pilih Juli = periode 26 Juni s/d 25 Juli."></i>
                        </label>
                        <select name="bulan" class="form-select form-select-sm sel-bulanan" id="selBulan">
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 small fw-semibold text-muted">Tahun</label>
                        <select name="tahun" class="form-select form-select-sm sel-bulanan" id="selTahun">
                            @foreach($tahunList as $y)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pemisah --}}
                    <div class="col-auto d-flex align-items-end pb-1">
                        <span class="text-muted small">atau</span>
                    </div>

                    {{-- Filter Rentang Tanggal --}}
                    <div class="col-auto">
                        <label class="form-label mb-1 small fw-semibold text-primary">
                            <i class="bi bi-calendar-range"></i> Dari Tanggal
                        </label>
                        <input type="date" name="dari_tanggal" id="inputDari"
                               class="form-control form-control-sm"
                               value="{{ $dariTanggal ?? '' }}"
                               max="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-auto d-flex align-items-end pb-1">
                        <span class="text-muted small">s/d</span>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 small fw-semibold text-primary">
                            <i class="bi bi-calendar-range"></i> Sampai Tanggal
                        </label>
                        <input type="date" name="sampai_tanggal" id="inputSampai"
                               class="form-control form-control-sm"
                               value="{{ $sampaiTanggal ?? '' }}"
                               max="{{ now()->format('Y-m-d') }}">
                    </div>

                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search"></i> Tampilkan
                        </button>
                        <a href="{{ route('admin.laporan-kinerja.index') }}" class="btn btn-outline-secondary btn-sm ms-1">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                    <div class="col-auto ms-auto d-flex gap-2 align-items-center">
                        <span class="badge {{ $isRange ? 'bg-primary' : 'bg-secondary' }} fs-6 px-3 py-2">
                            <i class="bi bi-calendar{{ $isRange ? '-range' : '3' }}"></i> {{ $namaBulan }}
                        </span>
                        @php
                            $exportParams = $isRange
                                ? ['dari_tanggal' => $dariTanggal, 'sampai_tanggal' => $sampaiTanggal]
                                : ['bulan' => $bulan, 'tahun' => $tahun];
                        @endphp
                        <a href="{{ route('admin.laporan-kinerja.export-excel', $exportParams) }}"
                           class="btn btn-success btn-sm">
                            <i class="bi bi-file-earmark-excel"></i> Excel
                        </a>
                        <a href="{{ route('admin.laporan-kinerja.export-pdf', $exportParams) }}"
                           class="btn btn-danger btn-sm">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                    </div>
                </form>
                <form action="{{ route('admin.laporan-kinerja.sync') }}" method="POST" class="mt-2 text-end">
                    @csrf
                    @foreach($exportParams as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" class="btn btn-outline-warning btn-sm"
                            onclick="return confirm('Sinkronkan riwayat poin PIC & Marketing sesuai ketentuan poin yang berlaku sekarang? Ini cuma mengisi riwayat yang belum ada, tidak menimpa data yang sudah ada.')">
                        <i class="bi bi-arrow-repeat"></i> Sinkron Data Point
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold">{{ $picRekap->count() }}</div>
                <div class="small">PIC Aktif</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white h-100">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold">{{ $totalPicTugas }}</div>
                <div class="small">Total Tugas PIC</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white h-100">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold">{{ $mktRekap->count() }}</div>
                <div class="small">Marketing Aktif</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-dark h-100">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold">{{ $totalMktSubmit }}</div>
                <div class="small">Total Submit Marketing</div>
            </div>
        </div>
    </div>

    {{-- Rekap PIC --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                <span><i class="bi bi-person-badge-fill"></i> Rekap Kinerja PIC — {{ $namaBulan }}</span>
                <span class="badge bg-white text-primary">{{ $picRekap->count() }} PIC aktif</span>
            </div>
            <div class="card-body p-0">
                @if($picRekap->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Tidak ada data kinerja PIC untuk bulan ini.
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered align-middle mb-0" style="font-size:0.82rem">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width:40px">No</th>
                                <th>Nama PIC</th>
                                @foreach($steps as $key => $label)
                                    <th class="text-center" style="min-width:60px" title="{{ $label }}">{{ $label }}</th>
                                @endforeach
                                <th class="text-center bg-success text-white" style="min-width:70px">Total Tugas</th>
                                <th class="text-center bg-warning text-dark" style="min-width:70px">Total Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($picRekap as $i => $row)
                            <tr>
                                <td class="text-center text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $row['pic']->name }}</div>
                                    @if($row['pic']->role)
                                        <small class="text-muted">{{ ucfirst($row['pic']->role) }}</small>
                                    @endif
                                </td>
                                @foreach($steps as $key => $label)
                                    <td class="text-center">
                                        @if($row['step_counts'][$key] > 0)
                                            <span class="badge bg-primary rounded-pill">{{ $row['step_counts'][$key] }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center fw-bold text-success">{{ $row['total_tugas'] }}</td>
                                <td class="text-center fw-bold text-warning">{{ $row['total_poin'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">TOTAL</td>
                                @foreach($steps as $key => $label)
                                    <td class="text-center">
                                        {{ $picRekap->sum(fn($r) => $r['step_counts'][$key]) ?: '—' }}
                                    </td>
                                @endforeach
                                <td class="text-center text-success">{{ $totalPicTugas }}</td>
                                <td class="text-center text-warning">{{ $totalPicPoin }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Rekap Marketing --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-info text-white">
                <span><i class="bi bi-megaphone-fill"></i> Rekap Kinerja Marketing — {{ $namaBulan }}</span>
                <span class="badge bg-white text-info">{{ $mktRekap->count() }} marketing aktif</span>
            </div>
            <div class="card-body p-0">
                @if($mktRekap->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Tidak ada data kinerja Marketing untuk bulan ini.
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered align-middle mb-0" style="font-size:0.85rem">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width:40px">No</th>
                                <th>Nama Marketing</th>
                                <th class="text-center" style="min-width:120px">Total Submit</th>
                                <th class="text-center" style="min-width:100px">Total Poin</th>
                                <th class="text-center" style="min-width:80px">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mktRekap as $i => $row)
                            <tr>
                                <td class="text-center text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $row['marketing']->name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info rounded-pill fs-6 px-3">{{ $row['total_submit'] }}</span>
                                </td>
                                <td class="text-center fw-bold text-warning">{{ $row['total_poin'] }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.marketing-points.show', $row['marketing']->id) }}" class="btn btn-outline-info btn-sm py-0">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">TOTAL</td>
                                <td class="text-center text-info">{{ $totalMktSubmit }}</td>
                                <td class="text-center text-warning">{{ $totalMktPoin }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    const inputDari   = document.getElementById('inputDari');
    const inputSampai = document.getElementById('inputSampai');
    const selBulan    = document.getElementById('selBulan');
    const selTahun    = document.getElementById('selTahun');

    function sync() {
        const isRange = inputDari.value !== '' || inputSampai.value !== '';
        selBulan.disabled = isRange;
        selTahun.disabled = isRange;
        selBulan.style.opacity = isRange ? '0.4' : '1';
        selTahun.style.opacity = isRange ? '0.4' : '1';

        // Sinkronkan min sampai_tanggal dengan nilai dari_tanggal
        if (inputDari.value) inputSampai.min = inputDari.value;
        if (inputSampai.value) inputDari.max = inputSampai.value;
        else inputDari.max = '{{ now()->format("Y-m-d") }}';
    }

    inputDari.addEventListener('input', sync);
    inputSampai.addEventListener('input', sync);
    sync();
})();
</script>
@endpush
@endsection
