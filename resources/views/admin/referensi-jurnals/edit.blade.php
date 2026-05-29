@extends('layouts.app')

@section('title', 'Edit Referensi Jurnal - ' . $appSettings['app_name'])
@section('page-title', 'Edit Referensi Jurnal')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <i class="bi bi-pencil-square"></i> Edit Referensi Jurnal
    </div>
    <div class="card-body">
        <form action="{{ route('admin.referensi-jurnals.update', $referensiJurnal) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="nama_jurnal" class="form-label">Nama Jurnal <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nama_jurnal') is-invalid @enderror"
                       id="nama_jurnal" name="nama_jurnal" value="{{ old('nama_jurnal', $referensiJurnal->nama_jurnal) }}" required>
                @error('nama_jurnal')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="jenis_jurnal" class="form-label">Jenis Jurnal <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('jenis_jurnal') is-invalid @enderror"
                           id="jenis_jurnal" name="jenis_jurnal" value="{{ old('jenis_jurnal', $referensiJurnal->jenis_jurnal) }}" required>
                    @error('jenis_jurnal')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="bidang_ilmu" class="form-label">Bidang Ilmu <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('bidang_ilmu') is-invalid @enderror"
                           id="bidang_ilmu" name="bidang_ilmu" value="{{ old('bidang_ilmu', $referensiJurnal->bidang_ilmu) }}" required>
                    @error('bidang_ilmu')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="tahun" class="form-label">Tahun <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('tahun') is-invalid @enderror"
                       id="tahun" name="tahun" value="{{ old('tahun', $referensiJurnal->tahun) }}"
                       min="1900" max="2100" required style="max-width:150px;">
                @error('tahun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="referensi" class="form-label">Referensi <span class="text-danger">*</span></label>
                <textarea class="form-control @error('referensi') is-invalid @enderror"
                          id="referensi" name="referensi" rows="4">{{ old('referensi', $referensiJurnal->referensi) }}</textarea>
                @error('referensi')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update
                </button>
                <a href="{{ route('admin.referensi-jurnals.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
