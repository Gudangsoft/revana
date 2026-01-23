@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Data Marketing')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-megaphone"></i> Daftar Marketing</span>
                <div class="btn-group">
                    <a href="{{ route('admin.marketings.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Marketing
                    </a>
                    <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                        <i class="bi bi-download"></i> Export/Import
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.marketings.export') }}">
                                <i class="bi bi-file-earmark-excel"></i> Export to Excel
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal">
                                <i class="bi bi-upload"></i> Import dari Excel
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.marketings.template') }}">
                                <i class="bi bi-file-earmark-arrow-down"></i> Download Template
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($marketings as $marketing)
                            <tr>
                                <td>{{ $loop->iteration + ($marketings->currentPage() - 1) * $marketings->perPage() }}</td>
                                <td><strong>{{ $marketing->name }}</strong></td>
                                <td>{{ $marketing->email ?? '-' }}</td>
                                <td>{{ $marketing->phone ?? '-' }}</td>
                                <td>
                                    @if($marketing->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.marketings.edit', $marketing) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($marketing->is_active)
                                        <form action="{{ route('admin.marketings.login-as', $marketing) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-info" title="Login sebagai {{ $marketing->name }}">
                                                <i class="bi bi-box-arrow-in-right"></i>
                                            </button>
                                        </form>
                                        @endif
                                        <form action="{{ route('admin.marketings.destroy', $marketing) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada data marketing</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    @include('components.simple-pagination', ['paginator' => $marketings])
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-upload"></i> Import Data Marketing
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.marketings.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Format File:</strong> Excel (.xlsx, .xls) atau CSV
                        <br>
                        <strong>Kolom yang diperlukan:</strong>
                        <ul class="mb-0 mt-2">
                            <li><code>nama</code> - Nama marketing (wajib)</li>
                            <li><code>email</code> - Email (opsional)</li>
                            <li><code>telepon</code> - Nomor telepon (opsional)</li>
                            <li><code>password</code> - Password (opsional, min 6 karakter)</li>
                            <li><code>status</code> - Aktif/Nonaktif (opsional, default: Aktif)</li>
                        </ul>
                        <div class="mt-2">
                            <a href="{{ route('admin.marketings.template') }}" class="text-decoration-none">
                                <i class="bi bi-download"></i> Download Template
                            </a>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Maksimal 2MB</div>
                    </div>
                    
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Perhatian:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Jika email sudah ada, data akan diupdate</li>
                            <li>Jika email baru, data akan ditambahkan</li>
                            <li>Password hanya diupdate jika diisi</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
