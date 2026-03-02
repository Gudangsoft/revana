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
                <!-- Edit Count Warning -->
                @php
                    $maxEditCount = \App\Services\FeatureSettingService::limit('max_fasttrack_edits');
                    $remainingEdits = $maxEditCount - ($submission->edit_count ?? 0);
                @endphp
                
                @if($remainingEdits <= 2)
                    <div class="alert {{ $remainingEdits == 1 ? 'alert-danger' : 'alert-warning' }} alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Perhatian!</strong> 
                        Submission ini sudah diedit <strong>{{ $submission->edit_count ?? 0 }}x</strong>. 
                        Sisa kesempatan edit: <strong>{{ $remainingEdits }}x</strong> lagi.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                
                <form action="{{ route('pic.fasttrack.update', $submission->id) }}" method="POST" enctype="multipart/form-data" id="editForm" novalidate>
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="search_journal">
                                    <i class="fas fa-book mr-1"></i>
                                    Jurnal <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="text" 
                                           class="form-control @error('journal_master_id') is-invalid @enderror" 
                                           id="search_journal" 
                                           placeholder="🔍 Ketik untuk mencari jurnal..." 
                                           value="{{ $submission->journalSlot->journalMaster->nama_jurnal ?? '' }}"
                                           autocomplete="off">
                                    <input type="hidden" name="journal_master_id" id="journal_master_id" value="{{ $submission->journalSlot->journal_master_id ?? '' }}">
                                    
                                    <!-- Dropdown hasil pencarian -->
                                    <div id="search_results" class="list-group position-absolute w-100 shadow" style="display: none; max-height: 350px; overflow-y: auto; z-index: 1050; background: white; border: 1px solid #dee2e6; border-radius: 0.25rem; margin-top: 2px;"></div>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle"></i> Ketik minimal 1 huruf untuk mencari
                                </small>
                                @error('journal_master_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="journal_slot_id">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Slot <span class="text-danger">*</span>
                                </label>
                                <select name="journal_slot_id" id="journal_slot_id" class="form-control @error('journal_slot_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Jurnal terlebih dahulu --</option>
                                </select>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> Slot akan ditampilkan setelah memilih jurnal
                                </small>
                                @error('journal_slot_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="judul_artikel">
                                    <i class="fas fa-heading mr-1"></i>
                                    Judul Artikel <span class="text-danger">*</span>
                                </label>
                                <textarea name="judul_artikel" id="judul_artikel" class="form-control" rows="3" required>{{ old('judul_artikel', $submission->judul_artikel) }}</textarea>
                                @error('judul_artikel')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="nama_penulis">
                                    <i class="fas fa-users mr-1"></i>
                                    Penulis <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nama_penulis" id="nama_penulis" class="form-control" value="{{ old('nama_penulis', $submission->nama_penulis) }}" required>
                                @error('nama_penulis')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="no_hp_penulis">
                                    <i class="fas fa-phone mr-1"></i>
                                    No HP Penulis
                                </label>
                                <input type="text" name="no_hp_penulis" id="no_hp_penulis" class="form-control" value="{{ old('no_hp_penulis', $submission->no_hp_penulis) }}">
                                @error('no_hp_penulis')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="notes">
                                    <i class="fas fa-file-alt mr-1"></i>
                                    Catatan
                                </label>
                                <textarea name="notes" id="notes" class="form-control" rows="5">{{ old('notes', $submission->notes) }}</textarea>
                                @error('notes')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="marketing_id">
                                    <i class="fas fa-bullhorn mr-1"></i>
                                    Marketing
                                </label>
                                <select name="marketing_id" id="marketing_id" class="form-control">
                                    <option value="">Pilih Marketing (Opsional)</option>
                                    @foreach($marketings as $marketing)
                                        <option value="{{ $marketing->id }}" {{ old('marketing_id', $submission->marketing_id) == $marketing->id ? 'selected' : '' }}>
                                            {{ $marketing->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    Opsional - Tidak wajib diisi
                                </small>
                                @error('marketing_id')
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== SCRIPT LOADED ===');
    
    const searchInput = document.getElementById('search_journal');
    const searchResults = document.getElementById('search_results');
    const journalMasterIdInput = document.getElementById('journal_master_id');
    const slotSelect = document.getElementById('journal_slot_id');
    const editForm = document.getElementById('editForm');
    
    console.log('Elements found:', {
        searchInput: !!searchInput,
        searchResults: !!searchResults,
        journalMasterIdInput: !!journalMasterIdInput,
        slotSelect: !!slotSelect,
        editForm: !!editForm
    });
    
    // All journals data
    const allJournals = @json($journals);
    const currentSlotId = {{ $submission->journal_slot_id ?? 'null' }};
    
    console.log('Total journals:', allJournals.length);
    console.log('Current slot ID:', currentSlotId);
    console.log('Journal Master ID:', journalMasterIdInput ? journalMasterIdInput.value : 'NOT FOUND');
    
    if (!searchInput || !searchResults || !journalMasterIdInput || !slotSelect) {
        console.error('Required elements not found!');
        return;
    }
    
    // Load initial slots if journal is selected
    if (journalMasterIdInput.value) {
        console.log('Loading initial slots for journal:', journalMasterIdInput.value);
        loadSlots(journalMasterIdInput.value, currentSlotId);
    }
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        
        console.log('Search query:', query);
        
        if (query.length === 0) {
            searchResults.style.display = 'none';
            return;
        }
        
        // Filter journals
        const filtered = allJournals.filter(j => 
            j.nama_jurnal.toLowerCase().includes(query) || 
            (j.publisher && j.publisher.toLowerCase().includes(query))
        ).slice(0, 15);
        
        console.log('Filtered journals:', filtered.length);
        
        if (filtered.length === 0) {
            searchResults.innerHTML = '<div class="list-group-item text-muted">Tidak ada hasil</div>';
        } else {
            searchResults.innerHTML = filtered.map(j => `
                <a href="#" class="list-group-item list-group-item-action" data-id="${j.id}" data-name="${j.nama_jurnal}">
                    <strong>${j.nama_jurnal}</strong>
                    <br><small class="text-muted">${j.publisher || '-'} | ${j.accreditation || '-'}</small>
                </a>
            `).join('');
        }
        
        searchResults.style.display = 'block';
    });
    
    // Click on search result
    searchResults.addEventListener('click', function(e) {
        e.preventDefault();
        const item = e.target.closest('.list-group-item');
        if (item && item.dataset.id) {
            journalMasterIdInput.value = item.dataset.id;
            searchInput.value = item.dataset.name;
            searchResults.style.display = 'none';
            
            console.log('Selected journal:', item.dataset.id);
            // Load slots
            loadSlots(item.dataset.id);
        }
    });
    
    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    
    // Load slots by journal
    function loadSlots(journalId, selectedSlotId = null) {
        console.log('loadSlots called with journalId:', journalId, 'selectedSlotId:', selectedSlotId);
        slotSelect.innerHTML = '<option value="">Memuat slot...</option>';
        
        const url = `{{ route('pic.journal-slots.get-by-journal') }}?journal_master_id=${journalId}`;
        console.log('Fetching from URL:', url);
        
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Slots received:', data);
                if (data.length === 0) {
                    slotSelect.innerHTML = '<option value="">-- Tidak ada slot tersedia --</option>';
                } else {
                    slotSelect.innerHTML = '<option value="">-- Pilih Slot --</option>' + 
                        data.map(s => {
                            const sisa = s.sisa !== undefined ? s.sisa : Math.max(0, s.jumlah_slot - s.slot_terpakai);
                            const isFull = s.is_full !== undefined ? s.is_full : (sisa <= 0);
                            const fullIndicator = isFull ? ' 🚫 PENUH - TIDAK TERSEDIA' : ` - Sisa: ${sisa}/${s.jumlah_slot} slot`;
                            const disabled = isFull ? ' disabled' : '';
                            const selected = selectedSlotId && s.id == selectedSlotId ? ' selected' : '';
                            const style = isFull ? ' style="color: #dc3545; font-weight: bold; background-color: #f8d7da;"' : (sisa <= 2 ? ' style="color: #fd7e14; font-weight: bold;"' : ' style="color: #28a745;"');
                            return `<option value="${s.id}"${disabled}${selected}${style}>${s.text}${fullIndicator}</option>`;
                        }).join('');
                }
            })
            .catch(error => {
                console.error('Error loading slots:', error);
                slotSelect.innerHTML = '<option value="">-- Error memuat slot --</option>';
                alert('Error memuat slot: ' + error.message);
            });
    }
    
    // Confirmation before submit
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            console.log('Form submit triggered');
            
            let hasError = false;
            
            // Required field validation
            const requiredFields = this.querySelectorAll('[required]');
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    hasError = true;
                    field.classList.add('is-invalid');
                    const existingError = field.nextElementSibling;
                    if (!existingError || !existingError.classList.contains('text-danger')) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'text-danger small mt-1';
                        errorDiv.textContent = 'Field ini wajib diisi';
                        field.parentNode.insertBefore(errorDiv, field.nextSibling);
                    }
                } else {
                    field.classList.remove('is-invalid');
                    const errorDiv = field.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('text-danger')) {
                        errorDiv.remove();
                    }
                }
            });
            
            if (hasError) {
                alert('Mohon lengkapi semua field yang wajib diisi');
                return false;
            }
            
            // Check if slot is full
            const selectedOption = slotSelect.options[slotSelect.selectedIndex];
            if (selectedOption && selectedOption.disabled) {
                alert('⚠️ Slot yang Anda pilih sudah PENUH!\\n\\nSilakan pilih slot lain yang masih tersedia.');
                slotSelect.value = '';
                slotSelect.focus();
                return false;
            }
            
            // Confirmation dialog
            const remainingEdits = {{ $remainingEdits ?? 3 }};
            let confirmMessage = '⚠️ KONFIRMASI PERUBAHAN ⚠️\\n\\n';
            confirmMessage += 'Apakah Anda yakin data yang diinput sudah BENAR dan SESUAI?\\n\\n';
            confirmMessage += '📝 Pastikan:\\n';
            confirmMessage += '✓ Jurnal & Slot sudah benar\\n';
            confirmMessage += '✓ Judul artikel sudah benar\\n';
            confirmMessage += '✓ Nama penulis sudah sesuai\\n';
            confirmMessage += '✓ Link publish sudah dicek\\n\\n';
            
            if (remainingEdits <= 2) {
                confirmMessage += '⚠️ PERHATIAN: Ini edit ke-{{ $submission->edit_count + 1 }}, sisa kesempatan: ' + (remainingEdits - 1) + 'x\\n\\n';
            }
            
            confirmMessage += 'Tekan OK untuk menyimpan atau Cancel untuk memeriksa kembali.';
            
            if (confirm(confirmMessage)) {
                console.log('Form confirmed, submitting...');
                // Submit the form
                this.submit();
            } else {
                console.log('Form submission cancelled by user');
            }
        });
    }
    
    // Auto-resize textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(function(textarea) {
        textarea.style.height = textarea.scrollHeight + 'px';
        textarea.style.overflowY = 'hidden';
        
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });
    
    // Remove validation error on input
    const allRequiredFields = document.querySelectorAll('[required]');
    allRequiredFields.forEach(function(field) {
        field.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                const errorDiv = this.nextElementSibling;
                if (errorDiv && errorDiv.classList.contains('text-danger')) {
                    errorDiv.remove();
                }
            }
        });
        
        field.addEventListener('change', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                const errorDiv = this.nextElementSibling;
                if (errorDiv && errorDiv.classList.contains('text-danger')) {
                    errorDiv.remove();
                }
            }
        });
    });
    
    console.log('=== SCRIPT INITIALIZATION COMPLETE ===');
});
</script>
@endsection
