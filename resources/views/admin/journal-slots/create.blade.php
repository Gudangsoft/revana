@extends('layouts.app')

@section('title', 'Tambah Slot - ' . $appSettings['app_name'])
@section('page-title', 'Tambah Slot')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-plus"></i> Tambah Data Slot
            </div>
            <div class="card-body">
                <form action="{{ route('admin.journal-slots.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="journal_master_id" class="form-label">Nama Jurnal <span class="text-danger">*</span></label>
                        <select class="form-select @error('journal_master_id') is-invalid @enderror" id="journal_master_id" name="journal_master_id" required>
                            <option value="">-- Pilih Jurnal --</option>
                            @foreach($journals as $journal)
                                <option value="{{ $journal->id }}" {{ old('journal_master_id', request('journal_master_id')) == $journal->id ? 'selected' : '' }}>
                                    {{ $journal->nama_jurnal }} ({{ $journal->publisher }})
                                </option>
                            @endforeach
                        </select>
                        @error('journal_master_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="kode_slot" class="form-label">Kode Slot <small class="text-muted">(Otomatis jika kosong)</small></label>
                        <input type="text" class="form-control @error('kode_slot') is-invalid @enderror" id="kode_slot" name="kode_slot" value="{{ old('kode_slot') }}" placeholder="Contoh: SLT20260001">
                        @error('kode_slot')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="volume" class="form-label">Volume <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('volume') is-invalid @enderror" id="volume" name="volume" value="{{ old('volume') }}" placeholder="Contoh: 1, 2, 3..." required>
                                @error('volume')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nomor" class="form-label">Nomor <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nomor') is-invalid @enderror" id="nomor" name="nomor" value="{{ old('nomor') }}" placeholder="Contoh: 1, 2, 3..." required>
                                @error('nomor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bulan" class="form-label">Bulan <span class="text-danger">*</span></label>
                                <select class="form-select @error('bulan') is-invalid @enderror" id="bulan" name="bulan" required>
                                    <option value="">-- Pilih Bulan --</option>
                                    @foreach($bulanOptions as $key => $value)
                                        <option value="{{ $key }}" {{ old('bulan') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('bulan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tahun" class="form-label">Tahun <span class="text-danger">*</span></label>
                                <select class="form-select @error('tahun') is-invalid @enderror" id="tahun" name="tahun" required>
                                    <option value="">-- Pilih Tahun --</option>
                                    @for($y = date('Y') + 2; $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ old('tahun', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                @error('tahun')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="jumlah_slot" class="form-label">Jumlah Slot <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('jumlah_slot') is-invalid @enderror" id="jumlah_slot" name="jumlah_slot" value="{{ old('jumlah_slot', 10) }}" min="1" required>
                        @error('jumlah_slot')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.journal-slots.index') }}" class="btn btn-secondary">
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
