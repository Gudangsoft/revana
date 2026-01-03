@extends('layouts.app')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Manajemen Bidang Ilmu</h1>
        <div class="btn-group">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-excel"></i> Import Excel
            </button>
            <a href="{{ route('admin.field-of-studies.template') }}" class="btn btn-info">
                <i class="fas fa-download"></i> Template
            </a>
            <a href="{{ route('admin.field-of-studies.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Nama Bidang Ilmu</th>
                            <th style="width: 120px;">Urutan</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 120px;">Reviewer</th>
                            <th style="width: 120px;">Pendaftar</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fields as $field)
                            <tr>
                                <td>{{ ($fields->currentPage() - 1) * $fields->perPage() + $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $field->name }}</strong>
                                    @if($field->description)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($field->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $field->order }}</span>
                                </td>
                                <td>
                                    @if($field->is_active)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times"></i> Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $field->users_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $field->reviewer_registrations_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-cog"></i> Aksi
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.field-of-studies.edit', $field) }}">
                                                    <i class="fas fa-edit text-warning"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.field-of-studies.toggle', $field) }}" method="POST">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-toggle-{{ $field->is_active ? 'on' : 'off' }} text-{{ $field->is_active ? 'secondary' : 'success' }}"></i>
                                                        {{ $field->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $field->id }}">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal{{ $field->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Apakah Anda yakin ingin menghapus bidang ilmu <strong>{{ $field->name }}</strong>?</p>
                                                    @if($field->users_count > 0 || $field->reviewer_registrations_count > 0)
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            Bidang ilmu ini sedang digunakan oleh:
                                                            <ul class="mb-0 mt-2">
                                                                @if($field->users_count > 0)
                                                                    <li>{{ $field->users_count }} reviewer</li>
                                                                @endif
                                                                @if($field->reviewer_registrations_count > 0)
                                                                    <li>{{ $field->reviewer_registrations_count }} pendaftar</li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <form action="{{ route('admin.field-of-studies.destroy', $field) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Hapus</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data bidang ilmu.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($fields->hasPages())
                <div class="mt-3">
                    {{ $fields->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="fas fa-file-excel"></i> Import Bidang Ilmu dari Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.field-of-studies.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Format File Excel:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Kolom: <code>name</code>, <code>description</code>, <code>order</code>, <code>is_active</code></li>
                                <li>Atau: <code>nama</code>, <code>deskripsi</code>, <code>urutan</code>, <code>status</code></li>
                                <li>Baris pertama adalah header</li>
                                <li>Format: .xlsx, .xls, atau .csv</li>
                                <li>Maksimal 2MB</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">
                                Pilih File Excel <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control @error('file') is-invalid @enderror" 
                                   id="file" 
                                   name="file" 
                                   accept=".xlsx,.xls,.csv"
                                   required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Catatan:</strong> Jika bidang ilmu dengan nama yang sama sudah ada, data akan diupdate.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload"></i> Upload & Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
