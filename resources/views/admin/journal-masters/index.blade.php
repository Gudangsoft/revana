@extends('layouts.app')

@section('title', 'Data Jurnal - ' . $appSettings['app_name'])
@section('page-title', 'Data Jurnal')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-bookmark"></i> Data Jurnal</span>
                <div class="btn-group">
                    <a href="{{ route('admin.journal-masters.template') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-file-earmark-arrow-down"></i> Template
                    </a>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-upload"></i> Import
                    </button>
                    <a href="{{ route('admin.journal-masters.export', request()->query()) }}" class="btn btn-info">
                        <i class="bi bi-download"></i> Export
                    </a>
                    <a href="{{ route('admin.journal-masters.create') }}" class="btn btn-primary">
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

                <!-- Search & Filter Form -->
                <form action="{{ route('admin.journal-masters.index') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Cari jurnal..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="accreditation">
                                <option value="">-- Semua Akreditasi --</option>
                                @foreach($accreditations as $acc)
                                <option value="{{ $acc->name }}" {{ request('accreditation') == $acc->name ? 'selected' : '' }}>{{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">-- Semua Status --</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            @if(request()->hasAny(['search', 'accreditation', 'status']))
                            <a href="{{ route('admin.journal-masters.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Jurnal</th>
                                <th>Nama Jurnal</th>
                                <th>Publisher</th>
                                <th>Link Jurnal</th>
                                <th>Akreditasi</th>
                                <th>Total Slot</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journals as $journal)
                            <tr>
                                <td>{{ $loop->iteration + ($journals->currentPage() - 1) * $journals->perPage() }}</td>
                                <td><code>{{ $journal->kode_jurnal }}</code></td>
                                <td>
                                    <strong>{{ Str::limit($journal->nama_jurnal, 50) }}</strong>
                                </td>
                                <td>{{ Str::limit($journal->publisher, 30) }}</td>
                                <td>
                                    <a href="{{ $journal->link_jurnal }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-link-45deg"></i> Buka
                                    </a>
                                </td>
                                <td>
                                    @if($journal->accreditation)
                                        <span class="badge bg-info">{{ $journal->accreditation }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $journal->total_slots }}</span>
                                    <small class="text-muted">({{ $journal->used_slots }} terpakai)</small>
                                </td>
                                <td>
                                    @if($journal->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.journal-masters.show', $journal) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.journal-masters.edit', $journal) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.journal-masters.toggle-active', $journal) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $journal->is_active ? 'btn-secondary' : 'btn-success' }}" title="{{ $journal->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="bi {{ $journal->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.journal-masters.destroy', $journal) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jurnal ini?')">
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
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada data jurnal
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    @include('components.simple-pagination', ['paginator' => $journals->withQueryString()])
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel"><i class="bi bi-upload"></i> Import Data Jurnal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.journal-masters.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">File Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">Format: xlsx, xls, csv. Maksimal 5MB</small>
                    </div>
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Petunjuk Import:</h6>
                        <ul class="mb-0 small">
                            <li>Download template terlebih dahulu untuk format yang benar</li>
                            <li>Kolom wajib: <strong>nama_jurnal</strong>, <strong>publisher</strong>, <strong>link_jurnal</strong></li>
                            <li>Kolom opsional: kode_jurnal, akreditasi, status</li>
                            <li>Jika kode_jurnal kosong, akan digenerate otomatis</li>
                            <li>Jika nama jurnal sudah ada, data akan diperbarui</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
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
