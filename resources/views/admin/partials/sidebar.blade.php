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
    <i class="bi bi-clipboard-check"></i> Review Assignments
</a>
<a href="{{ route('admin.reviewers.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.reviewers') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Reviewers
</a>
<a href="{{ route('admin.reviewer-registrations.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.reviewer-registrations') ? 'active' : '' }}">
    <i class="bi bi-person-plus-fill"></i> Pendaftaran Reviewer
</a>
<a href="{{ route('admin.leaderboard.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.leaderboard') ? 'active' : '' }}">
    <i class="bi bi-trophy-fill"></i> Leaderboard
</a>
<a href="{{ route('admin.redemptions.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.redemptions') ? 'active' : '' }}">
    <i class="bi bi-gift"></i> Reward Redemptions
</a>
<a href="{{ route('admin.points.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.points') ? 'active' : '' }}">
    <i class="bi bi-coin"></i> Point Management
</a>
<a href="{{ route('admin.rewards.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.rewards') ? 'active' : '' }}">
    <i class="bi bi-trophy"></i> Reward Management
</a>
<a href="{{ route('admin.marketings.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.marketings') ? 'active' : '' }}">
    <i class="bi bi-megaphone"></i> Marketing
</a>
<a href="{{ route('admin.pics.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.pics') ? 'active' : '' }}">
    <i class="bi bi-person-badge"></i> PIC
</a>
<hr>

<a href="{{ route('admin.settings.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.settings') ? 'active' : '' }}">
    <i class="bi bi-gear-fill"></i> Setting Web
</a>