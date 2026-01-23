@extends('pic.layouts.app')

@section('title', 'Data Akreditasi')
@section('page-title', 'Data Akreditasi')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
@php
    $peringkatColors = [
        'SINTA 1' => ['bg' => 'primary', 'icon' => 'trophy-fill'],
        'SINTA 2' => ['bg' => 'success', 'icon' => 'award-fill'],
        'SINTA 3' => ['bg' => 'info', 'icon' => 'bookmark-star-fill'],
        'SINTA 4' => ['bg' => 'warning', 'icon' => 'bookmark-fill'],
        'SINTA 5' => ['bg' => 'secondary', 'icon' => 'bookmark'],
        'SINTA 6' => ['bg' => 'dark', 'icon' => 'bookmark'],
    ];
@endphp

<!-- Akreditasi Cards -->
<div class="row g-3 mb-4">
    @foreach($accreditations as $accreditation)
    @php
        $color = $peringkatColors[$accreditation->name]['bg'] ?? 'secondary';
        $icon = $peringkatColors[$accreditation->name]['icon'] ?? 'bookmark';
        $journalCount = $accreditation->journals ? $accreditation->journals->count() : 0;
    @endphp
    <div class="col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--bs-{{ $color }}) !important;">
            <div class="card-body d-flex align-items-center py-3">
                <div class="rounded-circle bg-{{ $color }} bg-opacity-10 p-3 me-3">
                    <i class="bi bi-{{ $icon }} text-{{ $color }} fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="mb-1">
                        <span class="badge bg-{{ $color }}">{{ $accreditation->name }}</span>
                    </h5>
                    <p class="text-muted mb-0 small">
                        <i class="bi bi-journal-text"></i> {{ $journalCount }} Jurnal
                        @if($accreditation->is_active)
                            <span class="badge bg-success-subtle text-success ms-2">Aktif</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger ms-2">Non-Aktif</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Jurnal per Akreditasi -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0"><i class="bi bi-journal-bookmark text-primary"></i> Jurnal Berdasarkan Akreditasi</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($accreditations as $accreditation)
                @php
                    $color = $peringkatColors[$accreditation->name]['bg'] ?? 'secondary';
                    $journals = $accreditation->journals ?? collect();
                @endphp
                @if($journals->count() > 0)
                <div class="col-lg-6">
                    <div class="card border-{{ $color }} h-100">
                        <div class="card-header bg-{{ $color }} bg-opacity-10 py-2 d-flex justify-content-between align-items-center">
                            <span class="fw-semibold text-{{ $color }}">
                                <i class="bi bi-award"></i> {{ $accreditation->name }}
                            </span>
                            <span class="badge bg-{{ $color }}">{{ $journals->count() }}</span>
                        </div>
                        <div class="card-body py-2 px-3">
                            @foreach($journals->take(5) as $journal)
                                <div class="d-flex align-items-center py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <i class="bi bi-journal text-{{ $color }} me-2"></i>
                                    <span class="small text-truncate" title="{{ $journal->nama_jurnal }}">
                                        {{ $journal->nama_jurnal }}
                                    </span>
                                </div>
                            @endforeach
                            @if($journals->count() > 5)
                                <div class="text-center py-2">
                                    <span class="badge bg-light text-muted">
                                        +{{ $journals->count() - 5 }} jurnal lainnya
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
            
            @if($accreditations->every(fn($a) => !$a->journals || $a->journals->count() == 0))
            <div class="col-12 text-center py-5">
                <i class="bi bi-journal-x text-muted fs-1"></i>
                <p class="text-muted mt-2">Belum ada jurnal yang terdaftar</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
