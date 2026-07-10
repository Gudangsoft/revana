@extends('layouts.app')

@section('title', 'Setting Kwitansi — ' . $journal->nama_jurnal)
@section('page-title', 'Setting Kwitansi')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">

        {{-- Breadcrumb --}}
        <nav class="mb-3" style="font-size:.85rem;">
            <a href="{{ route('admin.kwitansi-master.index') }}" class="text-decoration-none">Master Kwitansi</a>
            <span class="text-muted mx-1">/</span>
            <span class="text-muted">{{ $journal->nama_jurnal }}</span>
        </nav>

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header bg-primary bg-opacity-10 fw-semibold">
                <i class="bi bi-person-badge me-1"></i> Bendahara (Penandatangan Kwitansi)
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Nama & tanda tangan di sini dipakai khusus untuk kwitansi pembayaran —
                    <strong>terpisah</strong> dari nama/tanda tangan Editor-in-Chief yang dipakai di LOA.
                </p>

                <form action="{{ route('admin.kwitansi-master.update', $journal) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama Bendahara</label>
                        <input type="text" class="form-control @error('bendahara_name') is-invalid @enderror"
                               name="bendahara_name"
                               value="{{ old('bendahara_name', $journal->bendahara_name) }}"
                               placeholder="Contoh: Siti Aminah, S.E.">
                        @error('bendahara_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanda Tangan Bendahara <small class="text-muted">(PNG transparan, maks 2MB)</small></label>
                        @if($journal->bendahara_signature_path)
                        <div class="mb-2">
                            <img src="{{ Storage::url($journal->bendahara_signature_path) }}" height="50"
                                 style="border:1px solid #eee; padding:4px; background:#fff;" alt="TTD Bendahara">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="remove_signature" id="remove_signature" value="1">
                                <label class="form-check-label text-danger small" for="remove_signature">Hapus TTD</label>
                            </div>
                        </div>
                        @endif
                        <input type="file" class="form-control @error('bendahara_signature') is-invalid @enderror"
                               name="bendahara_signature" accept="image/*">
                        @error('bendahara_signature')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.kwitansi-master.index') }}" class="btn btn-secondary">
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
