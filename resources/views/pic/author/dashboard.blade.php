@extends('pic.layouts.app')

@section('title', 'Dashboard PIC Author')
@section('page-title', 'Dashboard PIC Author')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
@if(session('motivational_message'))
<div class="alert alert-dismissible fade show border-0 mb-3" role="alert"
     style="background: linear-gradient(135deg,#10b981,#0891b2); color:#fff;">
    <div class="d-flex align-items-center gap-2">
        <span class="fs-5">{{ session('motivational_message') }}</span>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Pengingat kinerja harian --}}
@if($showReminder)
<div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-3 shadow-sm" role="alert">
    <i class="bi bi-alarm-fill fs-3 text-warning flex-shrink-0"></i>
    <div class="flex-grow-1">
        <strong>Pengingat!</strong> Anda belum mengisi catatan kinerja hari ini.
        <div class="small mt-1">Sudah lewat pukul {{ now()->format('H:i') }} — jangan lupa catat kegiatan Anda hari ini.</div>
    </div>
    <a href="{{ route('pic.laporan-harian.index') }}" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil-square me-1"></i>Isi Sekarang
    </a>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Sync Point Reminder --}}
@include('partials.sync-point-reminder', [
    'reminderId' => 'pic_sync_point',
    'syncRoute'  => route('pic.points.sync'),
    'syncLabel'  => 'Sinkronkan Point Saya Sekarang',
    'syncMethod' => 'POST',
])

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-info-circle"></i> Selamat Datang, {{ auth()->guard('pic')->user()->name }}</h5>
                <p class="card-text">Anda login sebagai <strong>PIC Author</strong>. Gunakan dashboard ini untuk mengelola tugas dan monitoring artikel.</p>
            </div>
        </div>
    </div>
</div>

{{-- Widget Catatan Kinerja Harian --}}
<div class="row g-3 mb-4">
    {{-- Status Hari Ini --}}
    <div class="col-md-4">
        @if($todayEntries->count() > 0)
        <a href="{{ route('pic.laporan-harian.index') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-semibold opacity-75">Catatan Kinerja Hari Ini</span>
                        <i class="bi bi-clipboard2-check-fill fs-4 opacity-75"></i>
                    </div>
                    <div class="fw-bold fs-5 mb-1">
                        <i class="bi bi-check-circle-fill me-1"></i>Sudah Diisi
                    </div>
                    <div class="small opacity-90">{{ $todayEntries->count() }} kegiatan &nbsp;·&nbsp; Rata-rata {{ round($todayEntries->avg('capaian_hasil')) }}%</div>
                    <div class="mt-2">
                        @foreach($todayEntries as $e)
                        <div class="small opacity-85 text-truncate">
                            <i class="bi bi-dot"></i>{{ $e->judul_kegiatan ?: \Str::limit($e->target_kerja, 40) }}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </a>
        @else
        <a href="{{ route('pic.laporan-harian.index') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-semibold opacity-75">Catatan Kinerja Hari Ini</span>
                        <i class="bi bi-clipboard2-x-fill fs-4 opacity-75"></i>
                    </div>
                    <div class="fw-bold fs-5 mb-1">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>Belum Diisi
                    </div>
                    <div class="small opacity-90">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
                    <div class="mt-2 small opacity-85">Klik untuk mengisi catatan kinerja hari ini</div>
                </div>
            </div>
        </a>
        @endif
    </div>

    {{-- Capaian Bulan Ini --}}
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm" style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold opacity-75">Capaian Bulan {{ now()->locale('id')->translatedFormat('F Y') }}</span>
                    <i class="bi bi-bar-chart-fill fs-4 opacity-75"></i>
                </div>
                <div class="fw-bold fs-3 mb-1">{{ round($monthAvgCapaian ?? 0) }}%</div>
                <div class="progress mb-2" style="height:6px;background:rgba(255,255,255,0.3);">
                    @php $avg = round($monthAvgCapaian ?? 0); @endphp
                    <div class="progress-bar bg-white" style="width:{{ $avg }}%"></div>
                </div>
                <div class="small opacity-90">{{ $monthTotalEntries }} kegiatan tercatat bulan ini</div>
            </div>
        </div>
    </div>

    {{-- Streak --}}
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm" style="background:linear-gradient(135deg,#ec4899,#db2777);color:#fff;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold opacity-75">Konsistensi Pengisian</span>
                    <i class="bi bi-fire fs-4 opacity-75"></i>
                </div>
                <div class="fw-bold fs-3 mb-1">
                    {{ $streak }} <span class="fs-6 fw-normal">hari</span>
                </div>
                <div class="small opacity-90">
                    @if($streak === 0)
                        Belum ada streak — isi hari ini untuk mulai!
                    @elseif($streak < 3)
                        Bagus! Pertahankan konsistensi pengisian.
                    @elseif($streak < 7)
                        Luar biasa! {{ $streak }} hari berturut-turut.
                    @else
                        Keren! Streak {{ $streak }} hari! 🔥
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-speedometer2"></i> Menu Utama
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="{{ route('pic.submissions.monitoring') }}" class="text-decoration-none">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="bi bi-list-check text-primary" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Monitoring & Tugas</h5>
                                    <p class="card-text text-muted">Lihat dan kelola tugas yang ditugaskan</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('pic.journals.index') }}" class="text-decoration-none">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="bi bi-journal-text text-success" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Data Jurnal</h5>
                                    <p class="card-text text-muted">Kelola data jurnal</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('pic.points.index') }}" class="text-decoration-none">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <i class="bi bi-trophy-fill text-warning" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Point Saya</h5>
                                    <p class="card-text text-muted">Lihat perolehan point</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('pic.submissions.index') }}" class="text-decoration-none">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <i class="bi bi-journal-bookmark text-info" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Kelola Jurnal</h5>
                                    <p class="card-text text-muted">Kelola submission jurnal normal</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('pic.fasttrack.index') }}" class="text-decoration-none">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <i class="bi bi-lightning-charge text-warning" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Kelola Jurnal FS</h5>
                                    <p class="card-text text-muted">Kelola submission jurnal fasttrack</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('pic.journal-slots.index') }}" class="text-decoration-none">
                            <div class="card h-100 border-danger">
                                <div class="card-body text-center">
                                    <i class="bi bi-rocket text-danger" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Data Jurnal FS</h5>
                                    <p class="card-text text-muted">Kelola data jurnal fasttrack</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('pic.points.rankings') }}" class="text-decoration-none">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="bi bi-bar-chart-fill text-success" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Peringkat Point</h5>
                                    <p class="card-text text-muted">Lihat peringkat point PIC & Marketing</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Point Rankings Section --}}
<div class="row mt-4">
    {{-- PIC Point Ranking --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy-fill"></i> Peringkat Point PIC</span>
                <a href="{{ route('pic.points.rankings') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-bar-chart-fill"></i> Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">Rank</th>
                                <th>Nama PIC</th>
                                <th class="text-center">Total Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPics as $index => $pic)
                            <tr class="{{ $pic->id == auth()->guard('pic')->user()->id ? 'table-info' : '' }}">
                                <td class="text-center">
                                    @if($index == 0)
                                        <span class="badge bg-warning text-dark" style="font-size: 1rem;">
                                            <i class="bi bi-trophy-fill"></i> 1
                                        </span>
                                    @elseif($index == 1)
                                        <span class="badge bg-secondary" style="font-size: 1rem;">
                                            <i class="bi bi-award-fill"></i> 2
                                        </span>
                                    @elseif($index == 2)
                                        <span class="badge bg-danger" style="font-size: 1rem;">
                                            <i class="bi bi-award"></i> 3
                                        </span>
                                    @else
                                        <span class="text-muted">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary me-2">
                                            {{ strtoupper(substr($pic->name, 0, 1)) }}
                                        </div>
                                        <strong>{{ $pic->name }}</strong>
                                        @if($pic->id == auth()->guard('pic')->user()->id)
                                            <span class="badge bg-info ms-1">Anda</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary" style="font-size: 1rem;">
                                        {{ number_format($pic->total_points ?? 0) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0">Belum ada data PIC</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Marketing Point Ranking --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy-fill"></i> Peringkat Point Marketing</span>
                <a href="{{ route('pic.points.rankings') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-bar-chart-fill"></i> Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">Rank</th>
                                <th>Nama Marketing</th>
                                <th class="text-center">Total Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topMarketings as $index => $mkt)
                            <tr>
                                <td class="text-center">
                                    @if($index == 0)
                                        <span class="badge bg-warning text-dark" style="font-size: 1rem;">
                                            <i class="bi bi-trophy-fill"></i> 1
                                        </span>
                                    @elseif($index == 1)
                                        <span class="badge bg-secondary" style="font-size: 1rem;">
                                            <i class="bi bi-award-fill"></i> 2
                                        </span>
                                    @elseif($index == 2)
                                        <span class="badge bg-danger" style="font-size: 1rem;">
                                            <i class="bi bi-award"></i> 3
                                        </span>
                                    @else
                                        <span class="text-muted">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-success me-2">
                                            {{ strtoupper(substr($mkt->name, 0, 1)) }}
                                        </div>
                                        <strong>{{ $mkt->name }}</strong>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success" style="font-size: 1rem;">
                                        {{ number_format($mkt->total_points ?? 0) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0">Belum ada data Marketing</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.9rem;
    flex-shrink: 0;
}
</style>
@endsection
