@extends('pic.layouts.app')

@section('title', 'Data Akreditasi')
@section('page-title', 'Data Akreditasi')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
@php
    $peringkatColors = [
        'Non SINTA' => 'dark',
        'SINTA 1' => 'primary',
        'SINTA 2' => 'success',
        'SINTA 3' => 'info',
        'SINTA 4' => 'warning',
        'SINTA 5' => 'secondary',
        'SINTA 6' => 'dark',
    ];
    $peringkatIcons = [
        'Non SINTA' => 'bookmark',
        'SINTA 1' => 'trophy-fill',
        'SINTA 2' => 'award-fill',
        'SINTA 3' => 'bookmark-star-fill',
        'SINTA 4' => 'bookmark-fill',
        'SINTA 5' => 'bookmark',
        'SINTA 6' => 'bookmark',
    ];
@endphp

<!-- Akreditasi Cards Summary -->
<div class="row g-3 mb-4">
    @foreach($accreditations as $accreditation)
    @php
        $color = $peringkatColors[$accreditation->name] ?? 'secondary';
        $icon = $peringkatIcons[$accreditation->name] ?? 'bookmark';
        $journalCount = $accreditation->journals ? $accreditation->journals->count() : 0;
    @endphp
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--bs-{{ $color }}) !important;">
            <div class="card-body text-center py-3">
                <div class="rounded-circle bg-{{ $color }} bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width: 45px; height: 45px;">
                    <i class="bi bi-{{ $icon }} text-{{ $color }} fs-5"></i>
                </div>
                <h6 class="mb-1">
                    <span class="badge bg-{{ $color }}">{{ $accreditation->name }}</span>
                </h6>
                <p class="text-muted mb-0 small">
                    <i class="bi bi-journal-text"></i> {{ $journalCount }} Jurnal
                </p>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Jurnal per Akreditasi -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-journal-bookmark text-primary"></i> Daftar Jurnal per Akreditasi</h6>
    </div>
    <div class="card-body">
        @php $hasJournals = false; @endphp
        
        @foreach($accreditations as $accreditation)
            @php
                $color = $peringkatColors[$accreditation->name] ?? 'secondary';
                $journals = $accreditation->journals ?? collect();
                $cardId = 'acc-' . $loop->index;
            @endphp
            
            @if($journals->count() > 0)
                @php $hasJournals = true; @endphp
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between bg-{{ $color }} bg-opacity-10 rounded px-3 py-2 mb-2">
                        <span class="fw-semibold">
                            <span class="badge bg-{{ $color }} me-2">{{ $accreditation->name }}</span>
                        </span>
                        <span class="badge bg-{{ $color }}">{{ $journals->count() }} Jurnal</span>
                    </div>
                    
                    <div class="ps-3">
                        @foreach($journals->take(5) as $journal)
                            <div class="py-1 {{ !$loop->last || $journals->count() > 5 ? 'border-bottom' : '' }}">
                                <i class="bi bi-journal text-{{ $color }} me-2"></i>
                                <span class="small">{{ $journal->nama_jurnal }}</span>
                            </div>
                        @endforeach
                        
                        @if($journals->count() > 5)
                            <div id="{{ $cardId }}-more" style="display: none;">
                                @foreach($journals->skip(5) as $journal)
                                    <div class="py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <i class="bi bi-journal text-{{ $color }} me-2"></i>
                                        <span class="small">{{ $journal->nama_jurnal }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-center py-2">
                                <button type="button" class="btn btn-sm btn-link text-{{ $color }} toggle-more" 
                                        data-target="{{ $cardId }}-more"
                                        data-count="{{ $journals->count() - 5 }}">
                                    <i class="bi bi-chevron-down"></i> 
                                    <span class="btn-text">Tampilkan {{ $journals->count() - 5 }} lainnya</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
        
        @if(!$hasJournals)
            <div class="text-center py-5">
                <i class="bi bi-journal-x text-muted fs-1"></i>
                <p class="text-muted mt-2">Belum ada jurnal yang terdaftar</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-more').forEach(function(btn) {
        var originalText = 'Tampilkan ' + btn.getAttribute('data-count') + ' lainnya';
        
        btn.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            var target = document.getElementById(targetId);
            var icon = this.querySelector('i');
            var text = this.querySelector('.btn-text');
            
            if (target.style.display === 'none') {
                target.style.display = 'block';
                icon.className = 'bi bi-chevron-up';
                text.textContent = 'Sembunyikan';
            } else {
                target.style.display = 'none';
                icon.className = 'bi bi-chevron-down';
                text.textContent = originalText;
            }
        });
    });
});
</script>
@endsection
