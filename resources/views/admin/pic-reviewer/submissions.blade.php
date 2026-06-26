@extends('layouts.app')

@section('title', ($pageTitle ?? 'Jurnal') . ' — PIC Reviewer')
@section('page-title', $pageTitle ?? 'Jurnal')

@section('content')

@php
    $typeColors = [
        'normal'    => ['bg' => '#c084fc', 'label' => 'Normal'],
        'fasttrack' => ['bg' => '#f59e0b', 'label' => 'Fasttrack'],
        'bkd'       => ['bg' => '#38bdf8', 'label' => 'BKD'],
        'jafa'      => ['bg' => '#4ade80', 'label' => 'JAFA'],
    ];
    $tc = $typeColors[$type] ?? ['bg' => '#6b7280', 'label' => ucfirst($type)];
@endphp

{{-- Header --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
         style="width:44px;height:44px;background:{{ $tc['bg'] }}22;">
        @if($type === 'fasttrack')
            <i class="bi bi-lightning-charge-fill" style="color:{{ $tc['bg'] }};font-size:1.3rem;"></i>
        @elseif($type === 'bkd')
            <i class="bi bi-briefcase-fill" style="color:{{ $tc['bg'] }};font-size:1.3rem;"></i>
        @elseif($type === 'jafa')
            <i class="bi bi-folder-fill" style="color:{{ $tc['bg'] }};font-size:1.3rem;"></i>
        @else
            <i class="bi bi-file-earmark-richtext-fill" style="color:{{ $tc['bg'] }};font-size:1.3rem;"></i>
        @endif
    </div>
    <div>
        <h5 class="fw-bold mb-0">{{ $pageTitle }}</h5>
        <small class="text-muted">{{ $submissions->total() }} submission ditemukan</small>
    </div>
    <span class="badge ms-auto px-3 py-2" style="background:{{ $tc['bg'] }}; font-size:0.8rem;">
        {{ $tc['label'] }}
    </span>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.pic-reviewer.submissions', $type) }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Cari</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Kode, judul, penulis..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Jurnal</label>
                <input type="text" name="journal_search" class="form-control form-control-sm"
                       placeholder="Nama jurnal..."
                       value="{{ request('journal_search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach($statusOptions as $val => $label)
                        <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
                @if(request()->hasAny(['search','status','journal_search']))
                <a href="{{ route('admin.pic-reviewer.submissions', $type) }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:40px;">No</th>
                        <th>Kode</th>
                        <th>Judul Artikel</th>
                        <th>Jurnal</th>
                        <th>Penulis</th>
                        <th>Reviewer</th>
                        <th>Status</th>
                        <th>Tgl Submit</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $i => $sub)
                    <tr>
                        <td class="ps-3 text-muted small">
                            {{ ($submissions->currentPage()-1) * $submissions->perPage() + $loop->iteration }}
                        </td>
                        <td>
                            <code class="small">{{ $sub->kode_submit }}</code>
                        </td>
                        <td style="max-width:220px;">
                            <div class="small fw-semibold" style="white-space:normal;line-height:1.3;">
                                {{ Str::limit($sub->judul_artikel, 70) }}
                            </div>
                        </td>
                        <td class="small text-muted" style="max-width:150px;">
                            {{ Str::limit($sub->journalSlot?->journalMaster?->nama_jurnal ?? '—', 35) }}
                        </td>
                        <td class="small" style="max-width:120px;">
                            {{ Str::limit($sub->nama_penulis ?? '—', 30) }}
                        </td>
                        <td class="small">
                            @if($sub->petugasReviewer1)
                                <div>{{ Str::limit($sub->petugasReviewer1->name, 20) }}</div>
                            @endif
                            @if($sub->petugasReviewer2)
                                <div class="text-muted">{{ Str::limit($sub->petugasReviewer2->name, 20) }}</div>
                            @endif
                            @if(!$sub->petugasReviewer1)
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'SUBMITTED'         => 'secondary',
                                    'SUBMIT_VALIDATED'  => 'info',
                                    'EDITOR1_SUBMITTED' => 'warning',
                                    'EDITOR1_VALIDATED' => 'primary',
                                    'REVIEW_SUBMITTED'  => 'warning',
                                    'REVIEW_VALIDATED'  => 'primary',
                                    'COMPLETED'         => 'success',
                                    'REJECTED'          => 'danger',
                                ];
                                $sc = $statusColors[$sub->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $sc }}" style="font-size:0.7rem;white-space:normal;max-width:120px;">
                                {{ str_replace('_', ' ', $sub->status) }}
                            </span>
                        </td>
                        <td class="small text-muted">
                            {{ $sub->tanggal_submit ? \Carbon\Carbon::parse($sub->tanggal_submit)->format('d M Y') : '—' }}
                        </td>
                        <td>
                            <a href="{{ route('admin.submissions.show', $sub) }}"
                               class="btn btn-sm btn-outline-primary" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Tidak ada submission ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($submissions->hasPages())
    <div class="card-footer bg-transparent d-flex align-items-center justify-content-between">
        <small class="text-muted">
            Menampilkan {{ $submissions->firstItem() }}–{{ $submissions->lastItem() }}
            dari {{ $submissions->total() }} submission
        </small>
        {{ $submissions->links() }}
    </div>
    @endif
</div>

@endsection
