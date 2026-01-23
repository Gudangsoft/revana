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
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Slot Jurnal <span class="text-danger">*</span></label>
                    <select name="journal_slot_id" class="form-select @error('journal_slot_id') is-invalid @enderror" required>
                        <option value="">Pilih Slot</option>
                        @foreach($slots as $slot)
                            <option value="{{ $slot->id }}" {{ old('journal_slot_id') == $slot->id ? 'selected' : '' }}>
                                {{ $slot->journalMaster->nama_jurnal ?? 'Unknown' }} - Vol. {{ $slot->volume }} No. {{ $slot->nomor }} ({{ $slot->tahun }})
                            </option>
                        @endforeach
                    </select>
                    @error('journal_slot_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">ID Artikel</label>
                    <input type="text" name="id_artikel" class="form-control @error('id_artikel') is-invalid @enderror" value="{{ old('id_artikel') }}" placeholder="Opsional">
                    @error('id_artikel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                <input type="text" name="judul_artikel" class="form-control @error('judul_artikel') is-invalid @enderror" value="{{ old('judul_artikel') }}" required>
                @error('judul_artikel')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label class="form-label">Link Artikel</label>
                <input type="url" name="link_artikel" class="form-control @error('link_artikel') is-invalid @enderror" value="{{ old('link_artikel') }}" placeholder="https://...">
                @error('link_artikel')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
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
document.getElementById('submissionForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
});
</script>
@endsection

@endsection
