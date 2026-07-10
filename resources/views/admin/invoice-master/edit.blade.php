@extends('layouts.app')

@section('title', 'Setting Invoice — ' . $journal->nama_jurnal)
@section('page-title', 'Setting Invoice')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">

        {{-- Breadcrumb --}}
        <nav class="mb-3" style="font-size:.85rem;">
            <a href="{{ route('admin.invoice-master.index') }}" class="text-decoration-none">Master Invoice</a>
            <span class="text-muted mx-1">/</span>
            <span class="text-muted">{{ $journal->nama_jurnal }}</span>
        </nav>

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header bg-primary bg-opacity-10 fw-semibold">
                <i class="bi bi-bank me-1"></i> Info Rekening Bank
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Info rekening ini otomatis muncul tiap kali invoice untuk jurnal ini dibuat —
                    tetap bisa diubah manual di halaman invoice untuk kasus tertentu tanpa mengubah setting ini.
                </p>

                <form action="{{ route('admin.invoice-master.update', $journal) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama Bank</label>
                        <input type="text" class="form-control @error('bank_name') is-invalid @enderror"
                               name="bank_name"
                               value="{{ old('bank_name', $journal->bank_name) }}"
                               placeholder="Contoh: Bank BCA">
                        @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor Rekening</label>
                        <input type="text" class="form-control @error('bank_account_number') is-invalid @enderror"
                               name="bank_account_number"
                               value="{{ old('bank_account_number', $journal->bank_account_number) }}"
                               placeholder="Contoh: 1234567890">
                        @error('bank_account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Atas Nama Rekening</label>
                        <input type="text" class="form-control @error('bank_account_holder') is-invalid @enderror"
                               name="bank_account_holder"
                               value="{{ old('bank_account_holder', $journal->bank_account_holder) }}"
                               placeholder="Contoh: PT. ALHAFI BERKAH INDONESIA">
                        @error('bank_account_holder')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.invoice-master.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
