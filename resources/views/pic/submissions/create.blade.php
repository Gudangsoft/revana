@extends('pic.layouts.app')

@section('title', 'Tambah Submission')
@section('page-title', 'Tambah Submission')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-earmark-plus"></i> Tambah Data Submit
            </div>
            <div class="card-body">
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form action="{{ route('pic.submissions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="alert alert-success mb-3">
                        <i class="bi bi-info-circle"></i> <strong>Kode Submit</strong> dan <strong>Kode LOA</strong> akan otomatis ter-generate setelah data disimpan.
                        <br><small class="text-muted">Format: Kode Submit = SUB[tahun][bulan][nomor urut], Kode LOA = [Kode Submit]SIPERA</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Pilih Jurnal <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" 
                                           class="form-control @error('journal_master_id') is-invalid @enderror" 
                                           id="search_journal" 
                                           placeholder="🔍 Ketik untuk mencari jurnal..." 
                                           autocomplete="off">
                                    <input type="hidden" name="journal_master_id" id="journal_master_id" value="{{ old('journal_master_id') }}" required>
                                    
                                    <!-- Dropdown hasil pencarian -->
                                    <div id="search_results" class="list-group position-absolute w-100 shadow" style="display: none; max-height: 350px; overflow-y: auto; z-index: 1050; background: white; border: 1px solid #dee2e6; border-radius: 0.25rem; margin-top: 2px;"></div>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-info-circle"></i> Ketik minimal 1 huruf untuk mencari. Klik jurnal untuk memilih.
                                </small>
                                @error('journal_master_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="journal_slot_id" class="form-label">Pilih Slot <span class="text-danger">*</span></label>
                                <select class="form-select @error('journal_slot_id') is-invalid @enderror" 
                                        id="journal_slot_id" 
                                        name="journal_slot_id" 
                                        required>
                                    <option value="">-- Pilih Jurnal terlebih dahulu --</option>
                                </select>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3"></i> Slot akan ditampilkan setelah memilih jurnal
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_artikel" class="form-label">ID Artikel <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('id_artikel') is-invalid @enderror" id="id_artikel" name="id_artikel" value="{{ old('id_artikel') }}" required>
                                @error('id_artikel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="link_artikel" class="form-label">Link Artikel</label>
                                <input type="url" class="form-control @error('link_artikel') is-invalid @enderror" id="link_artikel" name="link_artikel" value="{{ old('link_artikel') }}" placeholder="https://">
                                @error('link_artikel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="file_artikel" class="form-label">Upload File Artikel (Word/PDF)</label>
                                <input type="file" class="form-control @error('file_artikel') is-invalid @enderror" id="file_artikel" name="file_artikel" accept=".doc,.docx,.pdf">
                                @error('file_artikel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: DOC, DOCX, PDF. Maksimal 10MB</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="judul_artikel" class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('judul_artikel') is-invalid @enderror" id="judul_artikel" name="judul_artikel" rows="2" required>{{ old('judul_artikel') }}</textarea>
                        @error('judul_artikel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3"><i class="bi bi-person"></i> Data Penulis</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_penulis" class="form-label">Nama Penulis <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_penulis') is-invalid @enderror" id="nama_penulis" name="nama_penulis" value="{{ old('nama_penulis') }}" required>
                                @error('nama_penulis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="no_hp_penulis" class="form-label">No HP Penulis</label>
                                <input type="text" class="form-control @error('no_hp_penulis') is-invalid @enderror" id="no_hp_penulis" name="no_hp_penulis" value="{{ old('no_hp_penulis') }}">
                                @error('no_hp_penulis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username_author" class="form-label">Username Akses Author</label>
                                <input type="text" class="form-control @error('username_author') is-invalid @enderror" id="username_author" name="username_author" value="{{ old('username_author') }}">
                                @error('username_author')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_author" class="form-label">Password Akses Author</label>
                                <input type="text" class="form-control @error('password_author') is-invalid @enderror" id="password_author" name="password_author" value="{{ old('password_author') }}">
                                @error('password_author')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                                        <option value="{{ $marketing->id }}" {{ old('marketing_id') == $marketing->id ? 'selected' : '' }}>
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
                                        <option value="{{ $pic->id }}" {{ (old('petugas_submit_id') ?? $currentPic->id ?? null) == $pic->id ? 'selected' : '' }}>
                                            {{ $pic->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('petugas_submit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Pilih PIC yang melakukan submit.</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('pic.submissions.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search_journal');
    const hiddenInput = document.getElementById('journal_master_id');
    const searchResults = document.getElementById('search_results');
    const slotSelect = document.getElementById('journal_slot_id');
    
    // Data jurnal - menggunakan JSON encode yang aman
    const journalsData = {!! json_encode($journals->map(function($j) {
        return [
            'id' => $j->id,
            'nama' => $j->nama_jurnal,
            'publisher' => $j->publisher ?? ''
        ];
    })->values()) !!};
    
    // Add search field
    const journals = journalsData.map(j => ({
        ...j,
        search: (j.nama + ' ' + j.publisher).toLowerCase()
    }));
    
    console.log('✅ Journal data loaded:', journals.length, 'journals');
    if (journals.length > 0) {
        console.log('📋 Sample:', journals[0]);
    }
    
    if (journals.length === 0) {
        console.error('❌ No journals found! Check controller data.');
        searchInput.placeholder = '❌ Tidak ada data jurnal';
        searchInput.disabled = true;
        return;
    }
    
    let selectedJournalName = '';
    
    // Search input - tampilkan hasil SETIAP kali mengetik
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        console.log('🔍 Searching for:', searchTerm);
        
        // Jika kosong, sembunyikan hasil
        if (searchTerm.length === 0) {
            searchResults.style.display = 'none';
            hiddenInput.value = '';
            return;
        }
        
        // Filter jurnal yang cocok
        const filtered = journals.filter(j => j.search.includes(searchTerm));
        
        console.log('📋 Found:', filtered.length, 'matches');
        
        // Jika tidak ada hasil
        if (filtered.length === 0) {
            searchResults.innerHTML = '<div class="list-group-item text-muted"><i class="bi bi-info-circle"></i> Tidak ada hasil ditemukan untuk "' + escapeHtml(searchTerm) + '"</div>';
            searchResults.style.display = 'block';
            return;
        }
        
        // Tampilkan hasil (maksimal 15 untuk performa)
        let html = '';
        const maxResults = Math.min(filtered.length, 15);
        
        for (let i = 0; i < maxResults; i++) {
            const journal = filtered[i];
            html += `
                <a href="javascript:void(0)" class="list-group-item list-group-item-action py-2" data-id="${journal.id}" data-name="${escapeHtml(journal.nama)}">
                    <div><strong>${highlightMatch(journal.nama, searchTerm)}</strong></div>
                    ${journal.publisher ? '<div><small class="text-muted">' + escapeHtml(journal.publisher) + '</small></div>' : ''}
                </a>
            `;
        }
        
        if (filtered.length > 15) {
            html += `<div class="list-group-item text-muted small text-center">+ ${filtered.length - 15} jurnal lainnya. Ketik lebih spesifik...</div>`;
        }
        
        searchResults.innerHTML = html;
        searchResults.style.display = 'block';
    });
    
    // Helper function untuk escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Helper function untuk highlight match
    function highlightMatch(text, term) {
        if (!term) return escapeHtml(text);
        const escaped = escapeHtml(text);
        const regex = new RegExp('(' + term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return escaped.replace(regex, '<span style="background-color: #fff3cd; font-weight: bold;">$1</span>');
    }
    
    // Klik hasil pencarian
    searchResults.addEventListener('click', function(e) {
        e.preventDefault();
        const target = e.target.closest('.list-group-item-action');
        if (!target) return;
        
        const journalId = target.getAttribute('data-id');
        const journalName = target.getAttribute('data-name');
        
        console.log('🖱️ Clicked journal:', journalId, journalName);
        
        if (journalId) {
            selectJournal(journalId, journalName);
        }
    });
    
    // Keyboard navigation - Enter untuk pilih pertama
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const firstResult = searchResults.querySelector('.list-group-item-action[data-id]');
            if (firstResult) {
                const journalId = firstResult.getAttribute('data-id');
                const journalName = firstResult.getAttribute('data-name');
                console.log('⌨️ Enter pressed, selecting:', journalId);
                selectJournal(journalId, journalName);
            }
        } else if (e.key === 'Escape') {
            searchResults.style.display = 'none';
        }
    });
    
    // Klik di luar untuk tutup dropdown
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            if (searchResults.style.display === 'block') {
                console.log('👋 Closing dropdown - clicked outside');
                searchResults.style.display = 'none';
            }
        }
    });
    
    // Fungsi untuk select jurnal
    function selectJournal(journalId, journalName) {
        hiddenInput.value = journalId;
        selectedJournalName = journalName;
        searchInput.value = '✓ ' + journalName;
        searchInput.classList.add('is-valid');
        searchInput.classList.remove('is-invalid');
        searchResults.style.display = 'none';
        
        console.log('✅ Selected journal ID:', journalId);
        console.log('✅ Selected journal name:', journalName);
        
        // Load slots
        loadSlots(journalId);
    }
    
    // Clear selection when editing
    searchInput.addEventListener('focus', function() {
        if (hiddenInput.value && this.value.startsWith('✓ ')) {
            // Allow editing - show current search without checkmark
            this.value = selectedJournalName;
            this.select();
        }
    });
    
    // Load slots function
    function loadSlots(journalId) {
        console.log('⏳ Loading slots for journal ID:', journalId);
        
        slotSelect.innerHTML = '<option value="">⏳ Memuat slot...</option>';
        slotSelect.disabled = true;
        
        const url = `{{ url('pic/journal-slots/get-by-journal') }}?journal_master_id=${journalId}`;
        console.log('📡 Fetching from:', url);
        
        fetch(url)
            .then(response => {
                console.log('📡 Response status:', response.status);
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(data => {
                console.log('📦 Received slots:', data);
                
                if (!Array.isArray(data) || data.length === 0) {
                    slotSelect.innerHTML = '<option value="">❌ Tidak ada slot tersedia untuk jurnal ini</option>';
                    slotSelect.disabled = false;
                    console.log('⚠️ No slots available for this journal');
                    return;
                }
                
                let html = '<option value="">-- Pilih Slot --</option>';
                data.forEach(slot => {
                    html += `<option value="${slot.id}">${slot.text}</option>`;
                });
                
                slotSelect.innerHTML = html;
                slotSelect.disabled = false;
                console.log('✅ Slots loaded successfully -', data.length, 'slot(s) available');
            })
            .catch(error => {
                console.error('❌ Error loading slots:', error);
                slotSelect.innerHTML = '<option value="">❌ Error: ' + error.message + '</option>';
                slotSelect.disabled = false;
            });
    }
    
    // Restore old selection if exists (after form validation error)
    @if(old('journal_master_id'))
        const oldJournalId = '{{ old('journal_master_id') }}';
        const oldJournal = journals.find(j => j.id == oldJournalId);
        if (oldJournal) {
            console.log('♻️ Restoring previous selection:', oldJournalId);
            selectJournal(oldJournal.id, oldJournal.nama);
        }
    @endif
    
    // Focus on search input
    setTimeout(() => {
        searchInput.focus();
    }, 100);
    
    console.log('✅ Search functionality ready - Type to search!');
    console.log('💡 Try typing part of a journal name...');
});
</script>
@endpush
