@php
    $currentRoute   = Route::currentRouteName();
    $currentProgram = request('program');

    $isSubmissionRoute   = str_starts_with($currentRoute, 'admin.submissions');
    $isJournalRoute      = str_starts_with($currentRoute, 'admin.journal-masters')
        || str_starts_with($currentRoute, 'admin.journal-slots')
        || str_starts_with($currentRoute, 'admin.accreditations');
    $fastrackActive      = str_starts_with($currentRoute, 'admin.fasttrack-management')
        || str_starts_with($currentRoute, 'admin.fasttrack');
    $normalActive        = $isSubmissionRoute && !$currentProgram;
    $bkdActive           = $isSubmissionRoute && $currentProgram === 'bkd';
    $jafaActive          = $isSubmissionRoute && $currentProgram === 'jafa';

    $pendingValidationCount = \Illuminate\Support\Facades\Cache::remember('admin.pending_validation_count', 120,
        fn() => \App\Models\Submission::where('status', 'like', '%_SUBMITTED')->count()
    );

    try {
        $pendingReviewRequests = \Illuminate\Support\Facades\Cache::remember('admin.pending_review_requests', 60,
            fn() => \App\Models\ReviewRequest::where('status', 'pending')->count()
        );
    } catch (\Exception) {
        $pendingReviewRequests = 0;
    }
@endphp

{{-- ═══ UTAMA ═══ --}}
<a href="{{ route('admin.pic-reviewer.dashboard') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.pic-reviewer') ? 'active' : '' }}">
    <i class="bi bi-house-fill text-white"></i> Dashboard
</a>
<a href="{{ route('admin.monitoring') }}"
   class="nav-link {{ str_starts_with($currentRoute, 'admin.monitoring') ? 'active' : '' }}">
    <i class="bi bi-activity" style="color:#34d399;"></i> Monitoring Review
</a>

{{-- ═══ MANAJEMEN JURNAL ═══ --}}
<div class="sidebar-section-label">Manajemen Jurnal</div>

{{-- Data Jurnal --}}
<div class="accordion accordion-flush" id="accordionJournalPR">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $isJournalRoute ? 'active' : '' }}"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseJournalPR"
                    aria-expanded="{{ $isJournalRoute ? 'true' : 'false' }}">
                <i class="bi bi-database-fill" style="color:#818cf8;"></i> Data Jurnal
            </button>
        </h2>
        <div id="collapseJournalPR" class="accordion-collapse collapse {{ $isJournalRoute ? 'show' : '' }}">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.journal-masters.index') }}"
                   class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.journal-masters') ? 'active' : '' }}">
                    <i class="bi bi-journal-text" style="color:#818cf8;"></i> Master Jurnal
                </a>
                <a href="{{ route('admin.journal-slots.index') }}"
                   class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.journal-slots') ? 'active' : '' }}">
                    <i class="bi bi-calendar3" style="color:#818cf8;"></i> Slot & Monitoring
                </a>
                <a href="{{ route('admin.accreditations.index') }}"
                   class="nav-link ps-5 {{ str_starts_with($currentRoute, 'admin.accreditations') ? 'active' : '' }}">
                    <i class="bi bi-patch-check-fill" style="color:#818cf8;"></i> Akreditasi
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Jurnal Normal --}}
<div class="accordion accordion-flush" id="accordionNormalPR">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $normalActive ? 'active' : '' }}"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseNormalPR"
                    aria-expanded="{{ $normalActive ? 'true' : 'false' }}">
                <i class="bi bi-file-earmark-richtext-fill" style="color:#c084fc;"></i> Jurnal Normal
            </button>
        </h2>
        <div id="collapseNormalPR" class="accordion-collapse collapse {{ $normalActive ? 'show' : '' }}">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.submissions.index') }}"
                   class="nav-link ps-5 {{ $normalActive && str_starts_with($currentRoute, 'admin.submissions') ? 'active' : '' }}">
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
<div class="accordion accordion-flush" id="accordionFastrackPR">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $fastrackActive ? 'active' : '' }}"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseFastrackPR"
                    aria-expanded="{{ $fastrackActive ? 'true' : 'false' }}">
                <i class="bi bi-lightning-charge-fill text-warning"></i> Jurnal Fasttrack
            </button>
        </h2>
        <div id="collapseFastrackPR" class="accordion-collapse collapse {{ $fastrackActive ? 'show' : '' }}">
            <div class="accordion-body p-0">
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
<div class="accordion accordion-flush" id="accordionBKDPR">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $bkdActive ? 'active' : '' }}"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseBKDPR"
                    aria-expanded="{{ $bkdActive ? 'true' : 'false' }}">
                <i class="bi bi-briefcase-fill" style="color:#38bdf8;"></i> Jurnal BKD
            </button>
        </h2>
        <div id="collapseBKDPR" class="accordion-collapse collapse {{ $bkdActive ? 'show' : '' }}">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.submissions.index', ['program' => 'bkd']) }}"
                   class="nav-link ps-5 {{ $bkdActive && str_starts_with($currentRoute, 'admin.submissions') ? 'active' : '' }}">
                    <i class="bi bi-table" style="color:#38bdf8;"></i> Data Submit
                </a>
                <a href="{{ route('admin.submissions.monitoring', ['program' => 'bkd']) }}"
                   class="nav-link ps-5 {{ $bkdActive && $currentRoute == 'admin.submissions.monitoring' ? 'active' : '' }}">
                    <i class="bi bi-kanban-fill" style="color:#38bdf8;"></i> Monitoring
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Jurnal JAFA --}}
<div class="accordion accordion-flush" id="accordionJAFAPR">
    <div class="accordion-item bg-transparent border-0">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed nav-link text-white {{ $jafaActive ? 'active' : '' }}"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseJAFAPR"
                    aria-expanded="{{ $jafaActive ? 'true' : 'false' }}">
                <i class="bi bi-folder-fill" style="color:#4ade80;"></i> Jurnal JAFA
            </button>
        </h2>
        <div id="collapseJAFAPR" class="accordion-collapse collapse {{ $jafaActive ? 'show' : '' }}">
            <div class="accordion-body p-0">
                <a href="{{ route('admin.submissions.index', ['program' => 'jafa']) }}"
                   class="nav-link ps-5 {{ $jafaActive && str_starts_with($currentRoute, 'admin.submissions') ? 'active' : '' }}">
                    <i class="bi bi-table" style="color:#4ade80;"></i> Data Submit
                </a>
                <a href="{{ route('admin.submissions.monitoring', ['program' => 'jafa']) }}"
                   class="nav-link ps-5 {{ $jafaActive && $currentRoute == 'admin.submissions.monitoring' ? 'active' : '' }}">
                    <i class="bi bi-kanban-fill" style="color:#4ade80;"></i> Monitoring
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
    @if($pendingReviewRequests > 0)
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
