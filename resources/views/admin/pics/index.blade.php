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
                    <a href="{{ route('admin.pics.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah PIC
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Username</th>
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
                                <td><code>{{ $pic->username ?? '-' }}</code></td>
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
                                    <a href="{{ route('admin.pics.edit', $pic) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.pics.destroy', $pic) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada data PIC</td>
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
@endsection
