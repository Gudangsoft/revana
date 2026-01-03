@php
    $currentRoute = Route::currentRouteName();
@endphp

<a href="{{ route('admin.dashboard') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
{{-- <a href="{{ route('admin.journals.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.journals') ? 'active' : '' }}">
    <i class="bi bi-journal-text"></i> Jurnal
</a> --}}
{{-- <a href="{{ route('admin.accreditations.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.accreditations') ? 'active' : '' }}">
    <i class="bi bi-award"></i> Akreditasi
</a> --}}
<a href="{{ route('admin.assignments.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.assignments') ? 'active' : '' }}">
    <i class="bi bi-clipboard-check"></i> Penugasan Review
</a>
<a href="{{ route('admin.reviewers.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.reviewers') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Daftar Reviewer
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
<a href="{{ route('admin.pics.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.pics') ? 'active' : '' }}">
    <i class="bi bi-person-badge"></i> PIC
</a>
<a href="{{ route('admin.field-of-studies.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.field-of-studies') ? 'active' : '' }}">
    <i class="bi bi-book-fill"></i> Bidang Ilmu
</a>
<a href="{{ route('admin.certificates.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.certificates') ? 'active' : '' }}">
    <i class="bi bi-award-fill"></i> Kelola Sertifikat
</a>
<a href="{{ route('admin.users.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.users') ? 'active' : '' }}">
    <i class="bi bi-person-gear"></i> Pengelolaan Pengguna
</a>
<hr>
<a href="{{ route('admin.settings.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.settings') ? 'active' : '' }}">
    <i class="bi bi-gear-fill"></i> Setting Web
</a>