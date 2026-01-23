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
                                <label for="journal_master_id" class="form-label">Pilih Jurnal <span class="text-danger">*</span></label>
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
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="journal_slot_id" class="form-label">Pilih Slot <span class="text-danger">*</span></label>
                                <select class="form-select @error('journal_slot_id') is-invalid @enderror" id="journal_slot_id" name="journal_slot_id" required>
                                    <option value="">-- Pilih Jurnal terlebih dahulu --</option>
                                </select>
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
                                <label for="id_artikel" class="form-label">ID Artikel</label>
                                <input type="text" class="form-control @error('id_artikel') is-invalid @enderror" id="id_artikel" name="id_artikel" value="{{ old('id_artikel') }}" placeholder="Opsional">
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
                                <label for="pic_id" class="form-label">PIC Submit</label>
                                <select class="form-select @error('pic_id') is-invalid @enderror" id="pic_id" name="pic_id">
                                    <option value="">-- Pilih PIC --</option>
                                    @foreach($pics as $pic)
                                        <option value="{{ $pic->id }}" {{ (old('pic_id') ?? $currentPic->id ?? null) == $pic->id ? 'selected' : '' }}>
                                            {{ $pic->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pic_id')
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
    
    // Always show dropdown when typing, hide selected display
    journalSelect.classList.remove('d-none');
    selectedDisplay.classList.add('d-none');
    hiddenInput.value = ''; // Clear selection
    
    // If empty search, show all
    if (!searchTerm) {
        options.forEach(option => {
            option.style.display = '';
        });
        return;
    }
    
    options.forEach(option => {
        if (option.value === '') {
            option.style.display = 'none'; // Hide placeholder when searching
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
    
    console.log('Loading slots for journal:', journalId);
    slotSelect.innerHTML = '<option value="">Loading...</option>';
    
    const url = `{{ url('pic/journal-slots/get-by-journal') }}?journal_master_id=${journalId}`;
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
            console.log('Received slots data:', data);
            if (data.length === 0) {
                slotSelect.innerHTML = '<option value="">Tidak ada slot tersedia</option>';
                return;
            }
            let options = '<option value="">-- Pilih Slot --</option>';
            data.forEach(slot => {
                options += `<option value="${slot.id}">${slot.text}</option>`;
            });
            slotSelect.innerHTML = options;
            console.log('Slots loaded successfully, total:', data.length);
        })
        .catch(error => {
            console.error('Error loading slots:', error);
            slotSelect.innerHTML = '<option value="">Error loading slots</option>';
            alert('Error loading slots: ' + error.message);
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
@endpush
