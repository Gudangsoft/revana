@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Detail Tugas Review')

@section('sidebar')
    <a href="{{ route('reviewer.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('reviewer.tasks.index') }}" class="nav-link active">
        <i class="bi bi-clipboard-check"></i> My Tasks
    </a>
    <a href="{{ route('reviewer.rewards.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Rewards
    </a>
    <a href="{{ route('reviewer.leaderboard.index') }}" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="{{ route('reviewer.profile.edit') }}" class="nav-link">
        <i class="bi bi-person-circle"></i> My Profile
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-text"></i> Informasi Artikel
            </div>
            <div class="card-body">
                <h4>{{ $assignment->article_title ?? 'N/A' }}</h4>
                <hr>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Bahasa:</strong><br>
                        <span class="badge bg-secondary mt-1">{{ $assignment->language ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Deadline:</strong><br>
                        @if($assignment->deadline)
                            <span class="badge bg-warning text-dark mt-1" style="font-size: 1.2rem;">{{ $assignment->deadline->format('d M Y') }}</span>
                        @else
                            <span class="badge bg-secondary mt-1">N/A</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Link Submit:</strong><br>
                    @if($assignment->submit_link)
                        <a href="{{ $assignment->submit_link }}" target="_blank" class="btn btn-sm btn-primary mt-1">
                            <i class="bi bi-box-arrow-up-right"></i> Buka Link Submit
                        </a>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </div>

                <div class="mb-3">
                    <strong>Username Akun:</strong><br>
                    <code>{{ $assignment->account_username ?? 'N/A' }}</code>
                </div>

                <div class="mb-3">
                    <strong>Password Akun:</strong><br>
                    <code>{{ $assignment->account_password ?? 'N/A' }}</code>
                </div>

                <div class="mb-3">
                    <strong>Surat Tugas:</strong><br>
                    @if($assignment->assignment_letter_link)
                        <a href="{{ $assignment->assignment_letter_link }}" target="_blank" class="btn btn-sm btn-primary mt-1">
                            <i class="bi bi-file-earmark-pdf"></i> Lihat Surat Tugas
                        </a>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </div>

                @if($assignment->status == 'APPROVED')
                <div class="mb-3">
                    <strong>Link Sertifikat:</strong><br>
                    @if($assignment->certificate_link)
                        <a href="{{ $assignment->certificate_link }}" target="_blank" class="btn btn-sm btn-success mt-1">
                            <i class="bi bi-award"></i> Lihat Sertifikat
                        </a>
                    @else
                        <span class="text-muted">Sertifikat belum tersedia</span>
                    @endif
                </div>
                @endif

                <div class="mb-3">
                    <strong>Status:</strong><br>
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
                    <span class="badge bg-{{ $color }} mt-1" style="font-size: 1rem;">{{ $assignment->status }}</span>
                </div>

                @if($assignment->status == 'PENDING')
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> Tugas ini menunggu respon Anda. Silakan terima atau tolak tugas ini.
                </div>
                @endif

                @if($assignment->status == 'REVISION' && $assignment->reviewResult && $assignment->reviewResult->admin_feedback)
                <div class="alert alert-danger">
                    <strong><i class="bi bi-exclamation-circle"></i> Feedback Admin:</strong><br>
                    {{ $assignment->reviewResult->admin_feedback }}
                </div>
                @endif

                @if($assignment->reviewResult)
                <div class="mb-3">
                    <strong>Link Google Drive Review:</strong><br>
                    <a href="{{ $assignment->reviewResult->file_path }}" 
                       class="btn btn-sm btn-success mt-1" 
                       target="_blank">
                        <i class="bi bi-box-arrow-up-right"></i> Buka File Review
                    </a>
                </div>

                @if($assignment->reviewResult->recommendation)
                <div class="mb-3">
                    <strong>Rekomendasi:</strong><br>
                    <span class="badge bg-info mt-1">{{ $assignment->reviewResult->recommendation }}</span>
                </div>
                @endif

                @if($assignment->reviewResult->notes)
                <div class="mb-3">
                    <strong>Catatan Review:</strong><br>
                    <div class="p-2 bg-light rounded border mt-1">
                        {!! nl2br(e($assignment->reviewResult->notes)) !!}
                    </div>
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-gear"></i> Aksi
            </div>
            <div class="card-body">
                @if($assignment->status == 'PENDING')
                    <form action="{{ route('reviewer.tasks.accept', $assignment) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Terima Tugas
                        </button>
                    </form>

                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle"></i> Tolak Tugas
                    </button>
                @endif

                @if($assignment->status == 'ACCEPTED')
                    <form action="{{ route('reviewer.tasks.start', $assignment) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-play-circle"></i> Mulai Review
                        </button>
                    </form>
                @endif

                @if(in_array($assignment->status, ['ON_PROGRESS', 'REVISION']))
                    <a href="{{ route('reviewer.results.create', $assignment) }}" class="btn btn-success w-100">
                        <i class="bi bi-upload"></i> Upload Hasil Review
                    </a>
                @endif

                @if($assignment->status == 'SUBMITTED')
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Menunggu validasi admin
                    </div>
                @endif

                @if($assignment->status == 'APPROVED')
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Review telah disetujui!
                    </div>
                @endif

                <hr>
                <a href="{{ route('reviewer.tasks.index') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <i class="bi bi-clock-history"></i> Timeline
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> 
                        <strong>Ditugaskan:</strong><br>
                        <small>{{ $assignment->created_at->format('d M Y H:i') }}</small>
                    </li>
                    @if($assignment->accepted_at)
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> 
                        <strong>Diterima:</strong><br>
                        <small>{{ $assignment->accepted_at->format('d M Y H:i') }}</small>
                    </li>
                    @endif
                    @if($assignment->submitted_at)
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> 
                        <strong>Disubmit:</strong><br>
                        <small>{{ $assignment->submitted_at->format('d M Y H:i') }}</small>
                    </li>
                    @endif
                    @if($assignment->approved_at)
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> 
                        <strong>Disetujui:</strong><br>
                        <small>{{ $assignment->approved_at->format('d M Y H:i') }}</small>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('reviewer.tasks.reject', $assignment) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="rejection_reason" rows="4" required></textarea>
                        <small class="text-muted">Jelaskan alasan Anda menolak tugas ini</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Tugas</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
