@extends('layouts.app')

@section('title', 'Kelola Akreditasi - ' . $appSettings['app_name'])
@section('page-title', 'Kelola Akreditasi')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-award"></i> Daftar Akreditasi</h5>
        <div class="btn-group">
            <a href="{{ route('admin.accreditations.template') }}" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-arrow-down"></i> Template
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-upload"></i> Import Excel
            </button>
            <a href="{{ route('admin.accreditations.export', request()->query()) }}" class="btn btn-info">
                <i class="bi bi-download"></i> Export Excel
            </a>
            <a href="{{ route('admin.accreditations.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Akreditasi
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Search Form -->
        <form action="{{ route('admin.accreditations.index') }}" method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Cari akreditasi..." value="{{ request('search') }}">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        @if(request('search'))
                        <a href="{{ route('admin.accreditations.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nama Akreditasi</th>
                        <th>Jumlah Jurnal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accreditations as $accreditation)
                    <tr>
                        <td>
                            <strong>{{ $accreditation->name }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $accreditation->journals_count }} jurnal</span>
                        </td>
                        <td>
                            @if($accreditation->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.accreditations.edit', $accreditation) }}" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('admin.accreditations.destroy', $accreditation) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Yakin ingin menghapus akreditasi ini?')"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Belum ada data akreditasi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.per-page-selector', ['paginator' => $accreditations, 'default' => 20])
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="bi bi-upload"></i> Import Data Akreditasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.accreditations.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Pilih File Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">Format yang didukung: .xlsx, .xls, .csv (Max: 5MB)</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Petunjuk Import</h6>
                        <ul class="mb-0 small">
                            <li>Download template terlebih dahulu untuk format yang benar</li>
                            <li>Kolom wajib: <strong>nama</strong></li>
                            <li>Kolom opsional: <strong>status</strong></li>
                            <li>Jika nama akreditasi sudah ada, data akan diperbarui</li>
                            <li>Status: "Aktif", "Ya", "Yes", "1" = Aktif. Lainnya = Nonaktif</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('admin.accreditations.template') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-file-earmark-arrow-down"></i> Download Template
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
