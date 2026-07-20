@extends('layouts.app')

@section('title', 'Permintaan Perpanjangan Waktu')
@section('page-title', 'Permintaan Perpanjangan Waktu')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-hourglass-split"></i> Permintaan Perpanjangan Waktu Review
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Artikel</th>
                        <th>Reviewer</th>
                        <th>Deadline Saat Ini</th>
                        <th>Alasan</th>
                        <th>Diminta Sampai</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $r)
                    <tr>
                        <td style="max-width:220px;">
                            <a href="{{ route('admin.assignments.show', $r->reviewAssignment) }}" class="text-decoration-none">
                                {{ \Illuminate\Support\Str::limit($r->reviewAssignment->article_title, 45) }}
                            </a>
                            <div class="text-muted small">{{ $r->reviewAssignment->article_number }}</div>
                        </td>
                        <td>{{ $r->reviewer->name ?? '—' }}</td>
                        <td>
                            @if($r->reviewAssignment->deadline)
                                <span class="badge bg-warning text-dark">{{ $r->reviewAssignment->deadline->format('d M Y') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td style="max-width:260px;">
                            <small>{{ \Illuminate\Support\Str::limit($r->reason, 80) }}</small>
                        </td>
                        <td>
                            {{ $r->requested_deadline ? $r->requested_deadline->format('d M Y') : '—' }}
                        </td>
                        <td class="text-center">
                            @if($r->status === 'PENDING')
                                <span class="badge bg-warning text-dark">Menunggu</span>
                            @elseif($r->status === 'APPROVED')
                                <span class="badge bg-success">Disetujui</span>
                            @else
                                <span class="badge bg-danger">Ditolak</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($r->status === 'PENDING')
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $r->id }}">
                                    <i class="bi bi-check-circle"></i> Setujui
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $r->id }}">
                                    <i class="bi bi-x-circle"></i> Tolak
                                </button>
                            @else
                                <small class="text-muted">
                                    oleh {{ $r->respondedBy->name ?? '—' }}<br>
                                    {{ $r->responded_at?->format('d M Y H:i') }}
                                </small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada permintaan perpanjangan waktu.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $requests->links() }}
    </div>
</div>

{{-- Modals: Setujui / Tolak --}}
@foreach($requests as $r)
@if($r->status === 'PENDING')
<div class="modal fade" id="approveModal{{ $r->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.extension-requests.approve', $r) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Setujui Perpanjangan Waktu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">
                        Reviewer: <strong>{{ $r->reviewer->name ?? '—' }}</strong><br>
                        Alasan: {{ $r->reason }}
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Deadline Baru <span class="text-danger">*</span></label>
                        <input type="date" name="new_deadline" class="form-control"
                               value="{{ $r->requested_deadline?->toDateString() ?? $r->reviewAssignment->deadline?->addDays(7)?->toDateString() }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan (opsional)</label>
                        <textarea name="admin_note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Setujui & Perpanjang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal{{ $r->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.extension-requests.reject', $r) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Permintaan Perpanjangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">
                        Reviewer: <strong>{{ $r->reviewer->name ?? '—' }}</strong><br>
                        Alasan: {{ $r->reason }}
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan (opsional)</label>
                        <textarea name="admin_note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

@endsection
