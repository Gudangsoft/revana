<!-- PIC Sidebar -->
<nav class="nav flex-column">
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
    </a>
    <a href="{{ route('pic.points.index') }}" class="nav-link {{ request()->routeIs('pic.points.*') ? 'active' : '' }}">
        <i class="bi bi-trophy"></i> Point Saya
        @php
            $picUser = Auth::guard('pic')->user();
            $totalPoints = $picUser ? $picUser->total_points : 0;
        @endphp
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
