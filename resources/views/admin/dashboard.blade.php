@extends('layouts.app')

@section('title', 'Admin Dashboard - REVANA')
@section('page-title', 'Dashboard Admin')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="nav-link active">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.journals.index') }}" class="nav-link">
        <i class="bi bi-journal-text"></i> Jurnal
    </a>
    <a href="{{ route('admin.assignments.index') }}" class="nav-link">
        <i class="bi bi-clipboard-check"></i> Review Assignments
    </a>
    <a href="{{ route('admin.reviewers.index') }}" class="nav-link">
        <i class="bi bi-people"></i> Reviewers
    </a>
    <a href="{{ route('admin.leaderboard.index') }}" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="{{ route('admin.redemptions.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
    <a href="{{ route('admin.points.index') }}" class="nav-link">
        <i class="bi bi-coin"></i> Point Management
    </a>
    <a href="{{ route('admin.rewards.index') }}" class="nav-link">
        <i class="bi bi-trophy"></i> Reward Management
    </a>
@endsection

@section('content')
<!-- Notification Alert for Submitted Reviews -->
@if($submittedReviews > 0)
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-bell-fill me-2" style="font-size: 1.5rem;"></i>
        <div>
            <strong>Review Selesai Dikerjakan!</strong>
            <br>
            Ada <strong>{{ $submittedReviews }}</strong> review yang telah diselesaikan reviewer dan menunggu validasi Anda.
            <a href="{{ route('admin.assignments.index') }}" class="alert-link">Lihat Review</a>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Notification Alert for Pending Redemptions -->
@if($pendingRedemptions > 0)
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-gift-fill me-2" style="font-size: 1.5rem;"></i>
        <div>
            <strong>Penukaran Reward Menunggu!</strong>
            <br>
            Ada <strong>{{ $pendingRedemptions }}</strong> penukaran reward yang menunggu persetujuan Anda.
            <a href="{{ route('admin.redemptions.index') }}" class="alert-link">Lihat Redemptions</a>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Stats Cards -->
<div class="row">
    <div class="col-md-3">
        <div class="card stats-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Jurnal</h6>
                        <h2 class="mb-0">{{ $totalJournals }}</h2>
                    </div>
                    <div class="text-primary" style="font-size: 2.5rem;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Reviewers</h6>
                        <h2 class="mb-0">{{ $totalReviewers }}</h2>
                    </div>
                    <div class="text-success" style="font-size: 2.5rem;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Review Pending</h6>
                        <h2 class="mb-0">{{ $pendingReviews }}</h2>
                    </div>
                    <div class="text-warning" style="font-size: 2.5rem;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Perlu Validasi</h6>
                        <h2 class="mb-0">{{ $submittedReviews }}</h2>
                    </div>
                    <div class="text-danger" style="font-size: 2.5rem;">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning"></i> Quick Actions
            </div>
            <div class="card-body">
                <a href="{{ route('admin.journals.create') }}" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> Tambah Jurnal
                </a>
                <a href="{{ route('admin.assignments.create') }}" class="btn btn-success me-2">
                    <i class="bi bi-person-plus"></i> Tugaskan Reviewer
                </a>
                <a href="{{ route('admin.redemptions.index') }}" class="btn btn-warning me-2">
                    <i class="bi bi-gift"></i> Kelola Reward
                    @if($pendingRedemptions > 0)
                    <span class="badge bg-danger">{{ $pendingRedemptions }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Assignments -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-spreadsheet"></i> Laporan Jurnal Selesai Direview</span>
                <div>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle"></i> Total <strong>{{ $totalCompletedReviews }}</strong> jurnal telah selesai direview dan disetujui.
                    Menampilkan 20 data terbaru.
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Judul Jurnal</th>
                                <th>Akreditasi</th>
                                <th>Points</th>
                                <th class="hide-mobile">Terbitan</th>
                                <th>Reviewer</th>
                                <th class="hide-mobile">Institusi</th>
                                <th>Hasil</th>
                                <th>Tanggal Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($completedReviews as $review)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ Str::limit($review->journal->title, 40) }}</strong>
                                </td>
                                <td><span class="badge bg-info">{{ $review->journal->accreditation }}</span></td>
                                <td><span class="badge bg-success">{{ $review->journal->points }} pts</span></td>
                                <td class="hide-mobile">
                                    <small>{{ $review->journal->publisher ?? '-' }}</small>
                                </td>
                                <td>{{ Str::limit($review->reviewer->name, 25) }}</td>
                                <td class="hide-mobile">
                                    <small>{{ Str::limit($review->reviewer->institution ?? '-', 25) }}</small>
                                </td>
                                <td>
                                    @if($review->reviewResult)
                                        <span class="badge bg-primary">{{ $review->reviewResult->recommendation }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><small>{{ $review->approved_at ? $review->approved_at->format('d M Y') : '-' }}</small></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0">Belum ada jurnal yang selesai direview</p>
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

<!-- Review Assignment History -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history"></i> Review Terbaru</span>
                <a href="{{ route('admin.assignments.index') }}" class="btn btn-sm btn-outline-primary">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Jurnal</th>
                                <th>Reviewer</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAssignments as $assignment)
                            <tr>
                                <td>
                                    <strong>{{ Str::limit($assignment->journal->title, 50) }}</strong><br>
                                    <small class="text-muted">{{ $assignment->journal->accreditation }} ({{ $assignment->journal->points }} pts)</small>
                                </td>
                                <td>{{ $assignment->reviewer->name }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'PENDING' => 'warning',
                                            'ACCEPTED' => 'info',
                                            'REJECTED' => 'danger',
                                            'ON_PROGRESS' => 'primary',
                                            'SUBMITTED' => 'success',
                                            'APPROVED' => 'success',
                                            'REVISION' => 'secondary'
                                        ];
                                        $color = $statusColors[$assignment->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $assignment->status }}</span>
                                </td>
                                <td>{{ $assignment->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada assignment</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="bi bi-file-earmark-excel"></i> Export Laporan ke Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.export.completed.reviews') }}" method="GET">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Export semua jurnal yang telah selesai direview dan disetujui. Anda bisa filter berdasarkan tanggal atau export semua data.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai (Opsional)</label>
                        <input type="date" class="form-control" name="start_date">
                        <small class="text-muted">Kosongkan untuk export semua data</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Akhir (Opsional)</label>
                        <input type="date" class="form-control" name="end_date">
                        <small class="text-muted">Kosongkan untuk export semua data</small>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <strong>Data yang akan diexport:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Judul Jurnal & Link</li>
                            <li>Akreditasi & Points</li>
                            <li>Terbitan, Marketing, PIC</li>
                            <li>Data Reviewer</li>
                            <li>Hasil & Komentar Review</li>
                            <li>Tanggal-tanggal penting</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-download"></i> Download Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
