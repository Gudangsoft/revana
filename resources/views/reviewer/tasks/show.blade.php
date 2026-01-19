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

                <div class="mb-3">
                    <strong>File Artikel:</strong><br>
                    @if($assignment->article_file)
                        <a href="{{ asset('storage/' . $assignment->article_file) }}" target="_blank" class="btn btn-sm btn-success mt-1">
                            <i class="bi bi-file-earmark-word"></i> Download Artikel ({{ $assignment->article_file_original_name ?? 'File' }})
                        </a>
                    @else
                        <span class="text-muted">Tidak ada file artikel</span>
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

                @php
                    $myReviewResult = $assignment->reviewResults()->where('reviewer_id', auth()->id())->first();
                @endphp

                <!-- Form Input Review (untuk status ON_PROGRESS dan REVISION) -->
                @if(in_array($assignment->status, ['ON_PROGRESS', 'REVISION']) && !$assignment->isExpired())
                <div class="card mt-3 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> FORMULIR REVIEW ARTIKEL ILMIAH SIPERA</h5>
                        <small>(Untuk Reviewer/Mitra Bestari – Bahasa Indonesia)</small>
                    </div>
                    <div class="card-body">
                        @include('reviewer.results._form', ['assignment' => $assignment])
                    </div>
                </div>
                @endif

                @if($myReviewResult)
                <!-- Form Review yang Telah Disubmit - FULL DISPLAY -->
                <div class="card mt-3 border-success">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-file-text-fill"></i> <strong>FORMULIR REVIEW ARTIKEL ILMIAH SIPERA</strong>
                            <br><small>(Yang Telah Disubmit)</small>
                        </div>
                        <a href="{{ route('reviewer.results.downloadPdf', $assignment) }}" 
                           class="btn btn-light btn-sm" target="_blank">
                            <i class="bi bi-download"></i> Download PDF
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- A. INFORMASI NASKAH -->
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-file-text"></i> A. Informasi Naskah
                        </h6>
                        <table class="table table-sm table-bordered mb-4">
                            <tr>
                                <th width="30%">ID Manuskrip</th>
                                <td>{{ $myReviewResult->manuscript_id ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Judul Manuskrip</th>
                                <td>{{ $myReviewResult->manuscript_title ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jenis Artikel</th>
                                <td>{{ $myReviewResult->article_type ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Bidang/Section/Topik</th>
                                <td>{{ $myReviewResult->field_section_topic ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Review</th>
                                <td>{{ $myReviewResult->review_date ? \Carbon\Carbon::parse($myReviewResult->review_date)->format('d F Y') : '-' }}</td>
                            </tr>
                        </table>

                        <!-- B. PERNYATAAN KONFLIK KEPENTINGAN & ETIKA -->
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-shield-check"></i> B. Pernyataan Konflik Kepentingan & Etika
                        </h6>
                        <table class="table table-sm table-bordered mb-4">
                            <tr>
                                <th width="60%">Konflik Kepentingan</th>
                                <td>
                                    <span class="badge {{ $myReviewResult->conflict_of_interest ? 'bg-warning' : 'bg-success' }}">
                                        {{ $myReviewResult->conflict_of_interest ? 'Ya' : 'Tidak' }}
                                    </span>
                                    @if($myReviewResult->conflict_of_interest && $myReviewResult->conflict_explanation)
                                        <br><small class="text-muted">{{ $myReviewResult->conflict_explanation }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Plagiarisme Terdeteksi</th>
                                <td>
                                    <span class="badge {{ $myReviewResult->plagiarism_detected ? 'bg-danger' : 'bg-success' }}">
                                        {{ $myReviewResult->plagiarism_detected ? 'Ya' : 'Tidak' }}
                                    </span>
                                    @if($myReviewResult->plagiarism_detected && $myReviewResult->plagiarism_explanation)
                                        <br><small class="text-muted">{{ $myReviewResult->plagiarism_explanation }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Self-Citation Berlebihan</th>
                                <td>
                                    <span class="badge {{ $myReviewResult->excessive_self_citation ? 'bg-warning' : 'bg-success' }}">
                                        {{ $myReviewResult->excessive_self_citation ? 'Ya' : 'Tidak' }}
                                    </span>
                                    @if($myReviewResult->excessive_self_citation && $myReviewResult->self_citation_explanation)
                                        <br><small class="text-muted">{{ $myReviewResult->self_citation_explanation }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Masalah Etik Lain</th>
                                <td>
                                    <span class="badge {{ $myReviewResult->other_ethical_issues ? 'bg-warning' : 'bg-success' }}">
                                        {{ $myReviewResult->other_ethical_issues ? 'Ya' : 'Tidak' }}
                                    </span>
                                    @if($myReviewResult->other_ethical_issues && $myReviewResult->ethical_issues_explanation)
                                        <br><small class="text-muted">{{ $myReviewResult->ethical_issues_explanation }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Penggunaan AI</th>
                                <td>
                                    <span class="badge {{ $myReviewResult->ai_usage_statement ? 'bg-info' : 'bg-secondary' }}">
                                        {{ $myReviewResult->ai_usage_statement ? 'Menggunakan AI-assisted' : 'Tidak menggunakan AI' }}
                                    </span>
                                </td>
                            </tr>
                        </table>

                        <!-- C. PENILAIAN CEPAT -->
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-star"></i> C. Penilaian Cepat (Rating 1-5)
                        </h6>
                        <table class="table table-sm table-bordered mb-4">
                            <thead class="table-light">
                                <tr>
                                    <th width="40%">Aspek</th>
                                    <th width="15%" class="text-center">Skor</th>
                                    <th width="45%">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $ratingFields = [
                                    'scope' => 'Kesesuaian scope jurnal',
                                    'novelty' => 'Kebaruan/Originalitas',
                                    'significance' => 'Signifikansi kontribusi',
                                    'soundness' => 'Scientific soundness',
                                    'methodology' => 'Desain & metodologi',
                                    'analysis' => 'Kualitas analisis',
                                    'presentation' => 'Kualitas presentasi',
                                    'figures' => 'Kualitas gambar/tabel',
                                    'references' => 'Kualitas referensi',
                                    'language' => 'Kualitas bahasa',
                                ];
                                @endphp
                                @foreach($ratingFields as $field => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-center">
                                        @php
                                        $score = $myReviewResult->{'rating_'.$field} ?? '-';
                                        $colorClass = match((int)$score) {
                                            5 => 'success',
                                            4 => 'info',
                                            3 => 'warning',
                                            2 => 'orange',
                                            1 => 'danger',
                                            default => 'secondary'
                                        };
                                        @endphp
                                        <span class="badge bg-{{ $colorClass }}" style="font-size: 1.1rem;">{{ $score }}</span>
                                    </td>
                                    <td><small>{{ $myReviewResult->{'rating_'.$field.'_note'} ?? '-' }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- D. CHECKLIST EVALUASI DETAIL -->
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-list-check"></i> D. Checklist Evaluasi Detail
                        </h6>
                        <table class="table table-sm table-bordered mb-4">
                            <thead class="table-light">
                                <tr>
                                    <th width="60%">Pertanyaan</th>
                                    <th width="40%" class="text-center">Jawaban</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $checklistFields = [
                                    'abstract' => 'Abstrak jelas & sesuai isi',
                                    'intro' => 'Pendahuluan memberi latar belakang cukup',
                                    'novelty' => 'Novelty dinyatakan jelas',
                                    'literature' => 'Tinjauan pustaka relevan & mutakhir',
                                    'method' => 'Metode dijelaskan rinci & dapat direplikasi',
                                    'design' => 'Desain eksperimen/penelitian tepat',
                                    'results' => 'Hasil disajikan jelas (grafik/tabel tepat)',
                                    'discussion' => 'Diskusi membandingkan dengan studi sebelumnya',
                                    'conclusion' => 'Kesimpulan didukung data/hasil',
                                    'data_availability' => 'Data/kode tersedia atau dijelaskan aksesnya',
                                ];
                                @endphp
                                @foreach($checklistFields as $field => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-center">
                                        @php
                                        $value = $myReviewResult->{'checklist_'.$field} ?? '-';
                                        $badgeClass = match($value) {
                                            'Ya' => 'success',
                                            'Tidak' => 'danger',
                                            'Perlu Perbaikan' => 'warning',
                                            default => 'secondary'
                                        };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">{{ $value }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- E. EVALUASI REFERENSI -->
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-book"></i> E. Evaluasi Referensi
                        </h6>
                        <div class="mb-3">
                            <strong>Referensi Mencukupi:</strong>
                            <span class="badge {{ $myReviewResult->references_adequate ? 'bg-success' : 'bg-danger' }}">
                                {{ $myReviewResult->references_adequate ? 'Ya' : 'Tidak' }}
                            </span>
                        </div>
                        @if($myReviewResult->suggested_references)
                        <div class="mb-4">
                            <strong>Saran Referensi Tambahan:</strong>
                            <div class="p-3 bg-light rounded border mt-2">
                                <small style="white-space: pre-wrap;">{{ $myReviewResult->suggested_references }}</small>
                            </div>
                        </div>
                        @endif

                        <!-- F. REKOMENDASI AKHIR -->
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-check-circle"></i> F. Rekomendasi Akhir Reviewer
                        </h6>
                        <div class="mb-3">
                            <strong>Rekomendasi:</strong><br>
                            @php
                            $recommendations = [
                                'ACCEPT' => 'Terima tanpa revisi',
                                'MINOR_REVISION' => 'Terima dengan revisi minor',
                                'MAJOR_REVISION' => 'Revisi mayor – tinjau ulang',
                                'REJECT_RESUBMIT' => 'Tolak – dapat submit ulang jika diperbaiki',
                                'REJECT' => 'Tolak – tidak disarankan submit ulang'
                            ];
                            $recValue = $myReviewResult->recommendation ?? 'ACCEPT';
                            $recBadgeClass = match($recValue) {
                                'ACCEPT' => 'success',
                                'MINOR_REVISION' => 'info',
                                'MAJOR_REVISION' => 'warning',
                                'REJECT_RESUBMIT' => 'orange',
                                'REJECT' => 'danger',
                                default => 'secondary'
                            };
                            @endphp
                            <span class="badge bg-{{ $recBadgeClass }}" style="font-size: 1.1rem;">
                                {{ $recommendations[$recValue] ?? $recValue }}
                            </span>
                        </div>
                        <div class="mb-3">
                            <strong>Alasan Rekomendasi:</strong>
                            <div class="p-3 bg-light rounded border mt-2">
                                <small style="white-space: pre-wrap;">{{ $myReviewResult->recommendation_reason ?? '-' }}</small>
                            </div>
                        </div>

                        <!-- Pernyataan -->
                        <div class="alert alert-info mt-4">
                            <strong>Disubmit oleh:</strong> {{ auth()->user()->name }}<br>
                            <strong>Tanggal Submit:</strong> {{ $myReviewResult->created_at ? $myReviewResult->created_at->format('d F Y, H:i') : '-' }}
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
                {{-- Download Artikel Button - Always visible if file exists --}}
                @if($assignment->article_file)
                    <a href="{{ asset('storage/' . $assignment->article_file) }}" target="_blank" class="btn btn-success w-100 mb-2">
                        <i class="bi bi-download"></i> Download Artikel untuk Review
                    </a>
                    <hr>
                @endif

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

                    @if(in_array($assignment->status, ['SUBMITTED', 'APPROVED']))
                        @php
                            $myReviewResult = $assignment->reviewResults()->where('reviewer_id', auth()->id())->first();
                        @endphp
                        
                        @if($myReviewResult)
                            <a href="{{ route('reviewer.results.downloadPdf', $assignment) }}" 
                               class="btn btn-primary w-100 mb-2" target="_blank">
                                <i class="bi bi-file-pdf"></i> Download PDF Review
                            </a>

                            @if($assignment->status == 'SUBMITTED' && Route::has('reviewer.results.uploadRevision'))
                                <button type="button" class="btn btn-info w-100 mb-2" data-bs-toggle="modal" data-bs-target="#uploadRevisionModal">
                                    <i class="bi bi-upload"></i> Upload File Revisi Jurnal
                                </button>

                                @if($myReviewResult->merged_revision_file || $myReviewResult->revision_files)
                                <div class="alert alert-success p-2 mb-2">
                                    <small><i class="bi bi-file-earmark-check"></i> File revisi sudah diupload</small>
                                    <br>
                                    
                                    @if($myReviewResult->merged_revision_file)
                                        <!-- Download Merged PDF -->
                                        <a href="{{ Storage::url($myReviewResult->merged_revision_file) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-success mt-2 w-100">
                                            <i class="bi bi-download"></i> Download PDF Gabungan (Merged)
                                        </a>
                                        
                                        @if($myReviewResult->revision_files && count($myReviewResult->revision_files) > 0)
                                        <hr class="my-2">
                                        <small class="text-muted">
                                            <i class="bi bi-files"></i> {{ count($myReviewResult->revision_files) }} file individual:
                                        </small>
                                        <div class="mt-1">
                                            @foreach($myReviewResult->revision_files as $index => $file)
                                            <a href="{{ Storage::url($file) }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-primary mt-1 w-100 text-start">
                                                <i class="bi bi-file-pdf"></i> File {{ $index + 1 }}: {{ basename($file) }}
                                            </a>
                                            @endforeach
                                        </div>
                                        @endif
                                    @elseif($myReviewResult->revision_file)
                                        <!-- Legacy: single file -->
                                        <a href="{{ Storage::url($myReviewResult->revision_file) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-success mt-1">
                                            <i class="bi bi-download"></i> Lihat File
                                        </a>
                                    @endif
                                </div>
                                @else
                                <div class="alert alert-warning p-2 mb-2">
                                    <small><i class="bi bi-exclamation-triangle"></i> Silakan upload file revisi jurnal untuk validasi admin</small>
                                </div>
                                @endif

                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Menunggu validasi admin setelah file revisi diupload
                                </div>
                            @endif
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
@if(Route::has('reviewer.results.uploadRevision'))
<div class="modal fade" id="uploadRevisionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-upload"></i> Upload File Revisi Jurnal (Multiple PDFs)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('reviewer.results.uploadRevision', $assignment) }}" method="POST" enctype="multipart/form-data" id="revisionUploadForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Upload Multiple PDFs:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Anda dapat upload <strong>1-10 file PDF</strong> sekaligus</li>
                            <li>Semua file akan <strong>otomatis digabung</strong> menjadi 1 PDF</li>
                            <li>Format: <strong>PDF only</strong></li>
                            <li>Maksimal ukuran: <strong>10MB per file</strong></li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Pilih File PDF <span class="text-danger">*</span>
                            <small class="text-muted">(Dapat pilih lebih dari 1 file)</small>
                        </label>
                        <input type="file" 
                               class="form-control @error('revision_files.*') is-invalid @enderror" 
                               name="revision_files[]" 
                               id="revisionFilesInput"
                               multiple 
                               accept=".pdf"
                               required>
                        @error('revision_files.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('revision_files')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- File Preview List -->
                    <div id="filePreviewList" class="mt-3" style="display: none;">
                        <label class="form-label fw-bold">File yang akan diupload:</label>
                        <ul id="selectedFilesList" class="list-group">
                        </ul>
                        <small class="text-muted mt-2 d-block">
                            <i class="bi bi-arrow-down-up"></i> File akan digabung sesuai urutan di atas
                        </small>
                    </div>

                    @php
                        $myReviewResult = $assignment->reviewResults()->where('reviewer_id', auth()->id())->first();
                    @endphp
                    @if($myReviewResult && ($myReviewResult->revision_files || $myReviewResult->merged_revision_file))
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle"></i> File sebelumnya akan diganti dengan file baru
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-upload"></i> Upload & Gabungkan PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('revisionFilesInput');
    const filePreviewList = document.getElementById('filePreviewList');
    const selectedFilesList = document.getElementById('selectedFilesList');
    const submitBtn = document.getElementById('submitBtn');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            if (files.length === 0) {
                filePreviewList.style.display = 'none';
                return;
            }
            
            // Validate file count
            if (files.length > 10) {
                alert('Maksimal 10 file sekaligus!');
                fileInput.value = '';
                filePreviewList.style.display = 'none';
                return;
            }
            
            // Clear previous list
            selectedFilesList.innerHTML = '';
            
            // Show preview
            filePreviewList.style.display = 'block';
            
            // Add each file to list
            files.forEach((file, index) => {
                const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                
                let iconClass = 'bi-file-pdf text-danger';
                let statusBadge = `<span class="badge bg-success">PDF</span>`;
                
                // Validate PDF
                if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
                    iconClass = 'bi-file-x text-warning';
                    statusBadge = `<span class="badge bg-danger">BUKAN PDF!</span>`;
                }
                
                // Validate size
                if (file.size > 10 * 1024 * 1024) {
                    statusBadge += ` <span class="badge bg-warning">Terlalu besar!</span>`;
                }
                
                li.innerHTML = `
                    <div>
                        <i class="bi ${iconClass} me-2"></i>
                        <strong>${index + 1}.</strong> ${file.name}
                        <small class="text-muted">(${fileSize} MB)</small>
                    </div>
                    <div>
                        ${statusBadge}
                    </div>
                `;
                selectedFilesList.appendChild(li);
            });
            
            // Update submit button text
            submitBtn.innerHTML = `<i class="bi bi-upload"></i> Upload & Gabungkan ${files.length} PDF`;
        });
    }
});
</script>
@endif
@endsection
@push('styles')
<style>
.bg-orange {
    background-color: #fd7e14 !important;
}

.table td, .table th {
    vertical-align: middle;
}
</style>
@endpush