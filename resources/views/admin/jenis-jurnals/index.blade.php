@extends('layouts.app')

@section('title', 'Data Jenis Jurnal - ' . $appSettings['app_name'])
@section('page-title', 'Data Jenis Jurnal')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-journal-bookmark"></i> Data Jenis Jurnal</span>
        <a href="{{ route('admin.jenis-jurnals.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover">
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
                    @forelse($jenisJurnals as $jenis)
                    <tr>
                        <td>{{ $loop->iteration + ($jenisJurnals->currentPage() - 1) * $jenisJurnals->perPage() }}</td>
                        <td><strong>{{ $jenis->name }}</strong></td>
                        <td>{{ $jenis->description ?? '-' }}</td>
                        <td>
                            @if($jenis->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.jenis-jurnals.edit', $jenis) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.jenis-jurnals.toggle-active', $jenis) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $jenis->is_active ? 'btn-secondary' : 'btn-success' }}">
                                        <i class="bi {{ $jenis->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.jenis-jurnals.destroy', $jenis) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
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

        @include('partials.per-page-selector', ['paginator' => $jenisJurnals])
    </div>
</div>
@endsection
