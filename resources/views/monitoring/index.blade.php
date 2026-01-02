@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Monitoring & Pemantauan Review Artikel')

@section('sidebar')
    <a href="{{ route('monitoring.index') }}" class="nav-link active">
        <i class="bi bi-bar-chart-line"></i> Monitoring
    </a>
    @if(auth()->user()->role === 'admin')
    <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Admin Dashboard
    </a>
    <a href="{{ route('admin.journals.index') }}" class="nav-link">
        <i class="bi bi-journal-text"></i> Kelola Jurnal
    </a>
    <a href="{{ route('admin.assignments.index') }}" class="nav-link">
        <i class="bi bi-clipboard-check"></i> Kelola Assignment
    </a>
    @endif
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-clipboard-check fs-2 text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Total Assignments</h6>
                        <h3 class="mb-0">{{ $stats['total_assignments'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-hourglass-split fs-2 text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Pending Review</h6>
                        <h3 class="mb-0">{{ $stats['pending_reviews'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-clock-history fs-2 text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">In Progress</h6>
                        <h3 class="mb-0">{{ $stats['in_progress_reviews'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-check-circle fs-2 text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Completed</h6>
                        <h3 class="mb-0">{{ $stats['completed_reviews'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity & Journals Table -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Daftar Review Assignments</h5>
            </div>
            <div class="card-body p-0">
                @if($assignments->total() > 0)
                <div class="alert alert-info m-3">
                    <i class="bi bi-info-circle"></i> Total <strong>{{ $assignments->total() }}</strong> assignments telah selesai direview dan disetujui. Menampilkan <strong>{{ $assignments->count() }}</strong> data terbaru.
                </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0">No</th>
                                <th class="border-0">Judul Artikel</th>
                                <th class="border-0">Bahasa</th>
                                <th class="border-0">Reviewer</th>
                                <th class="border-0">Institusi</th>
                                <th class="border-0">Hasil</th>
                                <th class="border-0">Tanggal Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                            <tr>
                                <td>{{ $loop->iteration + ($assignments->currentPage() - 1) * $assignments->perPage() }}</td>
                                <td>
                                    <div class="text-truncate" style="max-width: 300px;" title="{{ $assignment->article_title ?? 'N/A' }}">
                                        <strong>{{ Str::limit($assignment->article_title ?? 'N/A', 50) }}</strong>
                                    </div>
                                    <small class="text-muted">
                                        @if($assignment->assignedBy)
                                            <i class="bi bi-person"></i> {{ $assignment->assignedBy->name }}
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $assignment->language ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    @if($assignment->reviewer)
                                        {{ $assignment->reviewer->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($assignment->reviewer && $assignment->reviewer->affiliation)
                                        <small>{{ Str::limit($assignment->reviewer->affiliation, 30) }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($assignment->status === 'APPROVED')
                                        @if($assignment->result)
                                            <a href="{{ $assignment->result->google_drive_link }}" target="_blank" class="btn btn-sm btn-success">
                                                <i class="bi bi-file-earmark-check"></i> Lihat
                                            </a>
                                        @else
                                            <span class="badge bg-success">Approved</span>
                                        @endif
                                    @elseif($assignment->status === 'SUBMITTED')
                                        <span class="badge bg-info">Submitted</span>
                                    @elseif($assignment->status === 'ON_PROGRESS')
                                        <span class="badge bg-primary">Progress</span>
                                    @elseif($assignment->status === 'PENDING')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($assignment->status === 'REJECTED')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $assignment->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($assignment->approved_at)
                                        {{ $assignment->approved_at->format('d M Y') }}
                                    @elseif($assignment->submitted_at)
                                        {{ $assignment->submitted_at->format('d M Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada assignment yang terdaftar
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($assignments->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $assignments->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
