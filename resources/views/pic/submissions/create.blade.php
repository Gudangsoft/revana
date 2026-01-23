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
                                <input type="text" class="form-control mb-2" id="search_journal" placeholder="🔍 Cari nama jurnal atau publisher..." autocomplete="off">
                                <select class="form-select @error('journal_master_id') is-invalid @enderror" 
                                        id="journal_master_id" 
                                        name="journal_master_id" 
                                        required 
                                        size="10" 
                                        style="min-height: 250px;">
                                    <option value="">-- Pilih Jurnal --</option>
                                    @foreach($journals as $journal)
                                        <option value="{{ $journal->id }}" 
                                                data-search="{{ strtolower($journal->nama_jurnal . ' ' . $journal->publisher) }}" 
                                                {{ old('journal_master_id') == $journal->id ? 'selected' : '' }}>
                                            {{ $journal->nama_jurnal }} ({{ $journal->publisher }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Ketik untuk mencari → Klik jurnal (atau tekan Enter) → Slot akan muncul otomatis
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
                                        required
                                        size="10" 
                                        style="min-height: 250px;">
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
    const journalSelect = document.getElementById('journal_master_id');
    const slotSelect = document.getElementById('journal_slot_id');
    const allOptions = Array.from(journalSelect.options);
    
    console.log('✅ Initialized:', allOptions.length - 1, 'journals');
    
    // Search functionality - filter journals in real-time
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        allOptions.forEach((option, index) => {
            if (index === 0) { // Skip first "-- Pilih Jurnal --"
                option.style.display = searchTerm ? 'none' : '';
                return;
            }
            
            const searchData = option.getAttribute('data-search') || '';
            option.style.display = searchData.includes(searchTerm) ? '' : 'none';
        });
        
        console.log('🔍 Search:', searchTerm, '- Filtered list updated');
    });
    
    // Enhanced: Press Enter on search to select first visible option
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const firstVisible = allOptions.find(opt => opt.value && opt.style.display !== 'none');
            if (firstVisible) {
                journalSelect.value = firstVisible.value;
                journalSelect.dispatchEvent(new Event('change'));
                console.log('⌨️ Enter pressed - Selected first match:', firstVisible.value);
            }
        }
    });
    
    // When journal selected - load slots immediately
    journalSelect.addEventListener('change', function() {
        const journalId = this.value;
        
        if (!journalId) {
            slotSelect.innerHTML = '<option value="">-- Pilih Jurnal terlebih dahulu --</option>';
            slotSelect.disabled = true;
            return;
        }
        
        console.log('📌 Journal selected, ID:', journalId);
        loadSlots(journalId);
    });
    
    // Click on option - immediate selection and slot loading
    journalSelect.addEventListener('click', function(e) {
        if (e.target.tagName === 'OPTION' && e.target.value) {
            console.log('🖱️ Option clicked:', e.target.value);
            this.value = e.target.value;
            // Small delay to ensure the value is set
            setTimeout(() => {
                this.dispatchEvent(new Event('change'));
            }, 50);
        }
    });
    
    // Double-click support for better UX
    journalSelect.addEventListener('dblclick', function() {
        if (this.value) {
            console.log('🖱️🖱️ Double-clicked, triggering change');
            this.dispatchEvent(new Event('change'));
        }
    });
    
    // Load slots function
    function loadSlots(journalId) {
        console.log('⏳ Loading slots for journal:', journalId);
        
        slotSelect.innerHTML = '<option value="">⏳ Memuat slot...</option>';
        slotSelect.disabled = true;
        
        fetch(`{{ url('pic/journal-slots/get-by-journal') }}?journal_master_id=${journalId}`)
            .then(response => {
                console.log('📡 Response status:', response.status);
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(data => {
                console.log('📦 Received slots:', data.length);
                
                if (!Array.isArray(data) || data.length === 0) {
                    slotSelect.innerHTML = '<option value="">❌ Tidak ada slot tersedia</option>';
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
                console.log('✅ Slots loaded successfully -', data.length, 'options available');
            })
            .catch(error => {
                console.error('❌ Error loading slots:', error);
                slotSelect.innerHTML = '<option value="">❌ Error: ' + error.message + '</option>';
                slotSelect.disabled = false;
            });
    }
    
    // Restore old selection if exists (after form validation error)
    if (journalSelect.value) {
        console.log('♻️ Restoring previous selection:', journalSelect.value);
        loadSlots(journalSelect.value);
    }
    
    // Focus on search input for better UX
    searchInput.focus();
    
    console.log('✅ Search and slot functionality ready');
});
</script>
@endpush
