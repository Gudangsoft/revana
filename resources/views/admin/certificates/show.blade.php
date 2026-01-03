@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Detail Sertifikat')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-award"></i> Detail Sertifikat
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Nama:</strong><br>
                    <h4>{{ $certificate->name }}</h4>
                </div>

                <div class="mb-3">
                    <strong>Deskripsi:</strong><br>
                    <p>{{ $certificate->description ?? '-' }}</p>
                </div>

                <div class="mb-3">
                    <strong>Status:</strong><br>
                    @if($certificate->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Tidak Aktif</span>
                    @endif
                </div>

                <div class="mb-3">
                    <strong>Preview Gambar:</strong><br>
                    <img src="{{ asset('storage/' . $certificate->file_path) }}" 
                         alt="{{ $certificate->name }}" 
                         style="max-width: 100%;"
                         class="img-thumbnail">
                </div>

                <div class="mb-3">
                    <strong>Dibuat:</strong> {{ $certificate->created_at->format('d M Y H:i') }}<br>
                    <strong>Terakhir Diupdate:</strong> {{ $certificate->updated_at->format('d M Y H:i') }}
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.certificates.edit', $certificate) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ asset('storage/' . $certificate->file_path) }}" download class="btn btn-info">
                        <i class="bi bi-download"></i> Download
                    </a>
                    <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <form action="{{ route('admin.certificates.destroy', $certificate) }}" 
                          method="POST" 
                          class="d-inline"
                          onsubmit="return confirm('Yakin ingin menghapus sertifikat ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Informasi
            </div>
            <div class="card-body">
                <p class="small">Sertifikat ini dapat digunakan untuk assignment review. Pastikan status aktif jika ingin menggunakannya.</p>
            </div>
        </div>
    </div>
</div>
@endsection
