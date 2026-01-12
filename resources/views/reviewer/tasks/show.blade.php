@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Detail Tugas Review')

@section('sidebar')
    @include('reviewer.partials.sidebar')
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
                        <strong>Nomor Artikel:</strong><br>
                        <span class="badge bg-primary mt-1">{{ $assignment->article_number ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Bahasa:</strong><br>
                        <span class="badge bg-secondary mt-1">{{ $assignment->language ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Deadline:</strong><br>
                    @if($assignment->deadline)
                        <span class="badge bg-warning text-dark mt-1" style="font-size: 1.2rem;">{{ $assignment->deadline->format('d M Y') }}</span>
                        @if($assignment->isExpired())
                            <br><span class="badge bg-danger mt-2"><i class="bi bi-clock-history"></i> EXPIRED</span>
                        @endif
                    @else
                        <span class="badge bg-secondary mt-1">N/A</span>
                    @endif
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

                @php
                    // Determine which reviewer number this user is
                    $reviewerNumber = null;
                    $myUsername = null;
                    $myPassword = null;
                    
                    if ($assignment->reviewer_id == auth()->id()) {
                        $reviewerNumber = 1;
                        $myUsername = $assignment->reviewer_1_username;
                        $myPassword = $assignment->reviewer_1_password;
                    } elseif ($assignment->reviewer_2_id == auth()->id()) {
                        $reviewerNumber = 2;
                        $myUsername = $assignment->reviewer_2_username;
                        $myPassword = $assignment->reviewer_2_password;
                    } elseif ($assignment->reviewer_3_id == auth()->id()) {
                        $reviewerNumber = 3;
                        $myUsername = $assignment->reviewer_3_username;
                        $myPassword = $assignment->reviewer_3_password;
                    } elseif ($assignment->reviewer_4_id == auth()->id()) {
                        $reviewerNumber = 4;
                        $myUsername = $assignment->reviewer_4_username;
                        $myPassword = $assignment->reviewer_4_password;
                    } elseif ($assignment->reviewer_5_id == auth()->id()) {
                        $reviewerNumber = 5;
                        $myUsername = $assignment->reviewer_5_username;
                        $myPassword = $assignment->reviewer_5_password;
                    }
                @endphp

                @if($reviewerNumber)
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Username Reviewer {{ $reviewerNumber }}:</strong><br>
                        <code>{{ $myUsername ?? 'N/A' }}</code>
                    </div>
                    <div class="col-md-6">
                        <strong>Password Reviewer {{ $reviewerNumber }}:</strong><br>
                        <code>{{ $myPassword ?? 'N/A' }}</code>
                    </div>
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
                
                @if($assignment->isExpired() && !in_array($assignment->status, ['APPROVED', 'SUBMITTED']))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-octagon"></i> <strong>TASK EXPIRED!</strong><br>
                    Tugas ini sudah melewati deadline ({{ $assignment->deadline->format('d M Y') }}) dan tidak dapat dikerjakan lagi.
                </div>
                @endif

                @if($assignment->status == 'REVISION' && $assignment->reviewResult && $assignment->reviewResult->admin_feedback)
                <div class="alert alert-danger">
                    <strong><i class="bi bi-exclamation-circle"></i> Feedback Admin:</strong><br>
                    {{ $assignment->reviewResult->admin_feedback }}
                </div>
                @endif

                @if($assignment->reviewResult)
                <!-- Hasil Review yang sudah disubmit -->
                <div class="card mt-3">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-file-text-fill"></i> Formulir Review yang Telah Disubmit
                    </div>
                    <div class="card-body">
                        <!-- Basic Information -->
                        <div class="mb-3">
                            <h6 class="fw-bold text-primary">Informasi Dasar</h6>
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
                                    <th>Tanggal Review</th>
                                    <td>{{ $assignment->reviewResult->review_date ? \Carbon\Carbon::parse($assignment->reviewResult->review_date)->format('d F Y') : '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Penilaian Substansi -->
                        <div class="mb-3">
                            <h6 class="fw-bold text-primary">I. Penilaian Substansi Artikel</h6>
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="40%">Aspek</th>
                                        <th width="10%">Skor</th>
                                        <th width="45%">Komentar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $aspects = [
                                        1 => 'Kebaruan dan relevansi topik',
                                        2 => 'Kesesuaian judul dengan isi',
                                        3 => 'Kejelasan latar belakang',
                                        4 => 'Kejelasan tujuan penelitian',
                                        5 => 'Ketepatan metode penelitian',
                                        6 => 'Kualitas analisis',
                                        7 => 'Kualitas hasil penelitian',
                                        8 => 'Kejelasan simpulan'
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
                                        <td><small>{{ $assignment->reviewResult->{'comment_'.$num} ?? '-' }}</small></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2" class="text-end">Total:</th>
                                        <th class="text-center">{{ $totalScore }}/40</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Penilaian Teknis -->
                        <div class="mb-3">
                            <h6 class="fw-bold text-primary">II. Penilaian Teknis</h6>
                            <ul class="list-unstyled">
                                <li>
                                    @if($assignment->reviewResult->technical_1)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-x-circle-fill text-secondary"></i>
                                    @endif
                                    Format dan sistematika jurnal
                                </li>
                                <li>
                                    @if($assignment->reviewResult->technical_2)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-x-circle-fill text-secondary"></i>
                                    @endif
                                    Bahasa dan tata tulis
                                </li>
                                <li>
                                    @if($assignment->reviewResult->technical_3)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-x-circle-fill text-secondary"></i>
                                    @endif
                                    Referensi memadai
                                </li>
                            </ul>
                        </div>

                        <!-- Saran Perbaikan -->
                        <div class="mb-3">
                            <h6 class="fw-bold text-primary">III. Saran Perbaikan</h6>
                            <div class="p-2 bg-light rounded border">
                                <small style="white-space: pre-wrap;">{{ $assignment->reviewResult->improvement_suggestions ?? '-' }}</small>
                            </div>
                        </div>

                        <!-- Rekomendasi -->
                        <div class="mb-3">
                            <h6 class="fw-bold text-primary">IV. Rekomendasi</h6>
                            @php
                            $recommendations = [
                                'ACCEPT' => 'Diterima tanpa revisi',
                                'MINOR_REVISION' => 'Diterima dengan revisi minor',
                                'MAJOR_REVISION' => 'Diterima dengan revisi mayor',
                                'REJECT' => 'Ditolak'
                            ];
                            $recValue = $assignment->reviewResult->recommendation ?? 'ACCEPT';
                            @endphp
                            <span class="badge bg-info">{{ $recommendations[$recValue] ?? $recValue }}</span>
                        </div>
                    </div>
                </div>
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
                @if($assignment->isExpired() && !in_array($assignment->status, ['APPROVED', 'SUBMITTED']))
                    <div class="alert alert-danger">
                        <i class="bi bi-lock-fill"></i> Task sudah expired dan tidak dapat dikerjakan
                    </div>
                @else
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
                        <a href="{{ route('reviewer.results.create', $assignment) }}" class="btn btn-success w-100 mb-2">
                            <i class="bi bi-file-text"></i> ISI FORMULIR REVIEW ARTIKEL
                        </a>

                        <button type="button" class="btn btn-info w-100 mb-2" data-bs-toggle="modal" data-bs-target="#uploadRevisionModal">
                            <i class="bi bi-upload"></i> Upload File Revisi
                        </button>

                        @if($assignment->revision_file)
                        <div class="alert alert-success p-2 mb-2">
                            <small><i class="bi bi-file-earmark-check"></i> File revisi sudah diupload</small>
                            <br>
                            <a href="{{ Storage::url($assignment->revision_file) }}" target="_blank" class="btn btn-sm btn-outline-success mt-1">
                                <i class="bi bi-download"></i> Lihat File
                            </a>
                        </div>
                        @endif
                    @endif

                    @if($assignment->status == 'SUBMITTED')
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Menunggu validasi admin
                        </div>
                    @endif

                    @if($assignment->status == 'APPROVED')
                        <div class="alert alert-success mb-2">
                            <i class="bi bi-check-circle"></i> Review telah disetujui!
                        </div>
                        
                        @if($assignment->reviewResult)
                        <a href="{{ route('reviewer.results.downloadPdf', $assignment) }}" 
                           class="btn btn-primary w-100 mb-2" target="_blank">
                            <i class="bi bi-file-pdf"></i> Download PDF Review
                        </a>
                        @endif
                    @endif
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

<!-- Upload Revision Modal -->
<div class="modal fade" id="uploadRevisionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload File Revisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('reviewer.tasks.uploadRevision', $assignment) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File Revisi <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="revision_file" required 
                               accept=".pdf,.doc,.docx,.zip,.rar">
                        <small class="text-muted">Format: PDF, DOC, DOCX, ZIP, RAR. Maksimal 10MB</small>
                    </div>
                    @if($assignment->revision_file)
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> File sebelumnya akan diganti dengan file baru
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
