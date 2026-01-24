@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Data PIC')

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

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-badge"></i> Daftar PIC</span>
                <div class="d-flex gap-2">
                    <div class="btn-group">
                        <a href="{{ route('admin.pics.export') }}" class="btn btn-success">
                            <i class="bi bi-download"></i> Export
                        </a>
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </div>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                        <i class="bi bi-key-fill"></i> Reset Semua Password
                    </button>
                    <a href="{{ route('admin.pics.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah PIC
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3 d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Cari Nama, Email, atau Telepon" value="{{ request('search') }}" style="max-width:300px">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Cari</button>
                    @if(request('search'))
                    <a href="{{ route('admin.pics.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
                    @endif
                </form>
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
                            @forelse($pics as $pic)
                            <tr>
                                <td>{{ $loop->iteration + ($pics->currentPage() - 1) * $pics->perPage() }}</td>
                                <td><strong>{{ $pic->name }}</strong></td>
                                <td>{{ $pic->email ?? '-' }}</td>
                                <td>{{ $pic->phone ?? '-' }}</td>
                                <td>
                                    @if($pic->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.pics.edit', $pic) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.pics.reset-password', $pic) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin mereset password {{ $pic->name }}?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-secondary" title="Reset Password">
                                                <i class="bi bi-key"></i>
                                            </button>
                                        </form>
                                        @if($pic->is_active)
                                        <form action="{{ route('admin.pics.login-as', $pic) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-info" title="Login sebagai {{ $pic->name }}">
                                                <i class="bi bi-box-arrow-in-right"></i>
                                            </button>
                                        </form>
                                        @endif
                                        <form action="{{ route('admin.pics.destroy', $pic) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
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
                                <td colspan="6" class="text-center text-muted">Belum ada data PIC</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    @include('components.simple-pagination', ['paginator' => $pics])
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upload"></i> Import Data PIC</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.pics.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Format File:</strong><br>
                        - Excel (.xlsx, .xls) atau CSV<br>
                        - Kolom: Nama, Email, Telepon, Status<br>
                        - Status: Aktif/Nonaktif atau 1/0
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File</label>
                        <input type="file" class="form-control" name="file" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="mb-3">
                        <a href="{{ route('admin.pics.template') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-download"></i> Download Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">
                        <i class="bi bi-upload"></i> Import
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
                <h5 class="modal-title"><i class="bi bi-key-fill"></i> Reset Semua Password PIC</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.pics.reset-all-passwords') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong>Perhatian!</strong><br>
                        Tindakan ini akan mereset password <strong>SEMUA PIC</strong> ke password default.
                    </div>
                    <div class="alert alert-info mb-0">
                        <strong>Password Default:</strong><br>
                        <code class="fs-5">pic123</code><br>
                        <small class="text-muted">Semua PIC akan menggunakan password ini untuk login</small>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="confirmReset" required>
                        <label class="form-check-label" for="confirmReset">
                            Saya memahami dan ingin melanjutkan reset password semua PIC
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btnResetPassword">
                        <i class="bi bi-key-fill"></i> Reset Semua Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const resetPasswordForm = document.querySelector('#resetPasswordModal form');
const btnResetPassword = document.getElementById('btnResetPassword');

if (resetPasswordForm) {
    resetPasswordForm.addEventListener('submit', function() {
        // Disable button and show loading
        btnResetPassword.disabled = true;
        btnResetPassword.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
        
        // Show loading overlay
        const modal = document.getElementById('resetPasswordModal');
        const modalBody = modal.querySelector('.modal-body');
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
