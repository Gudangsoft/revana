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
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1 fw-bold"><i class="bi bi-arrow-repeat text-primary"></i> Sinkronisasi Data</h4>
                <p class="text-muted mb-0">Pastikan semua counter data sesuai dengan data aktual di database.</p>
            </div>
            <form method="POST" action="{{ route('admin.sync.all') }}"
                  onsubmit="return confirm('Sinkronisasi SEMUA data sekarang?\n\nProses ini akan memperbarui:\n• Slot terpakai jurnal\n• Total point marketing\n• Total point PIC')">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-repeat"></i> Sinkronisasi Semua Sekarang
                </button>
            </form>
        </div>
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
                Klik tombol sinkronisasi pada masing-masing modul atau gunakan <strong>Sinkronisasi Semua</strong> di atas.
            </div>
        </div>
        @endif
    </div>

    {{-- ================================================ --}}
    {{-- SLOT JURNAL --}}
    {{-- ================================================ --}}
    <div class="col-lg-4">
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

    {{-- ================================================ --}}
    {{-- POINT MARKETING --}}
    {{-- ================================================ --}}
    <div class="col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                <i class="bi bi-megaphone text-info fs-5"></i>
                <span class="fw-semibold">Point Marketing</span>
                @if($stats['marketing']['out_of_sync'] > 0)
                    <span class="badge bg-warning text-dark ms-auto">{{ $stats['marketing']['out_of_sync'] }} tidak sinkron</span>
                @else
                    <span class="badge bg-success ms-auto">Sinkron</span>
                @endif
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <code>total_points</code> marketing dihitung ulang berdasarkan jumlah submission mereka yang berstatus bukan <em>REJECTED</em>.
                </p>
                <div class="row text-center g-2 mb-3">
                    <div class="col-4">
                        <div class="bg-light rounded p-2">
                            <div class="fw-bold fs-5">{{ $stats['marketing']['total'] }}</div>
                            <div class="text-muted small">Total</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-success bg-opacity-10 rounded p-2">
                            <div class="fw-bold fs-5 text-success">{{ $stats['marketing']['synced'] }}</div>
                            <div class="text-muted small">Sinkron</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-warning bg-opacity-10 rounded p-2">
                            <div class="fw-bold fs-5 text-warning">{{ $stats['marketing']['out_of_sync'] }}</div>
                            <div class="text-muted small">Perlu Sync</div>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.sync.marketing-points') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-info w-100">
                        <i class="bi bi-arrow-repeat"></i> Sinkronisasi Point Marketing
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ================================================ --}}
    {{-- POINT PIC --}}
    {{-- ================================================ --}}
    <div class="col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                <i class="bi bi-person-badge text-success fs-5"></i>
                <span class="fw-semibold">Point PIC</span>
                @if($stats['pic']['out_of_sync'] > 0)
                    <span class="badge bg-warning text-dark ms-auto">{{ $stats['pic']['out_of_sync'] }} tidak sinkron</span>
                @else
                    <span class="badge bg-success ms-auto">Sinkron</span>
                @endif
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <code>total_points</code> PIC dihitung ulang berdasarkan jumlah total poin dari riwayat tugas (<em>PicPointHistory</em>) mereka.
                </p>
                <div class="row text-center g-2 mb-3">
                    <div class="col-4">
                        <div class="bg-light rounded p-2">
                            <div class="fw-bold fs-5">{{ $stats['pic']['total'] }}</div>
                            <div class="text-muted small">Total</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-success bg-opacity-10 rounded p-2">
                            <div class="fw-bold fs-5 text-success">{{ $stats['pic']['synced'] }}</div>
                            <div class="text-muted small">Sinkron</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-warning bg-opacity-10 rounded p-2">
                            <div class="fw-bold fs-5 text-warning">{{ $stats['pic']['out_of_sync'] }}</div>
                            <div class="text-muted small">Perlu Sync</div>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.sync.pic-points') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-success w-100">
                        <i class="bi bi-arrow-repeat"></i> Sinkronisasi Point PIC
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
                    <li>Jika total point marketing/PIC tidak sesuai dengan riwayat point mereka.</li>
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection
