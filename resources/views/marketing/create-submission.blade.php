@extends('marketing.layouts.app')

@section('title', 'Tambah Submission')

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
                    <input type="text" class="form-control mb-2" id="search_journal" placeholder="🔍 Cari nama jurnal atau publisher..." autocomplete="off">
                    <input type="text" class="form-control mb-2 d-none" id="selected_journal_display" readonly style="cursor: pointer;">
                    <input type="hidden" id="journal_master_id" name="journal_master_id" value="{{ old('journal_master_id') }}">
                    <select class="form-select @error('journal_master_id') is-invalid @enderror" id="journal_master_select" size="8" style="height: auto;">
                        <option value="">-- Pilih Jurnal --</option>
                        @foreach($journals as $journal)
                            <option value="{{ $journal->id }}" data-name="{{ $journal->nama_jurnal }}" data-publisher="{{ $journal->publisher }}" data-search="{{ strtolower($journal->nama_jurnal . ' ' . $journal->publisher) }}" {{ old('journal_master_id') == $journal->id ? 'selected' : '' }}>
                                {{ $journal->nama_jurnal }} ({{ $journal->publisher }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Menampilkan {{ count($journals) }} jurnal. Ketik untuk mencari, klik/Enter untuk memilih.</small>
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
            <h6 class="text-muted mb-3"><i class="bi bi-file-text"></i> Data Artikel</h6>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">ID Artikel</label>
                    <input type="text" name="id_artikel" class="form-control @error('id_artikel') is-invalid @enderror" value="{{ old('id_artikel') }}" placeholder="Opsional">
                    @error('id_artikel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link Artikel</label>
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
// Journal search functionality with textbox display
const searchInput = document.getElementById('search_journal');
const journalSelect = document.getElementById('journal_master_select');
const selectedDisplay = document.getElementById('selected_journal_display');
const hiddenInput = document.getElementById('journal_master_id');
const options = journalSelect.querySelectorAll('option');

searchInput.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    let visibleCount = 0;
    let lastVisibleOption = null;
    
    // Reset hidden input if searching
    if (searchTerm) {
        journalSelect.classList.remove('d-none');
        selectedDisplay.classList.add('d-none');
    }
    
    options.forEach(option => {
        if (option.value === '') {
            option.style.display = searchTerm ? 'none' : '';
            return;
        }
        
        const searchData = option.getAttribute('data-search') || '';
        if (searchData.includes(searchTerm)) {
            option.style.display = '';
            visibleCount++;
            lastVisibleOption = option;
        } else {
            option.style.display = 'none';
        }
    });
    
    // Auto-select if only one result
    if (visibleCount === 1 && lastVisibleOption) {
        lastVisibleOption.selected = true;
    }
});

// Handle Enter key in search box
searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const selectedOption = journalSelect.options[journalSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            journalSelect.dispatchEvent(new Event('change'));
        }
    }
});

// When journal is selected from dropdown
journalSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const journalName = selectedOption.getAttribute('data-name');
        const publisher = selectedOption.getAttribute('data-publisher');
        
        // Set hidden input
        hiddenInput.value = selectedOption.value;
        
        // Show in textbox
        selectedDisplay.value = `${journalName} (${publisher})`;
        selectedDisplay.classList.remove('d-none');
        
        // Hide dropdown and search
        journalSelect.classList.add('d-none');
        searchInput.value = '';
        
        // Load slots
        loadSlots(selectedOption.value);
    }
});

// Single click on dropdown to select (more intuitive)
journalSelect.addEventListener('click', function(e) {
    if (e.target.tagName === 'OPTION' && e.target.value) {
        setTimeout(() => {
            this.dispatchEvent(new Event('change'));
        }, 100);
    }
});

// Also support double click
journalSelect.addEventListener('dblclick', function() {
    if (this.value) {
        this.dispatchEvent(new Event('change'));
    }
});

// Click on textbox to reopen search
selectedDisplay.addEventListener('click', function() {
    this.classList.add('d-none');
    journalSelect.classList.remove('d-none');
    searchInput.focus();
    hiddenInput.value = '';
});

// Focus search on page load
searchInput.focus();

// Load slots function
function loadSlots(journalId) {
    const slotSelect = document.getElementById('journal_slot_id');
    
    if (!journalId) {
        slotSelect.innerHTML = '<option value="">-- Pilih Jurnal terlebih dahulu --</option>';
        return;
    }
    
    slotSelect.innerHTML = '<option value="">Loading...</option>';
    
    fetch(`{{ url('marketing/journal-slots/get-by-journal') }}?journal_master_id=${journalId}`)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">-- Pilih Slot --</option>';
            data.forEach(slot => {
                options += `<option value="${slot.id}">${slot.text}</option>`;
            });
            slotSelect.innerHTML = options;
        })
        .catch(error => {
            console.error('Error:', error);
            slotSelect.innerHTML = '<option value="">Error loading slots</option>';
        });
}

// Load slots if journal already selected (from old input)
if (hiddenInput.value) {
    const selectedOption = Array.from(options).find(opt => opt.value == hiddenInput.value);
    if (selectedOption) {
        const journalName = selectedOption.getAttribute('data-name');
        const publisher = selectedOption.getAttribute('data-publisher');
        selectedDisplay.value = `${journalName} (${publisher})`;
        selectedDisplay.classList.remove('d-none');
        journalSelect.classList.add('d-none');
        loadSlots(hiddenInput.value);
    }
}
</script>
@endsection
