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
                <a href="{{ route('admin.journal-masters.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Jurnal
                </a>
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
                    @include('components.simple-pagination', ['paginator' => $journals])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
