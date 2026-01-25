@php
    $currentRoute = Route::currentRouteName();
    // Count pending validations
    $pendingValidationCount = \App\Models\Submission::where('status', 'like', '%_SUBMITTED')->count();
@endphp

<a href="{{ route('admin.dashboard') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
<a href="{{ route('admin.monitoring') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.monitoring') ? 'active' : '' }}">
    <i class="bi bi-graph-up"></i> Monitoring Review
</a>

{{-- Menu Pengelolaan Jurnal Baru --}}
<div class="accordion accordion-flush" id="accordionJournalNew">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ str_starts_with($currentRoute, 'admin.journal-masters') || str_starts_with($currentRoute, 'admin.journal-slots') || str_starts_with($currentRoute, 'admin.submissions') || str_starts_with($currentRoute, 'admin.accreditations') || str_starts_with($currentRoute, 'admin.kategoris') || str_starts_with($currentRoute, 'admin.jenis-jurnals') ? 'active' : '' }}" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#collapseJournalNew" 
                    aria-expanded="{{ str_starts_with($currentRoute, 'admin.journal-masters') || str_starts_with($currentRoute, 'admin.journal-slots') || str_starts_with($currentRoute, 'admin.submissions') || str_starts_with($currentRoute, 'admin.accreditations') || str_starts_with($currentRoute, 'admin.kategoris') || str_starts_with($currentRoute, 'admin.jenis-jurnals') ? 'true' : 'false' }}">
                <i class="bi bi-journal-bookmark-fill"></i> Pengelolaan Jurnal
            </button>
        </h2>
        <div id="collapseJournalNew" class="accordion-collapse collapse {{ str_starts_with($currentRoute, 'admin.journal-masters') || str_starts_with($currentRoute, 'admin.journal-slots') || str_starts_with($currentRoute, 'admin.submissions') || str_starts_with($currentRoute, 'admin.accreditations') || str_starts_with($currentRoute, 'admin.kategoris') || str_starts_with($currentRoute, 'admin.jenis-jurnals') ? 'show' : '' }}" data-bs-parent="#accordionJournalNew">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.journal-masters.index') }}" class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.journal-masters') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> Data Jurnal
                </a>
                <a href="{{ route('admin.journal-slots.index') }}" class="nav-link ps-5 {{ $currentRoute == 'admin.journal-slots.index' || $currentRoute == 'admin.journal-slots.create' || $currentRoute == 'admin.journal-slots.edit' || $currentRoute == 'admin.journal-slots.show' || $currentRoute == 'admin.journal-slots.monitoring' ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i> Data Slot & Monitoring
                </a>
                <a href="{{ route('admin.submissions.index') }}" class="nav-link ps-5 {{ $currentRoute == 'admin.submissions.index' || $currentRoute == 'admin.submissions.create' || $currentRoute == 'admin.submissions.edit' || $currentRoute == 'admin.submissions.show' || $currentRoute == 'admin.submissions.process' ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-plus"></i> Data Submit
                </a>
                <a href="{{ route('admin.submissions.monitoring') }}" class="nav-link ps-5 {{ $currentRoute == 'admin.submissions.monitoring' ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i> Monitoring Proses
                    @if($pendingValidationCount > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $pendingValidationCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.accreditations.index') }}" class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.accreditations') ? 'active' : '' }}">
                    <i class="bi bi-award"></i> Akreditasi
                </a>
                <a href="{{ route('admin.kategoris.index') }}" class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.kategoris') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i> Kategori
                </a>
                <a href="{{ route('admin.jenis-jurnals.index') }}" class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.jenis-jurnals') ? 'active' : '' }}">
                    <i class="bi bi-journal-bookmark"></i> Jenis Jurnal
                </a>
            </div>
        </div>
    </div>
</div>
<a href="{{ route('admin.assignments.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.assignments') ? 'active' : '' }}">
    <i class="bi bi-clipboard-check"></i> Penugasan Review
</a>
<a href="{{ route('admin.reviewers.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.reviewers') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Daftar Reviewer
</a>
<a href="{{ route('admin.review-requests.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.review-requests') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-text"></i> Permintaan Review
    @if(isset($pendingReviewRequests) && $pendingReviewRequests > 0)
        <span class="badge bg-warning rounded-pill ms-auto">{{ $pendingReviewRequests }}</span>
    @endif
</a>
<a href="{{ route('admin.leaderboard.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.leaderboard') ? 'active' : '' }}">
    <i class="bi bi-trophy-fill"></i> Papan Peringkat
</a>
<a href="{{ route('admin.redemptions.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.redemptions') ? 'active' : '' }}">
    <i class="bi bi-gift"></i> Penukaran Reward
</a>

{{-- Menu Point & Reward dengan Accordion --}}
<div class="accordion accordion-flush" id="accordionPointReward">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ str_starts_with($currentRoute, 'admin.points') || str_starts_with($currentRoute, 'admin.rewards') || str_starts_with($currentRoute, 'admin.point-settings') ? 'active' : '' }}" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#collapsePointReward" 
                    aria-expanded="{{ str_starts_with($currentRoute, 'admin.points') || str_starts_with($currentRoute, 'admin.rewards') || str_starts_with($currentRoute, 'admin.point-settings') ? 'true' : 'false' }}">
                <i class="bi bi-coin"></i> Point & Reward
            </button>
        </h2>
        <div id="collapsePointReward" class="accordion-collapse collapse {{ str_starts_with($currentRoute, 'admin.points') || str_starts_with($currentRoute, 'admin.rewards') || str_starts_with($currentRoute, 'admin.point-settings') ? 'show' : '' }}" data-bs-parent="#accordionPointReward">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.point-settings.index') }}" class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.point-settings') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Pengaturan Point
                </a>
                <a href="{{ route('admin.points.index') }}" class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.points') ? 'active' : '' }}">
                    <i class="bi bi-list-check"></i> Riwayat Point
                </a>
                <a href="{{ route('admin.rewards.index') }}" class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.rewards') ? 'active' : '' }}">
                    <i class="bi bi-trophy"></i> Kelola Reward
                </a>
            </div>
        </div>
    </div>
</div>

<a href="{{ route('admin.marketings.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.marketings') ? 'active' : '' }}">
    <i class="bi bi-megaphone"></i> Marketing
</a>
<a href="{{ route('admin.pics.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.pics.') ? 'active' : '' }}">
    <i class="bi bi-person-badge"></i> PIC
</a>
<a href="{{ route('admin.field-of-studies.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.field-of-studies') ? 'active' : '' }}">
    <i class="bi bi-book-fill"></i> Bidang Ilmu
</a>

<!-- Laporan Point -->
<div class="nav-item">
    <a class="nav-link {{ str_starts_with($currentRoute, 'admin.marketing-points') || str_starts_with($currentRoute, 'admin.pic-points') || str_starts_with($currentRoute, 'admin.pics.activity') ? '' : 'collapsed' }}" 
       data-bs-toggle="collapse" href="#pointReportMenu" role="button" 
       aria-expanded="{{ str_starts_with($currentRoute, 'admin.marketing-points') || str_starts_with($currentRoute, 'admin.pic-points') || str_starts_with($currentRoute, 'admin.pics.activity') ? 'true' : 'false' }}">
        <i class="bi bi-trophy-fill text-warning"></i> Laporan Point
        <i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <div class="collapse {{ str_starts_with($currentRoute, 'admin.marketing-points') || str_starts_with($currentRoute, 'admin.pic-points') || str_starts_with($currentRoute, 'admin.pics.activity') ? 'show' : '' }}" id="pointReportMenu">
        <div class="nav flex-column ms-3">
            <a href="{{ route('admin.marketing-points.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.marketing-points') ? 'active' : '' }}">
                <i class="bi bi-trophy text-info"></i> Point Marketing
            </a>
            <a href="{{ route('admin.pic-points.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.pic-points') ? 'active' : '' }}">
                <i class="bi bi-trophy text-success"></i> Point PIC
            </a>
            <a href="{{ route('admin.pics.activity-report') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.pics.activity') ? 'active' : '' }}">
                <i class="bi bi-bar-chart text-primary"></i> Aktivitas PIC
            </a>
        </div>
    </div>
</div>

<a href="{{ route('admin.certificates.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.certificates') ? 'active' : '' }}">
    <i class="bi bi-award-fill"></i> Kelola Sertifikat
</a>
<a href="{{ route('admin.users.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.users') ? 'active' : '' }}">
    <i class="bi bi-person-gear"></i> Pengelolaan Pengguna
</a>
<hr>
<a href="{{ route('admin.profile.edit') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.profile') ? 'active' : '' }}">
    <i class="bi bi-person-circle"></i> Edit Profile
</a>
<a href="{{ route('admin.settings.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.settings') && !str_starts_with($currentRoute, 'admin.email-settings') ? 'active' : '' }}">
    <i class="bi bi-gear-fill"></i> Setting Web
</a>
<a href="{{ route('admin.task-point-settings.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.task-point-settings') ? 'active' : '' }}">
    <i class="bi bi-coin"></i> Pengaturan Point
</a>
<a href="{{ route('admin.email-settings.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.email-settings') ? 'active' : '' }}">
    <i class="bi bi-envelope-at-fill"></i> Pengaturan Email
</a>