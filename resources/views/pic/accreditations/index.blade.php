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
                    $cardId = 'acc-' . Str::slug($accreditation->name);
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
                            {{-- Jurnal pertama (selalu tampil) --}}
                            @foreach($journals->take(5) as $journal)
                                <div class="d-flex align-items-center py-1 border-bottom">
                                    <i class="bi bi-journal text-{{ $color }} me-2"></i>
                                    <span class="small" title="{{ $journal->nama_jurnal }}">
                                        {{ $journal->nama_jurnal }}
                                    </span>
                                </div>
                            @endforeach
                            
                            {{-- Jurnal tersembunyi --}}
                            @if($journals->count() > 5)
                                <div id="{{ $cardId }}-more" style="display: none;">
                                    @foreach($journals->skip(5) as $journal)
                                        <div class="d-flex align-items-center py-1 border-bottom">
                                            <i class="bi bi-journal text-{{ $color }} me-2"></i>
                                            <span class="small" title="{{ $journal->nama_jurnal }}">
                                                {{ $journal->nama_jurnal }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="text-center py-2">
                                    <button type="button" class="btn btn-sm btn-outline-{{ $color }} toggle-more" 
                                            data-target="{{ $cardId }}-more">
                                        <i class="bi bi-chevron-down"></i> 
                                        <span class="btn-text">Tampilkan {{ $journals->count() - 5 }} lainnya</span>
                                    </button>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-more').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const target = document.getElementById(targetId);
            const icon = this.querySelector('i');
            const text = this.querySelector('.btn-text');
            
            if (target.style.display === 'none') {
                target.style.display = 'block';
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
                text.textContent = 'Sembunyikan';
            } else {
                target.style.display = 'none';
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
                text.textContent = this.getAttribute('data-original-text') || 'Tampilkan lainnya';
            }
        });
        
        // Store original text
        const text = btn.querySelector('.btn-text');
        btn.setAttribute('data-original-text', text.textContent);
    });
});
</script>
@endsection
