@extends('layouts.app')

@section('title', ' - Tambah Artikel')
@section('page-title', 'Tambah Artikel Baru')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <i class="bi bi-plus-circle"></i> Form Tambah Artikel
        </div>
        <div class="card-body">
            <form action="{{ route('admin.articles.store') }}" method="POST">
                @csrf

                <!-- Nav tabs -->
                <ul class="nav nav-tabs mb-3" id="articleTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                            <i class="bi bi-info-circle"></i> Info Dasar
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="author-tab" data-bs-toggle="tab" data-bs-target="#author" type="button" role="tab">
                            <i class="bi bi-person"></i> Data Author
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="workflow-tab" data-bs-toggle="tab" data-bs-target="#workflow" type="button" role="tab">
                            <i class="bi bi-diagram-3"></i> Workflow
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="links-tab" data-bs-toggle="tab" data-bs-target="#links" type="button" role="tab">
                            <i class="bi bi-link-45deg"></i> Links & Dates
                        </button>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <!-- Basic Info Tab -->
                    <div class="tab-pane fade show active" id="basic" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nomor Artikel <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('article_number') is-invalid @enderror" 
                                       name="article_number" value="{{ old('article_number') }}" required>
                                @error('article_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jurnal <span class="text-danger">*</span></label>
                                <select class="form-select @error('journal_id') is-invalid @enderror" name="journal_id" required>
                                    <option value="">Pilih Jurnal</option>
                                    @foreach($journals as $journal)
                                        <option value="{{ $journal->id }}" {{ old('journal_id') == $journal->id ? 'selected' : '' }}>
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
                                       name="title" value="{{ old('title') }}" required>
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                                    <option value="SUBMITTED" {{ old('status') == 'SUBMITTED' ? 'selected' : '' }}>SUBMITTED</option>
                                    <option value="REVIEW" {{ old('status') == 'REVIEW' ? 'selected' : '' }}>REVIEW</option>
                                    <option value="REVISION" {{ old('status') == 'REVISION' ? 'selected' : '' }}>REVISION</option>
                                    <option value="COPYEDITING" {{ old('status') == 'COPYEDITING' ? 'selected' : '' }}>COPYEDITING</option>
                                    <option value="PRODUCTION" {{ old('status') == 'PRODUCTION' ? 'selected' : '' }}>PRODUCTION</option>
                                    <option value="PUBLISHED" {{ old('status') == 'PUBLISHED' ? 'selected' : '' }}>PUBLISHED</option>
                                    <option value="REJECTED" {{ old('status') == 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Marketing</label>
                                <input type="text" class="form-control @error('marketing') is-invalid @enderror" 
                                       name="marketing" value="{{ old('marketing') }}">
                                @error('marketing')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Author Info Tab -->
                    <div class="tab-pane fade" id="author" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Author <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('author_name') is-invalid @enderror" 
                                       name="author_name" value="{{ old('author_name') }}" required>
                                @error('author_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nomor WA Author</label>
                                <input type="text" class="form-control @error('author_phone') is-invalid @enderror" 
                                       name="author_phone" value="{{ old('author_phone') }}" placeholder="628xxxxxxxxxx">
                                @error('author_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username Author</label>
                                <input type="text" class="form-control @error('author_username') is-invalid @enderror" 
                                       name="author_username" value="{{ old('author_username') }}">
                                @error('author_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password Author</label>
                                <input type="text" class="form-control @error('author_password') is-invalid @enderror" 
                                       name="author_password" value="{{ old('author_password') }}">
                                @error('author_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIC</label>
                                <input type="text" class="form-control @error('pic') is-invalid @enderror" 
                                       name="pic" value="{{ old('pic') }}">
                                @error('pic')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Workflow Tab -->
                    <div class="tab-pane fade" id="workflow" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12 mb-3"><h6 class="text-primary">Editor 1</h6></div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Editor 1</label>
                                <input type="text" class="form-control" name="editor1" value="{{ old('editor1') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIC Editor 1</label>
                                <input type="text" class="form-control" name="pic_editor1" value="{{ old('pic_editor1') }}">
                            </div>

                            <div class="col-md-12 mb-3"><hr><h6 class="text-primary">Author 1</h6></div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Author 1</label>
                                <input type="text" class="form-control" name="author1" value="{{ old('author1') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIC Author 1</label>
                                <input type="text" class="form-control" name="pic_author1" value="{{ old('pic_author1') }}">
                            </div>

                            <div class="col-md-12 mb-3"><hr><h6 class="text-primary">Editor 2</h6></div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Editor 2</label>
                                <input type="text" class="form-control" name="editor2" value="{{ old('editor2') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIC Editor 2</label>
                                <input type="text" class="form-control" name="pic_editor2" value="{{ old('pic_editor2') }}">
                            </div>

                            <div class="col-md-12 mb-3"><hr><h6 class="text-primary">Reviewers</h6></div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reviewer 1</label>
                                <input type="text" class="form-control" name="reviewer1" value="{{ old('reviewer1') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIC Reviewer 1</label>
                                <input type="text" class="form-control" name="pic_reviewer1" value="{{ old('pic_reviewer1') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reviewer 2</label>
                                <input type="text" class="form-control" name="reviewer2" value="{{ old('reviewer2') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIC Reviewer 2</label>
                                <input type="text" class="form-control" name="pic_reviewer2" value="{{ old('pic_reviewer2') }}">
                            </div>

                            <div class="col-md-12 mb-3"><hr><h6 class="text-primary">Copyediting & Production</h6></div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIC Copyediting</label>
                                <input type="text" class="form-control" name="pic_copyediting" value="{{ old('pic_copyediting') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIC Production</label>
                                <input type="text" class="form-control" name="pic_production" value="{{ old('pic_production') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Links & Dates Tab -->
                    <div class="tab-pane fade" id="links" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12 mb-3"><h6 class="text-primary">Links</h6></div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Link Submit</label>
                                <input type="url" class="form-control @error('submit_link') is-invalid @enderror" 
                                       name="submit_link" value="{{ old('submit_link') }}" placeholder="https://...">
                                @error('submit_link')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Link Turnitin</label>
                                <input type="url" class="form-control @error('turnitin_link') is-invalid @enderror" 
                                       name="turnitin_link" value="{{ old('turnitin_link') }}" placeholder="https://...">
                                @error('turnitin_link')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Link LOA</label>
                                <input type="url" class="form-control @error('loa_link') is-invalid @enderror" 
                                       name="loa_link" value="{{ old('loa_link') }}" placeholder="https://...">
                                @error('loa_link')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Link Copyediting</label>
                                <input type="url" class="form-control @error('copyediting_link') is-invalid @enderror" 
                                       name="copyediting_link" value="{{ old('copyediting_link') }}" placeholder="https://...">
                                @error('copyediting_link')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Link Publication</label>
                                <input type="url" class="form-control @error('publication_link') is-invalid @enderror" 
                                       name="publication_link" value="{{ old('publication_link') }}" placeholder="https://...">
                                @error('publication_link')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3 mt-3"><hr><h6 class="text-primary">Tanggal</h6></div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tgl Submission</label>
                                <input type="date" class="form-control" name="submission_date" value="{{ old('submission_date') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tgl Review</label>
                                <input type="date" class="form-control" name="review_date" value="{{ old('review_date') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tgl Revision</label>
                                <input type="date" class="form-control" name="revision_date" value="{{ old('revision_date') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tgl Acceptance</label>
                                <input type="date" class="form-control" name="acceptance_date" value="{{ old('acceptance_date') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tgl Publication</label>
                                <input type="date" class="form-control" name="publication_date" value="{{ old('publication_date') }}">
                            </div>

                            <div class="col-md-12 mb-3 mt-3">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" name="notes" rows="3">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
