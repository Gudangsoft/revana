@extends('marketing.layouts.app')

@section('title', 'Submit Artikel Baru')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Submit Artikel Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('marketing.submissions.store') }}">
                    @csrf
                    
                    <!-- Slot Jurnal -->
                    <div class="mb-3">
                        <label class="form-label">Slot Jurnal <span class="text-danger">*</span></label>
                        <select name="journal_slot_id" class="form-select @error('journal_slot_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Slot Jurnal --</option>
                            @foreach($slots as $slot)
                                <option value="{{ $slot->id }}" {{ old('journal_slot_id') == $slot->id ? 'selected' : '' }}>
                                    {{ $slot->journalMaster->nama_jurnal }} - 
                                    Volume {{ $slot->volume }}, Nomor {{ $slot->nomor }} 
                                    ({{ \Carbon\Carbon::parse($slot->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($slot->end_date)->format('d M Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('journal_slot_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Pilih slot jurnal yang masih aktif
                        </small>
                    </div>
                    
                    <!-- Judul Artikel -->
                    <div class="mb-3">
                        <label class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                        <textarea name="judul_artikel" class="form-control @error('judul_artikel') is-invalid @enderror" rows="3" required>{{ old('judul_artikel') }}</textarea>
                        @error('judul_artikel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Link Artikel -->
                    <div class="mb-3">
                        <label class="form-label">Link Artikel (Google Drive / Dropbox) <span class="text-danger">*</span></label>
                        <input type="url" name="link_artikel" class="form-control @error('link_artikel') is-invalid @enderror" value="{{ old('link_artikel') }}" placeholder="https://drive.google.com/..." required>
                        @error('link_artikel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Pastikan link dapat diakses oleh siapa saja
                        </small>
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="text-muted mb-3"><i class="bi bi-person"></i> Informasi Penulis</h6>
                    
                    <!-- Nama Penulis -->
                    <div class="mb-3">
                        <label class="form-label">Nama Penulis <span class="text-danger">*</span></label>
                        <input type="text" name="nama_penulis" class="form-control @error('nama_penulis') is-invalid @enderror" value="{{ old('nama_penulis') }}" required>
                        @error('nama_penulis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- No HP -->
                    <div class="mb-3">
                        <label class="form-label">No HP / WhatsApp Penulis</label>
                        <input type="text" name="no_hp_penulis" class="form-control @error('no_hp_penulis') is-invalid @enderror" value="{{ old('no_hp_penulis') }}" placeholder="08xxxxxxxxxx">
                        @error('no_hp_penulis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror>
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="text-muted mb-3"><i class="bi bi-key"></i> Author Access (Opsional)</h6>
                    <p class="small text-muted">Jika penulis sudah memiliki akun di sistem jurnal, isi username dan password</p>
                    
                    <div class="row">
                        <!-- Username Author -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username Author</label>
                            <input type="text" name="username_author" class="form-control @error('username_author') is-invalid @enderror" value="{{ old('username_author') }}" placeholder="username">
                            @error('username_author')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Password Author -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password Author</label>
                            <input type="text" name="password_author" class="form-control @error('password_author') is-invalid @enderror" value="{{ old('password_author') }}" placeholder="password">
                            @error('password_author')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill"></i> <strong>Catatan:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Pastikan semua data yang diisi sudah benar</li>
                            <li>Link artikel harus dapat diakses</li>
                            <li>Setelah submit, artikel akan diproses oleh tim PIC</li>
                            <li>Anda akan mendapat point setelah artikel berhasil dipublish</li>
                        </ul>
                    </div>
                    
                    <!-- Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="{{ route('marketing.submissions') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Submit Artikel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
