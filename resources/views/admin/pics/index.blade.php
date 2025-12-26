@extends('layouts.app')

@section('title', 'Data PIC - REVANA')
@section('page-title', 'Data PIC')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.journals.index') }}" class="nav-link">
        <i class="bi bi-journal-text"></i> Jurnal
    </a>
    <a href="{{ route('admin.assignments.index') }}" class="nav-link">
        <i class="bi bi-clipboard-check"></i> Review Assignments
    </a>
    <a href="{{ route('admin.reviewers.index') }}" class="nav-link">
        <i class="bi bi-people"></i> Reviewers
    </a>
    <a href="{{ route('admin.leaderboard.index') }}" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="{{ route('admin.redemptions.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
    <a href="{{ route('admin.points.index') }}" class="nav-link">
        <i class="bi bi-coin"></i> Point Management
    </a>
    <a href="{{ route('admin.rewards.index') }}" class="nav-link">
        <i class="bi bi-trophy"></i> Reward Management
    </a>
    <a href="{{ route('admin.marketings.index') }}" class="nav-link">
        <i class="bi bi-megaphone"></i> Marketing
    </a>
    <a href="{{ route('admin.pics.index') }}" class="nav-link active">
        <i class="bi bi-person-badge"></i> PIC
    </a>
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
                <span><i class="bi bi-person-badge"></i> Daftar PIC</span>
                <a href="{{ route('admin.PICs.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah PIC
                </a>
            </div>
            <div class="card-body">
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
                            @forelse($PICs as $PIC)
                            <tr>
                                <td>{{ $loop->iteration + ($PICs->currentPage() - 1) * $PICs->perPage() }}</td>
                                <td><strong>{{ $PIC->name }}</strong></td>
                                <td>{{ $PIC->email ?? '-' }}</td>
                                <td>{{ $PIC->phone ?? '-' }}</td>
                                <td>
                                    @if($PIC->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.PICs.edit', $PIC) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.PICs.destroy', $PIC) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
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
                                <td colspan="6" class="text-center text-muted">Belum ada data PIC</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $PICs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
