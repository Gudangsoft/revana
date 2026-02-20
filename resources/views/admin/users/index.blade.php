@extends('layouts.app')

@section('title', ' - Pengelolaan Pengguna')
@section('page-title', 'Pengelolaan Pengguna')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Pengguna</h5>
            <div class="d-flex gap-2">
                @include('partials.column-toggle', ['tableId' => 'dataTable', 'columns' => ['Nama', 'Email', 'Role', 'Tanggal Dibuat', 'Aksi'], 'columnOffset' => 1])
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Pengguna
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <form action="{{ route('admin.users.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau role..." value="{{ $search ?? '' }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            @if(request('search'))
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="dataTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge bg-danger">Admin</span>
                                @elseif($user->role === 'reviewer')
                                    <span class="badge bg-primary">Reviewer</span>
                                @else
                                    <span class="badge bg-info">{{ ucfirst($user->role) }}</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin mereset password pengguna ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-info">
                                            <i class="bi bi-key"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
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
                            <td colspan="6" class="text-center">Tidak ada data pengguna</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
            <div class="mt-3">
                <nav>
                    <ul class="pagination justify-content-center">
                        <!-- Previous Button -->
                        @if($users->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">Kembali</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->previousPageUrl() }}">Kembali</a>
                            </li>
                        @endif

                        <!-- Page Numbers -->
                        @php
                            $currentPage = $users->currentPage();
                            $lastPage = $users->lastPage();
                            $range = 2; // Show 2 pages on each side of current page
                        @endphp

                        <!-- First Page -->
                        @if($currentPage > $range + 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->url(1) }}">1</a>
                            </li>
                            @if($currentPage > $range + 2)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                        @endif

                        <!-- Pages around current page -->
                        @for($i = max(1, $currentPage - $range); $i <= min($lastPage, $currentPage + $range); $i++)
                            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                <a class="page-link" href="{{ $users->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        <!-- Last Page -->
                        @if($currentPage < $lastPage - $range)
                            @if($currentPage < $lastPage - $range - 1)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->url($lastPage) }}">{{ $lastPage }}</a>
                            </li>
                        @endif

                        <!-- Next Button -->
                        @if($users->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->nextPageUrl() }}">Lanjut</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">Lanjut</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
