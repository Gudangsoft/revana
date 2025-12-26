@extends('layouts.app')

@section('title', 'Reward Management - REVANA')
@section('page-title', 'Manajemen Reward')

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
    <a href="{{ route('admin.redemptions.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
    <a href="{{ route('admin.points.index') }}" class="nav-link">
        <i class="bi bi-coin"></i> Point Management
    </a>
    <a href="{{ route('admin.rewards.index') }}" class="nav-link active">
        <i class="bi bi-trophy"></i> Reward Management
    </a>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col">
        <a href="{{ route('admin.rewards.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Tambah Reward Baru
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card">
    <div class="card-header">
        <i class="bi bi-trophy"></i> Daftar Reward
    </div>
    <div class="card-body">
        @if($rewards->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada reward yang dibuat.
            <a href="{{ route('admin.rewards.create') }}">Buat reward pertama</a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Reward</th>
                        <th>Tipe</th>
                        <th>Points Required</th>
                        <th>Value</th>
                        <th>Total Redeemed</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rewards as $reward)
                    <tr>
                        <td>{{ $reward->id }}</td>
                        <td>
                            <div class="fw-bold">{{ $reward->name }}</div>
                            @if($reward->description)
                                <small class="text-muted">{{ Str::limit($reward->description, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $reward->type }}</span>
                        </td>
                        <td>
                            <span class="badge bg-warning text-dark">
                                {{ number_format($reward->points_required) }} pts
                            </span>
                        </td>
                        <td>
                            @if($reward->value)
                                Rp {{ number_format($reward->value, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $reward->redemptions_count }} kali</span>
                        </td>
                        <td>
                            @if($reward->is_active)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Aktif
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle"></i> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.rewards.edit', $reward) }}" 
                                   class="btn btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.rewards.toggle', $reward) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" 
                                            class="btn btn-{{ $reward->is_active ? 'secondary' : 'success' }}" 
                                            title="{{ $reward->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="bi bi-{{ $reward->is_active ? 'pause' : 'play' }}-circle"></i>
                                    </button>
                                </form>
                                @if($reward->redemptions_count == 0)
                                <form action="{{ route('admin.rewards.destroy', $reward) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus reward ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $rewards->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Stats Summary -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-muted">Total Rewards</h5>
                <h2>{{ $rewards->total() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-success">Active</h5>
                <h2>{{ $rewards->where('is_active', true)->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-secondary">Inactive</h5>
                <h2>{{ $rewards->where('is_active', false)->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-info">Total Redeemed</h5>
                <h2>{{ $rewards->sum('redemptions_count') }}</h2>
            </div>
        </div>
    </div>
</div>
@endsection
