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
                <div class="d-flex gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
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
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                        <i class="bi bi-key-fill"></i> Reset Semua Password
                    </button>
                    <a href="{{ route('admin.marketings.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Marketing
                    </a>
                    @include('partials.column-toggle', ['tableId' => 'dataTable', 'columns' => ['Nama', 'Email', 'Telepon', 'Status', 'Aksi'], 'columnOffset' => 1])
                </div>
            </div>
            <div class="card-body">
                <!-- Search Form -->
                <form method="GET" action="{{ route('admin.marketings.index') }}" class="mb-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" placeholder="🔍 Cari nama, email, atau telepon..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                                <a href="{{ route('admin.marketings.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="dataTable">
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
                                        <form action="{{ route('admin.marketings.reset-password', $marketing) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin mereset password {{ $marketing->name }}?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-secondary" title="Reset Password">
                                                <i class="bi bi-key"></i>
                                            </button>
                                        </form>
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

                @include('partials.per-page-selector', ['paginator' => $marketings, 'default' => 20])
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

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-key-fill"></i> Reset Semua Password Marketing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.marketings.reset-all-passwords') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong>Perhatian!</strong><br>
                        Tindakan ini akan mereset password <strong>SEMUA MARKETING</strong> ke password default.
                    </div>
                    <div class="alert alert-info mb-0">
                        <strong>Password Default:</strong><br>
                        <code class="fs-5">marketing123</code><br>
                        <small class="text-muted">Semua marketing akan menggunakan password ini untuk login</small>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="confirmResetMarketing" required>
                        <label class="form-check-label" for="confirmResetMarketing">
                            Saya memahami dan ingin melanjutkan reset password semua marketing
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btnResetPasswordMarketing">
                        <i class="bi bi-key-fill"></i> Reset Semua Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const resetPasswordFormMarketing = document.querySelector('#resetPasswordModal form');
const btnResetPasswordMarketing = document.getElementById('btnResetPasswordMarketing');

if (resetPasswordFormMarketing) {
    resetPasswordFormMarketing.addEventListener('submit', function() {
        // Disable button and show loading
        btnResetPasswordMarketing.disabled = true;
        btnResetPasswordMarketing.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
        
        // Show loading overlay
        const modal = document.getElementById('resetPasswordModal');
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.8);display:flex;align-items:center;justify-content:center;z-index:9999;';
        overlay.innerHTML = '<div class="spinner-border text-warning" role="status"></div>';
        modal.querySelector('.modal-content').style.position = 'relative';
        modal.querySelector('.modal-content').appendChild(overlay);
    });
}
</script>
@endpush
@endsection
