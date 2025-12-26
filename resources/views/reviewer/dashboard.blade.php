@extends('layouts.app')

@section('title', 'Reviewer Dashboard - REVANA')
@section('page-title', 'Dashboard Reviewer')

@section('sidebar')
    <a href="{{ route('reviewer.dashboard') }}" class="nav-link active">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('reviewer.tasks.index') }}" class="nav-link">
        <i class="bi bi-clipboard-check"></i> My Tasks
    </a>
    <a href="{{ route('reviewer.rewards.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Rewards
    </a>
    <a href="{{ route('reviewer.leaderboard.index') }}" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="{{ route('reviewer.profile.edit') }}" class="nav-link">
        <i class="bi bi-person-circle"></i> My Profile
    </a>
@endsection

@section('content')
<!-- Notification Alert for New Tasks -->
@if($pendingTasks > 0)
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-bell-fill me-2" style="font-size: 1.5rem;"></i>
        <div>
            <strong>Ada Tugas Baru!</strong>
            <br>
            Anda memiliki <strong>{{ $pendingTasks }}</strong> tugas review yang menunggu untuk dikerjakan.
            <a href="{{ route('reviewer.tasks.index') }}" class="alert-link">Lihat Tugas</a>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Profile Card -->
<div class="row">
    <div class="col-md-12">
        <div class="card text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important; border: none;">
            <div class="card-body" style="color: white !important;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 style="color: white !important;"><i class="bi bi-person-circle"></i> {{ $user->name }}</h3>
                        <p class="mb-2" style="color: rgba(255,255,255,0.9) !important;">{{ $user->email }}</p>
                        <div class="d-flex gap-3 mt-3">
                            <div>
                                <h4 class="mb-0" style="color: white !important;">{{ $user->total_points }}</h4>
                                <small style="color: rgba(255,255,255,0.8) !important;">Total Points</small>
                            </div>
                            <div>
                                <h4 class="mb-0" style="color: white !important;">{{ $user->available_points }}</h4>
                                <small style="color: rgba(255,255,255,0.8) !important;">Available Points</small>
                            </div>
                            <div>
                                <h4 class="mb-0" style="color: white !important;">{{ $user->completed_reviews }}</h4>
                                <small style="color: rgba(255,255,255,0.8) !important;">Completed Reviews</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        @foreach($user->badges as $badge)
                        <span class="badge bg-warning text-dark me-1" style="font-size: 1.2rem;" title="{{ $badge->description }}">
                            {{ $badge->icon }} {{ $badge->name }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Pending Tasks</h6>
                        <h2 class="mb-0">{{ $pendingTasks }}</h2>
                    </div>
                    <div class="text-warning" style="font-size: 2.5rem;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stats-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Active Tasks</h6>
                        <h2 class="mb-0">{{ $activeTasks }}</h2>
                    </div>
                    <div class="text-primary" style="font-size: 2.5rem;">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Completed</h6>
                        <h2 class="mb-0">{{ $completedTasks }}</h2>
                    </div>
                    <div class="text-success" style="font-size: 2.5rem;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row mt-4">
    <!-- Reviews Per Month Chart -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-bar-chart-line"></i> Grafik Review per Bulan (6 Bulan Terakhir)
            </div>
            <div class="card-body">
                <canvas id="reviewsChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- Status Distribution Chart -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-pie-chart"></i> Distribusi Status Review
            </div>
            <div class="card-body">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Points Chart -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-graph-up"></i> Grafik Points (6 Bulan Terakhir)
            </div>
            <div class="card-body">
                <canvas id="pointsChart" height="60"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Available Rewards -->
@if($availableRewards->count() > 0)
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-gift"></i> Rewards yang Bisa Ditukar
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($availableRewards->take(3) as $reward)
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h5>{{ $reward->name }}</h5>
                                <p class="text-muted">{{ $reward->description }}</p>
                                <h4 class="text-success">{{ $reward->points_required }} Points</h4>
                                <a href="{{ route('reviewer.rewards.index') }}" class="btn btn-success btn-sm">
                                    Tukar Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Recent Tasks -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history"></i> Tugas Terbaru</span>
                <a href="{{ route('reviewer.tasks.index') }}" class="btn btn-sm btn-outline-primary">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Jurnal</th>
                                <th>Akreditasi</th>
                                <th>Points</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAssignments as $assignment)
                            <tr>
                                <td>
                                    <strong>{{ Str::limit($assignment->journal->title, 50) }}</strong>
                                </td>
                                <td>{{ $assignment->journal->accreditation }}</td>
                                <td><span class="badge bg-info">{{ $assignment->journal->points }} pts</span></td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'PENDING' => 'warning',
                                            'ACCEPTED' => 'info',
                                            'REJECTED' => 'danger',
                                            'ON_PROGRESS' => 'primary',
                                            'SUBMITTED' => 'success',
                                            'APPROVED' => 'success',
                                            'REVISION' => 'secondary'
                                        ];
                                        $color = $statusColors[$assignment->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $assignment->status }}</span>
                                </td>
                                <td>{{ $assignment->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('reviewer.tasks.show', $assignment) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada tugas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Reviews Per Month Chart
const reviewsCtx = document.getElementById('reviewsChart').getContext('2d');
const reviewsChart = new Chart(reviewsCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            label: 'Reviews Completed',
            data: {!! json_encode($chartData) !!},
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            borderColor: 'rgba(79, 70, 229, 1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgba(79, 70, 229, 1)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 1
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    callback: function(value) {
                        return value + ' reviews';
                    }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusData = {!! json_encode($statusDistribution) !!};

const statusLabels = statusData.map(item => item.status);
const statusCounts = statusData.map(item => item.count);

const statusColors = {
    'PENDING': '#f59e0b',
    'ACCEPTED': '#3b82f6',
    'REJECTED': '#ef4444',
    'ON_PROGRESS': '#8b5cf6',
    'SUBMITTED': '#06b6d4',
    'APPROVED': '#10b981',
    'REVISION': '#6b7280'
};

const backgroundColors = statusLabels.map(status => statusColors[status] || '#6b7280');

const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusCounts,
            backgroundColor: backgroundColors,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 11
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// Points Chart
const pointsCtx = document.getElementById('pointsChart').getContext('2d');
const pointsChart = new Chart(pointsCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [
            {
                label: 'Points Earned',
                data: {!! json_encode($pointsEarned) !!},
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 1
            },
            {
                label: 'Points Spent',
                data: {!! json_encode($pointsSpent) !!},
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: 'rgba(239, 68, 68, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y + ' points';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + ' pts';
                    }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>
@endpush
