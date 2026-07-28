@extends('layouts.app')

@section('title', ' - Sinkronisasi Data')
@section('page-title', 'Sinkronisasi Data')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <h4 class="mb-1 fw-bold"><i class="bi bi-arrow-repeat text-primary"></i> Sinkronisasi Data</h4>
        <p class="text-muted mb-0">Pastikan slot jurnal sesuai dengan data aktual di database.</p>
    </div>

    {{-- Status Cards --}}
    @php
        $isAllSynced = $totalOutOfSync === 0;
    @endphp

    <div class="col-12">
        @if($isAllSynced)
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div>
                <strong>Semua data sudah sinkron!</strong>
                Tidak ada perbedaan antara counter tersimpan dan data aktual.
            </div>
        </div>
        @else
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong>{{ $totalOutOfSync }} item memerlukan sinkronisasi.</strong>
                Klik tombol sinkronisasi pada modul di bawah.
            </div>
        </div>
        @endif
    </div>

    {{-- Status Auto-Sync Otomatis (dipicu tiap ada admin membuka halaman admin,
    dibatasi 1x/15 menit — tidak butuh cron) — hanya membetulkan total_points
    PIC/Marketing dari riwayat yang SUDAH ADA, tidak pernah membuat riwayat baru
    dari data lama (lihat PointsAutoSync). --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                @if($autoSyncLastRunAt)
                    @php
                        $menitLalu = $autoSyncLastRunAt->diffInMinutes(now());
                        $sehat = $menitLalu <= 20; // toleransi: jadwal tiap 15 menit
                    @endphp
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <i class="bi {{ $sehat ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-warning' }} fs-5"></i>
                        <div>
                            <strong>Auto-Sync Poin Otomatis:</strong>
                            terakhir berjalan <strong>{{ $menitLalu }} menit yang lalu</strong>
                            ({{ $autoSyncLastRunAt->locale('id')->translatedFormat('d M Y, H:i') }})
                            @if($autoSyncLastResult)
                                — {{ $autoSyncLastResult['pic_synced'] }} PIC & {{ $autoSyncLastResult['mkt_synced'] }} marketing dikoreksi saat itu.
                            @endif
                        </div>
                    </div>
                @else
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <i class="bi bi-info-circle-fill text-muted fs-5"></i>
                        <div>
                            <strong>Auto-Sync Poin Otomatis belum pernah berjalan.</strong>
                            <span class="d-block small text-muted">
                                Akan berjalan otomatis begitu ada admin membuka halaman admin mana pun.
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ================================================ --}}
    {{-- SLOT JURNAL --}}
    {{-- ================================================ --}}
    <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                <i class="bi bi-calendar3 text-primary fs-5"></i>
                <span class="fw-semibold">Slot Jurnal</span>
                @if($stats['slots']['out_of_sync'] > 0)
                    <span class="badge bg-warning text-dark ms-auto">{{ $stats['slots']['out_of_sync'] }} tidak sinkron</span>
                @else
                    <span class="badge bg-success ms-auto">Sinkron</span>
                @endif
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Counter <code>slot_terpakai</code> dihitung ulang berdasarkan submission aktual yang berstatus bukan <em>REJECTED</em>.
                </p>
                <div class="row text-center g-2 mb-3">
                    <div class="col-4">
                        <div class="bg-light rounded p-2">
                            <div class="fw-bold fs-5">{{ $stats['slots']['total'] }}</div>
                            <div class="text-muted small">Total</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-success bg-opacity-10 rounded p-2">
                            <div class="fw-bold fs-5 text-success">{{ $stats['slots']['synced'] }}</div>
                            <div class="text-muted small">Sinkron</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-warning bg-opacity-10 rounded p-2">
                            <div class="fw-bold fs-5 text-warning">{{ $stats['slots']['out_of_sync'] }}</div>
                            <div class="text-muted small">Perlu Sync</div>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.sync.slots') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-arrow-repeat"></i> Sinkronisasi Slot
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Info Box --}}
    <div class="col-12">
        <div class="card border-0 bg-light">
            <div class="card-body">
                <h6 class="fw-semibold mb-2"><i class="bi bi-info-circle text-primary"></i> Kapan perlu sinkronisasi?</h6>
                <ul class="mb-0 small text-muted">
                    <li>Setelah melakukan import / migrasi data secara manual via SQL.</li>
                    <li>Setelah submission dihapus atau status berubah secara langsung di database.</li>
                    <li>Jika slot jurnal menampilkan <em>"Slot penuh"</em> padahal masih ada sisa.</li>
                </ul>
                <p class="small text-muted mb-0 mt-2">
                    Sinkronisasi total point PIC/Marketing sekarang murni otomatis lewat jadwal berkala di atas (tidak ada lagi tombol manual) — supaya data lama yang sengaja dihapus/direset tidak pernah dibangun ulang tanpa sengaja.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
