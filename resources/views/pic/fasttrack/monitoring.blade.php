@extends('pic.layouts.app')

@section('title', 'Monitoring Fasttrack')
@section('page-title', '')
@section('sidebar-class', 'auto-collapse')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<style>
/* Scrollbar Styles for Table */
.monitoring-scroll-wrapper {
    overflow-x: auto;
    overflow-y: auto;
    max-height: calc(100vh - 400px);
    min-height: 300px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    scrollbar-width: thin;
    scrollbar-color: #6c757d #dee2e6;
}

.monitoring-scroll-wrapper::-webkit-scrollbar {
    height: 12px;
    width: 12px;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 6px;
    border: 2px solid #f1f1f1;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-corner {
    background: #dee2e6;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-bar-chart text-warning"></i> Monitoring Fasttrack
    </h4>
    <a href="{{ route('pic.fasttrack.create') }}" class="btn btn-warning">
        <i class="bi bi-plus-circle"></i> Input Fasttrack
    </a>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center py-2">
                <h3 class="mb-0">{{ $totalFasttrack }}</h3>
                <small>Total Fasttrack</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center py-2">
                <h3 class="mb-0">{{ $thisMonthFasttrack }}</h3>
                <small>Bulan Ini</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form action="{{ route('pic.fasttrack.monitoring') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Cari</label>
                <input type="text" class="form-control form-control-sm" name="search" placeholder="Cari kode/judul/penulis..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Jurnal</label>
                <select class="form-select form-select-sm" name="journal_master_id">
                    <option value="">-- Semua --</option>
                    @foreach($journals as $journal)
                        <option value="{{ $journal->id }}" {{ request('journal_master_id') == $journal->id ? 'selected' : '' }}>
                            {{ Str::limit($journal->nama_jurnal, 25) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Dari Tanggal</label>
                <input type="date" class="form-control form-control-sm" name="from_date" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Sampai Tanggal</label>
                <input type="date" class="form-control form-control-sm" name="to_date" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'journal_master_id', 'from_date', 'to_date']))
                    <a href="{{ route('pic.fasttrack.monitoring') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-body p-2">
        <div class="monitoring-scroll-wrapper">
            <table class="table table-hover table-bordered mb-0" style="font-size: 0.85rem;">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Kode Submit</th>
                        <th>Jurnal</th>
                        <th>Judul Artikel</th>
                        <th>Penulis</th>
                        <th>Marketing</th>
                        <th>Link Publish</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $submission)
                    <tr>
                        <td>{{ $loop->iteration + ($submissions->currentPage() - 1) * $submissions->perPage() }}</td>
                        <td>
                            <code class="text-warning">{{ $submission->kode_submit }}</code>
                            <br><span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge"></i> Fasttrack</span>
                        </td>
                        <td>
                            @if($submission->journalSlot && $submission->journalSlot->journalMaster)
                                {{ Str::limit($submission->journalSlot->journalMaster->nama_jurnal, 20) }}
                                <br><small class="text-muted">
                                    {{ $submission->journalSlot->journalMaster->accreditation ?? '-' }}
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ Str::limit($submission->judul_artikel, 25) }}</td>
                        <td>{{ Str::limit($submission->nama_penulis, 15) }}</td>
                        <td>
                            @if($submission->marketing)
                                {{ $submission->marketing->name }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($submission->link_publish)
                                <a href="{{ $submission->link_publish }}" target="_blank" class="btn btn-sm btn-success">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $submission->tanggal_submit ? $submission->tanggal_submit->format('d/m/Y') : '-' }}</td>
                        <td>
                            <a href="{{ route('pic.fasttrack.show', $submission) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-inbox display-6"></i>
                            <p class="mt-2">Belum ada data fasttrack</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $submissions->withQueryString()->links() }}
</div>
@endsection
