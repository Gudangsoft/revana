@extends('pic.layouts.app')

@section('title', 'Tambah Submission' . (request('program') ? ' ' . strtoupper(request('program')) : ''))
@section('page-title', 'Tambah Submission' . (request('program') ? ' ' . strtoupper(request('program')) : ''))

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

<style>
    select option:disabled {
        color: #999 !important;
        font-style: italic;
    }
</style>

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-earmark-plus"></i> Tambah Data Submit{{ request('program') ? ' ' . strtoupper(request('program')) : '' }}
            </div>
            <div class="card-body">
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" id="validation-error-summary" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-x-circle-fill me-2 mt-1" style="font-size:1.2rem"></i>
                        <div class="flex-grow-1">
                            <strong>Submission gagal disimpan!</strong> Terdapat {{ $errors->count() }} kesalahan yang perlu diperbaiki:
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
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
                                <input type="text" class="form-control @error('id_artikel') is-invalid @enderror" id="id_artikel" name="id_artikel" value="{{ old('id_artikel') }}" placeholder="Masukkan ID Artikel" required>
                                @error('id_artikel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="link_artikel" class="form-label">Link Submit</label>
                                <input type="url" class="form-control @error('link_artikel') is-invalid @enderror" id="link_artikel" name="link_artikel" value="{{ old('link_artikel') }}" placeholder="https://">
                                @error('link_artikel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted"><i class="bi bi-info-circle"></i> Link Submit harus unik — setiap artikel harus memiliki link yang berbeda. Link yang sudah digunakan tidak dapat diinput ulang.</small>
                            </div>
                        </div>
                    </div>

                    @if(request('program') === 'bkd')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="link_publish" class="form-label">
                                    Link Publish <span class="badge bg-success ms-1">Opsional — Langsung PUBLISHED</span>
                                </label>
                                <input type="url" class="form-control @error('link_publish') is-invalid @enderror" id="link_publish" name="link_publish" value="{{ old('link_publish') }}" placeholder="https://... (isi jika artikel sudah terbit)">
                                @error('link_publish')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted"><i class="bi bi-info-circle"></i> Jika diisi, status submission akan langsung menjadi <strong>PUBLISHED</strong> tanpa melalui proses review.</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="file_artikel" class="form-label">Upload File Artikel (Word/PDF)</label>
                                <input type="file" class="form-control @error('file_artikel') is-invalid @enderror" id="file_artikel" name="file_artikel" accept=".doc,.docx,.pdf">
                                @error('file_artikel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: DOC, DOCX, PDF. Maksimal 50MB</small>
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
                                <input type="hidden" name="petugas_submit_id" value="{{ $currentPic->id ?? auth()->id() }}">
                                <select class="form-select" id="petugas_submit_id" disabled>
                                    @foreach($pics as $pic)
                                        <option value="{{ $pic->id }}" {{ ($currentPic->id ?? auth()->id()) == $pic->id ? 'selected' : '' }}>
                                            {{ $pic->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted"><i class="bi bi-lock"></i> PIC Submit otomatis diisi dengan akun Anda.</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('pic.submissions.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="button" class="btn btn-primary" onclick="showKonfirmasi()">
                            <i class="bi bi-clipboard-check me-1"></i>Periksa &amp; Simpan
                        </button>
                    </div>
                </form>

                {{-- Modal Konfirmasi --}}
                <div class="modal fade" id="modalKonfirmasi" tabindex="-1" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="background:#fff8e1;border-bottom:2px solid #ffc107">
                                <h5 class="modal-title fw-bold">
                                    <i class="bi bi-clipboard-check text-warning me-2"></i>Konfirmasi Data Submission
                                </h5>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-warning py-2 mb-3 small">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    <strong>Periksa kembali semua data di bawah ini.</strong>
                                    Data yang sudah tersimpan tidak bisa diubah sembarangan — pastikan sudah benar sebelum melanjutkan.
                                </div>
                                <table class="table table-bordered table-sm small mb-0">
                                    <tbody id="konfirmasiData"></tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-pencil-square me-1"></i>Koreksi Dulu
                                </button>
                                <button type="button" class="btn btn-primary px-4" id="btnSimpanFinal">
                                    <i class="bi bi-check-circle me-1"></i>Sudah Benar — Simpan Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    
    // Data jurnal dari server - di-load langsung dari database
    // Jika tidak ada data muncul, pastikan ada jurnal aktif di database
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
    
    console.log('=== DEBUG JOURNAL SEARCH ===');
    console.log('✅ Journal data loaded:', journals.length, 'journals');
    if (journals.length > 0) {
        console.log('📋 First 3 journals:');
        journals.slice(0, 3).forEach(function(j, idx) {
            console.log('  ' + (idx+1) + '. [' + j.id + '] ' + j.nama + ' - ' + j.publisher);
        });
    }
    console.log('=== END DEBUG ===');
    
    if (journals.length === 0) {
        console.error('❌ No journals found! Check database or controller.');
        searchInput.placeholder = '❌ Tidak ada data jurnal - silakan hubungi admin';
        searchInput.disabled = true;
        alert('Tidak ada data jurnal yang tersedia. Silakan tambahkan jurnal terlebih dahulu di menu Data Jurnal.');
        return;
    }
    
    let selectedJournalName = '';
    let isJournalSelected = false;
    
    // Search input
    searchInput.addEventListener('input', function() {
        // Jika jurnal sudah dipilih dan user mulai edit (tidak dimulai dengan ✓), clear selection
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
        
        console.log('📋 Found:', filtered.length, 'matches for "' + searchTerm + '"');
        
        if (filtered.length === 0) {
            searchResults.innerHTML = '<div class="list-group-item text-muted py-3">' +
                '<div><i class="bi bi-info-circle"></i> Tidak ada jurnal yang cocok dengan "<strong>' + escapeHtml(searchTerm) + '</strong>"</div>' +
                '<small class="text-muted mt-1 d-block">Coba kata kunci lain, atau <a href="{{ route("pic.journals.index") }}" target="_blank">lihat daftar jurnal</a></small>' +
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
        
        // Blur input to prevent keyboard staying open
        searchInput.blur();
        
        console.log('✅ Selected ID:', journalId);
        loadSlots(journalId);
    }
    
    // Edit selection - saat focus pada field yang sudah berisi selection
    searchInput.addEventListener('focus', function() {
        if (isJournalSelected && this.value.indexOf('✓') === 0) {
            // Jika user ingin edit, tampilkan nama jurnal tanpa ✓ dan select semua
            this.value = selectedJournalName;
            this.select();
            // Dropdown tidak ditampilkan sampai user mulai mengetik
        }
    });
    
    function loadSlots(journalId, restoreSlotId) {
        console.log('⏳ Loading slots...');
        
        slotSelect.innerHTML = '<option value="">⏳ Memuat...</option>';
        slotSelect.disabled = true;
        
        const url = '<?php echo url("pic/journal-slots/get-by-journal"); ?>?journal_master_id=' + journalId;
        
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

                // Restore slot yang dipilih sebelumnya (setelah validasi error)
                if (restoreSlotId) {
                    slotSelect.value = restoreSlotId;
                    if (!slotSelect.value) {
                        // Slot ID tidak ditemukan di daftar (mungkin sudah penuh)
                        console.warn('⚠️ Slot ID', restoreSlotId, 'tidak ditemukan.');
                    } else {
                        console.log('✅ Slot restored:', restoreSlotId);
                    }
                }
            })
            .catch(function(error) {
                console.error('❌ Error:', error);
                slotSelect.innerHTML = '<option value="">❌ Error</option>';
                slotSelect.disabled = false;
            });
    }
    
    // Tampilkan modal konfirmasi sebelum submit
    window.showKonfirmasi = function() {
        const slotEl = document.getElementById('journal_slot_id');
        const selectedOption = slotEl.options[slotEl.selectedIndex];

        if (!hiddenInput.value) {
            alert('⚠️ Harap pilih jurnal terlebih dahulu!');
            searchInput.focus();
            return;
        }
        if (!slotEl.value) {
            alert('⚠️ Harap pilih slot jurnal terlebih dahulu!');
            slotEl.focus();
            return;
        }
        if (selectedOption && selectedOption.disabled) {
            alert('🚫 SLOT SUDAH PENUH!\n\nSlot yang Anda pilih sudah tidak tersedia.\nSilakan pilih slot lain yang masih memiliki sisa kuota.');
            slotEl.focus();
            return;
        }

        const idArtikel   = document.getElementById('id_artikel').value.trim();
        const judulArtikel = document.getElementById('judul_artikel').value.trim();
        const namaPenulis  = document.getElementById('nama_penulis').value.trim();

        if (!idArtikel)    { alert('⚠️ ID Artikel wajib diisi!');    document.getElementById('id_artikel').focus();    return; }
        if (!judulArtikel) { alert('⚠️ Judul Artikel wajib diisi!'); document.getElementById('judul_artikel').focus(); return; }
        if (!namaPenulis)  { alert('⚠️ Nama Penulis wajib diisi!');  document.getElementById('nama_penulis').focus();  return; }

        const slotText       = selectedOption.text.replace(/[\u{1F6AB}⚠️✅]/gu, '').trim();
        const linkArtikel    = document.getElementById('link_artikel').value.trim();
        const linkPublishEl  = document.getElementById('link_publish');
        const linkPublish    = linkPublishEl ? linkPublishEl.value.trim() : '';
        const fileInput      = document.getElementById('file_artikel');
        const fileText       = fileInput && fileInput.files.length > 0 ? fileInput.files[0].name : '(tidak ada)';
        const noHp           = document.getElementById('no_hp_penulis').value.trim();
        const usernameAuthor = document.getElementById('username_author').value.trim();
        const passwordAuthor = document.getElementById('password_author').value.trim();
        const notes          = document.getElementById('notes').value.trim();
        const marketingEl    = document.getElementById('marketing_id');
        const marketingText  = marketingEl.selectedIndex > 0 ? marketingEl.options[marketingEl.selectedIndex].text.trim() : '(tidak dipilih)';

        const rows = [
            ['Jurnal',         selectedJournalName],
            ['Slot',           slotText],
            ['ID Artikel',     idArtikel],
            ['Judul Artikel',  judulArtikel],
            ['Link Submit',    linkArtikel  || '(kosong)'],
            ...(linkPublish    ? [['Link Publish',    linkPublish]]    : []),
            ['File Artikel',   fileText],
            ['Nama Penulis',   namaPenulis],
            ...(noHp           ? [['No HP Penulis',   noHp]]           : []),
            ...(usernameAuthor ? [['Username Author', usernameAuthor]] : []),
            ...(passwordAuthor ? [['Password Author', passwordAuthor]] : []),
            ...(notes          ? [['Catatan',         notes]]          : []),
            ['PIC Marketing',  marketingText],
        ];

        document.getElementById('konfirmasiData').innerHTML = rows.map(function(r) {
            return '<tr><th class="w-35 bg-light text-nowrap pe-3">' + r[0] + '</th><td>' + escapeHtml(String(r[1])) + '</td></tr>';
        }).join('');

        const alertEl = document.querySelector('#modalKonfirmasi .alert-warning');
        if (selectedOption.text.includes('HAMPIR PENUH')) {
            alertEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Perhatian: Slot hampir penuh!</strong> Periksa kembali data, lalu klik Simpan jika sudah yakin.';
        } else {
            alertEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Periksa kembali semua data di bawah ini.</strong> Data yang sudah tersimpan tidak bisa diubah sembarangan — pastikan sudah benar sebelum melanjutkan.';
        }

        bootstrap.Modal.getOrCreate(document.getElementById('modalKonfirmasi')).show();
    };

    document.getElementById('btnSimpanFinal').addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyimpan...';
        document.querySelector('form').submit();
    });
    
    // Restore old values setelah validasi error
    <?php if(old('journal_master_id')): ?>
        const oldId = '<?php echo old("journal_master_id"); ?>';
        const oldSlotId = '<?php echo old("journal_slot_id"); ?>';
        const oldJournal = journals.find(function(j) { return j.id == oldId; });
        if (oldJournal) {
            // selectJournal memanggil loadSlots; kita pass oldSlotId agar slot ikut ter-restore
            hiddenInput.value = oldId;
            selectedJournalName = oldJournal.nama;
            isJournalSelected = true;
            searchInput.value = '✓ ' + oldJournal.nama;
            searchInput.classList.add('is-valid');
            loadSlots(oldId, oldSlotId || null);
        }
    <?php endif; ?>
    
    // Auto-scroll ke ringkasan error validasi jika ada
    const errorSummary = document.getElementById('validation-error-summary');
    if (errorSummary) {
        errorSummary.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
        searchInput.focus();
    }
    
    console.log('✅ Ready! Type to search...');
});
</script>
@endsection
