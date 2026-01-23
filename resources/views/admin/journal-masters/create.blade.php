@extends('layouts.app')

@section('title', 'Tambah Jurnal - ' . $appSettings['app_name'])
@section('page-title', 'Tambah Jurnal')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-journal-plus"></i> Tambah Data Jurnal
            </div>
            <div class="card-body">
                <form action="{{ route('admin.journal-masters.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="kode_jurnal" class="form-label">Kode Jurnal <small class="text-muted">(Otomatis jika kosong)</small></label>
                        <input type="text" class="form-control @error('kode_jurnal') is-invalid @enderror" id="kode_jurnal" name="kode_jurnal" value="{{ old('kode_jurnal') }}" placeholder="Contoh: JRN20260001">
                        @error('kode_jurnal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nama_jurnal" class="form-label">Nama Jurnal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_jurnal') is-invalid @enderror" id="nama_jurnal" name="nama_jurnal" value="{{ old('nama_jurnal') }}" required>
                        @error('nama_jurnal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="publisher" class="form-label">Publisher <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('publisher') is-invalid @enderror" id="publisher" name="publisher" value="{{ old('publisher') }}" required>
                        @error('publisher')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="link_jurnal" class="form-label">Link Jurnal <span class="text-danger">*</span></label>
                        <input type="url" class="form-control @error('link_jurnal') is-invalid @enderror" id="link_jurnal" name="link_jurnal" value="{{ old('link_jurnal') }}" placeholder="https://" required>
                        @error('link_jurnal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="accreditation" class="form-label">Akreditasi</label>
                        <select class="form-select @error('accreditation') is-invalid @enderror" id="accreditation" name="accreditation">
                            <option value="">-- Pilih Akreditasi --</option>
                            @foreach($accreditations as $acc)
                                <option value="{{ $acc->name }}" {{ old('accreditation') == $acc->name ? 'selected' : '' }}>
                                    {{ $acc->name }} ({{ $acc->points }} pts)
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
                                <label for="kategori" class="form-label">Kategori</label>
                                <select class="form-select @error('kategori') is-invalid @enderror" id="kategori" name="kategori">
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
                                <label for="jenis_jurnal" class="form-label">Jenis Jurnal</label>
                                <select class="form-select @error('jenis_jurnal') is-invalid @enderror" id="jenis_jurnal" name="jenis_jurnal">
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

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.journal-masters.index') }}" class="btn btn-secondary">
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
