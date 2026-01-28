@extends('pic.layouts.app')

@section('title', 'Edit Submit Fasttrack')
@section('page-title', '')
@section('sidebar-class', '')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="page-header">
    <h4><i class="fas fa-edit mr-2"></i> Edit Submit Fasttrack</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('pic.fasttrack.index') }}">Fasttrack</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pic.fasttrack.monitoring') }}">Monitoring</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Submit Fasttrack: {{ $submission->submission_code }}
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('pic.fasttrack.update', $submission->id) }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="journal_slot_id">
                                    <i class="fas fa-book mr-1"></i>
                                    Jurnal & Slot
                                </label>
                                <select name="journal_slot_id" id="journal_slot_id" class="form-control" required>
                                    <option value="">Pilih Jurnal & Slot</option>
                                    @foreach($journals as $journal)
                                        @php
                                            $journalSlots = $slots->where('journal_master_id', $journal->id);
                                        @endphp
                                        @if($journalSlots->count() > 0)
                                            <optgroup label="{{ $journal->nama_jurnal }}">
                                                @foreach($journalSlots as $slot)
                                                    <option value="{{ $slot->id }}" 
                                                            {{ $submission->journal_slot_id == $slot->id ? 'selected' : '' }}>
                                                        Vol {{ $slot->volume }}, No {{ $slot->issue }} - {{ $slot->bulan }}/{{ $slot->tahun }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </select>
                                @error('journal_slot_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="title">
                                    <i class="fas fa-heading mr-1"></i>
                                    Judul Artikel <span class="text-danger">*</span>
                                </label>
                                <textarea name="title" id="title" class="form-control" rows="3" required>{{ old('title', $submission->title) }}</textarea>
                                @error('title')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="authors">
                                    <i class="fas fa-users mr-1"></i>
                                    Penulis <span class="text-danger">*</span>
                                </label>
                                <textarea name="authors" id="authors" class="form-control" rows="3" required>{{ old('authors', $submission->authors) }}</textarea>
                                <small class="form-text text-muted">
                                    Pisahkan nama penulis dengan koma
                                </small>
                                @error('authors')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="abstract">
                                    <i class="fas fa-file-alt mr-1"></i>
                                    Abstract
                                </label>
                                <textarea name="abstract" id="abstract" class="form-control" rows="5">{{ old('abstract', $submission->abstract) }}</textarea>
                                @error('abstract')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="keywords">
                                    <i class="fas fa-tags mr-1"></i>
                                    Keywords
                                </label>
                                <textarea name="keywords" id="keywords" class="form-control" rows="2">{{ old('keywords', $submission->keywords) }}</textarea>
                                <small class="form-text text-muted">
                                    Pisahkan keyword dengan koma
                                </small>
                                @error('keywords')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="volume_number">
                                            <i class="fas fa-book-open mr-1"></i>
                                            Volume
                                        </label>
                                        <input type="number" name="volume_number" id="volume_number" 
                                               class="form-control" value="{{ old('volume_number', $submission->volume_number) }}">
                                        @error('volume_number')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="issue_number">
                                            <i class="fas fa-bookmark mr-1"></i>
                                            Issue
                                        </label>
                                        <input type="number" name="issue_number" id="issue_number" 
                                               class="form-control" value="{{ old('issue_number', $submission->issue_number) }}">
                                        @error('issue_number')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="start_page">
                                            <i class="fas fa-file-alt mr-1"></i>
                                            Halaman Awal
                                        </label>
                                        <input type="number" name="start_page" id="start_page" 
                                               class="form-control" value="{{ old('start_page', $submission->start_page) }}">
                                        @error('start_page')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_page">
                                            <i class="fas fa-file-alt mr-1"></i>
                                            Halaman Akhir
                                        </label>
                                        <input type="number" name="end_page" id="end_page" 
                                               class="form-control" value="{{ old('end_page', $submission->end_page) }}">
                                        @error('end_page')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="marketing">
                                    <i class="fas fa-bullhorn mr-1"></i>
                                    Marketing
                                </label>
                                <input type="text" name="marketing" id="marketing" 
                                       class="form-control" value="{{ old('marketing', $submission->marketing) }}">
                                <small class="form-text text-muted">
                                    Opsional - Tidak wajib diisi
                                </small>
                                @error('marketing')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="link_publish">
                                    <i class="fas fa-link mr-1"></i>
                                    Link Publish
                                </label>
                                <input type="url" name="link_publish" id="link_publish" 
                                       class="form-control" value="{{ old('link_publish', $submission->link_publish) }}"
                                       placeholder="https://example.com/article">
                                <small class="form-text text-muted">
                                    Opsional - Tidak wajib diisi
                                </small>
                                @error('link_publish')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="file_artikel">
                                    <i class="fas fa-file-pdf mr-1"></i>
                                    File Artikel (PDF)
                                </label>
                                <input type="file" name="file_artikel" id="file_artikel" 
                                       class="form-control-file" accept=".pdf">
                                @if($submission->file_artikel)
                                    <small class="form-text text-info">
                                        <i class="fas fa-file-pdf mr-1"></i>
                                        File saat ini: <a href="#" class="text-info">{{ basename($submission->file_artikel) }}</a>
                                    </small>
                                @endif
                                <small class="form-text text-muted">
                                    Kosongkan jika tidak ingin mengubah file
                                </small>
                                @error('file_artikel')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('pic.fasttrack.monitoring') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left mr-2"></i>
                                        Kembali
                                    </a>
                                    <a href="{{ route('pic.fasttrack.show', $submission->id) }}" class="btn btn-info">
                                        <i class="fas fa-eye mr-2"></i>
                                        Detail
                                    </a>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save mr-2"></i>
                                        Update Submit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Additional Info Card -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle mr-2"></i>
                    Informasi Submit
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Kode Submit:</strong><br>
                        <span class="text-primary">{{ $submission->submission_code }}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Status Validasi:</strong><br>
                        @if($submission->is_validated)
                            <span class="badge badge-success">
                                <i class="fas fa-check-circle mr-1"></i>
                                Sudah Validasi
                            </span>
                        @else
                            <span class="badge badge-warning">
                                <i class="fas fa-clock mr-1"></i>
                                Belum Validasi
                            </span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <strong>Tanggal Submit:</strong><br>
                        {{ $submission->created_at->format('d M Y H:i') }}
                    </div>
                </div>
                
                @if($submission->updated_at != $submission->created_at)
                    <hr class="my-3">
                    <div class="row">
                        <div class="col-12">
                            <strong>Terakhir Diupdate:</strong><br>
                            <span class="text-muted">{{ $submission->updated_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-resize textareas
    $('textarea').each(function() {
        this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
    }).on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Form validation
    $('form').on('submit', function(e) {
        let hasError = false;
        
        // Required field validation
        $(this).find('[required]').each(function() {
            if (!$(this).val().trim()) {
                hasError = true;
                $(this).addClass('is-invalid');
                if (!$(this).next('.text-danger').length) {
                    $(this).after('<div class="text-danger small mt-1">Field ini wajib diisi</div>');
                }
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.text-danger').remove();
            }
        });
        
        if (hasError) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi');
        }
    });
    
    // Remove validation error on input
    $('[required]').on('input change', function() {
        if ($(this).val().trim()) {
            $(this).removeClass('is-invalid');
            $(this).next('.text-danger').remove();
        }
    });
});
</script>
@endpush