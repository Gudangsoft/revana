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

    /* Collapsible section header */
    .sidebar-toggle {
        width: 100%;
        background: none;
        border: none;
        border-radius: 0;
        text-align: left;
        cursor: pointer;
        transition: background 0.15s;
    }
    .sidebar-toggle:hover {
        background: #f0f0f0;
    }
    .sidebar-toggle .collapse-chevron {
        margin-left: auto;
        font-size: 0.75rem;
        opacity: 0.55;
        transition: transform 0.2s ease;
    }
    .sidebar-toggle:not(.collapsed) .collapse-chevron {
        transform: rotate(180deg);
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

    /* Sub-links (inside collapse) slightly indented */
    .nav-link-sub {
        padding-left: 32px !important;
        font-size: 0.875rem;
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

    .sidebar-new-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #dc3545;
        display: inline-block;
        animation: sidebarBlink 1s infinite;
    }

    @keyframes sidebarBlink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.2; }
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

        // Active section detection
        $ftActive      = request()->routeIs('pic.fasttrack.*');
        $normalActive  = request()->routeIs('pic.submissions.*') && !request('program');
        $bkdActive     = request()->routeIs('pic.submissions.*') && request('program') === 'bkd';
        $jafaActive    = request()->routeIs('pic.submissions.*') && request('program') === 'jafa';
    @endphp

    <div class="sidebar-divider"></div>

    <!-- Dashboard -->
    <div class="sidebar-section-header">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </div>
    <a href="{{ route('pic.dashboard') }}"
       class="nav-link {{ request()->routeIs('pic.dashboard', 'pic.author.dashboard') ? 'active' : '' }}"
       data-title="Dashboard">
        <i class="bi bi-house-door"></i>
        <span>Dashboard</span>
    </a>

    <!-- Tugas Saya -->
    <div class="sidebar-section-header">
        <i class="bi bi-person-check"></i>
        <span>Tugas Saya</span>
    </div>
    <a href="{{ route('pic.my-tasks.index') }}"
       class="nav-link {{ request()->routeIs('pic.my-tasks.*') ? 'active' : '' }}"
       data-title="Tugas Saya">
        <i class="bi bi-list-task"></i>
        <span>Tugas Saya</span>
        @if($pendingTasks > 0)
            <span class="badge bg-danger">{{ $pendingTasks }}</span>
        @endif
    </a>

    {{-- ===== PENGELOLAAN FASTTRACK (collapsible) ===== --}}
    @feature('fasttrack')
    @rolecap('pic', 'fasttrack')
    <button class="sidebar-section-header sidebar-toggle {{ $ftActive ? '' : 'collapsed' }}"
            data-bs-toggle="collapse" data-bs-target="#sec-pic-ft"
            aria-expanded="{{ $ftActive ? 'true' : 'false' }}">
        <i class="bi bi-lightning-charge text-warning"></i>
        <span>Pengelolaan Fasttrack</span>
        <i class="bi bi-chevron-down collapse-chevron"></i>
    </button>
    <div class="collapse {{ $ftActive ? 'show' : '' }}" id="sec-pic-ft">
        <a href="{{ route('pic.fasttrack.create') }}"
           class="nav-link nav-link-sub {{ request()->routeIs('pic.fasttrack.create') ? 'active' : '' }}">
            <i class="bi bi-plus-circle-fill text-warning"></i>
            <span>Input Data FS</span>
        </a>
        <a href="{{ route('pic.fasttrack.index') }}"
           class="nav-link nav-link-sub {{ request()->routeIs('pic.fasttrack.index', 'pic.fasttrack.edit', 'pic.fasttrack.show') ? 'active' : '' }}">
            <i class="bi bi-lightning-charge text-warning"></i>
            <span>Data Jurnal FS</span>
        </a>
        <a href="{{ route('pic.fasttrack.monitoring') }}"
           class="nav-link nav-link-sub {{ request()->routeIs('pic.fasttrack.monitoring') ? 'active' : '' }}">
            <i class="bi bi-graph-up text-warning"></i>
            <span>Monitoring Proses FS</span>
        </a>
    </div>
    @endrolecap
    @endfeature

    {{-- ===== PENGELOLAAN JURNAL NORMAL (collapsible) ===== --}}
    <button class="sidebar-section-header sidebar-toggle {{ $normalActive ? '' : 'collapsed' }}"
            data-bs-toggle="collapse" data-bs-target="#sec-pic-normal"
            aria-expanded="{{ $normalActive ? 'true' : 'false' }}"
            style="color:#6f42c1;">
        <i class="bi bi-journal-bookmark-fill" style="color:#6f42c1;"></i>
        <span>Pengelolaan Jurnal Normal</span>
        <i class="bi bi-chevron-down collapse-chevron"></i>
    </button>
    <div class="collapse {{ $normalActive ? 'show' : '' }}" id="sec-pic-normal">
        <a href="{{ route('pic.submissions.create') }}"
           class="nav-link nav-link-sub {{ request()->routeIs('pic.submissions.create') && !request('program') ? 'active' : '' }}">
            <i class="bi bi-plus-circle-fill" style="color:#6f42c1;"></i>
            <span>Input Data Normal</span>
        </a>
        <a href="{{ route('pic.submissions.index') }}"
           class="nav-link nav-link-sub {{ request()->routeIs('pic.submissions.index') && !request('program') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text" style="color:#6f42c1;"></i>
            <span>Data Submit Normal</span>
        </a>
        <a href="{{ route('pic.submissions.monitoring') }}"
           class="nav-link nav-link-sub {{ request()->routeIs('pic.submissions.monitoring') && !request('program') ? 'active' : '' }}">
            <i class="bi bi-list-check" style="color:#6f42c1;"></i>
            <span>Monitoring Proses</span>
        </a>
    </div>

    {{-- ===== PENGELOLAAN BKD (collapsible) ===== --}}
    <button class="sidebar-section-header sidebar-toggle {{ $bkdActive ? '' : 'collapsed' }}"
            data-bs-toggle="collapse" data-bs-target="#sec-pic-bkd"
            aria-expanded="{{ $bkdActive ? 'true' : 'false' }}"
            style="color:#0dcaf0;">
        <i class="bi bi-briefcase-fill text-info"></i>
        <span>Pengelolaan BKD</span>
        <i class="bi bi-chevron-down collapse-chevron"></i>
    </button>
    <div class="collapse {{ $bkdActive ? 'show' : '' }}" id="sec-pic-bkd">
        <a href="{{ route('pic.submissions.create', ['program' => 'bkd']) }}"
           class="nav-link nav-link-sub {{ request()->routeIs('pic.submissions.create') && request('program') === 'bkd' ? 'active' : '' }}">
            <i class="bi bi-plus-circle-fill text-info"></i>
            <span>Input Data BKD</span>
        </a>
        <a href="{{ route('pic.submissions.index', ['program' => 'bkd']) }}"
           class="nav-link nav-link-sub {{ request()->routeIs('pic.submissions.index') && request('program') === 'bkd' ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text text-info"></i>
            <span>Data Submit BKD</span>
        </a>
        <a href="{{ route('pic.submissions.monitoring', ['program' => 'bkd']) }}"
           class="nav-link nav-link-sub {{ request()->routeIs('pic.submissions.monitoring') && request('program') === 'bkd' ? 'active' : '' }}">
            <i class="bi bi-list-check text-info"></i>
            <span>Monitoring Proses BKD</span>
        </a>
    </div>

    {{-- ===== PENGELOLAAN JAFA (collapsible) ===== --}}
    <button class="sidebar-section-header sidebar-toggle {{ $jafaActive ? '' : 'collapsed' }}"
            data-bs-toggle="collapse" data-bs-target="#sec-pic-jafa"
            aria-expanded="{{ $jafaActive ? 'true' : 'false' }}"
            style="color:#198754;">
        <i class="bi bi-folder2-open text-success"></i>
        <span>Pengelolaan JAFA</span>
        <i class="bi bi-chevron-down collapse-chevron"></i>
    </button>
    <div class="collapse {{ $jafaActive ? 'show' : '' }}" id="sec-pic-jafa">
        <a href="{{ route('pic.submissions.create', ['program' => 'jafa']) }}"
           class="nav-link nav-link-sub {{ request()->routeIs('pic.submissions.create') && request('program') === 'jafa' ? 'active' : '' }}">
            <i class="bi bi-plus-circle-fill text-success"></i>
            <span>Input Data JAFA</span>
        </a>
        <a href="{{ route('pic.submissions.index', ['program' => 'jafa']) }}"
           class="nav-link nav-link-sub {{ request()->routeIs('pic.submissions.index') && request('program') === 'jafa' ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text text-success"></i>
            <span>Data Submit JAFA</span>
        </a>
        <a href="{{ route('pic.submissions.monitoring', ['program' => 'jafa']) }}"
           class="nav-link nav-link-sub {{ request()->routeIs('pic.submissions.monitoring') && request('program') === 'jafa' ? 'active' : '' }}">
            <i class="bi bi-list-check text-success"></i>
            <span>Monitoring Proses JAFA</span>
        </a>
    </div>

    <div class="sidebar-divider"></div>

    @feature('points')
    @rolecap('pic', 'points')
    <a href="{{ route('pic.points.index') }}"
       class="nav-link {{ request()->routeIs('pic.points.index') ? 'active' : '' }}"
       data-title="Point Saya">
        <i class="bi bi-trophy-fill"></i>
        <span>Point Saya</span>
        @if($totalPoints > 0)
            <span class="badge bg-success">{{ number_format($totalPoints) }}</span>
        @endif
    </a>
    <a href="{{ route('pic.points.rankings') }}"
       class="nav-link {{ request()->routeIs('pic.points.rankings') ? 'active' : '' }}"
       data-title="Peringkat Point">
        <i class="bi bi-bar-chart-fill text-warning"></i>
        <span>Peringkat Point</span>
    </a>
    @endrolecap
    @endfeature

    <a href="{{ route('pic.laporan-harian.index') }}"
       class="nav-link {{ request()->routeIs('pic.laporan-harian.*') ? 'active' : '' }}"
       data-title="Catatan Kinerja Harian">
        <i class="bi bi-clipboard2-check"></i>
        <span>Catatan Kinerja Harian</span>
        @if(\Carbon\Carbon::parse('2026-05-09')->diffInDays(now()) <= 7)
        <span class="ms-auto d-flex align-items-center gap-1">
            <span class="sidebar-new-dot"></span>
            <span class="badge bg-danger" style="font-size:0.6rem;padding:2px 5px;">New</span>
        </span>
        @endif
    </a>

    <a href="{{ route('pic.reports.journal-articles') }}"
       class="nav-link {{ request()->routeIs('pic.reports.*') ? 'active' : '' }}"
       data-title="Laporan">
        <i class="bi bi-file-earmark-bar-graph"></i>
        <span>Laporan Jurnal</span>
    </a>

    <div class="sidebar-divider"></div>

    <a href="{{ route('pic.profile.edit') }}"
       class="nav-link {{ request()->routeIs('pic.profile.*') ? 'active' : '' }}"
       data-title="Profile Saya">
        <i class="bi bi-person-circle"></i>
        <span>Profile Saya</span>
    </a>
</nav>
