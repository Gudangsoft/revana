@extends('layouts.app')

@section('title', ' - Detail Permintaan Review')
@section('page-title', 'Detail Permintaan Review')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Detail Permintaan Review</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Tanggal Pengajuan:</strong></div>
                    <div class="col-md-8">{{ $reviewRequest->created_at->format('d F Y, H:i') }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Reviewer:</strong></div>
                    <div class="col-md-8">
                        <a href="{{ route('admin.reviewers.show', $reviewRequest->reviewer->id) }}" target="_blank">
                            {{ $reviewRequest->reviewer->name }}
                        </a>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Email:</strong></div>
                    <div class="col-md-8">{{ $reviewRequest->reviewer->email }}</div>
                </div>

                @if($reviewRequest->reviewer->institution)
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Institusi:</strong></div>
                    <div class="col-md-8">{{ $reviewRequest->reviewer->institution }}</div>
                </div>
                @endif

                @if($reviewRequest->reviewer->fieldOfStudy)
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Bidang Ilmu:</strong></div>
                    <div class="col-md-8">
                        <span class="badge bg-primary">{{ $reviewRequest->reviewer->fieldOfStudy->name }}</span>
                    </div>
                </div>
                @endif

                @if($reviewRequest->reviewer->article_languages && is_array($reviewRequest->reviewer->article_languages))
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Bahasa Artikel:</strong></div>
                    <div class="col-md-8">
                        @foreach($reviewRequest->reviewer->article_languages as $lang)
                            <span class="badge bg-secondary me-1">{{ $lang }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Jumlah Jurnal:</strong></div>
                    <div class="col-md-8">
                        <span class="badge bg-info">{{ $reviewRequest->number_of_journals }} jurnal</span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Lama Hari:</strong></div>
                    <div class="col-md-8">
                        <span class="badge bg-secondary">{{ $reviewRequest->number_of_days }} hari</span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Status:</strong></div>
                    <div class="col-md-8">
                        @if($reviewRequest->status === 'pending')
                            <span class="badge bg-warning">
                                <i class="bi bi-clock"></i> Menunggu Persetujuan
                            </span>
                        @elseif($reviewRequest->status === 'approved')
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Disetujui
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Ditolak
                            </span>
                        @endif
                    </div>
                </div>

                @if($reviewRequest->notes)
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Catatan Reviewer:</strong></div>
                    <div class="col-md-8">
                        <div class="alert alert-light">{{ $reviewRequest->notes }}</div>
                    </div>
                </div>
                @endif

                @if($reviewRequest->admin_notes)
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Catatan Admin:</strong></div>
                    <div class="col-md-8">
                        <div class="alert alert-warning">{{ $reviewRequest->admin_notes }}</div>
                    </div>
                </div>
                @endif

                @if($reviewRequest->approver)
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Diproses Oleh:</strong></div>
                    <div class="col-md-8">
                        {{ $reviewRequest->approver->name }}
                        <br>
                        <small class="text-muted">{{ $reviewRequest->approved_at->format('d F Y, H:i') }}</small>
                    </div>
                </div>
                @endif

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.review-requests.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($reviewRequest->status === 'pending')
    <div class="col-md-4">
        <!-- Approve Form -->
        <div class="card border-success mb-3">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-check-circle"></i> Setujui Permintaan</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.review-requests.approve', $reviewRequest->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="admin_notes_approve" class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" id="admin_notes_approve" name="admin_notes" rows="3" placeholder="Tambahkan catatan untuk reviewer..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Apakah Anda yakin ingin menyetujui dan menugaskan reviewer ini?')">
                        <i class="bi bi-check-circle"></i> Setujui dan Tugaskan
                    </button>
                </form>
            </div>
        </div>

        <!-- Reject Form -->
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="bi bi-x-circle"></i> Tolak Permintaan</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.review-requests.reject', $reviewRequest->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="admin_notes_reject" class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('admin_notes') is-invalid @enderror" id="admin_notes_reject" name="admin_notes" rows="3" placeholder="Jelaskan alasan penolakan..." required></textarea>
                        @error('admin_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Apakah Anda yakin ingin menolak permintaan ini?')">
                        <i class="bi bi-x-circle"></i> Tolak
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
