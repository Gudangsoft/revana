@extends('marketing.layouts.app')

@section('title', 'Tambah Submission')

<style>
    select option:disabled {
        color: #999 !important;
        font-style: italic;
    }
</style>

@section('content')
<div class="card">
    <div class="card-header">
        <i class="bi bi-plus-circle"></i> Tambah Submission Baru
    </div>
    <div class="card-body">
        <form action="{{ route('marketing.submissions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle"></i> <strong>Kode Submit</strong> dan <strong>Kode LOA</strong> akan otomatis ter-generate setelah data disimpan.
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
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
                        <span class="badge bg-info ms-1">{{ $journals->count() }} jurnal tersedia</span>
                    </small>
                    @error('journal_master_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pilih Slot <span class="text-danger">*</span></label>
                    <select class="form-select @error('journal_slot_id') is-invalid @enderror" id="journal_slot_id" name="journal_slot_id" required>
                        <option value="">-- Pilih Jurnal terlebih dahulu --</option>
                    </select>
                    @error('journal_slot_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr>
            <h6 class="text-muted mb-3"><i class="bi bi-megaphone"></i> Data Marketing</h6>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Marketing <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" value="{{ $marketing->name }} ({{ $marketing->email }})" readonly style="background-color: #e9ecef;">
                    <input type="hidden" name="marketing_id" value="{{ $marketing->id }}">
                    <small class="text-muted">Marketing yang login</small>
                </div>
            </div>

            <hr>
            <h6 class="text-muted mb-3"><i class="bi bi-file-text"></i> Data Artikel</h6>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nomor Submit <span class="text-danger">*</span></label>
                    <input type="text" name="id_artikel" class="form-control @error('id_artikel') is-invalid @enderror" value="{{ old('id_artikel') }}" placeholder="Masukkan Nomor Submit" required>
                    @error('id_artikel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link Submit</label>
                    <input type="url" name="link_artikel" class="form-control @error('link_artikel') is-invalid @enderror" value="{{ old('link_artikel') }}" placeholder="https://...">
                    @error('link_artikel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                <textarea class="form-control @error('judul_artikel') is-invalid @enderror" name="judul_artikel" rows="2" required>{{ old('judul_artikel') }}</textarea>
                @error('judul_artikel')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr>
            <h6 class="text-muted mb-3"><i class="bi bi-person"></i> Data Penulis</h6>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Penulis <span class="text-danger">*</span></label>
                    <input type="text" name="nama_penulis" class="form-control @error('nama_penulis') is-invalid @enderror" value="{{ old('nama_penulis') }}" required>
                    @error('nama_penulis')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. HP Penulis</label>
                    <input type="text" name="no_hp_penulis" class="form-control @error('no_hp_penulis') is-invalid @enderror" value="{{ old('no_hp_penulis') }}">
                    @error('no_hp_penulis')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Username Author</label>
                    <input type="text" name="username_author" class="form-control @error('username_author') is-invalid @enderror" value="{{ old('username_author') }}">
                    @error('username_author')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password Author</label>
                    <input type="text" name="password_author" class="form-control @error('password_author') is-invalid @enderror" value="{{ old('password_author') }}">
                    @error('password_author')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('marketing.submissions') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search_journal');
    const hiddenInput = document.getElementById('journal_master_id');
    const searchResults = document.getElementById('search_results');
    const slotSelect = document.getElementById('journal_slot_id');
    
    // Data jurnal dari server
    let journalsRaw = [];
    try {
        journalsRaw = @json($journals->map(function($j) {
            return [
                'id' => $j->id,
                'nama' => $j->nama_jurnal,
                'publisher' => $j->publisher ?? ''
            ];
        })->values()->all());
    } catch (e) {
        console.error('❌ Error parsing journal data:', e);
    }
    
    // Add search field
    const journals = journalsRaw.map(function(j) {
        return {
            id: j.id,
            nama: j.nama,
            publisher: j.publisher,
            search: (j.nama + ' ' + j.publisher).toLowerCase()
        };
    });
    
    console.log('=== MARKETING JOURNAL SEARCH ===');
    console.log('✅ Journal data loaded:', journals.length, 'journals');
    
    if (journals.length === 0) {
        console.error('❌ No journals found!');
        searchInput.placeholder = '❌ Tidak ada data jurnal';
        searchInput.disabled = true;
        return;
    }
    
    let selectedJournalName = '';
    let isJournalSelected = false;
    
    // Search input
    searchInput.addEventListener('input', function() {
        // Jika jurnal sudah dipilih dan user mulai edit, clear selection
        if (isJournalSelected && this.value.indexOf('✓') !== 0) {
            isJournalSelected = false;
            hiddenInput.value = '';
            this.classList.remove('is-valid');
            slotSelect.innerHTML = '<option value="">-- Pilih Jurnal terlebih dahulu --</option>';
            slotSelect.disabled = true;
        }
        
        const searchTerm = this.value.toLowerCase().trim();
        
        console.log('🔍 Searching for:', searchTerm);
        
        if (searchTerm.length === 0) {
            searchResults.style.display = 'none';
            hiddenInput.value = '';
            return;
        }
        
        const filtered = journals.filter(function(j) {
            return j.search.indexOf(searchTerm) !== -1;
        });
        
        console.log('📋 Found:', filtered.length, 'matches');
        
        if (filtered.length === 0) {
            searchResults.innerHTML = '<div class="list-group-item text-muted py-3">' +
                '<div><i class="bi bi-info-circle"></i> Tidak ada jurnal yang cocok dengan "<strong>' + escapeHtml(searchTerm) + '</strong>"</div>' +
                '<small class="text-muted mt-1 d-block">Coba kata kunci lain</small>' +
                '</div>';
            searchResults.style.display = 'block';
            return;
        }
        
        let html = '';
        const maxResults = Math.min(filtered.length, 15);
        
        for (let i = 0; i < maxResults; i++) {
            const j = filtered[i];
            const escapedNama = escapeHtml(j.nama);
            const escapedPublisher = escapeHtml(j.publisher);
            
            html += '<a href="javascript:void(0)" class="list-group-item list-group-item-action py-2" data-id="' + j.id + '" data-name="' + escapedNama + '">';
            html += '<div><strong>' + escapedNama + '</strong></div>';
            if (j.publisher) {
                html += '<div><small class="text-muted">' + escapedPublisher + '</small></div>';
            }
            html += '</a>';
        }
        
        if (filtered.length > 15) {
            html += '<div class="list-group-item text-muted small text-center">+ ' + (filtered.length - 15) + ' jurnal lainnya</div>';
        }
        
        searchResults.innerHTML = html;
        searchResults.style.display = 'block';
    });
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Klik hasil
    searchResults.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const target = e.target.closest('.list-group-item-action');
        if (!target) return;
        
        const journalId = target.getAttribute('data-id');
        const journalName = target.getAttribute('data-name');
        
        console.log('🖱️ Selected:', journalId, journalName);
        
        if (journalId) {
            selectJournal(journalId, journalName);
        }
    });
    
    // Enter key
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const first = searchResults.querySelector('.list-group-item-action[data-id]');
            if (first) {
                const id = first.getAttribute('data-id');
                const name = first.getAttribute('data-name');
                selectJournal(id, name);
            }
        } else if (e.key === 'Escape') {
            searchResults.style.display = 'none';
        }
    });
    
    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    
    function selectJournal(journalId, journalName) {
        hiddenInput.value = journalId;
        selectedJournalName = journalName;
        isJournalSelected = true;
        searchInput.value = '✓ ' + journalName;
        searchInput.classList.add('is-valid');
        searchResults.style.display = 'none';
        
        // Blur input to prevent scroll
        searchInput.blur();
        
        console.log('✅ Selected ID:', journalId);
        loadSlots(journalId);
    }
    
    // Edit selection
    searchInput.addEventListener('focus', function() {
        if (isJournalSelected && this.value.indexOf('✓') === 0) {
            this.value = selectedJournalName;
            this.select();
        }
    });
    
    function loadSlots(journalId) {
        console.log('⏳ Loading slots...');
        
        slotSelect.innerHTML = '<option value="">⏳ Memuat...</option>';
        slotSelect.disabled = true;
        
        const url = '{{ url("marketing/journal-slots/get-by-journal") }}?journal_master_id=' + journalId;
        
        fetch(url)
            .then(function(response) {
                console.log('📡 Status:', response.status);
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(function(data) {
                console.log('📦 Slots:', data.length);
                
                if (!Array.isArray(data) || data.length === 0) {
                    slotSelect.innerHTML = '<option value="">❌ Tidak ada slot</option>';
                    slotSelect.disabled = false;
                    return;
                }
                
                let html = '<option value="">-- Pilih Slot --</option>';
                for (let i = 0; i < data.length; i++) {
                    const slot = data[i];
                    const sisa = slot.sisa !== undefined ? slot.sisa : Math.max(0, slot.jumlah_slot - slot.slot_terpakai);
                    const isFull = slot.is_full !== undefined ? slot.is_full : (sisa <= 0);
                    
                    if (isFull) {
                        html += '<option value="' + slot.id + '" disabled style="color: #dc3545; font-weight: bold; background-color: #f8d7da;">🚫 SLOT PENUH - ' + slot.text + ' (0/' + slot.jumlah_slot + ') - TIDAK TERSEDIA</option>';
                    } else if (sisa <= 2) {
                        html += '<option value="' + slot.id + '" style="color: #fd7e14; font-weight: bold;">⚠️ HAMPIR PENUH - ' + slot.text + ' (' + sisa + '/' + slot.jumlah_slot + ' tersisa)</option>';
                    } else {
                        html += '<option value="' + slot.id + '" style="color: #28a745;">✅ ' + slot.text + ' (' + sisa + '/' + slot.jumlah_slot + ' tersisa)</option>';
                    }
                }
                
                slotSelect.innerHTML = html;
                slotSelect.disabled = false;
                console.log('✅ Slots loaded');
            })
            .catch(function(error) {
                console.error('❌ Error:', error);
                slotSelect.innerHTML = '<option value="">❌ Error</option>';
                slotSelect.disabled = false;
            });
    }
    
    // Validasi form submit - cegah pemilihan slot penuh
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const slotSelect = document.getElementById('journal_slot_id');
            const selectedOption = slotSelect.options[slotSelect.selectedIndex];
            
            // Cek jika tidak ada slot yang dipilih
            if (!slotSelect.value) {
                e.preventDefault();
                alert('⚠️ Harap pilih slot jurnal terlebih dahulu!');
                slotSelect.focus();
                return false;
            }
            
            // Cek jika slot yang dipilih disabled (penuh)
            if (selectedOption && selectedOption.disabled) {
                e.preventDefault();
                alert('🚫 SLOT SUDAH PENUH!\n\nSlot yang Anda pilih sudah tidak tersedia.\nSilakan pilih slot lain yang masih memiliki sisa kuota.');
                slotSelect.focus();
                return false;
            }
            
            // Peringatan untuk slot yang hampir penuh (<=2)
            const optionText = selectedOption.text;
            if (optionText.includes('⚠️ HAMPIR PENUH')) {
                const confirmed = confirm('⚠️ PERINGATAN: Slot Hampir Penuh!\n\n' + 
                    'Slot yang Anda pilih hanya memiliki sisa slot terbatas.\n' +
                    'Apakah Anda yakin ingin melanjutkan?\n\n' +
                    'Klik OK untuk melanjutkan atau Cancel untuk memilih slot lain.');
                if (!confirmed) {
                    return false;
                }
            }
            
            // Konfirmasi final sebelum submit
            const journalName = document.getElementById('search_journal').value;
            const finalConfirm = confirm('📝 KONFIRMASI SUBMISSION\n\n' +
                'Jurnal: ' + journalName + '\n' +
                'Slot: ' + optionText.replace(/[🚫⚠️✅]/g, '').trim() + '\n\n' +
                'Apakah data sudah benar dan yakin ingin submit?');
            if (!finalConfirm) {
                return false;
            }
        });
    }
    
    // Restore old
    @if(old('journal_master_id'))
        const oldId = '{{ old("journal_master_id") }}';
        const oldJournal = journals.find(function(j) { return j.id == oldId; });
        if (oldJournal) {
            selectJournal(oldJournal.id, oldJournal.nama);
        }
    @elseif(isset($preselectedJournalId) && $preselectedJournalId)
        const preselectedJournalId = '{{ $preselectedJournalId }}';
        const preselectedSlotId = '{{ $preselectedSlotId ?? "" }}';
        const preJournal = journals.find(function(j) { return j.id == preselectedJournalId; });
        if (preJournal) {
            selectJournal(preJournal.id, preJournal.nama);
            // Auto-select the slot after loading
            if (preselectedSlotId) {
                const origLoadSlots = loadSlots;
                const checkSlotInterval = setInterval(function() {
                    if (!slotSelect.disabled && slotSelect.options.length > 1) {
                        slotSelect.value = preselectedSlotId;
                        clearInterval(checkSlotInterval);
                    }
                }, 200);
                // Safety timeout
                setTimeout(function() { clearInterval(checkSlotInterval); }, 5000);
            }
        }
    @endif
    
    searchInput.focus();
    console.log('✅ Ready! Type to search...');
});
</script>
@endsection
