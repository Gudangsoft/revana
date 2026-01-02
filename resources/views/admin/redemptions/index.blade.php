@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Daftar Reward Redemptions')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
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
        <i class="bi bi-gift"></i> Semua Reward Redemptions
    </div>
    <div class="card-body">
        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-3" id="statusTabs">
            <li class="nav-item">
                <button class="nav-link active" onclick="filterRedemptions('all')">
                    Semua <span class="badge bg-secondary ms-1">{{ $redemptions->total() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" onclick="filterRedemptions('PENDING')">
                    Pending <span class="badge bg-warning ms-1">{{ $redemptions->where('status', 'PENDING')->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" onclick="filterRedemptions('APPROVED')">
                    Approved <span class="badge bg-info ms-1">{{ $redemptions->where('status', 'APPROVED')->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" onclick="filterRedemptions('COMPLETED')">
                    Completed <span class="badge bg-success ms-1">{{ $redemptions->where('status', 'COMPLETED')->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" onclick="filterRedemptions('REJECTED')">
                    Rejected <span class="badge bg-danger ms-1">{{ $redemptions->where('status', 'REJECTED')->count() }}</span>
                </button>
            </li>
        </ul>

        @if($redemptions->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada reward redemption yang dibuat.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reviewer</th>
                        <th>Reward</th>
                        <th>Points</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="redemptionTableBody">
                    @foreach($redemptions as $redemption)
                    <tr data-status="{{ $redemption->status }}">
                        <td>{{ $redemption->id }}</td>
                        <td>
                            <div class="fw-bold">{{ $redemption->user->name }}</div>
                            <small class="text-muted">{{ $redemption->user->email }}</small>
                        </td>
                        <td>
                            <div>{{ $redemption->reward->name }}</div>
                            <small class="text-muted">
                                <span class="badge bg-secondary">{{ $redemption->reward->type }}</span>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-warning text-dark">
                                {{ number_format($redemption->points_used) }} pts
                            </span>
                        </td>
                        <td>
                            @if($redemption->status === 'PENDING')
                                <span class="badge bg-warning">
                                    <i class="bi bi-clock"></i> Pending
                                </span>
                            @elseif($redemption->status === 'APPROVED')
                                <span class="badge bg-info">
                                    <i class="bi bi-check"></i> Approved
                                </span>
                            @elseif($redemption->status === 'COMPLETED')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Completed
                                </span>
                            @elseif($redemption->status === 'REJECTED')
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle"></i> Rejected
                                </span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $redemption->created_at->format('d M Y') }}</small>
                            <br>
                            <small class="text-muted">{{ $redemption->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.redemptions.show', $redemption) }}" 
                                   class="btn btn-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($redemption->status === 'PENDING')
                                <form action="{{ route('admin.redemptions.approve', $redemption) }}" 
                                      method="POST" 
                                      class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success" title="Approve"
                                            onclick="return confirm('Approve redemption ini?')">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.redemptions.reject', $redemption) }}" 
                                      method="POST" 
                                      class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" title="Reject"
                                            onclick="return confirm('Reject redemption ini?')">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                                @endif
                                @if($redemption->status === 'APPROVED')
                                <form action="{{ route('admin.redemptions.complete', $redemption) }}" 
                                      method="POST" 
                                      class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary" title="Mark Complete"
                                            onclick="return confirm('Tandai sebagai complete?')">
                                        <i class="bi bi-check2-all"></i>
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
            {{ $redemptions->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Stats Summary -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-muted">Total</h5>
                <h2>{{ $redemptions->total() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-warning">Pending</h5>
                <h2>{{ $redemptions->where('status', 'PENDING')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-info">Approved</h5>
                <h2>{{ $redemptions->where('status', 'APPROVED')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-success">Completed</h5>
                <h2>{{ $redemptions->where('status', 'COMPLETED')->count() }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Total Points Redeemed -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="text-muted">Total Points Redeemed</h5>
                <h2 class="text-primary">
                    {{ number_format($redemptions->sum('points_used')) }} Points
                </h2>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterRedemptions(status) {
    // Update active tab
    document.querySelectorAll('#statusTabs button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    const rows = document.querySelectorAll('#redemptionTableBody tr[data-status]');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        
        if (status === 'all') {
            row.style.display = '';
            visibleCount++;
        } else if (rowStatus === status) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endpush
@endsection

