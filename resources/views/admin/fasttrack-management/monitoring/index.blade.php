@extends('layouts.app')

@section('title', 'Monitoring Proses FS - ' . $appSettings['app_name'])
@section('page-title', 'Monitoring Proses FS')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<style>
/* Sticky Table Styles for Monitoring */
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
</style>

<div class="row mb-3">
    <!-- Statistics Cards -->
    <div class="col-md-2 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4>{{ $stats['total'] }}</h4>
                <small>Total Fasttrack</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4>{{ $stats['published'] }}</h4>
                <small>Published</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4>{{ $stats['in_process'] }}</h4>
                <small>In Process</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h4>{{ $stats['rejected'] }}</h4>
                <small>Rejected</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4>{{ $pendingCount }}</h4>
                <small>Pending Review</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart"></i> Monitoring Proses Fasttrack (Filter Tanggal)</span>
                <a href="{{ route('admin.fasttrack-management.submissions.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Filter -->
                <form action="{{ route('admin.fasttrack-management.monitoring.index') }}" method="GET" class="mb-4">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label for="tanggal_dari" class="form-label small mb-1">Tanggal Dari</label>
                            <input type="date" class="form-control form-control-sm" id="tanggal_dari" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="tanggal_sampai" class="form-label small mb-1">Tanggal Sampai</label>
                            <input type="date" class="form-control form-control-sm" id="tanggal_sampai" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="journal_master_id" class="form-label small mb-1">Jurnal</label>
                            <select class="form-select form-select-sm" id="journal_master_id" name="journal_master_id">
                                <option value="">-- Semua Jurnal --</option>
                                @foreach($journals as $journal)
                                    <option value="{{ $journal->id }}" {{ request('journal_master_id') == $journal->id ? 'selected' : '' }}>
                                        {{ $journal->nama_jurnal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label small mb-1">Status</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="">-- Semua Status --</option>
                                @foreach($statusOptions as $key => $value)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="btn-group btn-group-sm w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.fasttrack-management.monitoring.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="alert alert-warning">
                    <i class="bi bi-lightning-charge"></i> <strong>Fasttrack Monitoring:</strong> Proses fasttrack adalah artikel yang sudah published langsung, tanpa workflow review normal.
                </div>

                <!-- Monitoring Table -->
                <div class="monitoring-scroll-wrapper">
                    <table class="table table-sm table-hover mb-0" style="min-width: 1200px;">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th style="min-width: 120px;">Kode Submit</th>
                                <th style="min-width: 250px;">Artikel</th>
                                <th style="min-width: 200px;">Jurnal/Slot</th>
                                <th style="min-width: 100px;">Tanggal Submit</th>
                                <th style="min-width: 120px;">Status</th>
                                <th style="min-width: 150px;">Marketing</th>
                                <th style="min-width: 150px;">PIC Submit</th>
                                <th style="min-width: 200px;">Link Publish</th>
                                <th style="min-width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $s)
                            <tr>
                                <td>
                                    <code class="text-primary">{{ $s->kode_submit }}</code>
                                    <br><small class="badge bg-warning">Fasttrack</small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ Str::limit($s->judul_artikel, 35) }}</div>
                                    <small class="text-muted">{{ $s->nama_penulis }}</small>
                                </td>
                                <td>
                                    @if($s->journalSlot)
                                        <div>
                                            <strong>{{ $s->journalSlot->journalMaster->nama_jurnal }}</strong>
                                            <br><small class="text-muted">
                                                Vol.{{ $s->journalSlot->volume }} No.{{ $s->journalSlot->nomor }} - {{ $s->journalSlot->tahun }}
                                            </small>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $s->tanggal_submit ? $s->tanggal_submit->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($s->status) {
                                            'PUBLISHED' => 'bg-success',
                                            'REJECTED' => 'bg-danger',
                                            default => 'bg-warning'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $s->status }}</span>
                                </td>
                                <td>
                                    @if($s->marketing)
                                        <span class="badge bg-info">{{ $s->marketing->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($s->petugasSubmit)
                                        <span class="badge bg-primary">{{ $s->petugasSubmit->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($s->link_publish)
                                        <a href="{{ $s->link_publish }}" target="_blank" class="btn btn-outline-success btn-sm">
                                            <i class="bi bi-link-45deg"></i> View
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.submissions.show', $s) }}" class="btn btn-outline-info" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.submissions.edit', $s) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    Tidak ada data fasttrack yang ditemukan
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
@endsection