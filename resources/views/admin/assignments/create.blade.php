@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Tugaskan Reviewer')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-plus"></i> Form Assign Reviewer
            </div>
            <div class="card-body">
                <form action="{{ route('admin.assignments.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('article_title') is-invalid @enderror" 
                               name="article_title" value="{{ old('article_title') }}" 
                               placeholder="Masukkan judul artikel" required>
                        @error('article_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor Artikel <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('article_number') is-invalid @enderror" 
                               name="article_number" value="{{ old('article_number') }}" 
                               placeholder="Contoh: ART-2026-001" required>
                        @error('article_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link Submit <span class="text-danger">*</span></label>
                        <input type="url" class="form-control @error('submit_link') is-invalid @enderror" 
                               name="submit_link" value="{{ old('submit_link') }}" 
                               placeholder="https://" required>
                        @error('submit_link')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                        <label class="form-label">Pilih Reviewer 1 <span class="text-danger">*</span></label>
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
            </div>
        </div>
    </div>

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
</div>

@push('scripts')
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
                
                // Check for duplicate reviewers
                checkDuplicateReviewers(num);
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
    
    function checkDuplicateReviewers(currentNum) {
        const allSelects = document.querySelectorAll('select[id^="reviewer"]');
        const selectedValues = [];
        
        allSelects.forEach((select, index) => {
            if (select.value) {
                selectedValues.push({
                    num: index + 1,
                    value: select.value,
                    select: select,
                    search: document.getElementById(`searchReviewer${index + 1}`)
                });
            }
        });
        
        // Check for duplicates
        const valueMap = {};
        selectedValues.forEach(item => {
            if (valueMap[item.value]) {
                alert(`Reviewer ${item.num} sama dengan Reviewer ${valueMap[item.value].num}. Silakan pilih reviewer yang berbeda.`);
                item.select.value = '';
                item.search.value = '';
            } else {
                valueMap[item.value] = item;
            }
        });
    }
    
    // Initialize reviewer 1
    initializeReviewerSearch(searchReviewer1, reviewer1, 1);
});
</script>
@endpush

@endsection

