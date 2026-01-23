@extends('pic.layouts.app')

@section('title', 'Tambah Jurnal')
@section('page-title', 'Tambah Jurnal')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle"></i> Form Tambah Jurnal
            </div>
            <div class="card-body">
                <form action="{{ route('pic.journals.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Nama Jurnal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_jurnal') is-invalid @enderror" 
                               name="nama_jurnal" value="{{ old('nama_jurnal') }}" required>
                        @error('nama_jurnal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Penerbit</label>
                        <input type="text" class="form-control @error('publisher') is-invalid @enderror" 
                               name="publisher" value="{{ old('publisher') }}">
                        @error('publisher')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link Jurnal</label>
                        <input type="url" class="form-control @error('link_jurnal') is-invalid @enderror" 
                               name="link_jurnal" value="{{ old('link_jurnal') }}" placeholder="https://...">
                        @error('link_jurnal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Akreditasi</label>
                        <select class="form-select @error('accreditation') is-invalid @enderror" name="accreditation">
                            <option value="">Pilih Akreditasi</option>
                            @foreach($accreditations as $accreditation)
                                <option value="{{ $accreditation->name }}" {{ old('accreditation') == $accreditation->name ? 'selected' : '' }}>
                                    {{ $accreditation->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('accreditation')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select class="form-select @error('kategori') is-invalid @enderror" name="kategori">
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Penelitian" {{ old('kategori') == 'Penelitian' ? 'selected' : '' }}>Penelitian</option>
                                    <option value="PKM" {{ old('kategori') == 'PKM' ? 'selected' : '' }}>PKM</option>
                                </select>
                                @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jenis Jurnal</label>
                                <select class="form-select @error('jenis_jurnal') is-invalid @enderror" name="jenis_jurnal">
                                    <option value="">-- Pilih Jenis Jurnal --</option>
                                    <option value="Jurnal Nasional" {{ old('jenis_jurnal') == 'Jurnal Nasional' ? 'selected' : '' }}>Jurnal Nasional</option>
                                    <option value="Jurnal Internasional" {{ old('jenis_jurnal') == 'Jurnal Internasional' ? 'selected' : '' }}>Jurnal Internasional</option>
                                </select>
                                @error('jenis_jurnal')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                   id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Status Aktif
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <a href="{{ route('pic.journals.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
