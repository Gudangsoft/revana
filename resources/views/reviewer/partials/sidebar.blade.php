@php
    $currentRoute = Route::currentRouteName();
@endphp

<a href="{{ route('reviewer.dashboard') }}" class="nav-link {{ str_starts_with($currentRoute, 'reviewer.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
<a href="{{ route('reviewer.tasks.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'reviewer.tasks') ? 'active' : '' }}">
    <i class="bi bi-clipboard-check"></i> My Tasks
</a>
<a href="{{ route('reviewer.review-requests.my-requests') }}" class="nav-link {{ str_starts_with($currentRoute, 'reviewer.review-requests') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-text"></i> Permintaan Review
</a>
<a href="{{ route('reviewer.certificates.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'reviewer.certificates') ? 'active' : '' }}">
    <i class="bi bi-award-fill"></i> Sertifikat
</a>
{{-- <a href="{{ route('reviewer.rewards.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'reviewer.rewards') ? 'active' : '' }}">
    <i class="bi bi-gift"></i> Rewards
</a> --}}
<a href="{{ route('reviewer.leaderboard.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'reviewer.leaderboard') ? 'active' : '' }}">
    <i class="bi bi-trophy-fill"></i> Leaderboard
</a>
<a href="{{ route('reviewer.profile.edit') }}" class="nav-link {{ str_starts_with($currentRoute, 'reviewer.profile') ? 'active' : '' }}">
    <i class="bi bi-person-circle"></i> My Profile
</a>
