<!-- PIC Sidebar -->
<nav class="nav flex-column">
    @php
        $picUser = Auth::guard('pic')->user();
        $picId = $picUser ? $picUser->id : 0;
        
        // Count pending tasks that require PIC's action
        $pendingTasks = 0;
        if ($picId) {
            $allTasks = \App\Models\Submission::where(function($q) use ($picId) {
                $q->where('petugas_editor1_id', $picId)
                  ->orWhere('petugas_author1_id', $picId)
                  ->orWhere('petugas_editor2_id', $picId)
                  ->orWhere('petugas_editor3_id', $picId)
                  ->orWhere('petugas_author2_id', $picId)
                  ->orWhere('petugas_production_id', $picId);
            })->whereNotIn('status', ['PUBLISHED', 'published'])->get();
            
            // Count only tasks where status matches PIC's role
            $urgentMappings = [
                'EDITOR1' => ['petugas_editor1_id'],
                'AUTHOR1' => ['petugas_author1_id'],
                'EDITOR2' => ['petugas_editor2_id'],
                'EDITOR3' => ['petugas_editor3_id'],
                'AUTHOR2' => ['petugas_author2_id'],
                'PRODUCTION' => ['petugas_production_id'],
            ];
            foreach ($allTasks as $task) {
                $status = strtoupper($task->status);
                foreach ($urgentMappings as $statusKey => $fields) {
                    if (str_contains($status, $statusKey)) {
                        foreach ($fields as $field) {
                            if ($task->$field == $picId) {
                                $pendingTasks++;
                                break 2;
                            }
                        }
                    }
                }
            }
        }
        $totalPoints = $picUser ? $picUser->total_points : 0;
    @endphp
    <h6 class="px-3 py-2 text-muted text-uppercase small">
        <i class="bi bi-speedometer2"></i> Dashboard
    </h6>
    <a href="{{ route('pic.dashboard') }}" class="nav-link {{ request()->routeIs('pic.dashboard', 'pic.author.dashboard') ? 'active' : '' }}">
        <i class="bi bi-house"></i> Dashboard
    </a>
    <a href="{{ route('pic.author.create') }}" class="nav-link {{ request()->routeIs('pic.author.create') ? 'active' : '' }}">
        <i class="bi bi-plus-circle"></i> Input Artikel Baru
    </a>
    <a href="{{ route('pic.my-tasks.index') }}" class="nav-link {{ request()->routeIs('pic.my-tasks.*') ? 'active' : '' }}">
        <i class="bi bi-list-task"></i> Tugas Saya
        @if($pendingTasks > 0)
            <span class="badge bg-danger ms-1">{{ $pendingTasks }}</span>
        @endif
    </a>
    <a href="{{ route('pic.points.index') }}" class="nav-link {{ request()->routeIs('pic.points.*') ? 'active' : '' }}">
        <i class="bi bi-trophy"></i> Point Saya
        @if($totalPoints > 0)
            <span class="badge bg-success ms-1">{{ number_format($totalPoints) }}</span>
        @endif
    </a>
    
    <hr class="mx-3">
    
    <h6 class="px-3 py-2 text-muted text-uppercase small">
        <i class="bi bi-journal-bookmark"></i> Pengelolaan Jurnal
    </h6>
    <a href="{{ route('pic.journals.index') }}" class="nav-link {{ request()->routeIs('pic.journals.*') ? 'active' : '' }}">
        <i class="bi bi-journal-text"></i> Data Jurnal
    </a>
    <a href="{{ route('pic.journal-slots.index') }}" class="nav-link {{ request()->routeIs('pic.journal-slots.index', 'pic.journal-slots.create', 'pic.journal-slots.edit') ? 'active' : '' }}">
        <i class="bi bi-calendar3"></i> Data Slot
    </a>
    <a href="{{ route('pic.journal-slots.monitoring') }}" class="nav-link {{ request()->routeIs('pic.journal-slots.monitoring') ? 'active' : '' }}">
        <i class="bi bi-bar-chart"></i> Monitoring Slot
    </a>
    <a href="{{ route('pic.submissions.index') }}" class="nav-link {{ request()->routeIs('pic.submissions.index', 'pic.submissions.create', 'pic.submissions.edit', 'pic.submissions.show') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-plus"></i> Data Submit
    </a>
    <a href="{{ route('pic.submissions.monitoring') }}" class="nav-link {{ request()->routeIs('pic.submissions.monitoring') ? 'active' : '' }}">
        <i class="bi bi-graph-up"></i> Monitoring Proses
    </a>
    <a href="{{ route('pic.accreditations.index') }}" class="nav-link {{ request()->routeIs('pic.accreditations.*') ? 'active' : '' }}">
        <i class="bi bi-award"></i> Akreditasi
    </a>
    
    <hr class="mx-3">
    
    <h6 class="px-3 py-2 text-muted text-uppercase small">
        <i class="bi bi-people"></i> Tim
    </h6>
    <a href="{{ route('pic.reviewers.index') }}" class="nav-link {{ request()->routeIs('pic.reviewers.*') ? 'active' : '' }}">
        <i class="bi bi-person-check"></i> Reviewer
    </a>
</nav>
