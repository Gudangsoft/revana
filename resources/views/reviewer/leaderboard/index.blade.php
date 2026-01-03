@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', '🏆 Leaderboard Reviewer')

@section('sidebar')
    <a href="{{ route('reviewer.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('reviewer.tasks.index') }}" class="nav-link">
        <i class="bi bi-clipboard-check"></i> My Tasks
    </a>
    <a href="{{ route('reviewer.certificates.index') }}" class="nav-link">
        <i class="bi bi-award-fill"></i> Sertifikat
    </a>
    <a href="{{ route('reviewer.rewards.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Rewards
    </a>
    <a href="{{ route('reviewer.leaderboard.index') }}" class="nav-link active">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="{{ route('reviewer.profile.edit') }}" class="nav-link">
        <i class="bi bi-person-circle"></i> My Profile
    </a>
@endsection

@section('content')
<!-- My Rank Card -->
@if($myRank)
<div class="card mb-4" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;">
    <div class="card-body text-white">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="text-white mb-3">
                    <i class="bi bi-star-fill"></i> Peringkat Saya
                </h4>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div>
                        @if($myRank->rank == 1)
                            <span class="badge" style="background: linear-gradient(135deg, #ffd700, #ffed4e); color: #000; font-size: 1.5rem; padding: 0.75rem 1rem;">
                                🥇 Rank #{{ $myRank->rank }}
                            </span>
                        @elseif($myRank->rank == 2)
                            <span class="badge" style="background: linear-gradient(135deg, #c0c0c0, #e8e8e8); color: #000; font-size: 1.5rem; padding: 0.75rem 1rem;">
                                🥈 Rank #{{ $myRank->rank }}
                            </span>
                        @elseif($myRank->rank == 3)
                            <span class="badge" style="background: linear-gradient(135deg, #cd7f32, #b87333); color: #fff; font-size: 1.5rem; padding: 0.75rem 1rem;">
                                🥉 Rank #{{ $myRank->rank }}
                            </span>
                        @else
                            <span class="badge bg-light text-dark" style="font-size: 1.5rem; padding: 0.75rem 1rem;">
                                Rank #{{ $myRank->rank }}
                            </span>
                        @endif
                    </div>
                    <div class="text-white">
                        <div><strong>{{ $myRank->name }}</strong></div>
                        <small style="opacity: 0.9;">{{ $myRank->email }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="p-2 bg-white bg-opacity-25 rounded">
                            <h5 class="mb-0 text-white">{{ $myRank->total_redemptions }}</h5>
                            <small style="opacity: 0.9;">Total Rewards</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-white bg-opacity-25 rounded">
                            <h5 class="mb-0 text-white">{{ $myRank->total_reviews }}</h5>
                            <small style="opacity: 0.9;">Reviews</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-white bg-opacity-25 rounded">
                            <small style="opacity: 0.9;">💎 {{ $myRank->platinum_count }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-white bg-opacity-25 rounded">
                            <small style="opacity: 0.9;">🥇 {{ $myRank->gold_count }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size: 2rem;">🏆</div>
                <h6 class="text-muted mb-1">Total Reviewer</h6>
                <h3 class="mb-0">{{ $reviewers->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size: 2rem;">💎</div>
                <h6 class="text-muted mb-1">Platinum Rewards</h6>
                <h3 class="mb-0">{{ $reviewers->sum('platinum_count') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size: 2rem;">🥇</div>
                <h6 class="text-muted mb-1">Gold Rewards</h6>
                <h3 class="mb-0">{{ $reviewers->sum('gold_count') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size: 2rem;">📊</div>
                <h6 class="text-muted mb-1">Total Reviews</h6>
                <h3 class="mb-0">{{ $reviewers->sum('total_reviews') }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Top 3 Highlight -->
<div class="card mb-4">
    <div class="card-header bg-warning">
        <i class="bi bi-trophy-fill"></i> Top 3 Performers
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($reviewers->take(3) as $index => $top)
            <div class="col-md-4">
                <div class="card h-100 {{ $top->id == auth()->id() ? 'border-primary border-3' : '' }}">
                    <div class="card-body text-center">
                        @if($index == 0)
                            <div style="font-size: 3rem;">🥇</div>
                        @elseif($index == 1)
                            <div style="font-size: 3rem;">🥈</div>
                        @else
                            <div style="font-size: 3rem;">🥉</div>
                        @endif
                        
                        @if($top->photo)
                            <img src="{{ asset('storage/' . $top->photo) }}" 
                                 class="rounded-circle mt-2" 
                                 style="width: 80px; height: 80px; object-fit: cover;"
                                 alt="{{ $top->name }}">
                        @else
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mt-2"
                                 style="width: 80px; height: 80px; font-size: 2rem;">
                                {{ strtoupper(substr($top->name, 0, 1)) }}
                            </div>
                        @endif
                        
                        <h5 class="mt-3 mb-1">{{ $top->name }}</h5>
                        @if($top->institution)
                        <small class="text-muted d-block mb-2">{{ $top->institution }}</small>
                        @endif
                        
                        <div class="d-flex justify-content-center gap-2 mb-2">
                            @if($top->platinum_count > 0)
                                <span class="badge bg-primary">💎 {{ $top->platinum_count }}</span>
                            @endif
                            @if($top->gold_count > 0)
                                <span class="badge bg-warning">🥇 {{ $top->gold_count }}</span>
                            @endif
                        </div>
                        
                        <div class="small">
                            <div><strong>{{ $top->total_redemptions }}</strong> total rewards</div>
                            <div><strong>{{ $top->total_reviews }}</strong> reviews</div>
                        </div>
                        
                        @if($top->id == auth()->id())
                        <div class="mt-2">
                            <span class="badge bg-primary">Itu Saya!</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Full Leaderboard -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-ol"></i> Semua Peringkat</span>
        <span class="badge bg-info">Diurutkan berdasarkan tier reward</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="80">Rank</th>
                        <th>Reviewer</th>
                        <th class="text-center hide-mobile">Reviews</th>
                        <th class="text-center">💎</th>
                        <th class="text-center">🥇</th>
                        <th class="text-center hide-mobile">🥈</th>
                        <th class="text-center hide-mobile">🥉</th>
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reviewers as $reviewer)
                    <tr class="{{ $reviewer->id == auth()->id() ? 'table-primary' : '' }}">
                        <td>
                            @if($reviewer->rank == 1)
                                <span class="badge" style="background: linear-gradient(135deg, #ffd700, #ffed4e); color: #000; font-size: 1rem; padding: 0.5rem 0.75rem;">
                                    🥇 #1
                                </span>
                            @elseif($reviewer->rank == 2)
                                <span class="badge" style="background: linear-gradient(135deg, #c0c0c0, #e8e8e8); color: #000; font-size: 1rem; padding: 0.5rem 0.75rem;">
                                    🥈 #2
                                </span>
                            @elseif($reviewer->rank == 3)
                                <span class="badge" style="background: linear-gradient(135deg, #cd7f32, #b87333); color: #fff; font-size: 1rem; padding: 0.5rem 0.75rem;">
                                    🥉 #3
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    #{{ $reviewer->rank }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($reviewer->photo)
                                    <img src="{{ asset('storage/' . $reviewer->photo) }}" 
                                         class="rounded-circle" 
                                         style="width: 40px; height: 40px; object-fit: cover;"
                                         alt="{{ $reviewer->name }}">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px; font-size: 1rem;">
                                        {{ strtoupper(substr($reviewer->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold">
                                        {{ $reviewer->name }}
                                        @if($reviewer->id == auth()->id())
                                            <span class="badge bg-primary ms-1">Saya</span>
                                        @endif
                                    </div>
                                    @if($reviewer->institution)
                                    <small class="text-muted d-none d-md-block">{{ Str::limit($reviewer->institution, 30) }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center hide-mobile">
                            <span class="badge bg-info">{{ $reviewer->total_reviews }}</span>
                        </td>
                        <td class="text-center">
                            @if($reviewer->platinum_count > 0)
                                <span class="badge" style="background: linear-gradient(135deg, #b7a1d8, #7c3aed);">
                                    {{ $reviewer->platinum_count }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($reviewer->gold_count > 0)
                                <span class="badge" style="background: linear-gradient(135deg, #fcd34d, #f59e0b);">
                                    {{ $reviewer->gold_count }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center hide-mobile">
                            @if($reviewer->silver_count > 0)
                                <span class="badge" style="background: linear-gradient(135deg, #cbd5e1, #64748b);">
                                    {{ $reviewer->silver_count }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center hide-mobile">
                            @if($reviewer->bronze_count > 0)
                                <span class="badge" style="background: linear-gradient(135deg, #d97706, #92400e); color: white;">
                                    {{ $reviewer->bronze_count }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success">
                                {{ $reviewer->total_redemptions }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Info Box -->
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <i class="bi bi-info-circle"></i> Cara Meningkatkan Peringkat
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6><i class="bi bi-trophy"></i> Sistem Poin Tier:</h6>
                <ul>
                    <li>💎 <strong>Platinum</strong> = 1,000 poin</li>
                    <li>🥇 <strong>Gold</strong> = 100 poin</li>
                    <li>🥈 <strong>Silver</strong> = 10 poin</li>
                    <li>🥉 <strong>Bronze</strong> = 1 poin</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6><i class="bi bi-lightbulb"></i> Tips Naik Peringkat:</h6>
                <ul>
                    <li>Selesaikan review dengan berkualitas untuk dapat poin</li>
                    <li>Kumpulkan poin dan tukar dengan reward tier tinggi</li>
                    <li>Fokus pada Platinum & Gold untuk naik peringkat cepat</li>
                    <li>Konsisten mengerjakan review yang diberikan</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
