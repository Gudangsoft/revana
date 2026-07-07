@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Tugaskan Reviewer')

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

@if(isset($preselectedReviewer))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle"></i> Reviewer <strong>{{ $preselectedReviewer->name }}</strong> telah dipilih secara otomatis. 
        @if($journalCount > 1)
            Silakan lengkapi form penugasan untuk <strong>{{ $journalCount }} jurnal</strong> di bawah.
        @else
            Silakan lengkapi form penugasan di bawah.
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-plus"></i> Form Assign Reviewer
                @if($journalCount > 1)
                    <span class="badge bg-primary">{{ $journalCount }} Jurnal</span>
                @endif
            </div>
            <div class="card-body">
                @if($journalCount > 1)
                    {{-- Multi-Journal Form --}}
                    <form action="{{ route('admin.assignments.store-batch') }}" method="POST" id="multiJournalForm">
                        @csrf
                        
                        <input type="hidden" name="reviewer_id" value="{{ $preselectedReviewer->id ?? '' }}">
                        <input type="hidden" name="review_request_id" value="{{ $reviewRequestId ?? '' }}">
                        
                        {{-- Reviewer Info --}}
                        @if(isset($preselectedReviewer))
                            <div class="alert alert-primary mb-4">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 class="mb-2"><i class="bi bi-person-check"></i> Informasi Reviewer</h6>
                                        <p class="mb-1"><strong>Nama:</strong> {{ $preselectedReviewer->name }}</p>
                                        <p class="mb-1"><strong>Email:</strong> {{ $preselectedReviewer->email }}</p>
                                        @if($preselectedReviewer->institution)
                                            <p class="mb-1"><strong>Institusi:</strong> {{ $preselectedReviewer->institution }}</p>
                                        @endif
                                        @if($preselectedReviewer->fieldOfStudy)
                                            <p class="mb-0"><strong>Bidang Ilmu:</strong> <span class="badge bg-success">{{ $preselectedReviewer->fieldOfStudy->name }}</span></p>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Request:</strong> {{ $journalCount }} jurnal</p>
                                        @if($preselectedReviewer->article_languages)
                                            <p class="mb-0"><strong>Bahasa:</strong> 
                                                @foreach($preselectedReviewer->article_languages as $lang)
                                                    <span class="badge bg-secondary">{{ $lang }}</span>
                                                @endforeach
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Bulk Actions --}}
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="mb-3"><i class="bi bi-lightning"></i> Set Untuk Semua Jurnal</h6>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Deadline</label>
                                        <input type="date" class="form-control" id="bulkDeadline">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Bahasa</label>
                                        <select class="form-select" id="bulkLanguage">
                                            <option value="">-- Pilih --</option>
                                            <option value="Indonesia">Indonesia</option>
                                            <option value="Inggris">Inggris</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Bidang Ilmu</label>
                                        <select class="form-select" id="bulkFieldOfStudy">
                                            <option value="">-- Pilih --</option>
                                            @foreach($fieldOfStudies as $field)
                                                <option value="{{ $field->id }}" {{ (isset($preselectedReviewer) && $preselectedReviewer->field_of_study_id == $field->id) ? 'selected' : '' }}>
                                                    {{ $field->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-primary w-100" id="applyBulkBtn">
                                            <i class="bi bi-check-all"></i> Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Journal Accordion --}}
                        <div class="accordion" id="journalAccordion">
                            @for($i = 0; $i < $journalCount; $i++)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $i }}">
                                        <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $i }}" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}">
                                            <i class="bi bi-journal-text me-2"></i> 
                                            <strong>Jurnal {{ $i + 1 }}</strong>
                                            <span class="ms-3 journal-title-preview text-muted" data-index="{{ $i }}">
                                                <small id="titlePreview{{ $i }}">Belum diisi</small>
                                            </span>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#journalAccordion">
                                        <div class="accordion-body">
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control journal-title @error('journals.' . $i . '.article_title') is-invalid @enderror" 
                                                           name="journals[{{ $i }}][article_title]" 
                                                           placeholder="Masukkan judul artikel"
                                                           data-index="{{ $i }}"
                                                           required>
                                                    @error('journals.' . $i . '.article_title')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Nomor Artikel <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control @error('journals.' . $i . '.article_number') is-invalid @enderror" 
                                                           name="journals[{{ $i }}][article_number]" 
                                                           placeholder="Contoh: ART-2026-{{ str_pad($i + 1, 3, '0', STR_PAD_LEFT) }}"
                                                           required>
                                                    @error('journals.' . $i . '.article_number')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Link Submit <span class="text-danger">*</span></label>
                                                    <input type="url" class="form-control @error('journals.' . $i . '.submit_link') is-invalid @enderror" 
                                                           name="journals[{{ $i }}][submit_link]" 
                                                           placeholder="https://"
                                                           required>
                                                    @error('journals.' . $i . '.submit_link')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label">Deadline <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control journal-deadline @error('journals.' . $i . '.deadline') is-invalid @enderror" 
                                                           name="journals[{{ $i }}][deadline]"
                                                           required>
                                                    @error('journals.' . $i . '.deadline')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label">Bahasa <span class="text-danger">*</span></label>
                                                    <select class="form-select journal-language @error('journals.' . $i . '.language') is-invalid @enderror" 
                                                            name="journals[{{ $i }}][language]"
                                                            required>
                                                        <option value="">-- Pilih --</option>
                                                        <option value="Indonesia">Indonesia</option>
                                                        <option value="Inggris">Inggris</option>
                                                    </select>
                                                    @error('journals.' . $i . '.language')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label">Bidang Ilmu <span class="text-danger">*</span></label>
                                                    <select class="form-select journal-field @error('journals.' . $i . '.field_of_study_id') is-invalid @enderror" 
                                                            name="journals[{{ $i }}][field_of_study_id]"
                                                            required>
                                                        <option value="">-- Pilih --</option>
                                                        @foreach($fieldOfStudies as $field)
                                                            <option value="{{ $field->id }}" {{ (isset($preselectedReviewer) && $preselectedReviewer->field_of_study_id == $field->id) ? 'selected' : '' }}>
                                                                {{ $field->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('journals.' . $i . '.field_of_study_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Username untuk Reviewer <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control @error('journals.' . $i . '.reviewer_1_username') is-invalid @enderror" 
                                                           name="journals[{{ $i }}][reviewer_1_username]" 
                                                           placeholder="Username untuk reviewer"
                                                           required>
                                                    @error('journals.' . $i . '.reviewer_1_username')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Password untuk Reviewer <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control @error('journals.' . $i . '.reviewer_1_password') is-invalid @enderror" 
                                                           name="journals[{{ $i }}][reviewer_1_password]" 
                                                           placeholder="Password untuk reviewer"
                                                           required>
                                                    @error('journals.' . $i . '.reviewer_1_password')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Simpan Semua Assignment ({{ $journalCount }} Jurnal)
                            </button>
                        </div>
                    </form>
                @else
                    {{-- Single Journal Form (existing form) --}}
                    <form action="{{ route('admin.assignments.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                    {{-- Data dari Pengelolaan Jurnal --}}
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="bi bi-database"></i> Ambil Data dari Pengelolaan Jurnal</h6>
                            <div class="mb-3">
                                <label class="form-label">Pilih Submission <small class="text-muted">(Opsional - pilih untuk mengisi otomatis)</small></label>
                                <input type="text" class="form-control mb-2" id="searchSubmission" placeholder="🔍 Cari kode artikel atau judul..." autocomplete="off">
                                <div class="row g-2 mb-2">
                                    <div class="col-6 col-md-4">
                                        <label class="form-label form-label-sm mb-1 text-muted">Tanggal submit dari</label>
                                        <input type="date" class="form-control form-control-sm" id="submissionDateFrom">
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <label class="form-label form-label-sm mb-1 text-muted">sampai</label>
                                        <input type="date" class="form-control form-control-sm" id="submissionDateTo">
                                    </div>
                                    <div class="col-12 col-md-4 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClearSubmissionDate">
                                            <i class="bi bi-x-circle me-1"></i>Hapus filter tanggal
                                        </button>
                                    </div>
                                </div>
                                <select class="form-select" id="submissionSelect" size="5" style="height: auto;">
                                    <option value="">-- Pilih Submission atau Input Manual --</option>
                                    @if(isset($submissions))
                                    @foreach($submissions as $sub)
                                        <option value="{{ $sub->id }}"
                                                data-article-id="{{ $sub->id_artikel }}"
                                                data-article-title="{{ $sub->judul_artikel }}"
                                                data-article-link="{{ $sub->link_artikel }}"
                                                data-journal-name="{{ $sub->journalSlot?->journalMaster?->nama_jurnal ?? '' }}">
                                            [{{ $sub->id_artikel }}] {{ Str::limit($sub->judul_artikel, 50) }} - {{ $sub->journalSlot?->journalMaster?->nama_jurnal ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                    @endif
                                </select>
                                <small class="text-muted">Menampilkan 30 submission terbaru. Ketik untuk mencari di seluruh data (termasuk yang lebih lama). Memilih submission akan mengisi otomatis Nomor Artikel, Judul Artikel, dan Link Submit</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('article_title') is-invalid @enderror" 
                               name="article_title" id="article_title" value="{{ old('article_title') }}" 
                               placeholder="Masukkan judul artikel" required>
                        @error('article_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor Artikel <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('article_number') is-invalid @enderror" 
                               name="article_number" id="article_number" value="{{ old('article_number') }}" 
                               placeholder="Contoh: ART-2026-001" required>
                        @error('article_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link Submit <span class="text-danger">*</span></label>
                        <input type="url" class="form-control @error('submit_link') is-invalid @enderror" 
                               name="submit_link" id="submit_link" value="{{ old('submit_link') }}" 
                               placeholder="https://" required>
                        @error('submit_link')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload File Artikel (Word/PDF)</label>
                        <input type="file" class="form-control @error('article_file') is-invalid @enderror" 
                               name="article_file" accept=".doc,.docx,.pdf">
                        @error('article_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">File artikel yang akan direview. Format: DOC, DOCX, PDF. Maksimal 10MB.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deadline <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('deadline') is-invalid @enderror" 
                               name="deadline" value="{{ old('deadline') }}" required>
                        @error('deadline')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bahasa <span class="text-danger">*</span></label>
                        <select class="form-select @error('language') is-invalid @enderror" 
                                name="language" required>
                            <option value="Indonesia" {{ old('language') == 'Indonesia' ? 'selected' : '' }}>Indonesia</option>
                            <option value="Inggris" {{ old('language') == 'Inggris' ? 'selected' : '' }}>Inggris</option>
                        </select>
                        @error('language')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bidang/Section/Topik <span class="text-danger">*</span></label>
                        <select class="form-select @error('field_of_study_id') is-invalid @enderror" 
                                name="field_of_study_id" required>
                            <option value="">-- Pilih Bidang Ilmu --</option>
                            @foreach($fieldOfStudies as $field)
                            <option value="{{ $field->id }}" {{ old('field_of_study_id') == $field->id ? 'selected' : '' }}>
                                {{ $field->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('field_of_study_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Bidang ini akan muncul otomatis di form reviewer & PDF</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih Reviewer 1 <span class="text-danger">*</span></label>
                        @if(isset($preselectedReviewer))
                            <div class="alert alert-success mb-2">
                                <i class="bi bi-person-check"></i> <strong>{{ $preselectedReviewer->name }}</strong> - {{ $preselectedReviewer->email }}
                                @if($preselectedReviewer->institution)
                                    <br><small class="text-muted">{{ $preselectedReviewer->institution }}</small>
                                @endif
                            </div>
                            <input type="hidden" name="reviewer_id" value="{{ $preselectedReviewer->id }}">
                        @else
                            <input type="text" class="form-control mb-2" id="searchReviewer1" placeholder="🔍 Ketik untuk mencari reviewer (nama atau email)..." autocomplete="off">
                            <select class="form-select @error('reviewer_id') is-invalid @enderror" 
                                    name="reviewer_id" id="reviewer1" size="5" required style="height: 200px; display: none;">
                                <option value="">-- Pilih Reviewer 1 --</option>
                                @foreach($reviewers as $reviewer)
                                <option value="{{ $reviewer->id }}" {{ old('reviewer_id') == $reviewer->id ? 'selected' : '' }}
                                        data-search="{{ strtolower($reviewer->name . ' ' . $reviewer->email) }}"
                                        data-name="{{ $reviewer->name }}"
                                        data-email="{{ $reviewer->email }}">
                                    {{ $reviewer->name }} - {{ $reviewer->email }}
                                    @if($reviewer->article_languages)
                                        [{{ implode(', ', $reviewer->article_languages) }}]
                                    @endif
                                    ({{ $reviewer->completed_reviews }} reviews, {{ $reviewer->total_points }} pts)
                                </option>
                                @endforeach
                            </select>
                            @error('reviewer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                        
                        <!-- Username & Password for Reviewer 1 -->
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('reviewer_1_username') is-invalid @enderror" 
                                       name="reviewer_1_username" value="{{ old('reviewer_1_username') }}" 
                                       placeholder="Username untuk Reviewer 1" required>
                                @error('reviewer_1_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('reviewer_1_password') is-invalid @enderror" 
                                       name="reviewer_1_password" value="{{ old('reviewer_1_password') }}" 
                                       placeholder="Password untuk Reviewer 1" required>
                                @error('reviewer_1_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Container for additional reviewers -->
                    <div id="additionalReviewers"></div>

                    <!-- Button to add more reviewers -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addReviewerBtn">
                            <i class="bi bi-plus-circle"></i> Tambah Reviewer
                        </button>
                        <small class="text-muted d-block mt-1">Maksimal 5 reviewer per assignment</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Assign Reviewer
                        </button>
                        <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
                @endif
                {{-- End Single/Multi Journal Form --}}
            </div>
        </div>
    </div>

    @if($journalCount == 1)
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Informasi
            </div>
            <div class="card-body">
                <h6>Tips Assign Reviewer:</h6>
                <ul class="small">
                    <li>Pilih reviewer yang sesuai dengan bidang artikel</li>
                    <li>Perhatikan beban kerja reviewer saat ini</li>
                    <li>Reviewer akan menerima notifikasi tugas baru</li>
                    <li>Reviewer bisa menerima atau menolak tugas</li>
                </ul>
                <hr>
                <p class="mb-0 small text-muted">
                    Setelah reviewer menyelesaikan dan hasil review disetujui, 
                    reviewer akan mendapatkan point.
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-people"></i> Statistik Reviewer
            </div>
            <div class="card-body">
                <p class="mb-0"><strong>Total Reviewer:</strong> {{ $reviewers->count() }}</p>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
@if($journalCount > 1)
{{-- Multi-Journal JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update title preview
    const titleInputs = document.querySelectorAll('.journal-title');
    titleInputs.forEach(input => {
        input.addEventListener('input', function() {
            const index = this.getAttribute('data-index');
            const preview = document.getElementById('titlePreview' + index);
            if (preview) {
                preview.textContent = this.value || 'Belum diisi';
            }
        });
    });

    // Bulk Apply functionality
    const applyBulkBtn = document.getElementById('applyBulkBtn');
    applyBulkBtn.addEventListener('click', function() {
        const bulkDeadline = document.getElementById('bulkDeadline').value;
        const bulkLanguage = document.getElementById('bulkLanguage').value;
        const bulkFieldOfStudy = document.getElementById('bulkFieldOfStudy').value;

        let appliedCount = 0;
        
        if (bulkDeadline) {
            document.querySelectorAll('.journal-deadline').forEach(input => {
                input.value = bulkDeadline;
                appliedCount++;
            });
        }
        
        if (bulkLanguage) {
            document.querySelectorAll('.journal-language').forEach(select => {
                select.value = bulkLanguage;
            });
        }
        
        if (bulkFieldOfStudy) {
            document.querySelectorAll('.journal-field').forEach(select => {
                select.value = bulkFieldOfStudy;
            });
        }

        if (appliedCount > 0 || bulkLanguage || bulkFieldOfStudy) {
            // Show success feedback
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Diterapkan!';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 2000);
        } else {
            alert('Silakan pilih minimal satu field untuk diterapkan ke semua jurnal');
        }
    });

    // Form validation before submit
    const form = document.getElementById('multiJournalForm');
    form.addEventListener('submit', function(e) {
        let isValid = true;
        let emptyCount = 0;

        // Check if all required fields are filled
        const titleInputs = document.querySelectorAll('.journal-title');
        titleInputs.forEach(input => {
            if (!input.value.trim()) {
                emptyCount++;
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert(`Masih ada ${emptyCount} jurnal yang belum diisi judulnya. Silakan lengkapi semua form.`);
            return false;
        }

        // Confirm before submit
        if (!confirm('Anda akan membuat {{ $journalCount }} assignment sekaligus untuk reviewer {{ $preselectedReviewer->name ?? "" }}. Lanjutkan?')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@else
{{-- Single Journal JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    let reviewerCount = 1;
    const maxReviewers = 5;
    const reviewersData = @json($reviewers);
    
    const reviewer1 = document.getElementById('reviewer1');
    const searchReviewer1 = document.getElementById('searchReviewer1');
    
    // Dynamic reviewer management
    const addReviewerBtn = document.getElementById('addReviewerBtn');
    const additionalReviewersContainer = document.getElementById('additionalReviewers');
    
    if (addReviewerBtn) {
        addReviewerBtn.addEventListener('click', function() {
            if (reviewerCount >= maxReviewers) {
                alert('Maksimal 5 reviewer per assignment');
                return;
            }
            
            reviewerCount++;
            addReviewerField(reviewerCount);
            
            if (reviewerCount >= maxReviewers) {
                addReviewerBtn.disabled = true;
                addReviewerBtn.classList.add('disabled');
            }
        });
    }
    
    function addReviewerField(num) {
        const isRequired = num === 1 ? 'required' : '';
        const label = num === 1 ? '<span class="text-danger">*</span>' : '<span class="text-muted">(Opsional)</span>';
        
        const reviewerHtml = `
            <div class="mb-3 reviewer-field" id="reviewerField${num}" data-reviewer-num="${num}">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label">Pilih Reviewer ${num} ${label}</label>
                    ${num > 1 ? `<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeReviewer(${num})">
                        <i class="bi bi-trash"></i> Hapus
                    </button>` : ''}
                </div>
                <input type="text" class="form-control mb-2" id="searchReviewer${num}" 
                       placeholder="🔍 Ketik untuk mencari reviewer (nama atau email)..." autocomplete="off">
                <select class="form-select" name="reviewer_${num}_id" id="reviewer${num}" 
                        size="5" ${isRequired} style="height: 200px; display: none;">
                    <option value="">-- Pilih Reviewer ${num} --</option>
                    ${reviewersData.map(reviewer => `
                        <option value="${reviewer.id}" 
                                data-search="${(reviewer.name + ' ' + reviewer.email).toLowerCase()}"
                                data-name="${reviewer.name}"
                                data-email="${reviewer.email}">
                            ${reviewer.name} - ${reviewer.email}
                            ${reviewer.article_languages ? '[' + reviewer.article_languages.join(', ') + ']' : ''}
                            (${reviewer.completed_reviews} reviews, ${reviewer.total_points} pts)
                        </option>
                    `).join('')}
                </select>
                
                <!-- Username & Password for this reviewer -->
                <div class="row mt-2">
                    <div class="col-md-6">
                        <input type="text" class="form-control" 
                               name="reviewer_${num}_username" 
                               placeholder="Username untuk Reviewer ${num}" ${isRequired}>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" 
                               name="reviewer_${num}_password" 
                               placeholder="Password untuk Reviewer ${num}" ${isRequired}>
                    </div>
                </div>
            </div>
        `;
        
        additionalReviewersContainer.insertAdjacentHTML('beforeend', reviewerHtml);
        
        // Initialize search for new reviewer field
        const searchInput = document.getElementById(`searchReviewer${num}`);
        const selectElement = document.getElementById(`reviewer${num}`);
        initializeReviewerSearch(searchInput, selectElement, num);
    }
    
    window.removeReviewer = function(num) {
        const field = document.getElementById(`reviewerField${num}`);
        if (field) {
            field.remove();
            reviewerCount--;
            addReviewerBtn.disabled = false;
            addReviewerBtn.classList.remove('disabled');
        }
    };
    
    function initializeReviewerSearch(searchInput, selectElement, num) {
        if (!searchInput || !selectElement) return;
        
        // Focus handler
        searchInput.addEventListener('focus', function() {
            if (this.value.length > 0) {
                selectElement.style.display = 'block';
            }
        });
        
        // Input handler for search and filter
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const options = selectElement.querySelectorAll('option');
            let visibleCount = 0;
            
            options.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'none';
                    return;
                }
                
                const searchData = option.getAttribute('data-search') || '';
                if (searchData.includes(searchTerm)) {
                    option.style.display = 'block';
                    visibleCount++;
                } else {
                    option.style.display = 'none';
                }
            });
            
            if (visibleCount > 0 && searchTerm.length > 0) {
                selectElement.style.display = 'block';
            } else if (searchTerm.length === 0) {
                selectElement.style.display = 'none';
            }
        });
        
        // Selection handler
        selectElement.addEventListener('change', function() {
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                const name = selectedOption.getAttribute('data-name');
                const email = selectedOption.getAttribute('data-email');
                
                searchInput.value = name + ' - ' + email;
                this.style.display = 'none';
            }
        });
        
        // Clear selection when typing again
        searchInput.addEventListener('keydown', function() {
            if (selectElement.value) {
                selectElement.value = '';
            }
        });
        
        // Hide select when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !selectElement.contains(e.target)) {
                if (selectElement.value === '') {
                    selectElement.style.display = 'none';
                }
            }
        });
    }
    
    // Initialize reviewer 1 if exists
    if (searchReviewer1 && reviewer1) {
        initializeReviewerSearch(searchReviewer1, reviewer1, 1);
    }
    
    // Submission search and auto-fill functionality
    const searchSubmission = document.getElementById('searchSubmission');
    const submissionSelect = document.getElementById('submissionSelect');
    const dateFrom = document.getElementById('submissionDateFrom');
    const dateTo = document.getElementById('submissionDateTo');
    const btnClearDate = document.getElementById('btnClearSubmissionDate');
    const articleTitle = document.getElementById('article_title');
    const articleNumber = document.getElementById('article_number');
    const submitLink = document.getElementById('submit_link');

    if (searchSubmission && submissionSelect) {
        let searchDebounce = null;
        let searchAbortCtrl = null;

        function renderSubmissionOptions(items) {
            const frag = document.createDocumentFragment();
            const emptyOpt = document.createElement('option');
            emptyOpt.value = '';
            emptyOpt.textContent = '-- Pilih Submission atau Input Manual --';
            frag.appendChild(emptyOpt);

            items.forEach(function (sub) {
                const opt = document.createElement('option');
                opt.value = sub.id;
                opt.setAttribute('data-article-id', sub.id_artikel || '');
                opt.setAttribute('data-article-title', sub.judul_artikel || '');
                opt.setAttribute('data-article-link', sub.link_artikel || '');
                opt.setAttribute('data-journal-name', sub.nama_jurnal || '');
                const title = (sub.judul_artikel || '').length > 50
                    ? sub.judul_artikel.substring(0, 50) + '…'
                    : (sub.judul_artikel || '');
                const tanggal = sub.tanggal_submit ? ' (' + sub.tanggal_submit + ')' : '';
                opt.textContent = '[' + sub.id_artikel + '] ' + title + ' - ' + (sub.nama_jurnal || 'N/A') + tanggal;
                frag.appendChild(opt);
            });

            submissionSelect.innerHTML = '';
            submissionSelect.appendChild(frag);
        }

        // Cari langsung ke server (bukan cuma filter opsi yang sudah dimuat) supaya
        // submission lama tidak "hilang" gara-gara batas jumlah data yang di-preload.
        function performSubmissionSearch() {
            const term = searchSubmission.value.trim();
            const params = new URLSearchParams({ q: term });
            if (dateFrom.value) params.set('tanggal_dari', dateFrom.value);
            if (dateTo.value) params.set('tanggal_sampai', dateTo.value);

            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(function () {
                if (searchAbortCtrl) searchAbortCtrl.abort();
                searchAbortCtrl = new AbortController();

                fetch('{{ route("admin.assignments.search-submissions") }}?' + params.toString(), {
                    signal: searchAbortCtrl.signal,
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function (res) { return res.json(); })
                    .then(function (items) { renderSubmissionOptions(items); })
                    .catch(function (err) { if (err.name !== 'AbortError') console.error(err); });
            }, 300);
        }

        searchSubmission.addEventListener('input', performSubmissionSearch);
        dateFrom.addEventListener('change', performSubmissionSearch);
        dateTo.addEventListener('change', performSubmissionSearch);
        btnClearDate.addEventListener('click', function () {
            dateFrom.value = '';
            dateTo.value = '';
            performSubmissionSearch();
        });

        // Auto-fill when submission selected
        submissionSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value) {
                const articleId = selectedOption.getAttribute('data-article-id');
                const title = selectedOption.getAttribute('data-article-title');
                const link = selectedOption.getAttribute('data-article-link');
                
                if (articleNumber) articleNumber.value = articleId || '';
                if (articleTitle) articleTitle.value = title || '';
                if (submitLink && link) submitLink.value = link;
                
                // Show confirmation
                searchSubmission.value = '[' + articleId + '] ' + (title ? title.substring(0, 50) : '');
            }
        });
    }
});
</script>
@endif
@endpush

@endsection

