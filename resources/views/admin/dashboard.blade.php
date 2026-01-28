@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Dashboard Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
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

{{-- <!-- Notification Alert for Pending Redemptions -->
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
@endif --}}

<!-- Notification Alert for Pending Review Requests -->
@if($pendingReviewRequests > 0)
<div class="alert alert-primary alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-file-earmark-text-fill me-2" style="font-size: 1.5rem;"></i>
        <div>
            <strong>Permintaan Review Baru!</strong>
            <br>
            Ada <strong>{{ $pendingReviewRequests }}</strong> permintaan review dari reviewer yang menunggu persetujuan Anda.
            <a href="{{ route('admin.review-requests.index', ['status' => 'pending']) }}" class="alert-link">Lihat Permintaan</a>
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
        <div class="card stats-card info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Submissions</h6>
                        <h2 class="mb-0">{{ $totalSubmissions }}</h2>
                    </div>
                    <div class="text-info" style="font-size: 2.5rem;">
                        <i class="bi bi-file-earmark-text"></i>
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
                        <h6 class="text-muted mb-2">Perlu Review</h6>
                        <h2 class="mb-0">{{ $pendingSubmissions + $newSubmissions }}</h2>
                    </div>
                    <div class="text-warning" style="font-size: 2.5rem;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="text-primary">{{ $approvedSubmissions }}</h5>
                <small class="text-muted">Disetujui</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="text-info">{{ $inProgressSubmissions }}</h5>
                <small class="text-muted">Sedang Diproses</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="text-success">{{ $regularSubmissions }}</h5>
                <small class="text-muted">Regular</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="text-warning">{{ $fasttrackSubmissions }}</h5>
                <small class="text-muted">Fasttrack</small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning"></i> Quick Actions
            </div>
            <div class="card-body">
                <a href="{{ route('admin.journals.create') }}" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> Tambah Jurnal
                </a>
                <a href="{{ route('admin.submissions.index') }}" class="btn btn-success me-2">
                    <i class="bi bi-file-earmark-text"></i> Kelola Submissions
                </a>
                <a href="{{ route('admin.fasttrack.index') }}" class="btn btn-info me-2">
                    <i class="bi bi-lightning"></i> Fasttrack Jurnal
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-award"></i> Jurnal by Akreditasi
            </div>
            <div class="card-body">
                @foreach($journalsByAccreditation->take(5) as $accreditation)
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ $accreditation->accreditation ?: 'Tidak Terakreditasi' }}</span>
                    <span class="badge bg-primary">{{ $accreditation->count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Recent Assignments -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-spreadsheet"></i> Submissions yang Sudah Disetujui</span>
                <div>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle"></i> Total <strong>{{ $totalCompletedReviews }}</strong> submissions telah disetujui.
                    Menampilkan 20 data terbaru.
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Submit</th>
                                <th>Judul Artikel</th>
                                <th>Jurnal</th>
                                <th>Penulis</th>
                                <th class="hide-mobile">Institusi</th>
                                <th>File</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($completedReviews as $submission)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $submission->kode_submit ?? 'N/A' }}</strong></td>
                                <td>
                                    <strong>{{ Str::limit($submission->judul_artikel ?? 'N/A', 40) }}</strong>
                                </td>
                                <td><span class="badge bg-secondary">{{ $submission->journalSlot->journalMaster->name ?? 'N/A' }}</span></td>
                                <td>{{ Str::limit($submission->nama_penulis ?? 'N/A', 25) }}</td>
                                <td class="hide-mobile">
                                    <small>{{ Str::limit($submission->institusi_penulis ?? '-', 25) }}</small>
                                </td>
                                <td>
                                    @if($submission->file_pdf)
                                        <a href="{{ Storage::url($submission->file_pdf) }}" target="_blank" class="btn btn-sm btn-success">
                                            <i class="bi bi-file-earmark-check"></i> Lihat
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><small>{{ $submission->updated_at ? $submission->updated_at->format('d M Y') : '-' }}</small></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0">Belum ada artikel yang selesai direview</p>
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
                <span><i class="bi bi-clock-history"></i> Submissions Terbaru</span>
                <a href="{{ route('admin.submissions.index') }}" class="btn btn-sm btn-outline-primary">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kode Submit</th>
                                <th>Judul Artikel</th>
                                <th>Jurnal</th>
                                <th>Penulis</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSubmissions as $submission)
                            <tr>
                                <td><strong>{{ $submission->kode_submit }}</strong></td>
                                <td>
                                    <strong>{{ Str::limit($submission->judul_artikel ?? 'N/A', 50) }}</strong><br>
                                    <small class="text-muted">
                                        <span class="badge bg-secondary">{{ $submission->process_type ?? 'Regular' }}</span>
                                    </small>
                                </td>
                                <td>{{ $submission->journalSlot->journalMaster->name ?? 'N/A' }}</td>
                                <td>{{ Str::limit($submission->nama_penulis, 25) }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'new' => 'info',
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'in_progress' => 'primary'
                                        ];
                                        $color = $statusColors[$submission->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ ucfirst($submission->status) }}</span>
                                </td>
                                <td>{{ $submission->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.submissions.show', $submission) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada submission</td>
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
                            <li>Judul Artikel & Link Submit</li>
                            <li>Bahasa & Deadline</li>
                            <li>Data Reviewer & Institusi</li>
                            <li>Hasil Review (Link Google Drive)</li>
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
