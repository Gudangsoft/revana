@extends('layouts.app')

@section('title', ' - Audit Log')
@section('page-title', 'Audit Log Aktivitas')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Jenis Aksi</label>
                <select name="event" class="form-select form-select-sm">
                    <option value="">Semua Aksi</option>
                    @foreach($events as $ev)
                        <option value="{{ $ev }}" {{ request('event') == $ev ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $ev)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Nama User</label>
                <input type="text" name="causer" class="form-control form-control-sm"
                       value="{{ request('causer') }}" placeholder="Cari nama...">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <span class="fw-semibold">
            <i class="bi bi-shield-check text-primary"></i> Audit Log
        </span>
        <small class="text-muted">{{ $logs->total() }} entri</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0" style="font-size:.84rem;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                        <th class="px-3 py-2 text-muted fw-semibold" style="width:160px;">WAKTU</th>
                        <th class="py-2 text-muted fw-semibold" style="width:140px;">USER</th>
                        <th class="py-2 text-muted fw-semibold" style="width:160px;">AKSI</th>
                        <th class="py-2 text-muted fw-semibold" style="width:200px;">SUBJEK</th>
                        <th class="py-2 text-muted fw-semibold">DETAIL</th>
                        <th class="py-2 text-muted fw-semibold" style="width:110px;">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td class="px-3">
                            <span class="text-muted" style="font-size:.78rem;">
                                {{ $log->created_at->format('d M Y') }}<br>
                                <span style="color:#94a3b8;">{{ $log->created_at->format('H:i:s') }}</span>
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $log->causer_name }}</div>
                            <small class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.68rem;">
                                {{ $log->causer_guard }}
                            </small>
                        </td>
                        <td>
                            <span class="badge {{ $log->event_badge_class }}" style="font-size:.74rem;">
                                {{ ucfirst(str_replace('_', ' ', $log->event)) }}
                            </span>
                        </td>
                        <td>
                            <small class="text-secondary">
                                {{ class_basename($log->subject_type) }}
                                @if($log->subject_id)
                                    <span class="text-muted">#{{ $log->subject_id }}</span>
                                @endif
                            </small>
                        </td>
                        <td>
                            @if($log->new_values)
                                @foreach($log->new_values as $k => $v)
                                <span class="badge bg-light text-dark border me-1 mb-1" style="font-size:.7rem;font-weight:500;">
                                    {{ $k }}: <span class="text-primary">{{ Str::limit((string)$v, 40) }}</span>
                                </span>
                                @endforeach
                            @elseif($log->old_values)
                                @foreach($log->old_values as $k => $v)
                                <span class="badge bg-light text-dark border me-1 mb-1" style="font-size:.7rem;font-weight:500;">
                                    {{ $k }}: <span class="text-danger">{{ Str::limit((string)$v, 40) }}</span>
                                </span>
                                @endforeach
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted font-monospace" style="font-size:.75rem;">{{ $log->ip_address }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-shield" style="font-size:2.5rem;opacity:.3;"></i>
                            <p class="mb-0 mt-2 small">Belum ada log aktivitas</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white border-top py-2 px-3">
        {{ $logs->links() }}
    </div>
    @endif
</div>

@endsection
