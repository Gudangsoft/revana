@extends('layouts.app')

@section('title', 'Edit Slot - ' . $appSettings['app_name'])
@section('page-title', 'Edit Slot')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar3"></i> Edit Data Slot
            </div>
            <div class="card-body">
                <form action="{{ route('admin.journal-slots.update', $journalSlot) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="journal_master_id" class="form-label">Nama Jurnal <span class="text-danger">*</span></label>
                        <select class="form-select @error('journal_master_id') is-invalid @enderror" id="journal_master_id" name="journal_master_id" required>
                            <option value="">-- Pilih Jurnal --</option>
                            @foreach($journals as $journal)
                                <option value="{{ $journal->id }}" {{ old('journal_master_id', $journalSlot->journal_master_id) == $journal->id ? 'selected' : '' }}>
                                    {{ $journal->nama_jurnal }} ({{ $journal->publisher }})
                                </option>
                            @endforeach
                        </select>
                        @error('journal_master_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="kode_slot" class="form-label">Kode Slot <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('kode_slot') is-invalid @enderror" id="kode_slot" name="kode_slot" value="{{ old('kode_slot', $journalSlot->kode_slot) }}" required>
                        @error('kode_slot')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="volume" class="form-label">Volume <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('volume') is-invalid @enderror" id="volume" name="volume" value="{{ old('volume', $journalSlot->volume) }}" required>
                                @error('volume')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nomor" class="form-label">Nomor <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nomor') is-invalid @enderror" id="nomor" name="nomor" value="{{ old('nomor', $journalSlot->nomor) }}" required>
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
                                        <option value="{{ $key }}" {{ old('bulan', $journalSlot->bulan) == $key ? 'selected' : '' }}>{{ $value }}</option>
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
                                        <option value="{{ $y }}" {{ old('tahun', $journalSlot->tahun) == $y ? 'selected' : '' }}>{{ $y }}</option>
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
                        <input type="number" class="form-control @error('jumlah_slot') is-invalid @enderror" id="jumlah_slot" name="jumlah_slot" value="{{ old('jumlah_slot', $journalSlot->jumlah_slot) }}" min="{{ $journalSlot->slot_terpakai }}" required>
                        <small class="text-muted">Minimal: {{ $journalSlot->slot_terpakai }} (slot yang sudah terpakai)</small>
                        @error('jumlah_slot')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $journalSlot->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Aktif
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Info Slot:</strong>
                        Terpakai: <span class="badge bg-warning">{{ $journalSlot->slot_terpakai }}</span> |
                        Tersedia: <span class="badge bg-success">{{ $journalSlot->slot_tersedia }}</span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.journal-slots.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
