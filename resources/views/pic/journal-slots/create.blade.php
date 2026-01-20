@extends('pic.layouts.app')

@section('title', 'Tambah Slot Jurnal')
@section('page-title', 'Tambah Slot Jurnal')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle"></i> Form Tambah Slot Jurnal
            </div>
            <div class="card-body">
                <form action="{{ route('pic.journal-slots.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Jurnal <span class="text-danger">*</span></label>
                        <select class="form-select @error('journal_master_id') is-invalid @enderror" name="journal_master_id" required>
                            <option value="">Pilih Jurnal</option>
                            @foreach($journals as $journal)
                                <option value="{{ $journal->id }}" {{ old('journal_master_id') == $journal->id ? 'selected' : '' }}>
                                    {{ $journal->nama_jurnal }}
                                </option>
                            @endforeach
                        </select>
                        @error('journal_master_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tahun <span class="text-danger">*</span></label>
                                <select class="form-select @error('tahun') is-invalid @enderror" name="tahun" required>
                                    @for($y = date('Y') + 1; $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ old('tahun', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                @error('tahun')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Bulan <span class="text-danger">*</span></label>
                                <select class="form-select @error('bulan') is-invalid @enderror" name="bulan" required>
                                    @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $m)
                                        <option value="{{ $i + 1 }}" {{ old('bulan', date('n')) == ($i + 1) ? 'selected' : '' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                                @error('bulan')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Volume</label>
                                <input type="text" class="form-control @error('volume') is-invalid @enderror" 
                                       name="volume" value="{{ old('volume') }}">
                                @error('volume')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nomor</label>
                                <input type="text" class="form-control @error('nomor') is-invalid @enderror" 
                                       name="nomor" value="{{ old('nomor') }}">
                                @error('nomor')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah Slot <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('jumlah_slot') is-invalid @enderror" 
                               name="jumlah_slot" value="{{ old('jumlah_slot', 10) }}" min="1" required>
                        @error('jumlah_slot')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <a href="{{ route('pic.journal-slots.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
