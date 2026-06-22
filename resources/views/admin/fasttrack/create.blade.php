@extends('layouts.app')

@section('title', 'Input Fasttrack - ' . $appSettings['app_name'])
@section('page-title', 'Input Fasttrack')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-lightning-charge"></i> Input Submission Fasttrack - Admin
            </div>
            <div class="card-body">
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <div class="alert alert-warning mb-4">
                    <i class="bi bi-lightning-charge"></i> <strong>Fasttrack</strong> - Proses cepat untuk artikel yang sudah publish.
                    <br><small>Artikel akan langsung berstatus "Published" tanpa melalui workflow normal.</small>
                </div>

                <form action="{{ route('admin.fasttrack.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf

                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i> <strong>Kode Submit</strong> akan otomatis ter-generate dengan prefix <code>FT</code> (Fasttrack).
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
                                    <input type="hidden" name="journal_master_id" id="journal_master_id" value="{{ old('journal_master_id') }}">
                                    
                                    <!-- Dropdown hasil pencarian -->
                                    <div id="search_results" class="list-group position-absolute w-100 shadow" style="display: none; max-height: 350px; overflow-y: auto; z-index: 1050; background: white; border: 1px solid #dee2e6; border-radius: 0.25rem; margin-top: 2px;"></div>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-info-circle"></i> Ketik minimal 1 huruf untuk mencari.
                                    <span id="journal_count_info" class="badge bg-info ms-1">{{ $journals->count() }} jurnal tersedia</span>
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

                    <input type="hidden" name="program_type" value="{{ old('program_type', request('program')) }}">

                    <hr>
                    <h6 class="text-muted mb-3"><i class="bi bi-file-text"></i> Data Artikel</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_artikel" class="form-label">ID Artikel <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('id_artikel') is-invalid @enderror" 
                                       id="id_artikel" 
                                       name="id_artikel" 
                                       value="{{ old('id_artikel') }}" 
                                       required>
                                @error('id_artikel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="link_artikel" class="form-label">Link Submit</label>
                                <input type="url" 
                                       class="form-control @error('link_artikel') is-invalid @enderror" 
                                       id="link_artikel" 
                                       name="link_artikel" 
                                       value="{{ old('link_artikel') }}" 
                                       placeholder="https://">
                                @error('link_artikel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted"><i class="bi bi-info-circle"></i> Link Submit harus unik — setiap artikel harus memiliki link yang berbeda. Link yang sudah digunakan tidak dapat diinput ulang.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="file_artikel" class="form-label">Upload File Artikel (Word/PDF)</label>
                                <input type="file" 
                                       class="form-control @error('file_artikel') is-invalid @enderror" 
                                       id="file_artikel" 
                                       name="file_artikel" 
                                       accept=".doc,.docx,.pdf">
                                @error('file_artikel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: DOC, DOCX, PDF. Maksimal 10MB</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="judul_artikel" class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('judul_artikel') is-invalid @enderror" 
                                          id="judul_artikel" 
                                          name="judul_artikel" 
                                          rows="2" 
                                          required>{{ old('judul_artikel') }}</textarea>
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
                                       value="{{ old('link_publish') }}">
                                <small class="text-muted"><i class="bi bi-link-45deg"></i> Link artikel yang sudah publish (Opsional: Jika belum ada, artikel perlu penugasan)</small>
                                @error('link_publish')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3"><i class="bi bi-person"></i> Data Penulis</h6>

                    <div class="row g-2 mb-3">
                        <div class="col-12">
                            <label for="nama_penulis" class="form-label form-label-sm mb-1">
                                Nama Penulis <span class="text-danger">*</span>
                                <span class="text-muted fw-normal">(pisahkan dengan koma jika lebih dari 1)</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('nama_penulis') is-invalid @enderror"
                                   id="nama_penulis"
                                   name="nama_penulis"
                                   value="{{ old('nama_penulis') }}"
                                   placeholder="Contoh: Ekosiswanto, Maulidiah Zulfa, Zaenal"
                                   maxlength="500" required>
                            <div class="d-flex justify-content-end mt-1">
                                <small id="nama_penulis_counter" class="text-muted">0 / 500 karakter</small>
                            </div>
                            @error('nama_penulis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="affiliation_penulis" class="form-label form-label-sm mb-1">Afiliasi Penulis <span class="text-muted fw-normal">(institusi/universitas)</span></label>
                            <input type="text"
                                   class="form-control @error('affiliation_penulis') is-invalid @enderror"
                                   id="affiliation_penulis" name="affiliation_penulis"
                                   value="{{ old('affiliation_penulis') }}"
                                   placeholder="Nama institusi / universitas penulis">
                            @error('affiliation_penulis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="no_hp_penulis" class="form-label form-label-sm mb-1">No HP Penulis</label>
                            <input type="text"
                                   class="form-control @error('no_hp_penulis') is-invalid @enderror"
                                   id="no_hp_penulis" name="no_hp_penulis"
                                   value="{{ old('no_hp_penulis') }}" placeholder="08xx-xxxx-xxxx">
                            @error('no_hp_penulis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email_penulis" class="form-label form-label-sm mb-1">Email Penulis</label>
                            <input type="email"
                                   class="form-control @error('email_penulis') is-invalid @enderror"
                                   id="email_penulis" name="email_penulis"
                                   value="{{ old('email_penulis') }}" placeholder="email@domain.com">
                            @error('email_penulis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username_author" class="form-label">Username Akses Author</label>
                                <input type="text" 
                                       class="form-control @error('username_author') is-invalid @enderror" 
                                       id="username_author" 
                                       name="username_author" 
                                       value="{{ old('username_author') }}">
                                @error('username_author')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_author" class="form-label">Password Akses Author</label>
                                <input type="text" 
                                       class="form-control @error('password_author') is-invalid @enderror" 
                                       id="password_author" 
                                       name="password_author" 
                                       value="{{ old('password_author') }}">
                                @error('password_author')
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
                                          rows="2">{{ old('notes') }}</textarea>
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
                                        <option value="{{ $pic->id }}" {{ old('petugas_submit_id') == $pic->id ? 'selected' : '' }}>
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
                        <a href="{{ route('admin.fasttrack-management.monitoring.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-lightning-charge"></i> Simpan Fasttrack
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search_journal');
    const searchResults = document.getElementById('search_results');
    const journalMasterIdInput = document.getElementById('journal_master_id');
    const slotSelect = document.getElementById('journal_slot_id');
    
    // All journals data
    const allJournals = @json($journals);
    
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
    function loadSlots(journalId) {
        slotSelect.innerHTML = '<option value="">Memuat slot...</option>';
        
        fetch(`{{ route('admin.journal-slots.get-by-journal') }}?journal_master_id=${journalId}`)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    slotSelect.innerHTML = '<option value="">-- Tidak ada slot tersedia --</option>';
                } else {
                    slotSelect.innerHTML = '<option value="">-- Pilih Slot --</option>' + 
                        data.map(s => {
                            const sisa = s.sisa !== undefined ? s.sisa : Math.max(0, s.jumlah_slot - s.slot_terpakai);
                            const isFull = s.is_full !== undefined ? s.is_full : (sisa <= 0);
                            const fullIndicator = isFull ? ' 🚫 PENUH - TIDAK TERSEDIA' : ` - Sisa: ${sisa}/${s.jumlah_slot} slot`;
                            const disabled = isFull ? ' disabled' : '';
                            const style = isFull ? ' style="color: #dc3545; font-weight: bold; background-color: #f8d7da;"' : (sisa <= 2 ? ' style="color: #fd7e14; font-weight: bold;"' : ' style="color: #28a745;"');
                            return `<option value="${s.id}"${disabled}${style}>${s.text}${fullIndicator}</option>`;
                        }).join('');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                slotSelect.innerHTML = '<option value="">-- Error memuat slot --</option>';
            });
    }
    
    // Add validation when form is submitted
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const selectedOption = slotSelect.options[slotSelect.selectedIndex];
        if (selectedOption && selectedOption.disabled) {
            e.preventDefault();
            alert('⚠️ Slot yang Anda pilih sudah PENUH!\n\nSilakan pilih slot lain yang masih tersedia.');
            slotSelect.value = '';
            slotSelect.focus();
            return false;
        }
    });
});

// Nama penulis character counter
document.addEventListener('DOMContentLoaded', function() {
    var inp = document.getElementById('nama_penulis');
    var counter = document.getElementById('nama_penulis_counter');
    if (!inp || !counter) return;
    var MAX = parseInt(inp.getAttribute('maxlength')) || 500;
    function update() {
        var n = inp.value.length;
        counter.textContent = n + ' / ' + MAX + ' karakter';
        counter.className = n >= MAX * 0.95 ? 'text-danger fw-semibold' : n >= MAX * 0.80 ? 'text-warning fw-semibold' : 'text-muted';
    }
    inp.addEventListener('input', update);
    update();
});
</script>
@endsection
