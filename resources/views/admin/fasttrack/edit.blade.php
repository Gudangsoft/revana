@extends('layouts.app')

@section('title', 'Edit Fasttrack - ' . $appSettings['app_name'])
@section('page-title', 'Edit Submission Fasttrack')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== ADMIN EDIT SCRIPT LOADED ===');
    
    const searchInput = document.getElementById('search_journal');
    const searchResults = document.getElementById('search_results');
    const journalMasterIdInput = document.getElementById('journal_master_id');
    const slotSelect = document.getElementById('journal_slot_id');
    const editForm = document.getElementById('editForm');
    
    console.log('Elements found:', {
        searchInput: !!searchInput,
        searchResults: !!searchResults,
        journalMasterIdInput: !!journalMasterIdInput,
        slotSelect: !!slotSelect
    });
    
    // All journals data
    const allJournals = @json($journals);
    const currentSlotId = {{ $submission->journal_slot_id ?? 'null' }};
    
    console.log('Total journals:', allJournals.length);
    console.log('Current slot ID:', currentSlotId);
    
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
        console.log('loadSlots called with journalId:', journalId);
        slotSelect.innerHTML = '<option value="">Memuat slot...</option>';
        
        const url = `{{ route('admin.journal-slots.get-by-journal') }}?journal_master_id=${journalId}`;
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
                            const available = s.jumlah_slot - s.slot_terpakai;
                            const isFull = available <= 0;
                            const fullIndicator = isFull ? ' 🚫 PENUH' : ` - Sisa: ${available}/${s.jumlah_slot} slot`;
                            const disabled = isFull ? ' disabled' : '';
                            const selected = selectedSlotId && s.id == selectedSlotId ? ' selected' : '';
                            return `<option value="${s.id}"${disabled}${selected}>${s.text}${fullIndicator}</option>`;
                        }).join('');
                }
            })
            .catch(error => {
                console.error('Error loading slots:', error);
                slotSelect.innerHTML = '<option value="">-- Error memuat slot --</option>';
                alert('Error memuat slot: ' + error.message);
            });
    }
    
    // Form validation before submit
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            let hasError = false;
            
            // Required field validation
            const requiredFields = this.querySelectorAll('[required]');
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    hasError = true;
                    field.classList.add('is-invalid');
                    const existingError = field.nextElementSibling;
                    if (!existingError || !existingError.classList.contains('invalid-feedback')) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback d-block';
                        errorDiv.textContent = 'Field ini wajib diisi';
                        field.parentNode.insertBefore(errorDiv, field.nextSibling);
                    }
                } else {
                    field.classList.remove('is-invalid');
                    const errorDiv = field.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv.remove();
                    }
                }
            });
            
            if (hasError) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi');
                return false;
            }
            
            // Check if slot is full
            const selectedOption = slotSelect.options[slotSelect.selectedIndex];
            if (selectedOption && selectedOption.disabled) {
                e.preventDefault();
                alert('⚠️ Slot yang Anda pilih sudah PENUH!\\n\\nSilakan pilih slot lain yang masih tersedia.');
                slotSelect.value = '';
                slotSelect.focus();
                return false;
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
    const allFields = document.querySelectorAll('.form-control, .form-select');
    allFields.forEach(function(field) {
        field.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                const errorDiv = this.nextElementSibling;
                if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                    errorDiv.remove();
                }
            }
        });
    });
    
    console.log('=== ADMIN EDIT SCRIPT COMPLETE ===');
});
</script>
@endsection
@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <span><i class="bi bi-pencil"></i> Edit Submission Fasttrack</span>
                <span class="badge bg-dark">{{ $submission->kode_submit }}</span>
            </div>
            <div class="card-body">
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form action="{{ route('admin.fasttrack.update', $submission) }}" method="POST" id="editForm" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="search_journal" class="form-label">
                                    <i class="bi bi-journal"></i> Jurnal <span class="text-danger">*</span>
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
                                    <i class="bi bi-info-circle"></i> Ketik minimal 1 huruf untuk mencari
                                </small>
                                @error('journal_master_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="journal_slot_id" class="form-label">
                                    <i class="bi bi-calendar3"></i> Slot <span class="text-danger">*</span>
                                </label>
                                <select name="journal_slot_id" id="journal_slot_id" class="form-select @error('journal_slot_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Jurnal terlebih dahulu --</option>
                                </select>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> Slot akan ditampilkan setelah memilih jurnal
                                </small>
                                @error('journal_slot_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3"><i class="bi bi-file-text"></i> Data Artikel</h6>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="judul_artikel" class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('judul_artikel') is-invalid @enderror" 
                                          id="judul_artikel" 
                                          name="judul_artikel" 
                                          rows="2" 
                                          required>{{ old('judul_artikel', $submission->judul_artikel) }}</textarea>
                                @error('judul_artikel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="link_publish" class="form-label">Link Publish</label>
                                <input type="url" 
                                       class="form-control @error('link_publish') is-invalid @enderror" 
                                       id="link_publish" 
                                       name="link_publish" 
                                       placeholder="https://..." 
                                       value="{{ old('link_publish', $submission->link_publish) }}">
                                <small class="text-muted"><i class="bi bi-link-45deg"></i> Link artikel yang sudah publish (Opsional: Jika belum ada, artikel perlu penugasan)</small>
                                @error('link_publish')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3"><i class="bi bi-person"></i> Data Penulis</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_penulis" class="form-label">Nama Penulis <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('nama_penulis') is-invalid @enderror" 
                                       id="nama_penulis" 
                                       name="nama_penulis" 
                                       value="{{ old('nama_penulis', $submission->nama_penulis) }}"
                                       required>
                                @error('nama_penulis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="no_hp_penulis" class="form-label">No HP Penulis</label>
                                <input type="text" 
                                       class="form-control @error('no_hp_penulis') is-invalid @enderror" 
                                       id="no_hp_penulis" 
                                       name="no_hp_penulis" 
                                       value="{{ old('no_hp_penulis', $submission->no_hp_penulis) }}">
                                @error('no_hp_penulis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3"><i class="bi bi-chat-left-text"></i> Catatan</h6>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="2">{{ old('notes', $submission->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3"><i class="bi bi-people"></i> PIC & Petugas</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="marketing_id" class="form-label">PIC Marketing</label>
                                <select class="form-select @error('marketing_id') is-invalid @enderror" id="marketing_id" name="marketing_id">
                                    <option value="">-- Pilih PIC Marketing --</option>
                                    @foreach($marketings as $marketing)
                                        <option value="{{ $marketing->id }}" {{ old('marketing_id', $submission->marketing_id) == $marketing->id ? 'selected' : '' }}>
                                            {{ $marketing->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('marketing_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="petugas_submit_id" class="form-label">PIC Submit</label>
                                <select class="form-select @error('petugas_submit_id') is-invalid @enderror" id="petugas_submit_id" name="petugas_submit_id">
                                    <option value="">-- Pilih PIC --</option>
                                    @foreach($pics as $pic)
                                        <option value="{{ $pic->id }}" {{ old('petugas_submit_id', $submission->petugas_submit_id) == $pic->id ? 'selected' : '' }}>
                                            {{ $pic->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('petugas_submit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.fasttrack.show', $submission->id) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
