@extends('layouts.app')

@section('title', 'Log Pengiriman Email')
@section('page-title', 'Log Pengiriman Email')

@section('sidebar')@include('admin.partials.sidebar')@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="fw-bold mb-0"><i class="bi bi-envelope-check me-2 text-primary"></i>Laporan Pengiriman Email</h5>
            <small class="text-muted">Riwayat semua pengiriman email dari sistem template</small>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10" style="width:48px;height:48px;">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold text-success lh-1">{{ $summary['sent'] ?? 0 }}</div>
                        <div class="text-muted small">Berhasil Terkirim</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10" style="width:48px;height:48px;">
                        <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold text-danger lh-1">{{ $summary['failed'] ?? 0 }}</div>
                        <div class="text-muted small">Gagal</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10" style="width:48px;height:48px;">
                        <i class="bi bi-clock-fill text-warning fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold text-warning lh-1">{{ $summary['pending'] ?? 0 }}</div>
                        <div class="text-muted small">Pending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Email / Nama / Subjek..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="sent"    {{ request('status')=='sent'    ?'selected':'' }}>Terkirim</option>
                        <option value="failed"  {{ request('status')=='failed'  ?'selected':'' }}>Gagal</option>
                        <option value="pending" {{ request('status')=='pending' ?'selected':'' }}>Pending</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="trigger_key" class="form-select form-select-sm">
                        <option value="">Semua Trigger</option>
                        @foreach($triggerLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('trigger_key')==$key ?'selected':'' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" name="start_date" class="form-control form-control-sm"
                           value="{{ request('start_date') }}" placeholder="Dari Tanggal">
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" name="end_date" class="form-control form-control-sm"
                           value="{{ request('end_date') }}" placeholder="Sampai Tanggal">
                </div>
                <div class="col-12 col-md-auto d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('admin.email-logs.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:150px">Waktu</th>
                            <th style="width:130px">Status</th>
                            <th>Trigger</th>
                            <th>Penerima</th>
                            <th>Subjek</th>
                            <th>Submission</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="ps-3">
                                <div class="small text-nowrap">{{ $log->created_at->format('d/m/Y') }}</div>
                                <div class="small text-muted">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td>
                                @if($log->status === 'sent')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i>Terkirim
                                    </span>
                                @elseif($log->status === 'failed')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        <i class="bi bi-x-circle me-1"></i>Gagal
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                        <i class="bi bi-clock me-1"></i>Pending
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="small text-muted">{{ $triggerLabels[$log->trigger_key] ?? $log->trigger_key }}</span>
                            </td>
                            <td>
                                <div class="small fw-semibold">{{ $log->recipient_name ?? '-' }}</div>
                                <div class="small text-muted">{{ $log->recipient_email }}</div>
                            </td>
                            <td>
                                <div class="small text-truncate" style="max-width:260px" title="{{ $log->subject }}">
                                    {{ $log->subject ?? '-' }}
                                </div>
                            </td>
                            <td>
                                @if($log->submission)
                                    <a href="{{ route('admin.submissions.show', $log->submission_id) }}"
                                       class="small text-decoration-none text-primary text-nowrap">
                                        {{ $log->submission->kode_submit }}
                                    </a>
                                    <div class="small text-muted text-truncate" style="max-width:120px">
                                        {{ $log->submission->judul_artikel }}
                                    </div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @if($log->status === 'failed' && $log->error_message)
                                    <button type="button" class="btn btn-link btn-sm p-0 text-danger"
                                            data-bs-toggle="popover"
                                            data-bs-trigger="click"
                                            data-bs-placement="left"
                                            data-bs-content="{{ e($log->error_message) }}"
                                            title="Pesan Error">
                                        <i class="bi bi-info-circle-fill"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Belum ada log email.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer bg-white py-2 px-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <small class="text-muted">
                    Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ $logs->total() }} log
                </small>
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
        new bootstrap.Popover(el, { html: false });
    });
});
</script>
@endpush
