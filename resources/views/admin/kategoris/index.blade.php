@extends('layouts.app')

@section('title', 'Data Kategori - ' . $appSettings['app_name'])
@section('page-title', 'Data Kategori')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-tags"></i> Data Kategori</span>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            @include('partials.column-toggle', ['tableId' => 'dataTable', 'columns' => ['Nama', 'Deskripsi', 'Status', 'Aksi'], 'columnOffset' => 1])
            <a href="{{ route('admin.kategoris.export') }}" class="btn btn-sm btn-info">
                <i class="bi bi-download"></i> Export Excel
            </a>
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-upload"></i> Import Excel
            </button>
            <a href="{{ route('admin.kategoris.template') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-file-earmark-arrow-down"></i> Template
            </a>
            <a href="{{ route('admin.kategoris.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover" id="dataTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategoris as $kategori)
                    <tr>
                        <td>{{ $loop->iteration + ($kategoris->currentPage() - 1) * $kategoris->perPage() }}</td>
                        <td><strong>{{ $kategori->name }}</strong></td>
                        <td>{{ $kategori->description ?? '-' }}</td>
                        <td>
                            @if($kategori->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.kategoris.edit', $kategori) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.kategoris.toggle-active', $kategori) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $kategori->is_active ? 'btn-secondary' : 'btn-success' }}">
                                        <i class="bi {{ $kategori->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.kategoris.destroy', $kategori) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Belum ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.per-page-selector', ['paginator' => $kategoris])
    </div>
</div>

{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upload"></i> Import Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.kategoris.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">File Excel / CSV</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Format: .xlsx, .xls, .csv — Maks 2MB</div>
                    </div>
                    <div class="alert alert-info py-2 mb-0" style="font-size:0.85rem;">
                        <strong>Kolom yang diperlukan:</strong><br>
                        <code>name</code> (wajib), <code>description</code>, <code>is_active</code> (1/0 atau Aktif/Nonaktif)<br>
                        <small class="text-muted">Data yang sudah ada (nama sama) akan diperbarui.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('admin.kategoris.template') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-file-earmark-arrow-down"></i> Download Template
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
