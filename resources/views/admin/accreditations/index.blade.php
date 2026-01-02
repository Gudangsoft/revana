@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Kelola Akreditasi')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-award"></i> Daftar Akreditasi</h5>
        <a href="{{ route('admin.accreditations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Akreditasi
        </a>
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

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nama Akreditasi</th>
                        <th>Points</th>
                        <th>Deskripsi</th>
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
                            <span class="badge bg-primary">{{ $accreditation->points }} points</span>
                        </td>
                        <td>
                            <small class="text-muted">{{ $accreditation->description ?? '-' }}</small>
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

        @if($accreditations->hasPages())
        <div class="mt-3">
            {{ $accreditations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
