@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Tugas Review Saya')

@section('sidebar')
    <a href="{{ route('reviewer.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('reviewer.tasks.index') }}" class="nav-link active">
        <i class="bi bi-clipboard-check"></i> My Tasks
    </a>
    <a href="{{ route('reviewer.rewards.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Rewards
    </a>
    <a href="{{ route('reviewer.leaderboard.index') }}" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="{{ route('reviewer.profile.edit') }}" class="nav-link">
        <i class="bi bi-person-circle"></i> My Profile
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clipboard-check"></i> Daftar Tugas Review
            </div>
            <div class="card-body">
                <!-- Filter Tabs -->
                <ul class="nav nav-tabs mb-3" id="statusTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" onclick="filterTasks('all')">
                            Semua <span class="badge bg-secondary ms-1">{{ $assignments->total() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" onclick="filterTasks('PENDING')">
                            Pending <span class="badge bg-warning ms-1">{{ $assignments->where('status', 'PENDING')->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" onclick="filterTasks('active')">
                            Active <span class="badge bg-info ms-1">{{ $assignments->whereIn('status', ['ACCEPTED', 'ON_PROGRESS', 'SUBMITTED'])->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" onclick="filterTasks('APPROVED')">
                            Completed <span class="badge bg-success ms-1">{{ $assignments->where('status', 'APPROVED')->count() }}</span>
                        </button>
                    </li>
                </ul>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Artikel</th>
                                <th>Bahasa</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="taskTableBody">
                            @forelse($assignments as $assignment)
                            <tr data-status="{{ $assignment->status }}">
                                <td>{{ $loop->iteration + ($assignments->currentPage() - 1) * $assignments->perPage() }}</td>
                                <td>
                                    <strong>{{ Str::limit($assignment->article_title ?? 'N/A', 50) }}</strong><br>
                                    <small class="text-muted">
                                        <i class="bi bi-link-45deg"></i>
                                        @if($assignment->submit_link)
                                            <a href="{{ $assignment->submit_link }}" target="_blank">Buka Link Submit</a>
                                        @else
                                            N/A
                                        @endif
                                    </small>
                                    @if($assignment->reviewer2 && $assignment->reviewer2_id != auth()->id())
                                        <br>
                                        <small class="text-info">
                                            <i class="bi bi-people"></i> Dengan: {{ $assignment->reviewer2->name }}
                                        </small>
                                    @elseif($assignment->reviewer_id != auth()->id() && $assignment->reviewer)
                                        <br>
                                        <small class="text-info">
                                            <i class="bi bi-people"></i> Dengan: {{ $assignment->reviewer->name }}
                                        </small>
                                    @endif
                                    @if($assignment->status === 'APPROVED')
                                        <br>
                                        <span class="badge bg-success mt-1">
                                            <i class="bi bi-check-circle-fill"></i> Review Selesai
                                        </span>
                                    @endif
                                </td>
                                <td><span class="badge bg-secondary">{{ $assignment->language ?? 'N/A' }}</span></td>
                                <td>
                                    @if($assignment->deadline)
                                        <span class="badge bg-warning text-dark">
                                            {{ $assignment->deadline->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'PENDING' => 'warning',
                                            'ACCEPTED' => 'info',
                                            'REJECTED' => 'danger',
                                            'ON_PROGRESS' => 'primary',
                                            'SUBMITTED' => 'success',
                                            'APPROVED' => 'success',
                                            'REVISION' => 'secondary'
                                        ];
                                        $color = $statusColors[$assignment->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $assignment->status }}</span>
                                </td>
                                <td>{{ $assignment->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('reviewer.tasks.show', $assignment) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Belum ada tugas</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $assignments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterTasks(status) {
    // Update active tab
    document.querySelectorAll('#statusTabs button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    const rows = document.querySelectorAll('#taskTableBody tr[data-status]');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        
        if (status === 'all') {
            row.style.display = '';
            visibleCount++;
        } else if (status === 'active') {
            // Active includes ACCEPTED, ON_PROGRESS, SUBMITTED
            if (['ACCEPTED', 'ON_PROGRESS', 'SUBMITTED'].includes(rowStatus)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        } else {
            if (rowStatus === status) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        }
    });
    
    // Show/hide empty message
    const emptyRow = document.querySelector('#taskTableBody tr:not([data-status])');
    if (emptyRow) {
        emptyRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}
</script>
@endpush
@endsection
