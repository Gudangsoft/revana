@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Detail Review Assignment')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    <div class="col-md-8">
        <!-- Assignment Info -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-clipboard-check"></i> Informasi Assignment
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Assignment ID:</strong>
                    </div>
                    <div class="col-md-8">
                        #{{ $assignment->id }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Status:</strong>
                    </div>
                    <div class="col-md-8">
                        @if($assignment->status === 'PENDING')
                            <span class="badge bg-warning">
                                <i class="bi bi-clock"></i> Pending
                            </span>
                        @elseif($assignment->status === 'ACCEPTED')
                            <span class="badge bg-info">
                                <i class="bi bi-check"></i> Accepted
                            </span>
                        @elseif($assignment->status === 'REJECTED')
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Rejected
                            </span>
                        @elseif($assignment->status === 'SUBMITTED')
                            <span class="badge bg-primary">
                                <i class="bi bi-send"></i> Submitted
                            </span>
                        @elseif($assignment->status === 'APPROVED')
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Approved
                            </span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Assigned By:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $assignment->assignedBy->name }}
                        <br>
                        <small class="text-muted">{{ $assignment->created_at->format('d M Y H:i') }}</small>
                    </div>
                </div>
                @if($assignment->accepted_at)
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Accepted At:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $assignment->accepted_at->format('d M Y H:i') }}
                    </div>
                </div>
                @endif
                @if($assignment->submitted_at)
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Submitted At:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $assignment->submitted_at->format('d M Y H:i') }}
                    </div>
                </div>
                @endif
                @if($assignment->approved_at)
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Approved At:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $assignment->approved_at->format('d M Y H:i') }}
                    </div>
                </div>
                @endif
                @if($assignment->rejection_reason)
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Rejection Reason:</strong>
                    </div>
                    <div class="col-md-8">
                        <div class="alert alert-danger">
                            {{ $assignment->rejection_reason }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Journal Info -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <i class="bi bi-file-text"></i> Informasi Artikel
            </div>
            <div class="card-body">
                <h5 class="mb-3">{{ $assignment->article_title ?? 'N/A' }}</h5>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Bahasa:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge bg-secondary">{{ $assignment->language ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Link Submit:</strong>
                    </div>
                    <div class="col-md-8">
                        @if($assignment->submit_link)
                            <a href="{{ $assignment->submit_link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-link-45deg"></i> Buka Link
                            </a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Username Akun:</strong>
                    </div>
                    <div class="col-md-8">
                        <code>{{ $assignment->account_username ?? 'N/A' }}</code>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Password Akun:</strong>
                    </div>
                    <div class="col-md-8">
                        <code>{{ $assignment->account_password ?? 'N/A' }}</code>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Surat Tugas:</strong>
                    </div>
                    <div class="col-md-8">
                        @if($assignment->assignment_letter_link)
                            <a href="{{ $assignment->assignment_letter_link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat Surat
                            </a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Link Sertifikat:</strong>
                    </div>
                    <div class="col-md-8">
                        @if($assignment->certificate_link)
                            <a href="{{ $assignment->certificate_link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-award"></i> Lihat Sertifikat
                            </a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Deadline:</strong>
                    </div>
                    <div class="col-md-8">
                        @if($assignment->deadline)
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-calendar-event"></i> {{ $assignment->deadline->format('d M Y') }}
                            </span>
                        @else
                            <span class="badge bg-secondary">N/A</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviewer Info -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-people"></i> Informasi Reviewer
            </div>
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-person-badge"></i> Reviewer 1</h6>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Nama:</strong>
                    </div>
                    <div class="col-md-8">
                        <a href="{{ route('admin.reviewers.show', $assignment->reviewer) }}">
                            {{ $assignment->reviewer->name }}
                        </a>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Email:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $assignment->reviewer->email }}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Total Reviews:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $assignment->reviewer->completed_reviews }} completed
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Total Points:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge bg-warning text-dark">{{ $assignment->reviewer->total_points }} Points</span>
                    </div>
                </div>
                
                @if($assignment->reviewer2)
                    <hr>
                    <h6 class="mb-3 mt-3"><i class="bi bi-person-badge"></i> Reviewer 2</h6>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Nama:</strong>
                        </div>
                        <div class="col-md-8">
                            <a href="{{ route('admin.reviewers.show', $assignment->reviewer2) }}">
                                {{ $assignment->reviewer2->name }}
                            </a>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Email:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $assignment->reviewer2->email }}
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Total Reviews:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $assignment->reviewer2->completed_reviews }} completed
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Total Points:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge bg-warning text-dark">{{ $assignment->reviewer2->total_points }} Points</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Review Result -->
        @if($assignment->reviewResult)
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-file-text"></i> Hasil Review - Formulir Review Artikel Ilmiah
            </div>
            <div class="card-body">
                <!-- Basic Information -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3">Informasi Dasar</h6>
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th width="30%">Nama Jurnal</th>
                            <td>{{ $assignment->reviewResult->journal_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kode Artikel</th>
                            <td>{{ $assignment->reviewResult->article_code ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Judul Artikel</th>
                            <td>{{ $assignment->reviewResult->article_title ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Nama Reviewer</th>
                            <td>{{ $assignment->reviewResult->reviewer_name ?? $assignment->reviewer->name }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Review</th>
                            <td>{{ $assignment->reviewResult->review_date ? \Carbon\Carbon::parse($assignment->reviewResult->review_date)->format('d F Y') : '-' }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Section I: Penilaian Substansi -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3">I. Penilaian Substansi Artikel</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="35%">Aspek Penilaian</th>
                                <th width="10%">Skor</th>
                                <th width="50%">Komentar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $aspects = [
                                1 => 'Kebaruan dan relevansi topik penelitian',
                                2 => 'Kesesuaian judul dengan isi artikel',
                                3 => 'Kejelasan latar belakang dan rumusan masalah',
                                4 => 'Kejelasan tujuan dan kontribusi penelitian',
                                5 => 'Ketepatan metode dan pendekatan penelitian',
                                6 => 'Kualitas analisis dan pembahasan',
                                7 => 'Kualitas hasil penelitian',
                                8 => 'Kejelasan simpulan dan implikasi penelitian'
                            ];
                            $totalScore = 0;
                            @endphp

                            @foreach($aspects as $num => $aspect)
                            @php
                                $score = $assignment->reviewResult->{'score_'.$num} ?? 0;
                                $totalScore += $score;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $num }}</td>
                                <td>{{ $aspect }}</td>
                                <td class="text-center"><strong>{{ $score }}</strong></td>
                                <td>{{ $assignment->reviewResult->{'comment_'.$num} ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-end">Total Skor:</th>
                                <th class="text-center">{{ $totalScore }}/40</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Section II: Penilaian Teknis -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3">II. Penilaian Teknis Penulisan</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="75%">Kriteria Teknis</th>
                                <th width="20%" class="text-center">Ya / Tidak</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">1</td>
                                <td>Artikel mengikuti format dan sistematika jurnal</td>
                                <td class="text-center">
                                    @if($assignment->reviewResult->technical_1)
                                        <span class="badge bg-success">✓ Ya</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">2</td>
                                <td>Bahasa dan tata tulis sesuai kaidah ilmiah</td>
                                <td class="text-center">
                                    @if($assignment->reviewResult->technical_2)
                                        <span class="badge bg-success">✓ Ya</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">3</td>
                                <td>Referensi memadai dan terkini</td>
                                <td class="text-center">
                                    @if($assignment->reviewResult->technical_3)
                                        <span class="badge bg-success">✓ Ya</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Section III: Saran Perbaikan -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3">III. Saran Perbaikan untuk Penulis</h6>
                    <div class="p-3 bg-light rounded border">
                        <p style="white-space: pre-wrap;">{{ $assignment->reviewResult->improvement_suggestions ?? '-' }}</p>
                    </div>
                </div>

                <!-- Section IV: Rekomendasi -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3">IV. Rekomendasi Reviewer</h6>
                    @php
                    $recommendations = [
                        'ACCEPT' => 'Diterima tanpa revisi',
                        'MINOR_REVISION' => 'Diterima dengan revisi minor',
                        'MAJOR_REVISION' => 'Diterima dengan revisi mayor',
                        'REJECT' => 'Ditolak'
                    ];
                    $recValue = $assignment->reviewResult->recommendation ?? 'ACCEPT';
                    @endphp

                    @foreach($recommendations as $value => $label)
                    <div class="mb-2">
                        @if($recValue == $value)
                            <i class="bi bi-check-square-fill text-success"></i> <strong>{{ $label }}</strong>
                        @else
                            <i class="bi bi-square"></i> {{ $label }}
                        @endif
                    </div>
                    @endforeach
                </div>

                <!-- Section V: Pernyataan -->
                <div class="mb-3">
                    <h6 class="fw-bold text-primary mb-3">V. Pernyataan Reviewer</h6>
                    <div class="alert alert-info">
                        <p class="mb-2">Saya menyatakan bahwa penilaian ini dilakukan secara objektif berdasarkan keilmuan, tanpa konflik kepentingan, dan sesuai dengan etika akademik.</p>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong>Nama:</strong> {{ $assignment->reviewResult->reviewer_signature ?? $assignment->reviewer->name }}
                            </div>
                            <div class="col-md-6">
                                <strong>Tanggal:</strong> {{ $assignment->reviewResult->statement_date ? \Carbon\Carbon::parse($assignment->reviewResult->statement_date)->format('d F Y') : '-' }}
                            </div>
                        </div>
                        @if($assignment->reviewer->signature)
                        <div class="mt-3">
                            <strong>Tanda Tangan:</strong><br>
                            <img src="{{ asset('storage/' . $assignment->reviewer->signature) }}" 
                                 alt="Signature" 
                                 style="max-width: 200px; max-height: 80px; margin-top: 10px;">
                        </div>
                        @endif
                    </div>
                </div>
                
                @if($assignment->reviewResult->admin_feedback)
                <div class="mb-3">
                    <h6 class="fw-bold text-danger mb-3">Admin Feedback</h6>
                    <div class="alert alert-warning">
                        {!! nl2br(e($assignment->reviewResult->admin_feedback)) !!}
                    </div>
                </div>
                @endif

                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> Submitted: {{ $assignment->reviewResult->created_at->format('d M Y H:i') }}
                    </small>
                </div>
            </div>
        </div>
        @else
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <i class="bi bi-file-text"></i> Hasil Review
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> Reviewer belum mengirimkan hasil review.
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Actions -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-gear"></i> Actions
            </div>
            <div class="card-body">
                @if($assignment->status === 'SUBMITTED')
                    <form action="{{ route('admin.assignments.approve', $assignment) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve review ini dan berikan points?')">
                            <i class="bi bi-check-circle"></i> Approve Review
                        </button>
                    </form>
                    
                    <button type="button" class="btn btn-warning w-100 mt-2" data-bs-toggle="modal" data-bs-target="#revisionModal">
                        <i class="bi bi-arrow-clockwise"></i> Request Revision
                    </button>
                @endif

                @if($assignment->status === 'PENDING')
                    <form action="{{ route('admin.assignments.destroy', $assignment) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus assignment ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Hapus Assignment
                        </button>
                    </form>
                @endif

                @if($assignment->status === 'APPROVED')
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle"></i> Review sudah disetujui dan points telah diberikan.
                    </div>
                @endif
            </div>
        </div>

        <!-- Timeline -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Timeline
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <i class="bi bi-plus-circle text-primary"></i>
                        <div>
                            <strong>Created</strong>
                            <br>
                            <small>{{ $assignment->created_at->format('d M Y H:i') }}</small>
                        </div>
                    </div>
                    @if($assignment->accepted_at)
                    <div class="timeline-item">
                        <i class="bi bi-check text-info"></i>
                        <div>
                            <strong>Accepted</strong>
                            <br>
                            <small>{{ $assignment->accepted_at->format('d M Y H:i') }}</small>
                        </div>
                    </div>
                    @endif
                    @if($assignment->submitted_at)
                    <div class="timeline-item">
                        <i class="bi bi-send text-primary"></i>
                        <div>
                            <strong>Submitted</strong>
                            <br>
                            <small>{{ $assignment->submitted_at->format('d M Y H:i') }}</small>
                        </div>
                    </div>
                    @endif
                    @if($assignment->approved_at)
                    <div class="timeline-item">
                        <i class="bi bi-check-circle text-success"></i>
                        <div>
                            <strong>Approved</strong>
                            <br>
                            <small>{{ $assignment->approved_at->format('d M Y H:i') }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revision Modal -->
<div class="modal fade" id="revisionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Revision</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.assignments.revision', $assignment) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Admin Feedback <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="admin_feedback" rows="5" required placeholder="Jelaskan revisi yang diperlukan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Kirim Request Revision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
    padding-left: 20px;
}

.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: 8px;
    top: 20px;
    height: 100%;
    width: 2px;
    background: #dee2e6;
}

.timeline-item i {
    position: absolute;
    left: 0;
    top: 2px;
    font-size: 1.2rem;
}
</style>
@endsection

