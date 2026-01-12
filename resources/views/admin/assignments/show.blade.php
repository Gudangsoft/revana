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
                        @elseif(in_array($assignment->status, ['ON_PROGRESS', 'REVISION']))
                            <span class="badge bg-warning">
                                <i class="bi bi-hourglass-split"></i> {{ $assignment->status === 'ON_PROGRESS' ? 'On Progress' : 'Revision' }}
                            </span>
                            <br>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Menunggu reviewer menyelesaikan review
                            </small>
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
                @php
                    $allReviewers = $assignment->getAllReviewers();
                @endphp
                
                @foreach($allReviewers as $index => $reviewerData)
                    @if($index > 0)
                        <hr class="my-4">
                    @endif
                    
                    <h6 class="mb-3"><i class="bi bi-person-badge"></i> Reviewer {{ $reviewerData['number'] }}</h6>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Nama:</strong>
                        </div>
                        <div class="col-md-8">
                            <a href="{{ route('admin.reviewers.show', $reviewerData['user']) }}">
                                {{ $reviewerData['user']->name }}
                            </a>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Email:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $reviewerData['user']->email }}
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Username:</strong>
                        </div>
                        <div class="col-md-8">
                            <code>{{ $reviewerData['username'] ?? '-' }}</code>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Password:</strong>
                        </div>
                        <div class="col-md-8">
                            <code>{{ $reviewerData['password'] ?? '-' }}</code>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Total Reviews:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $reviewerData['user']->completed_reviews }} completed
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Total Points:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge bg-warning text-dark">{{ $reviewerData['user']->total_points }} Points</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Review Result -->
        @if($assignment->reviewResult)
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-file-text"></i> Hasil Review - Formulir Review Artikel Ilmiah SIPERA
            </div>
            <div class="card-body">
                <!-- A. Informasi Naskah -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-file-text"></i> A. Informasi Naskah</h6>
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th width="30%">ID Manuskrip</th>
                            <td>{{ $assignment->reviewResult->manuscript_id ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Judul Manuskrip</th>
                            <td>{{ $assignment->reviewResult->manuscript_title ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Artikel</th>
                            <td><span class="badge bg-info">{{ $assignment->reviewResult->article_type ?? '-' }}</span></td>
                        </tr>
                        <tr>
                            <th>Bidang/Section/Topik</th>
                            <td>{{ $assignment->reviewResult->field_section_topic ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Review</th>
                            <td>{{ $assignment->reviewResult->review_date ? \Carbon\Carbon::parse($assignment->reviewResult->review_date)->format('d F Y') : '-' }}</td>
                        </tr>
                    </table>
                </div>

                <!-- B. Pernyataan Konflik Kepentingan & Etika -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-shield-check"></i> B. Pernyataan Konflik Kepentingan & Etika</h6>
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th width="50%">Konflik Kepentingan</th>
                            <td>
                                @if($assignment->reviewResult->conflict_of_interest)
                                    <span class="badge bg-warning">Ya</span>
                                    <br><small class="text-muted">{{ $assignment->reviewResult->conflict_explanation }}</small>
                                @else
                                    <span class="badge bg-success">Tidak</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Plagiarisme Terdeteksi</th>
                            <td>
                                @if($assignment->reviewResult->plagiarism_detected)
                                    <span class="badge bg-danger">Ya</span>
                                    <br><small class="text-muted">{{ $assignment->reviewResult->plagiarism_explanation }}</small>
                                @else
                                    <span class="badge bg-success">Tidak</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Self-Citation Berlebihan</th>
                            <td>
                                @if($assignment->reviewResult->excessive_self_citation)
                                    <span class="badge bg-warning">Ya</span>
                                    <br><small class="text-muted">{{ $assignment->reviewResult->self_citation_explanation }}</small>
                                @else
                                    <span class="badge bg-success">Tidak</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Masalah Etik Lain</th>
                            <td>
                                @if($assignment->reviewResult->other_ethical_issues)
                                    <span class="badge bg-danger">Ya</span>
                                    <br><small class="text-muted">{{ $assignment->reviewResult->ethical_issues_explanation }}</small>
                                @else
                                    <span class="badge bg-success">Tidak</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Penggunaan AI</th>
                            <td>
                                @if($assignment->reviewResult->ai_usage_statement)
                                    <span class="badge bg-info">Menggunakan AI</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Menggunakan AI</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- C. Penilaian Cepat (Rating Umum) -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-star"></i> C. Penilaian Cepat (Rating Umum)</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="35%">Aspek</th>
                                <th width="10%">Skor (1-5)</th>
                                <th width="50%">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $ratings = [
                                ['field' => 'scope', 'label' => 'Kesesuaian dengan scope jurnal'],
                                ['field' => 'novelty', 'label' => 'Kebaruan/Originalitas'],
                                ['field' => 'significance', 'label' => 'Signifikansi kontribusi'],
                                ['field' => 'soundness', 'label' => 'Kebenaran teknis/Scientific soundness'],
                                ['field' => 'methodology', 'label' => 'Desain riset & metodologi'],
                                ['field' => 'analysis', 'label' => 'Kualitas analisis & hasil'],
                                ['field' => 'presentation', 'label' => 'Kualitas presentasi (struktur, alur)'],
                                ['field' => 'figures', 'label' => 'Kualitas gambar/tabel'],
                                ['field' => 'references', 'label' => 'Kualitas referensi/bibliografi'],
                                ['field' => 'language', 'label' => 'Kualitas bahasa'],
                            ];
                            $totalRating = 0;
                            @endphp
                            @foreach($ratings as $index => $rating)
                            @php
                                $score = $assignment->reviewResult->{'rating_'.$rating['field']} ?? 0;
                                $totalRating += $score;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $rating['label'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $score }}</span>
                                </td>
                                <td><small>{{ $assignment->reviewResult->{'rating_'.$rating['field'].'_note'} ?? '-' }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-end">Total Skor:</th>
                                <th class="text-center"><span class="badge bg-success">{{ $totalRating }}/50</span></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- D. Checklist Evaluasi Detail -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-list-check"></i> D. Checklist Evaluasi Detail</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="60%">Pertanyaan</th>
                                <th width="35%">Jawaban</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $checklists = [
                                ['field' => 'abstract', 'label' => 'Abstrak jelas & sesuai isi'],
                                ['field' => 'intro', 'label' => 'Pendahuluan memberi latar belakang cukup'],
                                ['field' => 'novelty', 'label' => 'Novelty dinyatakan jelas'],
                                ['field' => 'literature', 'label' => 'Tinjauan pustaka relevan & mutakhir'],
                                ['field' => 'method', 'label' => 'Metode dijelaskan rinci & dapat direplikasi'],
                                ['field' => 'design', 'label' => 'Desain eksperimen/penelitian tepat'],
                                ['field' => 'results', 'label' => 'Hasil disajikan jelas (grafik/tabel tepat)'],
                                ['field' => 'discussion', 'label' => 'Diskusi membandingkan dengan studi sebelumnya'],
                                ['field' => 'conclusion', 'label' => 'Kesimpulan didukung data/hasil'],
                                ['field' => 'data_availability', 'label' => 'Data/kode tersedia atau dijelaskan aksesnya'],
                            ];
                            @endphp
                            @foreach($checklists as $index => $checklist)
                            @php
                                $answer = $assignment->reviewResult->{'checklist_'.$checklist['field']} ?? '-';
                                $badgeClass = $answer == 'Ya' ? 'success' : ($answer == 'Tidak' ? 'danger' : 'warning');
                            @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $checklist['label'] }}</td>
                                <td><span class="badge bg-{{ $badgeClass }}">{{ $answer }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- E. Evaluasi Referensi -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-book"></i> E. Evaluasi Referensi</h6>
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th width="40%">Referensi Relevan & Mencukupi</th>
                            <td>
                                @if($assignment->reviewResult->references_adequate)
                                    <span class="badge bg-success">Ya</span>
                                @else
                                    <span class="badge bg-danger">Tidak</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Ada Manipulasi Referensi</th>
                            <td>
                                @if($assignment->reviewResult->references_manipulation)
                                    <span class="badge bg-danger">Ya</span>
                                @else
                                    <span class="badge bg-success">Tidak</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                    
                    @if($assignment->reviewResult->irrelevant_references)
                    <div class="mt-3">
                        <strong>Referensi Tidak Relevan:</strong>
                        <div class="p-2 bg-light rounded border mt-2">
                            <small style="white-space: pre-wrap;">{{ $assignment->reviewResult->irrelevant_references }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($assignment->reviewResult->suggested_references)
                    <div class="mt-3">
                        <strong>Saran Referensi Tambahan:</strong>
                        <div class="p-2 bg-light rounded border mt-2">
                            <small style="white-space: pre-wrap;">{{ $assignment->reviewResult->suggested_references }}</small>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- F. Rekomendasi Akhir Reviewer -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-check-circle"></i> F. Rekomendasi Akhir Reviewer</h6>
                    @php
                    $recommendations = [
                        'ACCEPT' => 'Terima tanpa revisi (Accept in present form)',
                        'MINOR_REVISION' => 'Terima dengan revisi minor (Accept after minor revision)',
                        'MAJOR_REVISION' => 'Revisi mayor – tinjau ulang (Major revision / Reconsider after major revision)',
                        'REJECT_RESUBMIT' => 'Tolak – dapat submit ulang jika diperbaiki (Reject but resubmission possible)',
                        'REJECT' => 'Tolak – tidak disarankan submit ulang (Reject – serious flaws)'
                    ];
                    $recValue = $assignment->reviewResult->recommendation ?? 'ACCEPT';
                    @endphp

                    <div class="mb-3">
                        <strong>Rekomendasi:</strong>
                        @foreach($recommendations as $value => $label)
                            @if($recValue == $value)
                                <div class="alert alert-success mt-2">
                                    <i class="bi bi-check-circle-fill"></i> <strong>{{ $label }}</strong>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if($assignment->reviewResult->recommendation_reason)
                    <div class="mt-3">
                        <strong>Alasan Rekomendasi:</strong>
                        <div class="p-3 bg-light rounded border mt-2">
                            <p style="white-space: pre-wrap;">{{ $assignment->reviewResult->recommendation_reason }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Komentar Umum untuk Penulis -->
                @if($assignment->reviewResult->general_comments)
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-chat-left-text"></i> G. Komentar Umum untuk Penulis</h6>
                    <div class="p-3 bg-light rounded border">
                        <p style="white-space: pre-wrap;">{{ $assignment->reviewResult->general_comments }}</p>
                    </div>
                </div>
                @endif

                <!-- Komentar Rahasia untuk Editor -->
                @if($assignment->reviewResult->confidential_comments)
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-lock"></i> H. Komentar Rahasia untuk Editor</h6>
                    <div class="p-3 bg-warning bg-opacity-10 rounded border border-warning">
                        <p style="white-space: pre-wrap;">{{ $assignment->reviewResult->confidential_comments }}</p>
                    </div>
                </div>
                @endif

                <!-- Pernyataan Reviewer -->
                <div class="mb-3">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-check"></i> Pernyataan Reviewer</h6>
                    <div class="alert alert-info">
                        <p class="mb-2">Saya menyatakan bahwa penilaian ini dilakukan secara objektif berdasarkan keilmuan, tanpa konflik kepentingan, dan sesuai dengan etika akademik.</p>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong>Nama:</strong> {{ $assignment->reviewer->name }}
                            </div>
                            <div class="col-md-6">
                                <strong>Tanggal:</strong> {{ $assignment->reviewResult->review_date ? \Carbon\Carbon::parse($assignment->reviewResult->review_date)->format('d F Y') : '-' }}
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

