@extends('layouts.app')

@section('title', 'Tambah Submit - ' . $appSettings['app_name'])
@section('page-title', 'Tambah Submit')

@section('sidebar')
    @include('admin.partials.sidebar')
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

                <form action="{{ route('admin.submissions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="journal_master_id" class="form-label">Pilih Jurnal <span class="text-danger">*</span></label>
                                <select class="form-select @error('journal_master_id') is-invalid @enderror" id="journal_master_id" name="journal_master_id" required>
                                    <option value="">-- Pilih Jurnal --</option>
                                    @foreach($journals as $journal)
                                        <option value="{{ $journal->id }}" {{ old('journal_master_id') == $journal->id ? 'selected' : '' }}>
                                            {{ $journal->nama_jurnal }} ({{ $journal->publisher }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('journal_master_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
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

                    <hr>
                    <h6 class="text-muted mb-3"><i class="bi bi-people"></i> PIC & Petugas</h6>

                    <div class="mb-3">
                        <label for="pic_marketing" class="form-label">PIC Marketing</label>
                        <input type="text" class="form-control @error('pic_marketing') is-invalid @enderror" id="pic_marketing" name="pic_marketing" value="{{ old('pic_marketing') }}" placeholder="Isi manual">
                        @error('pic_marketing')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Petugas Submit akan terisi otomatis dengan user yang sedang login.
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.submissions.index') }}" class="btn btn-secondary">
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

@push('scripts')
<script>
document.getElementById('journal_master_id').addEventListener('change', function() {
    const journalId = this.value;
    const slotSelect = document.getElementById('journal_slot_id');
    
    if (!journalId) {
        slotSelect.innerHTML = '<option value="">-- Pilih Jurnal terlebih dahulu --</option>';
        return;
    }
    
    slotSelect.innerHTML = '<option value="">Loading...</option>';
    
    fetch(`{{ url('admin/journal-slots/get-by-journal') }}?journal_master_id=${journalId}`)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">-- Pilih Slot --</option>';
            data.forEach(slot => {
                options += `<option value="${slot.id}">${slot.text}</option>`;
            });
            slotSelect.innerHTML = options;
        })
        .catch(error => {
            slotSelect.innerHTML = '<option value="">Error loading slots</option>';
        });
});
</script>
@endpush
@endsection
