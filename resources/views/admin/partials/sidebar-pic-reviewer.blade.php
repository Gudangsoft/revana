@php
    $currentRoute = Route::currentRouteName();

    $isJournalRoute = str_starts_with($currentRoute, 'admin.journal-masters')
        || str_starts_with($currentRoute, 'admin.journal-slots')
        || str_starts_with($currentRoute, 'admin.accreditations');

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
<a href="{{ route('admin.pic-reviewer.submissions', 'normal') }}"
   class="nav-link {{ $currentRoute === 'admin.pic-reviewer.submissions' && request()->route('type') === 'normal' ? 'active' : '' }}">
    <i class="bi bi-file-earmark-richtext-fill" style="color:#c084fc;"></i> Jurnal Normal
</a>

{{-- Jurnal Fasttrack --}}
@feature('fasttrack')
<a href="{{ route('admin.pic-reviewer.submissions', 'fasttrack') }}"
   class="nav-link {{ $currentRoute === 'admin.pic-reviewer.submissions' && request()->route('type') === 'fasttrack' ? 'active' : '' }}">
    <i class="bi bi-lightning-charge-fill text-warning"></i> Jurnal Fasttrack
</a>
@endfeature

{{-- Jurnal BKD --}}
<a href="{{ route('admin.pic-reviewer.submissions', 'bkd') }}"
   class="nav-link {{ $currentRoute === 'admin.pic-reviewer.submissions' && request()->route('type') === 'bkd' ? 'active' : '' }}">
    <i class="bi bi-briefcase-fill" style="color:#38bdf8;"></i> Jurnal BKD
</a>

{{-- Jurnal JAFA --}}
<a href="{{ route('admin.pic-reviewer.submissions', 'jafa') }}"
   class="nav-link {{ $currentRoute === 'admin.pic-reviewer.submissions' && request()->route('type') === 'jafa' ? 'active' : '' }}">
    <i class="bi bi-folder-fill" style="color:#4ade80;"></i> Jurnal JAFA
</a>

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
