@php
    $currentRoute = Route::currentRouteName();
    // Count pending validations
    $pendingValidationCount = \App\Models\Submission::where('status', 'like', '%_SUBMITTED')->count();
    // Count out-of-sync items for sync badge
    try {
        $syncOutOfSyncCount = \App\Http\Controllers\Admin\SyncController::countOutOfSync();
    } catch (\Exception $e) {
        $syncOutOfSyncCount = 0;
    }
@endphp

<a href="{{ route('admin.dashboard') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
<a href="{{ route('public.slot.info') }}" class="nav-link" target="_blank">
    <i class="bi bi-calendar-check text-info"></i> Info Slot
    <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.75rem;"></i>
</a>
<a href="{{ route('admin.monitoring') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.monitoring') ? 'active' : '' }}">
    <i class="bi bi-graph-up"></i> Monitoring Review
</a>


@php
    $currentProgram = request('program');
    $isSubmissionRoute = str_starts_with($currentRoute, 'admin.submissions');
    $isSharedJournalRoute = str_starts_with($currentRoute, 'admin.journal-masters')
        || str_starts_with($currentRoute, 'admin.journal-slots')
        || str_starts_with($currentRoute, 'admin.accreditations')
        || str_starts_with($currentRoute, 'admin.kategoris')
        || str_starts_with($currentRoute, 'admin.jenis-jurnals');

    $journalActive  = $isSharedJournalRoute || ($isSubmissionRoute && !in_array($currentProgram, ['bkd', 'jafa']));
    $fastrackActive = str_starts_with($currentRoute, 'admin.fasttrack-management') || str_starts_with($currentRoute, 'admin.fasttrack');
    $bkdActive      = $isSubmissionRoute && $currentProgram === 'bkd';
    $jafaActive     = $isSubmissionRoute && $currentProgram === 'jafa';
@endphp

{{-- Menu Pengelolaan Jurnal --}}
<div class="accordion accordion-flush" id="accordionJournalManagement">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $journalActive ? 'active' : '' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseJournalManagement"
                    aria-expanded="{{ $journalActive ? 'true' : 'false' }}">
                <i class="bi bi-journal-bookmark-fill text-primary"></i> Pengelolaan Jurnal
            </button>
        </h2>
        <div id="collapseJournalManagement" class="accordion-collapse collapse {{ $journalActive ? 'show' : '' }}" data-bs-parent="#accordionJournalManagement">
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

{{-- Menu Pengelolaan Jurnal Fasttrack --}}
@feature('fasttrack')
<div class="accordion accordion-flush" id="accordionFastrackManagement">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $fastrackActive ? 'active' : '' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseFastrackManagement"
                    aria-expanded="{{ $fastrackActive ? 'true' : 'false' }}">
                <i class="bi bi-lightning-charge text-warning"></i> Pengelolaan Jurnal Fasttrack
            </button>
        </h2>
        <div id="collapseFastrackManagement" class="accordion-collapse collapse {{ $fastrackActive ? 'show' : '' }}" data-bs-parent="#accordionFastrackManagement">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.fasttrack.create') }}" class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.fasttrack') && !str_starts_with($currentRoute, 'admin.fasttrack-management') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle text-warning"></i> Input Fasttrack
                </a>
                <a href="{{ route('admin.fasttrack-management.slots.index') }}" class="nav-link ps-5 {{ $currentRoute == 'admin.fasttrack-management.slots.index' ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i> Data Slot Fasttrack
                </a>
                <a href="{{ route('admin.fasttrack-management.submissions.index') }}" class="nav-link ps-5 {{ $currentRoute == 'admin.fasttrack-management.submissions.index' ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i> Data Submit Fasttrack
                </a>
                <a href="{{ route('admin.fasttrack-management.monitoring.index') }}" class="nav-link ps-5 {{ $currentRoute == 'admin.fasttrack-management.monitoring.index' ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i> Monitoring Proses Fasttrack
                </a>
            </div>
        </div>
    </div>
</div>
@endfeature

{{-- Menu Pengelolaan Jurnal BKD --}}
<div class="accordion accordion-flush" id="accordionBKD">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $bkdActive ? 'active' : '' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseBKD"
                    aria-expanded="{{ $bkdActive ? 'true' : 'false' }}">
                <i class="bi bi-briefcase-fill text-info"></i> Pengelolaan Jurnal BKD
            </button>
        </h2>
        <div id="collapseBKD" class="accordion-collapse collapse {{ $bkdActive ? 'show' : '' }}" data-bs-parent="#accordionBKD">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.submissions.create', ['program' => 'bkd']) }}" class="nav-link ps-5 {{ $currentRoute == 'admin.submissions.create' && $currentProgram === 'bkd' ? 'active' : '' }}">
                    <i class="bi bi-plus-circle-fill text-info"></i> Input Langsung BKD
                </a>
                <a href="{{ route('admin.submissions.index', ['program' => 'bkd']) }}" class="nav-link ps-5 {{ $bkdActive && in_array($currentRoute, ['admin.submissions.index','admin.submissions.edit','admin.submissions.show','admin.submissions.process']) ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-plus"></i> Data Submit BKD
                </a>
                <a href="{{ route('admin.submissions.monitoring', ['program' => 'bkd']) }}" class="nav-link ps-5 {{ $bkdActive && $currentRoute == 'admin.submissions.monitoring' ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i> Monitoring Proses BKD
                    @if($pendingValidationCount > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $pendingValidationCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Menu Pengelolaan Jurnal JAFA --}}
<div class="accordion accordion-flush" id="accordionJAFA">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $jafaActive ? 'active' : '' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseJAFA"
                    aria-expanded="{{ $jafaActive ? 'true' : 'false' }}">
                <i class="bi bi-folder2-open text-success"></i> Pengelolaan Jurnal JAFA
            </button>
        </h2>
        <div id="collapseJAFA" class="accordion-collapse collapse {{ $jafaActive ? 'show' : '' }}" data-bs-parent="#accordionJAFA">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.submissions.create', ['program' => 'jafa']) }}" class="nav-link ps-5 {{ $currentRoute == 'admin.submissions.create' && $currentProgram === 'jafa' ? 'active' : '' }}">
                    <i class="bi bi-plus-circle-fill text-success"></i> Input Langsung JAFA
                </a>
                <a href="{{ route('admin.submissions.index', ['program' => 'jafa']) }}" class="nav-link ps-5 {{ $jafaActive && in_array($currentRoute, ['admin.submissions.index','admin.submissions.edit','admin.submissions.show','admin.submissions.process']) ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-plus"></i> Data Submit JAFA
                </a>
                <a href="{{ route('admin.submissions.monitoring', ['program' => 'jafa']) }}" class="nav-link ps-5 {{ $jafaActive && $currentRoute == 'admin.submissions.monitoring' ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i> Monitoring Proses JAFA
                    @if($pendingValidationCount > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $pendingValidationCount }}</span>
                    @endif
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
@feature('review_requests')
<a href="{{ route('admin.review-requests.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.review-requests') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-text"></i> Permintaan Review
    @if(isset($pendingReviewRequests) && $pendingReviewRequests > 0)
        <span class="badge bg-warning rounded-pill ms-auto">{{ $pendingReviewRequests }}</span>
    @endif
</a>
@endfeature
@feature('leaderboard')
<a href="{{ route('admin.leaderboard.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.leaderboard') ? 'active' : '' }}">
    <i class="bi bi-trophy-fill"></i> Papan Peringkat
</a>
@endfeature
{{-- <a href="{{ route('admin.redemptions.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.redemptions') ? 'active' : '' }}">
    <i class="bi bi-gift"></i> Penukaran Reward
</a> --}}

{{-- Menu Point & Reward dengan Accordion --}}
<div class="accordion accordion-flush" id="accordionPointReward">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ str_starts_with($currentRoute, 'admin.points') || str_starts_with($currentRoute, 'admin.point-settings') ? 'active' : '' }}" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#collapsePointReward" 
                    aria-expanded="{{ str_starts_with($currentRoute, 'admin.points') || str_starts_with($currentRoute, 'admin.point-settings') ? 'true' : 'false' }}">
                <i class="bi bi-coin"></i> Point & Reward
            </button>
        </h2>
        <div id="collapsePointReward" class="accordion-collapse collapse {{ str_starts_with($currentRoute, 'admin.points') || str_starts_with($currentRoute, 'admin.point-settings') ? 'show' : '' }}" data-bs-parent="#accordionPointReward">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.point-settings.index') }}" class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.point-settings') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Pengaturan Point
                </a>
                <a href="{{ route('admin.points.index') }}" class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.points') ? 'active' : '' }}">
                    <i class="bi bi-list-check"></i> Riwayat Point
                </a>
                {{-- <a href="{{ route('admin.rewards.index') }}" class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.rewards') ? 'active' : '' }}">
                    <i class="bi bi-trophy"></i> Kelola Reward
                </a> --}}
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
    <a class="nav-link {{ str_starts_with($currentRoute, 'admin.marketing-points') || str_starts_with($currentRoute, 'admin.pic-points') || str_starts_with($currentRoute, 'admin.pics.activity') || str_starts_with($currentRoute, 'admin.task-point-settings') || str_starts_with($currentRoute, 'admin.point-rankings') || str_starts_with($currentRoute, 'admin.team-') ? '' : 'collapsed' }}" 
       data-bs-toggle="collapse" href="#pointReportMenu" role="button" 
       aria-expanded="{{ str_starts_with($currentRoute, 'admin.marketing-points') || str_starts_with($currentRoute, 'admin.pic-points') || str_starts_with($currentRoute, 'admin.pics.activity') || str_starts_with($currentRoute, 'admin.task-point-settings') || str_starts_with($currentRoute, 'admin.point-rankings') || str_starts_with($currentRoute, 'admin.team-') ? 'true' : 'false' }}">
        <i class="bi bi-trophy-fill text-warning"></i> Laporan Point
        <i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <div class="collapse {{ str_starts_with($currentRoute, 'admin.marketing-points') || str_starts_with($currentRoute, 'admin.pic-points') || str_starts_with($currentRoute, 'admin.pics.activity') || str_starts_with($currentRoute, 'admin.task-point-settings') || str_starts_with($currentRoute, 'admin.point-rankings') || str_starts_with($currentRoute, 'admin.team-') ? 'show' : '' }}" id="pointReportMenu">
        <div class="nav flex-column ms-3">
            <a href="{{ route('admin.point-rankings') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.point-rankings') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill text-warning"></i> Peringkat Point
            </a>
            <a href="{{ route('admin.task-point-settings.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.task-point-settings') ? 'active' : '' }}">
                <i class="bi bi-gear text-secondary"></i> Pengaturan Point
            </a>
            <a href="{{ route('admin.marketing-points.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.marketing-points') ? 'active' : '' }}">
                <i class="bi bi-trophy text-info"></i> Point Marketing
            </a>
            <a href="{{ route('admin.pic-points.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.pic-points') ? 'active' : '' }}">
                <i class="bi bi-trophy text-success"></i> Point PIC
            </a>
            <a href="{{ route('admin.pics.activity-report') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.pics.activity') ? 'active' : '' }}">
                <i class="bi bi-bar-chart text-primary"></i> Aktivitas PIC
            </a>
            <hr class="my-2 mx-3 border-secondary">
            <span class="nav-link text-muted small">
                <i class="bi bi-people-fill"></i> Laporan Tim Performa
            </span>
            <a href="{{ route('admin.team-performance', ['step' => 'submit']) }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.team-performance') && request('step') != 'marketing' ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow text-success"></i> Performa Tim PIC
            </a>
            <a href="{{ route('admin.team-performance', ['step' => 'marketing']) }}" class="nav-link {{ request('step') == 'marketing' ? 'active' : '' }}">
                <i class="bi bi-megaphone-fill text-danger"></i> Performa Tim Marketing
            </a>
        </div>
    </div>
</div>

@feature('certificates')
<a href="{{ route('admin.certificates.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.certificates') ? 'active' : '' }}">
    <i class="bi bi-award-fill"></i> Kelola Sertifikat
</a>
@endfeature
<a href="{{ route('admin.users.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.users') ? 'active' : '' }}">
    <i class="bi bi-person-gear"></i> Pengelolaan Pengguna
</a>
<a href="{{ route('admin.reports.journal-articles') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.reports') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-bar-graph"></i> Laporan Jurnal
</a>
<a href="{{ route('admin.laporan-kinerja.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.laporan-kinerja') ? 'active' : '' }}">
    <i class="bi bi-bar-chart-steps text-warning"></i> Laporan Kinerja
</a>
<hr>
<a href="{{ route('admin.profile.edit') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.profile') ? 'active' : '' }}">
    <i class="bi bi-person-circle"></i> Edit Profile
</a>
<a href="{{ route('admin.settings.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.settings') && !str_starts_with($currentRoute, 'admin.email-settings') ? 'active' : '' }}">
    <i class="bi bi-gear-fill"></i> Setting Web
</a>
<a href="{{ route('admin.email-settings.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.email-settings') ? 'active' : '' }}">
    <i class="bi bi-envelope-at-fill"></i> Pengaturan Email
</a>
<a href="{{ route('admin.sms-gateway.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.sms-gateway') ? 'active' : '' }}">
    <i class="bi bi-whatsapp text-success"></i> SMS Gateway
</a>
<a href="{{ route('admin.component-overview') }}" class="nav-link {{ $currentRoute == 'admin.component-overview' ? 'active' : '' }}">
    <i class="bi bi-puzzle-fill text-info"></i> Component Overview
</a>
<a href="{{ route('admin.feature-management') }}" class="nav-link {{ $currentRoute == 'admin.feature-management' ? 'active' : '' }}">
    <i class="bi bi-toggles text-warning"></i> Feature Management
</a>
<a href="{{ route('admin.sync.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.sync') ? 'active' : '' }}">
    <i class="bi bi-arrow-repeat text-info"></i> Sinkronisasi Data
    @if($syncOutOfSyncCount > 0)
        <span class="badge bg-warning text-dark ms-1 rounded-pill">{{ $syncOutOfSyncCount }}</span>
    @endif
</a>