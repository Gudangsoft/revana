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
    
    .submit-buttons {
        padding: 15px 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .btn-submit {
        width: 100%;
        padding: 10px 15px;
        font-size: 0.9rem;
        font-weight: 600;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    
    .btn-submit i {
        font-size: 1.1rem;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .btn-submit-regular {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        color: white;
        border: none;
    }
    
    .btn-submit-regular:hover {
        background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%);
        color: white;
    }
    
    .btn-submit-fasttrack {
        background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
        color: #000;
        border: none;
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
                  ->orWhere('petugas_production_id', $picId)
                  ->orWhere('petugas_validator_id', $picId);
            })->whereNotIn('status', ['PUBLISHED', 'REJECTED'])->get();
            
            // Count only tasks where status matches PIC's role
            $urgentMappings = [
                'EDITOR1' => ['petugas_editor1_id'],
                'AUTHOR1' => ['petugas_author1_id'],
                'EDITOR2' => ['petugas_editor2_id'],
                'EDITOR3' => ['petugas_editor3_id'],
                'AUTHOR2' => ['petugas_author2_id'],
                'PRODUCTION' => ['petugas_production_id'],
                'VALIDATOR' => ['petugas_validator_id'],
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
    
    <div class="sidebar-divider"></div>
    
    <!-- Dashboard Section -->
    <div class="sidebar-section-header">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </div>
    
    <a href="{{ route('pic.dashboard') }}" class="nav-link {{ request()->routeIs('pic.dashboard', 'pic.author.dashboard') ? 'active' : '' }}" data-title="Dashboard">
        <i class="bi bi-house-door"></i>
        <span>Dashboard</span>
    </a>
    
    @feature('fasttrack')
    @rolecap('pic', 'fasttrack')
    <!-- Fasttrack Input -->
    <div class="sidebar-section-header">
        <i class="bi bi-lightning-charge text-warning"></i>
        <span>Pengelolaan Fasttrack</span>
    </div>
    <a href="{{ route('pic.fasttrack.create') }}" class="nav-link {{ request()->routeIs('pic.fasttrack.create') ? 'active' : '' }}" data-title="Input Data FS">
        <i class="bi bi-plus-circle-fill text-warning"></i>
        <span>Input Data FS</span>
    </a>
    <a href="{{ route('pic.fasttrack.index') }}" class="nav-link {{ request()->routeIs('pic.fasttrack.index', 'pic.fasttrack.edit', 'pic.fasttrack.show') ? 'active' : '' }}" data-title="Data Jurnal FS">
        <i class="bi bi-lightning-charge text-warning"></i>
        <span>Data Jurnal FS</span>
    </a>
    <a href="{{ route('pic.fasttrack.monitoring') }}" class="nav-link {{ request()->routeIs('pic.fasttrack.monitoring') ? 'active' : '' }}" data-title="Monitoring FS">
        <i class="bi bi-graph-up text-warning"></i>
        <span>Monitoring Proses FS</span>
    </a>
    @endrolecap
    @endfeature
    
    <!-- Tugas Saya -->
    <div class="sidebar-section-header">
        <i class="bi bi-person-check"></i>
        <span>Tugas Saya</span>
    </div>
    <a href="{{ route('pic.my-tasks.index') }}" class="nav-link {{ request()->routeIs('pic.my-tasks.*') ? 'active' : '' }}" data-title="Tugas Saya">
        <i class="bi bi-list-task"></i>
        <span>Tugas Saya</span>
        @if($pendingTasks > 0)
            <span class="badge bg-danger">{{ $pendingTasks }}</span>
        @endif
    </a>

    <!-- Normal Section -->
    <div class="sidebar-section-header">
        <i class="bi bi-journal-bookmark-fill" style="color:#6f42c1;"></i>
        <span>Pengelolaan Jurnal Normal</span>
    </div>
    <a href="{{ route('pic.submissions.create') }}" class="nav-link {{ request()->routeIs('pic.submissions.create') && !request('program') ? 'active' : '' }}" data-title="Input Data Normal">
        <i class="bi bi-plus-circle-fill" style="color:#6f42c1;"></i>
        <span>Input Data Normal</span>
    </a>
    <a href="{{ route('pic.submissions.index') }}" class="nav-link {{ request()->routeIs('pic.submissions.index') && !request('program') ? 'active' : '' }}" data-title="Data Submit Normal">
        <i class="bi bi-file-earmark-text" style="color:#6f42c1;"></i>
        <span>Data Submit Normal</span>
    </a>
    <a href="{{ route('pic.submissions.monitoring') }}" class="nav-link {{ request()->routeIs('pic.submissions.monitoring') && !request('program') ? 'active' : '' }}" data-title="Monitoring Normal">
        <i class="bi bi-list-check" style="color:#6f42c1;"></i>
        <span>Monitoring Proses</span>
    </a>

    <!-- BKD Section -->
    <div class="sidebar-section-header">
        <i class="bi bi-briefcase-fill text-info"></i>
        <span>Pengelolaan BKD</span>
    </div>
    <a href="{{ route('pic.submissions.create', ['program' => 'bkd']) }}" class="nav-link {{ request()->routeIs('pic.submissions.create') && request('program') === 'bkd' ? 'active' : '' }}" data-title="Input Data BKD">
        <i class="bi bi-plus-circle-fill text-info"></i>
        <span>Input Data BKD</span>
    </a>
    <a href="{{ route('pic.submissions.index', ['program' => 'bkd']) }}" class="nav-link {{ request()->routeIs('pic.submissions.index') && request('program') === 'bkd' ? 'active' : '' }}" data-title="Data Submit BKD">
        <i class="bi bi-file-earmark-text text-info"></i>
        <span>Data Submit BKD</span>
    </a>
    <a href="{{ route('pic.submissions.monitoring', ['program' => 'bkd']) }}" class="nav-link {{ request()->routeIs('pic.submissions.monitoring') && request('program') === 'bkd' ? 'active' : '' }}" data-title="Monitoring BKD">
        <i class="bi bi-list-check text-info"></i>
        <span>Monitoring Proses BKD</span>
    </a>

    <!-- JAFA Section -->
    <div class="sidebar-section-header">
        <i class="bi bi-folder2-open text-success"></i>
        <span>Pengelolaan JAFA</span>
    </div>
    <a href="{{ route('pic.submissions.create', ['program' => 'jafa']) }}" class="nav-link {{ request()->routeIs('pic.submissions.create') && request('program') === 'jafa' ? 'active' : '' }}" data-title="Input Data JAFA">
        <i class="bi bi-plus-circle-fill text-success"></i>
        <span>Input Data JAFA</span>
    </a>
    <a href="{{ route('pic.submissions.index', ['program' => 'jafa']) }}" class="nav-link {{ request()->routeIs('pic.submissions.index') && request('program') === 'jafa' ? 'active' : '' }}" data-title="Data Submit JAFA">
        <i class="bi bi-file-earmark-text text-success"></i>
        <span>Data Submit JAFA</span>
    </a>
    <a href="{{ route('pic.submissions.monitoring', ['program' => 'jafa']) }}" class="nav-link {{ request()->routeIs('pic.submissions.monitoring') && request('program') === 'jafa' ? 'active' : '' }}" data-title="Monitoring JAFA">
        <i class="bi bi-list-check text-success"></i>
        <span>Monitoring Proses JAFA</span>
    </a>

    <div class="sidebar-divider"></div>
    
    @feature('points')
    @rolecap('pic', 'points')
    <a href="{{ route('pic.points.index') }}" class="nav-link {{ request()->routeIs('pic.points.index') ? 'active' : '' }}" data-title="Point Saya">
        <i class="bi bi-trophy-fill"></i>
        <span>Point Saya</span>
        @if($totalPoints > 0)
            <span class="badge bg-success">{{ number_format($totalPoints) }}</span>
        @endif
    </a>
    <a href="{{ route('pic.points.rankings') }}" class="nav-link {{ request()->routeIs('pic.points.rankings') ? 'active' : '' }}" data-title="Peringkat Point">
        <i class="bi bi-bar-chart-fill text-warning"></i>
        <span>Peringkat Point</span>
    </a>
    @endrolecap
    @endfeature
    
    <a href="{{ route('pic.laporan-harian.index') }}" class="nav-link {{ request()->routeIs('pic.laporan-harian.*') ? 'active' : '' }}" data-title="Laporan Kinerja Harian">
        <i class="bi bi-clipboard2-check"></i>
        <span>Catatan Kinerja Harian</span>
    </a>

    <a href="{{ route('pic.reports.journal-articles') }}" class="nav-link {{ request()->routeIs('pic.reports.*') ? 'active' : '' }}" data-title="Laporan">
        <i class="bi bi-file-earmark-bar-graph"></i>
        <span>Laporan Jurnal</span>
    </a>
    
    <div class="sidebar-divider"></div>
    
    <a href="{{ route('pic.profile.edit') }}" class="nav-link {{ request()->routeIs('pic.profile.*') ? 'active' : '' }}" data-title="Profile Saya">
        <i class="bi bi-person-circle"></i>
        <span>Profile Saya</span>
    </a>
</nav>
