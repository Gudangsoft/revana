<!-- PIC Sidebar -->
<style>
    .sidebar-section-header {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 12px 20px 8px;
        margin-top: 8px;
        color: #6c757d;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .sidebar-section-header i {
        font-size: 0.8rem;
        opacity: 0.7;
    }
    
    .nav-link {
        padding: 10px 20px;
        color: #495057;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        border-left: 3px solid transparent;
        font-size: 0.9rem;
    }
    
    .nav-link i {
        font-size: 1.1rem;
        width: 20px;
        text-align: center;
        opacity: 0.8;
    }
    
    .nav-link:hover {
        background-color: #f8f9fa;
        color: #0d6efd;
        border-left-color: #0d6efd;
        text-decoration: none;
    }
    
    .nav-link:hover i {
        opacity: 1;
        transform: scale(1.1);
    }
    
    .nav-link.active {
        background-color: #e7f1ff;
        color: #0d6efd;
        font-weight: 600;
        border-left-color: #0d6efd;
    }
    
    .nav-link.active i {
        opacity: 1;
        color: #0d6efd;
    }
    
    .nav-link .badge {
        margin-left: auto;
        font-size: 0.7rem;
        padding: 3px 7px;
        border-radius: 10px;
        font-weight: 600;
    }
    
    .sidebar-divider {
        margin: 12px 20px;
        border-top: 1px solid #dee2e6;
        opacity: 0.5;
    }
</style>

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
    
    <!-- Dashboard Section -->
    <div class="sidebar-section-header">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </div>
    
    <a href="{{ route('pic.dashboard') }}" class="nav-link {{ request()->routeIs('pic.dashboard', 'pic.author.dashboard') ? 'active' : '' }}" data-title="Dashboard">
        <i class="bi bi-house-door"></i>
        <span>Dashboard</span>
    </a>
    
    <a href="{{ route('pic.submissions.monitoring') }}" class="nav-link {{ request()->routeIs('pic.submissions.monitoring', 'pic.my-tasks.*') ? 'active' : '' }}" data-title="Monitoring & Tugas">
        <i class="bi bi-list-check"></i>
        <span>Monitoring & Tugas Saya</span>
        @if($pendingTasks > 0)
            <span class="badge bg-danger">{{ $pendingTasks }}</span>
        @endif
    </a>
    
    <a href="{{ route('pic.submissions.index') }}" class="nav-link {{ request()->routeIs('pic.submissions.index', 'pic.submissions.create', 'pic.submissions.edit', 'pic.submissions.show') ? 'active' : '' }}" data-title="Data Submit">
        <i class="bi bi-file-earmark-text"></i>
        <span>Data Submit</span>
    </a>
    
    <!-- Fasttrack (Gabungan Data & Monitoring) -->
    <a href="{{ route('pic.fasttrack.monitoring') }}" class="nav-link {{ request()->routeIs('pic.fasttrack.*') ? 'active' : '' }}" data-title="Monitoring Fasttrack">
        <i class="bi bi-lightning-charge text-warning"></i>
        <span>Fasttrack</span>
    </a>
    
    <div class="sidebar-divider"></div>
    
    <a href="{{ route('pic.points.index') }}" class="nav-link {{ request()->routeIs('pic.points.*') ? 'active' : '' }}" data-title="Point Saya">
        <i class="bi bi-trophy-fill"></i>
        <span>Point Saya</span>
        @if($totalPoints > 0)
            <span class="badge bg-success">{{ number_format($totalPoints) }}</span>
        @endif
    </a>
    
    <div class="sidebar-divider"></div>
    
    <a href="{{ route('pic.profile.edit') }}" class="nav-link {{ request()->routeIs('pic.profile.*') ? 'active' : '' }}" data-title="Profile Saya">
        <i class="bi bi-person-circle"></i>
        <span>Profile Saya</span>
    </a>
</nav>
