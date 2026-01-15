@extends('layouts.app')

@section('title', ' - Edit Artikel')
@section('page-title', 'Edit Artikel: ' . $article->article_number)

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Timeline Progress -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-clock-history"></i> Timeline Progress
        </div>
        <div class="card-body">
            <div class="timeline-container">
                <div class="row text-center">
                    <div class="col timeline-step {{ $article->submission_completed ? 'completed' : ($article->submission_date ? 'active' : 'pending') }}">
                        <div class="timeline-icon">
                            <i class="bi bi-upload"></i>
                        </div>
                        <div class="timeline-label">Submission</div>
                        <small class="text-muted">{{ $article->submission_date?->format('d/m/Y') ?? '-' }}</small>
                    </div>
                    <div class="col timeline-step {{ $article->review_completed ? 'completed' : ($article->review_start_date ? 'active' : 'pending') }}">
                        <div class="timeline-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <div class="timeline-label">Review</div>
                        <small class="text-muted">{{ $article->review_end_date?->format('d/m/Y') ?? '-' }}</small>
                    </div>
                    <div class="col timeline-step {{ $article->revision_completed ? 'completed' : ($article->revision_start_date ? 'active' : 'pending') }}">
                        <div class="timeline-icon">
                            <i class="bi bi-pencil"></i>
                        </div>
                        <div class="timeline-label">Revision</div>
                        <small class="text-muted">{{ $article->revision_end_date?->format('d/m/Y') ?? '-' }}</small>
                    </div>
                    <div class="col timeline-step {{ $article->acceptance_completed ? 'completed' : ($article->acceptance_date ? 'active' : 'pending') }}">
                        <div class="timeline-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="timeline-label">LOA</div>
                        <small class="text-muted">{{ $article->acceptance_date?->format('d/m/Y') ?? '-' }}</small>
                    </div>
                    <div class="col timeline-step {{ $article->copyediting_completed ? 'completed' : ($article->copyediting_start_date ? 'active' : 'pending') }}">
                        <div class="timeline-icon">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <div class="timeline-label">Copyediting</div>
                        <small class="text-muted">{{ $article->copyediting_end_date?->format('d/m/Y') ?? '-' }}</small>
                    </div>
                    <div class="col timeline-step {{ $article->production_completed ? 'completed' : ($article->production_start_date ? 'active' : 'pending') }}">
                        <div class="timeline-icon">
                            <i class="bi bi-gear"></i>
                        </div>
                        <div class="timeline-label">Production</div>
                        <small class="text-muted">{{ $article->production_end_date?->format('d/m/Y') ?? '-' }}</small>
                    </div>
                    <div class="col timeline-step {{ $article->publication_completed ? 'completed' : ($article->publication_date ? 'active' : 'pending') }}">
                        <div class="timeline-icon">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <div class="timeline-label">Published</div>
                        <small class="text-muted">{{ $article->publication_date?->format('d/m/Y') ?? '-' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Basic Info -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <i class="bi bi-info-circle"></i> Informasi Dasar
        </div>
        <div class="card-body">
            <form action="{{ route('admin.articles.update', $article) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor Artikel <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('article_number') is-invalid @enderror" 
                               name="article_number" value="{{ old('article_number', $article->article_number) }}" required>
                        @error('article_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jurnal <span class="text-danger">*</span></label>
                        <select class="form-select @error('journal_id') is-invalid @enderror" name="journal_id" required>
                            @foreach($journals as $journal)
                                <option value="{{ $journal->id }}" {{ old('journal_id', $article->journal_id) == $journal->id ? 'selected' : '' }}>
                                    {{ $journal->title }} ({{ $journal->volume }})
                                </option>
                            @endforeach
                        </select>
                        @error('journal_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               name="title" value="{{ old('title', $article->title) }}" required>
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Author <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('author_name') is-invalid @enderror" 
                               name="author_name" value="{{ old('author_name', $article->author_name) }}" required>
                        @error('author_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor WA Author</label>
                        <input type="text" class="form-control @error('author_phone') is-invalid @enderror" 
                               name="author_phone" value="{{ old('author_phone', $article->author_phone) }}" placeholder="628xxxxxxxxxx">
                        @error('author_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Username Author</label>
                        <input type="text" class="form-control @error('author_username') is-invalid @enderror" 
                               name="author_username" value="{{ old('author_username', $article->author_username) }}">
                        @error('author_username')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password Author</label>
                        <input type="text" class="form-control @error('author_password') is-invalid @enderror" 
                               name="author_password" value="{{ old('author_password', $article->author_password) }}">
                        @error('author_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Marketing</label>
                        <input type="text" class="form-control" name="marketing" value="{{ old('marketing', $article->marketing) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">PIC</label>
                        <input type="text" class="form-control" name="pic" value="{{ old('pic', $article->pic) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="SUBMITTED" {{ old('status', $article->status) == 'SUBMITTED' ? 'selected' : '' }}>SUBMITTED</option>
                            <option value="REVIEW" {{ old('status', $article->status) == 'REVIEW' ? 'selected' : '' }}>REVIEW</option>
                            <option value="REVISION" {{ old('status', $article->status) == 'REVISION' ? 'selected' : '' }}>REVISION</option>
                            <option value="COPYEDITING" {{ old('status', $article->status) == 'COPYEDITING' ? 'selected' : '' }}>COPYEDITING</option>
                            <option value="PRODUCTION" {{ old('status', $article->status) == 'PRODUCTION' ? 'selected' : '' }}>PRODUCTION</option>
                            <option value="PUBLISHED" {{ old('status', $article->status) == 'PUBLISHED' ? 'selected' : '' }}>PUBLISHED</option>
                            <option value="REJECTED" {{ old('status', $article->status) == 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Catatan Umum</label>
                        <textarea class="form-control" name="notes" rows="2">{{ old('notes', $article->notes) }}</textarea>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Informasi Dasar
                    </button>
                    <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Workflow Stages Accordion -->
    <div class="accordion" id="workflowAccordion">
        
        <!-- 1. SUBMISSION -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button {{ $article->submission_completed ? 'bg-success text-white' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#submission">
                    <i class="bi bi-upload me-2"></i> 1. SUBMISSION
                    @if($article->submission_completed)
                        <span class="badge bg-light text-success ms-2">✓ Selesai</span>
                    @endif
                </button>
            </h2>
            <div id="submission" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <form action="{{ route('admin.articles.update-submission', $article) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Submission</label>
                                <input type="date" class="form-control" name="submission_date" 
                                       value="{{ old('submission_date', $article->submission_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Link Submit</label>
                                <input type="url" class="form-control" name="submit_link" 
                                       value="{{ old('submit_link', $article->submit_link) }}" placeholder="https://...">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Komentar Submission</label>
                                <textarea class="form-control" name="submission_comment" rows="3">{{ old('submission_comment', $article->submission_comment) }}</textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="submission_completed" value="1" 
                                           {{ old('submission_completed', $article->submission_completed) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        <strong>Submission Selesai</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Simpan Submission
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2. REVIEW -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed {{ $article->review_completed ? 'bg-success text-white' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#review">
                    <i class="bi bi-search me-2"></i> 2. REVIEW
                    @if($article->review_completed)
                        <span class="badge bg-light text-success ms-2">✓ Selesai</span>
                    @endif
                </button>
            </h2>
            <div id="review" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <form action="{{ route('admin.articles.update-review', $article) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reviewer 1</label>
                                <input type="text" class="form-control" name="reviewer1" 
                                       value="{{ old('reviewer1', $article->reviewer1) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIC Reviewer 1</label>
                                <input type="text" class="form-control" name="pic_reviewer1" 
                                       value="{{ old('pic_reviewer1', $article->pic_reviewer1) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reviewer 2</label>
                                <input type="text" class="form-control" name="reviewer2" 
                                       value="{{ old('reviewer2', $article->reviewer2) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIC Reviewer 2</label>
                                <input type="text" class="form-control" name="pic_reviewer2" 
                                       value="{{ old('pic_reviewer2', $article->pic_reviewer2) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai Review</label>
                                <input type="date" class="form-control" name="review_start_date" 
                                       value="{{ old('review_start_date', $article->review_start_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Selesai Review</label>
                                <input type="date" class="form-control" name="review_end_date" 
                                       value="{{ old('review_end_date', $article->review_end_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Komentar Review</label>
                                <textarea class="form-control" name="review_comment" rows="3">{{ old('review_comment', $article->review_comment) }}</textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="review_completed" value="1" 
                                           {{ old('review_completed', $article->review_completed) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        <strong>Review Selesai</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Simpan Review
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 3. REVISION -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed {{ $article->revision_completed ? 'bg-success text-white' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#revision">
                    <i class="bi bi-pencil me-2"></i> 3. REVISION
                    @if($article->revision_completed)
                        <span class="badge bg-light text-success ms-2">✓ Selesai</span>
                    @endif
                </button>
            </h2>
            <div id="revision" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <form action="{{ route('admin.articles.update-revision', $article) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Editor 1</label>
                                <input type="text" class="form-control" name="editor1" 
                                       value="{{ old('editor1', $article->editor1) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIC Editor 1</label>
                                <input type="text" class="form-control" name="pic_editor1" 
                                       value="{{ old('pic_editor1', $article->pic_editor1) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai Revisi</label>
                                <input type="date" class="form-control" name="revision_start_date" 
                                       value="{{ old('revision_start_date', $article->revision_start_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Selesai Revisi</label>
                                <input type="date" class="form-control" name="revision_end_date" 
                                       value="{{ old('revision_end_date', $article->revision_end_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Komentar Revisi</label>
                                <textarea class="form-control" name="revision_comment" rows="3">{{ old('revision_comment', $article->revision_comment) }}</textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="revision_completed" value="1" 
                                           {{ old('revision_completed', $article->revision_completed) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        <strong>Revisi Selesai</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Simpan Revision
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 4. ACCEPTANCE (LOA) -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed {{ $article->acceptance_completed ? 'bg-success text-white' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#acceptance">
                    <i class="bi bi-check-circle me-2"></i> 4. ACCEPTANCE (LOA)
                    @if($article->acceptance_completed)
                        <span class="badge bg-light text-success ms-2">✓ Selesai</span>
                    @endif
                </button>
            </h2>
            <div id="acceptance" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <form action="{{ route('admin.articles.update-acceptance', $article) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Acceptance</label>
                                <input type="date" class="form-control" name="acceptance_date" 
                                       value="{{ old('acceptance_date', $article->acceptance_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Link LOA</label>
                                <input type="url" class="form-control" name="loa_link" 
                                       value="{{ old('loa_link', $article->loa_link) }}" placeholder="https://...">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Komentar LOA</label>
                                <textarea class="form-control" name="acceptance_comment" rows="3">{{ old('acceptance_comment', $article->acceptance_comment) }}</textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="acceptance_completed" value="1" 
                                           {{ old('acceptance_completed', $article->acceptance_completed) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        <strong>LOA Sudah Diterima</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Simpan Acceptance
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 5. COPYEDITING -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed {{ $article->copyediting_completed ? 'bg-success text-white' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#copyediting">
                    <i class="bi bi-file-text me-2"></i> 5. COPYEDITING
                    @if($article->copyediting_completed)
                        <span class="badge bg-light text-success ms-2">✓ Selesai</span>
                    @endif
                </button>
            </h2>
            <div id="copyediting" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <form action="{{ route('admin.articles.update-copyediting', $article) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Editor 2</label>
                                <input type="text" class="form-control" name="editor2" 
                                       value="{{ old('editor2', $article->editor2) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIC Editor 2</label>
                                <input type="text" class="form-control" name="pic_editor2" 
                                       value="{{ old('pic_editor2', $article->pic_editor2) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Author 1</label>
                                <input type="text" class="form-control" name="author1" 
                                       value="{{ old('author1', $article->author1) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIC Author 1</label>
                                <input type="text" class="form-control" name="pic_author1" 
                                       value="{{ old('pic_author1', $article->pic_author1) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai Copyediting</label>
                                <input type="date" class="form-control" name="copyediting_start_date" 
                                       value="{{ old('copyediting_start_date', $article->copyediting_start_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Selesai Copyediting</label>
                                <input type="date" class="form-control" name="copyediting_end_date" 
                                       value="{{ old('copyediting_end_date', $article->copyediting_end_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Link Copyediting</label>
                                <input type="url" class="form-control" name="copyediting_link" 
                                       value="{{ old('copyediting_link', $article->copyediting_link) }}" placeholder="https://...">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Komentar Copyediting</label>
                                <textarea class="form-control" name="copyediting_comment" rows="3">{{ old('copyediting_comment', $article->copyediting_comment) }}</textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="copyediting_completed" value="1" 
                                           {{ old('copyediting_completed', $article->copyediting_completed) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        <strong>Copyediting Selesai</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Simpan Copyediting
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 6. PRODUCTION -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed {{ $article->production_completed ? 'bg-success text-white' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#production">
                    <i class="bi bi-gear me-2"></i> 6. PRODUCTION
                    @if($article->production_completed)
                        <span class="badge bg-light text-success ms-2">✓ Selesai</span>
                    @endif
                </button>
            </h2>
            <div id="production" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <form action="{{ route('admin.articles.update-production', $article) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai Production</label>
                                <input type="date" class="form-control" name="production_start_date" 
                                       value="{{ old('production_start_date', $article->production_start_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Selesai Production</label>
                                <input type="date" class="form-control" name="production_end_date" 
                                       value="{{ old('production_end_date', $article->production_end_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Link Turnitin</label>
                                <input type="url" class="form-control" name="turnitin_link" 
                                       value="{{ old('turnitin_link', $article->turnitin_link) }}" placeholder="https://...">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Komentar Production</label>
                                <textarea class="form-control" name="production_comment" rows="3">{{ old('production_comment', $article->production_comment) }}</textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="production_completed" value="1" 
                                           {{ old('production_completed', $article->production_completed) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        <strong>Production Selesai</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Simpan Production
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 7. PUBLICATION -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed {{ $article->publication_completed ? 'bg-success text-white' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#publication">
                    <i class="bi bi-trophy me-2"></i> 7. PUBLICATION
                    @if($article->publication_completed)
                        <span class="badge bg-light text-success ms-2">✓ Selesai</span>
                    @endif
                </button>
            </h2>
            <div id="publication" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <form action="{{ route('admin.articles.update-publication', $article) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Publikasi</label>
                                <input type="date" class="form-control" name="publication_date" 
                                       value="{{ old('publication_date', $article->publication_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Link Publication</label>
                                <input type="url" class="form-control" name="publication_link" 
                                       value="{{ old('publication_link', $article->publication_link) }}" placeholder="https://...">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Komentar Akhir</label>
                                <textarea class="form-control" name="publication_comment" rows="3">{{ old('publication_comment', $article->publication_comment) }}</textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="publication_completed" value="1" 
                                           {{ old('publication_completed', $article->publication_completed) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        <strong>Artikel Sudah Published</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Simpan Publication
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.timeline-container {
    position: relative;
    padding: 20px 0;
}

.timeline-step {
    position: relative;
    padding: 10px;
}

.timeline-step::before {
    content: '';
    position: absolute;
    top: 35px;
    left: 0;
    right: 0;
    height: 3px;
    background: #dee2e6;
    z-index: 0;
}

.timeline-step:first-child::before {
    left: 50%;
}

.timeline-step:last-child::before {
    right: 50%;
}

.timeline-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 10px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    position: relative;
    z-index: 1;
    background: white;
    border: 3px solid #dee2e6;
    color: #6c757d;
}

.timeline-step.completed .timeline-icon {
    background: #198754;
    border-color: #198754;
    color: white;
}

.timeline-step.active .timeline-icon {
    background: #ffc107;
    border-color: #ffc107;
    color: white;
    animation: pulse 2s infinite;
}

.timeline-step.pending .timeline-icon {
    background: #f8f9fa;
    border-color: #dee2e6;
    color: #adb5bd;
}

.timeline-label {
    font-weight: 600;
    margin-top: 5px;
}

.timeline-step.completed::before {
    background: #198754;
}

.timeline-step.active::before {
    background: linear-gradient(to right, #198754, #ffc107);
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
    }
}

.accordion-button.bg-success {
    background-color: #d1e7dd !important;
    color: #0a3622 !important;
}

.accordion-button.bg-success:not(.collapsed) {
    background-color: #d1e7dd !important;
    color: #0a3622 !important;
}
</style>
@endsection
