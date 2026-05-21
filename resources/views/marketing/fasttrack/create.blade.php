@extends('marketing.layouts.app')

@section('title', 'Input Fasttrack')

@section('styles')
<style>
/* Style untuk slot penuh */
#journal_slot_id option:disabled {
    color: #dc3545 !important;
    background-color: #f8d7da !important;
    font-weight: bold;
}

#journal_slot_id option[disabled]::before {
    content: '🚫 ';
}
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-warning text-dark">
        <i class="bi bi-lightning-charge"></i> Input Submission Fasttrack
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

        <form action="{{ route('marketing.fasttrack.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle"></i> <strong>Kode Submit</strong> akan otomatis ter-generate dengan prefix <code>FT</code> (Fasttrack).
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
                        <input type="hidden" name="journal_master_id" id="journal_master_id" value="{{ old('journal_master_id') }}">
                        
                        <!-- Dropdown hasil pencarian -->
                        <div id="search_results" class="list-group position-absolute w-100 shadow" style="display: none; max-height: 350px; overflow-y: auto; z-index: 1050; background: white; border: 1px solid #dee2e6; border-radius: 0.25rem; margin-top: 2px;"></div>
                    </div>
                    <small class="text-muted d-block mt-1">
                        <i class="bi bi-info-circle"></i> Ketik minimal 1 huruf untuk mencari.
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
                    <label class="form-label">ID Artikel <span class="text-danger">*</span></label>
                    <input type="text" name="id_artikel" class="form-control @error('id_artikel') is-invalid @enderror" value="{{ old('id_artikel') }}" required>
                    @error('id_artikel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link Submit</label>
                    <input type="url" name="link_artikel" class="form-control @error('link_artikel') is-invalid @enderror" value="{{ old('link_artikel') }}" placeholder="https://">
                    @error('link_artikel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted"><i class="bi bi-info-circle"></i> Link Submit harus unik — setiap artikel harus memiliki link yang berbeda. Link yang sudah digunakan tidak dapat diinput ulang.</small>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Upload File Artikel (Word/PDF)</label>
                    <input type="file" name="file_artikel" class="form-control @error('file_artikel') is-invalid @enderror" accept=".doc,.docx,.pdf">
                    @error('file_artikel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Format: DOC, DOCX, PDF. Maksimal 50MB</small>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                    <textarea name="judul_artikel" class="form-control @error('judul_artikel') is-invalid @enderror" rows="2" required>{{ old('judul_artikel') }}</textarea>
                    @error('judul_artikel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Link Publish</label>
                    <input type="url" name="link_publish" class="form-control @error('link_publish') is-invalid @enderror" value="{{ old('link_publish') }}" placeholder="https://...">
                    <small class="text-muted"><i class="bi bi-link-45deg"></i> Link artikel yang sudah publish (Jika belum ada, artikel perlu penugasan oleh admin)</small>
                    @error('link_publish')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
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
                    <label class="form-label">No HP Penulis</label>
                    <input type="text" name="no_hp_penulis" class="form-control @error('no_hp_penulis') is-invalid @enderror" value="{{ old('no_hp_penulis') }}">
                    @error('no_hp_penulis')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Username Akses Author</label>
                    <input type="text" name="username_author" class="form-control @error('username_author') is-invalid @enderror" value="{{ old('username_author') }}">
                    @error('username_author')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password Akses Author</label>
                    <input type="text" name="password_author" class="form-control @error('password_author') is-invalid @enderror" value="{{ old('password_author') }}">
                    @error('password_author')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr>
            <h6 class="text-muted mb-3"><i class="bi bi-chat-left-text"></i> Catatan</h6>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('marketing.fasttrack.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="button" class="btn btn-warning" onclick="showKonfirmasi()">
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
                            <i class="bi bi-clipboard-check text-warning me-2"></i>Konfirmasi Data Fasttrack
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
                        <button type="button" class="btn btn-warning px-4" id="btnSimpanFinal">
                            <i class="bi bi-check-circle me-1"></i>Sudah Benar — Simpan Sekarang
                        </button>
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
        
        fetch(`{{ route('marketing.journal-slots.get-by-journal') }}?journal_master_id=${journalId}`)
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
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Tampilkan modal konfirmasi sebelum submit
    window.showKonfirmasi = function() {
        const selectedOption = slotSelect.options[slotSelect.selectedIndex];

        if (!journalMasterIdInput.value) {
            alert('⚠️ Harap pilih jurnal terlebih dahulu!');
            searchInput.focus();
            return;
        }
        if (!slotSelect.value) {
            alert('⚠️ Harap pilih slot jurnal terlebih dahulu!');
            slotSelect.focus();
            return;
        }
        if (selectedOption && selectedOption.disabled) {
            alert('🚫 SLOT SUDAH PENUH!\n\nSlot yang Anda pilih sudah tidak tersedia.\nSilakan pilih slot lain yang masih memiliki sisa kuota.');
            slotSelect.focus();
            return;
        }

        const idArtikel    = document.querySelector('[name="id_artikel"]').value.trim();
        const judulArtikel = document.querySelector('[name="judul_artikel"]').value.trim();
        const namaPenulis  = document.querySelector('[name="nama_penulis"]').value.trim();

        if (!idArtikel)    { alert('⚠️ ID Artikel wajib diisi!');    document.querySelector('[name="id_artikel"]').focus();    return; }
        if (!judulArtikel) { alert('⚠️ Judul Artikel wajib diisi!'); document.querySelector('[name="judul_artikel"]').focus(); return; }
        if (!namaPenulis)  { alert('⚠️ Nama Penulis wajib diisi!');  document.querySelector('[name="nama_penulis"]').focus();  return; }

        const slotText       = selectedOption.text.replace(/[\u{1F6AB}⚠️✅]/gu, '').replace('PENUH - TIDAK TERSEDIA','').trim();
        const linkArtikel    = document.querySelector('[name="link_artikel"]').value.trim();
        const linkPublish    = document.querySelector('[name="link_publish"]').value.trim();
        const fileInput      = document.querySelector('[name="file_artikel"]');
        const fileText       = fileInput && fileInput.files.length > 0 ? fileInput.files[0].name : '(tidak ada)';
        const noHp           = document.querySelector('[name="no_hp_penulis"]').value.trim();
        const usernameAuthor = document.querySelector('[name="username_author"]').value.trim();
        const passwordAuthor = document.querySelector('[name="password_author"]').value.trim();
        const notes          = document.querySelector('[name="notes"]').value.trim();

        const rows = [
            ['Jurnal',        searchInput.value],
            ['Slot',          slotText],
            ['ID Artikel',    idArtikel],
            ['Judul Artikel', judulArtikel],
            ['Link Submit',   linkArtikel  || '(kosong)'],
            ...(linkPublish    ? [['Link Publish',    linkPublish]]    : []),
            ['File Artikel',  fileText],
            ['Nama Penulis',  namaPenulis],
            ...(noHp           ? [['No HP Penulis',   noHp]]           : []),
            ...(usernameAuthor ? [['Username Author', usernameAuthor]] : []),
            ...(passwordAuthor ? [['Password Author', passwordAuthor]] : []),
            ...(notes          ? [['Catatan',         notes]]          : []),
        ];

        document.getElementById('konfirmasiData').innerHTML = rows.map(r =>
            `<tr><th class="w-35 bg-light text-nowrap pe-3">${r[0]}</th><td>${escapeHtml(String(r[1]))}</td></tr>`
        ).join('');

        bootstrap.Modal.getOrCreate(document.getElementById('modalKonfirmasi')).show();
    };

    document.getElementById('btnSimpanFinal').addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyimpan...';
        document.querySelector('form').submit();
    });
});
</script>
@endsection
