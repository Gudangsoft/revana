@php
    $currentRoute = Route::currentRouteName();
    $pendingValidationCount = \App\Models\Submission::where('status', 'like', '%_SUBMITTED')->count();
    try {
        $syncOutOfSyncCount = \App\Http\Controllers\Admin\SyncController::countOutOfSync();
    } catch (\Exception $e) {
        $syncOutOfSyncCount = 0;
    }

    $currentProgram  = request('program');
    $isSubmissionRoute = str_starts_with($currentRoute, 'admin.submissions');
    $isSharedJournalRoute = str_starts_with($currentRoute, 'admin.journal-masters')
        || str_starts_with($currentRoute, 'admin.journal-slots')
        || str_starts_with($currentRoute, 'admin.accreditations')
        || str_starts_with($currentRoute, 'admin.kategoris')
        || str_starts_with($currentRoute, 'admin.jenis-jurnals');

    $normalActive   = $isSubmissionRoute && !$currentProgram;
    $journalActive  = $isSharedJournalRoute;
    $fastrackActive = str_starts_with($currentRoute, 'admin.fasttrack-management') || str_starts_with($currentRoute, 'admin.fasttrack');
    $bkdActive      = $isSubmissionRoute && $currentProgram === 'bkd';
    $jafaActive     = $isSubmissionRoute && $currentProgram === 'jafa';

    $pointActive    = str_starts_with($currentRoute, 'admin.points') || str_starts_with($currentRoute, 'admin.point-settings');
    $laporanPointActive = str_starts_with($currentRoute, 'admin.marketing-points')
        || str_starts_with($currentRoute, 'admin.pic-points')
        || str_starts_with($currentRoute, 'admin.pics.activity')
        || str_starts_with($currentRoute, 'admin.task-point-settings')
        || str_starts_with($currentRoute, 'admin.point-rankings')
        || str_starts_with($currentRoute, 'admin.team-');
@endphp

{{-- ═══ UTAMA ═══ --}}
<a href="{{ route('admin.dashboard') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.dashboard') ? 'active' : '' }}">
    <i class="bi bi-house-fill text-white"></i> Dashboard
</a>
<a href="{{ route('admin.monitoring') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.monitoring') ? 'active' : '' }}">
    <i class="bi bi-activity" style="color:#34d399;"></i> Monitoring Review
</a>
<a href="{{ route('public.slot.info') }}" class="nav-link" target="_blank">
    <i class="bi bi-calendar-event-fill" style="color:#67e8f9;"></i> Info Slot
    <i class="bi bi-box-arrow-up-right ms-1 opacity-50" style="font-size:0.7rem;"></i>
</a>

{{-- ═══ MANAJEMEN JURNAL ═══ --}}
<div class="sidebar-section-label">Manajemen Jurnal</div>

{{-- Data Master Jurnal --}}
<div class="accordion accordion-flush" id="accordionJournalManagement">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $journalActive ? 'active' : '' }}"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseJournalManagement"
                    aria-expanded="{{ $journalActive ? 'true' : 'false' }}">
                <i class="bi bi-database-fill" style="color:#818cf8;"></i> Data Jurnal
            </button>
        </h2>
        <div id="collapseJournalManagement" class="accordion-collapse collapse {{ $journalActive ? 'show' : '' }}">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.journal-masters.index') }}"
                   class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.journal-masters') ? 'active' : '' }}">
                    <i class="bi bi-journal-text" style="color:#818cf8;"></i> Master Jurnal
                </a>
                <a href="{{ route('admin.journal-slots.index') }}"
                   class="nav-link ps-5 {{ in_array($currentRoute, ['admin.journal-slots.index','admin.journal-slots.create','admin.journal-slots.edit','admin.journal-slots.show','admin.journal-slots.monitoring']) ? 'active' : '' }}">
                    <i class="bi bi-calendar3" style="color:#818cf8;"></i> Slot & Monitoring
                </a>
                <a href="{{ route('admin.accreditations.index') }}"
                   class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.accreditations') ? 'active' : '' }}">
                    <i class="bi bi-patch-check-fill" style="color:#818cf8;"></i> Akreditasi
                </a>
                <a href="{{ route('admin.kategoris.index') }}"
                   class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.kategoris') ? 'active' : '' }}">
                    <i class="bi bi-tags-fill" style="color:#818cf8;"></i> Kategori
                </a>
                <a href="{{ route('admin.jenis-jurnals.index') }}"
                   class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.jenis-jurnals') ? 'active' : '' }}">
                    <i class="bi bi-bookmarks-fill" style="color:#818cf8;"></i> Jenis Jurnal
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Jurnal Normal --}}
<div class="accordion accordion-flush" id="accordionNormal">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $normalActive ? 'active' : '' }}"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseNormal"
                    aria-expanded="{{ $normalActive ? 'true' : 'false' }}">
                <i class="bi bi-file-earmark-richtext-fill" style="color:#c084fc;"></i> Jurnal Normal
            </button>
        </h2>
        <div id="collapseNormal" class="accordion-collapse collapse {{ $normalActive ? 'show' : '' }}">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.submissions.create') }}"
                   class="nav-link ps-5 {{ $normalActive && $currentRoute == 'admin.submissions.create' ? 'active' : '' }}">
                    <i class="bi bi-plus-circle-fill" style="color:#c084fc;"></i> Input Data
                </a>
                <a href="{{ route('admin.submissions.index') }}"
                   class="nav-link ps-5 {{ $normalActive && in_array($currentRoute, ['admin.submissions.index','admin.submissions.edit','admin.submissions.show','admin.submissions.process']) ? 'active' : '' }}">
                    <i class="bi bi-table" style="color:#c084fc;"></i> Data Submit
                </a>
                <a href="{{ route('admin.submissions.monitoring') }}"
                   class="nav-link ps-5 {{ $normalActive && $currentRoute == 'admin.submissions.monitoring' ? 'active' : '' }}">
                    <i class="bi bi-kanban-fill" style="color:#c084fc;"></i> Monitoring
                    @if($pendingValidationCount > 0)
                        <span class="badge bg-warning text-dark ms-1">{{ $pendingValidationCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Jurnal Fasttrack --}}
@feature('fasttrack')
<div class="accordion accordion-flush" id="accordionFastrackManagement">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $fastrackActive ? 'active' : '' }}"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseFastrackManagement"
                    aria-expanded="{{ $fastrackActive ? 'true' : 'false' }}">
                <i class="bi bi-lightning-charge-fill text-warning"></i> Jurnal Fasttrack
            </button>
        </h2>
        <div id="collapseFastrackManagement" class="accordion-collapse collapse {{ $fastrackActive ? 'show' : '' }}">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.fasttrack.create') }}"
                   class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.fasttrack') && !str_starts_with($currentRoute, 'admin.fasttrack-management') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle-fill text-warning"></i> Input Fasttrack
                </a>
                <a href="{{ route('admin.fasttrack-management.slots.index') }}"
                   class="nav-link ps-5 {{ $currentRoute == 'admin.fasttrack-management.slots.index' ? 'active' : '' }}">
                    <i class="bi bi-calendar2-week text-warning"></i> Slot Fasttrack
                </a>
                <a href="{{ route('admin.fasttrack-management.submissions.index') }}"
                   class="nav-link ps-5 {{ $currentRoute == 'admin.fasttrack-management.submissions.index' ? 'active' : '' }}">
                    <i class="bi bi-table text-warning"></i> Data Submit
                </a>
                <a href="{{ route('admin.fasttrack-management.monitoring.index') }}"
                   class="nav-link ps-5 {{ $currentRoute == 'admin.fasttrack-management.monitoring.index' ? 'active' : '' }}">
                    <i class="bi bi-kanban text-warning"></i> Monitoring
                </a>
            </div>
        </div>
    </div>
</div>
@endfeature

{{-- Jurnal BKD --}}
<div class="accordion accordion-flush" id="accordionBKD">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $bkdActive ? 'active' : '' }}"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseBKD"
                    aria-expanded="{{ $bkdActive ? 'true' : 'false' }}">
                <i class="bi bi-briefcase-fill" style="color:#38bdf8;"></i> Jurnal BKD
            </button>
        </h2>
        <div id="collapseBKD" class="accordion-collapse collapse {{ $bkdActive ? 'show' : '' }}">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.submissions.create', ['program' => 'bkd']) }}"
                   class="nav-link ps-5 {{ $currentRoute == 'admin.submissions.create' && $currentProgram === 'bkd' ? 'active' : '' }}">
                    <i class="bi bi-plus-circle-fill" style="color:#38bdf8;"></i> Input Data
                </a>
                <a href="{{ route('admin.submissions.index', ['program' => 'bkd']) }}"
                   class="nav-link ps-5 {{ $bkdActive && in_array($currentRoute, ['admin.submissions.index','admin.submissions.edit','admin.submissions.show','admin.submissions.process']) ? 'active' : '' }}">
                    <i class="bi bi-table" style="color:#38bdf8;"></i> Data Submit
                </a>
                <a href="{{ route('admin.submissions.monitoring', ['program' => 'bkd']) }}"
                   class="nav-link ps-5 {{ $bkdActive && $currentRoute == 'admin.submissions.monitoring' ? 'active' : '' }}">
                    <i class="bi bi-kanban-fill" style="color:#38bdf8;"></i> Monitoring
                    @if($pendingValidationCount > 0)
                        <span class="badge bg-warning text-dark ms-1">{{ $pendingValidationCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Jurnal JAFA --}}
<div class="accordion accordion-flush" id="accordionJAFA">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $jafaActive ? 'active' : '' }}"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseJAFA"
                    aria-expanded="{{ $jafaActive ? 'true' : 'false' }}">
                <i class="bi bi-folder-fill" style="color:#4ade80;"></i> Jurnal JAFA
            </button>
        </h2>
        <div id="collapseJAFA" class="accordion-collapse collapse {{ $jafaActive ? 'show' : '' }}">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.submissions.create', ['program' => 'jafa']) }}"
                   class="nav-link ps-5 {{ $currentRoute == 'admin.submissions.create' && $currentProgram === 'jafa' ? 'active' : '' }}">
                    <i class="bi bi-plus-circle-fill" style="color:#4ade80;"></i> Input Data
                </a>
                <a href="{{ route('admin.submissions.index', ['program' => 'jafa']) }}"
                   class="nav-link ps-5 {{ $jafaActive && in_array($currentRoute, ['admin.submissions.index','admin.submissions.edit','admin.submissions.show','admin.submissions.process']) ? 'active' : '' }}">
                    <i class="bi bi-table" style="color:#4ade80;"></i> Data Submit
                </a>
                <a href="{{ route('admin.submissions.monitoring', ['program' => 'jafa']) }}"
                   class="nav-link ps-5 {{ $jafaActive && $currentRoute == 'admin.submissions.monitoring' ? 'active' : '' }}">
                    <i class="bi bi-kanban-fill" style="color:#4ade80;"></i> Monitoring
                    @if($pendingValidationCount > 0)
                        <span class="badge bg-warning text-dark ms-1">{{ $pendingValidationCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ═══ REVIEWER ═══ --}}
<div class="sidebar-section-label">Reviewer</div>

<a href="{{ route('admin.assignments.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.assignments') ? 'active' : '' }}">
    <i class="bi bi-clipboard2-check-fill" style="color:#86efac;"></i> Penugasan Review
</a>
<a href="{{ route('admin.reviewers.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.reviewers') ? 'active' : '' }}">
    <i class="bi bi-people-fill" style="color:#67e8f9;"></i> Daftar Reviewer
</a>
@feature('review_requests')
<a href="{{ route('admin.review-requests.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.review-requests') ? 'active' : '' }}">
    <i class="bi bi-envelope-open-fill" style="color:#fcd34d;"></i> Permintaan Review
    @if(isset($pendingReviewRequests) && $pendingReviewRequests > 0)
        <span class="badge bg-warning rounded-pill ms-auto">{{ $pendingReviewRequests }}</span>
    @endif
</a>
@endfeature
@feature('leaderboard')
<a href="{{ route('admin.leaderboard.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.leaderboard') ? 'active' : '' }}">
    <i class="bi bi-trophy-fill text-warning"></i> Papan Peringkat
</a>
@endfeature

{{-- ═══ SDM ═══ --}}
<div class="sidebar-section-label">SDM</div>

<a href="{{ route('admin.marketings.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.marketings') ? 'active' : '' }}">
    <i class="bi bi-megaphone-fill" style="color:#f87171;"></i> Marketing
</a>
<a href="{{ route('admin.pics.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.pics.') ? 'active' : '' }}">
    <i class="bi bi-person-badge-fill" style="color:#60a5fa;"></i> PIC
</a>
<a href="{{ route('admin.field-of-studies.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.field-of-studies') ? 'active' : '' }}">
    <i class="bi bi-mortarboard-fill" style="color:#a78bfa;"></i> Bidang Ilmu
</a>

{{-- ═══ LAPORAN ═══ --}}
<div class="sidebar-section-label">Laporan</div>

{{-- Laporan Point accordion --}}
<div class="accordion accordion-flush" id="accordionLaporanPoint">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $laporanPointActive ? 'active' : '' }}"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseLaporanPoint"
                    aria-expanded="{{ $laporanPointActive ? 'true' : 'false' }}">
                <i class="bi bi-graph-up-arrow text-warning"></i> Laporan Point
            </button>
        </h2>
        <div id="collapseLaporanPoint" class="accordion-collapse collapse {{ $laporanPointActive ? 'show' : '' }}">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.point-rankings') }}"
                   class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.point-rankings') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-fill text-warning"></i> Peringkat Point
                </a>
                <a href="{{ route('admin.task-point-settings.index') }}"
                   class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.task-point-settings') ? 'active' : '' }}">
                    <i class="bi bi-sliders text-secondary"></i> Pengaturan Point
                </a>
                <a href="{{ route('admin.marketing-points.index') }}"
                   class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.marketing-points') ? 'active' : '' }}">
                    <i class="bi bi-coin" style="color:#f87171;"></i> Point Marketing
                </a>
                <a href="{{ route('admin.pic-points.index') }}"
                   class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.pic-points') ? 'active' : '' }}">
                    <i class="bi bi-coin" style="color:#4ade80;"></i> Point PIC
                </a>
                <a href="{{ route('admin.pics.activity-report') }}"
                   class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.pics.activity') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart text-primary"></i> Aktivitas PIC
                </a>
                <a href="{{ route('admin.team-performance', ['step' => 'submit']) }}"
                   class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.team-performance') && request('step') != 'marketing' ? 'active' : '' }}">
                    <i class="bi bi-people" style="color:#86efac;"></i> Performa Tim PIC
                </a>
                <a href="{{ route('admin.team-performance', ['step' => 'marketing']) }}"
                   class="nav-link ps-5 {{ request('step') == 'marketing' ? 'active' : '' }}">
                    <i class="bi bi-people" style="color:#f87171;"></i> Performa Marketing
                </a>
            </div>
        </div>
    </div>
</div>

<a href="{{ route('admin.reports.journal-articles') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.reports') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-bar-graph-fill" style="color:#60a5fa;"></i> Laporan Jurnal
</a>
<a href="{{ route('admin.laporan-kinerja.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.laporan-kinerja') ? 'active' : '' }}">
    <i class="bi bi-bar-chart-line-fill" style="color:#34d399;"></i> Laporan Kinerja
</a>
<a href="{{ route('admin.laporan-harian.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.laporan-harian') ? 'active' : '' }}">
    <i class="bi bi-clipboard2-check-fill" style="color:#818cf8;"></i> Catatan Kinerja Harian
</a>

{{-- ═══ PENGATURAN ═══ --}}
<div class="sidebar-section-label">Pengaturan</div>

@feature('certificates')
<a href="{{ route('admin.certificates.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.certificates') ? 'active' : '' }}">
    <i class="bi bi-award-fill text-warning"></i> Kelola Sertifikat
</a>
@endfeature
<a href="{{ route('admin.users.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.users') ? 'active' : '' }}">
    <i class="bi bi-person-gear" style="color:#94a3b8;"></i> Pengelolaan Pengguna
</a>
<a href="{{ route('admin.profile.edit') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.profile') ? 'active' : '' }}">
    <i class="bi bi-person-circle" style="color:#67e8f9;"></i> Edit Profil
</a>
<a href="{{ route('admin.settings.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.settings') && !str_starts_with($currentRoute, 'admin.email-settings') ? 'active' : '' }}">
    <i class="bi bi-sliders" style="color:#94a3b8;"></i> Setting Web
</a>
<a href="{{ route('admin.email-settings.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.email-settings') ? 'active' : '' }}">
    <i class="bi bi-envelope-at-fill" style="color:#fcd34d;"></i> Pengaturan Email
</a>
<a href="{{ route('admin.sms-gateway.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.sms-gateway') ? 'active' : '' }}">
    <i class="bi bi-whatsapp" style="color:#4ade80;"></i> SMS Gateway
</a>
<a href="{{ route('admin.sync.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.sync') ? 'active' : '' }}">
    <i class="bi bi-arrow-repeat" style="color:#67e8f9;"></i> Sinkronisasi Data
    @if($syncOutOfSyncCount > 0)
        <span class="badge bg-warning text-dark ms-1 rounded-pill">{{ $syncOutOfSyncCount }}</span>
    @endif
</a>
<a href="{{ route('admin.component-overview') }}"
   class="nav-link {{ $currentRoute == 'admin.component-overview' ? 'active' : '' }}">
    <i class="bi bi-puzzle-fill" style="color:#818cf8;"></i> Component Overview
</a>
<a href="{{ route('admin.feature-management') }}"
   class="nav-link {{ $currentRoute == 'admin.feature-management' ? 'active' : '' }}">
    <i class="bi bi-toggles" style="color:#fcd34d;"></i> Feature Management
</a>
<a href="{{ route('admin.activity-logs.index') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.activity-logs') ? 'active' : '' }}">
    <i class="bi bi-shield-check" style="color:#4ade80;"></i> Audit Log
</a>
