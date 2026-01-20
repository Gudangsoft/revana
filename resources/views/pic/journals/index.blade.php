@extends('pic.layouts.app')

@section('title', 'Data Jurnal')
@section('page-title', 'Data Jurnal')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-journal-text"></i> Daftar Jurnal</span>
        <a href="{{ route('pic.journals.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah Jurnal
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Jurnal</th>
                        <th>Penerbit</th>
                        <th>Link Jurnal</th>
                        <th>Akreditasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $journal)
                    <tr>
                        <td>{{ $loop->iteration + ($journals->currentPage() - 1) * $journals->perPage() }}</td>
                        <td><strong>{{ $journal->nama_jurnal }}</strong></td>
                        <td>{{ $journal->publisher ?? '-' }}</td>
                        <td>
                            @if($journal->link_jurnal)
                                <a href="{{ $journal->link_jurnal }}" target="_blank" class="text-primary">
                                    <i class="bi bi-link-45deg"></i> Link
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($journal->accreditation)
                                <span class="badge bg-info">{{ $journal->accreditation }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($journal->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('pic.journals.edit', $journal) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('pic.journals.destroy', $journal) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
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
                        <td colspan="7" class="text-center text-muted">Belum ada data jurnal</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $journals->links() }}
        </div>
    </div>
</div>
@endsection
