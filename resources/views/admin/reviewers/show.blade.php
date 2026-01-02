@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Detail Reviewer')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<!-- Back Button & Actions -->
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.reviewers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <a href="{{ route('admin.reviewers.edit', $reviewer) }}" class="btn btn-primary">
        <i class="bi bi-pencil-square"></i> Edit Data Reviewer
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Profile Card -->
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @if($reviewer->photo)
                    <img src="{{ asset('storage/' . $reviewer->photo) }}" 
                         alt="Profile Photo" 
                         class="rounded-circle mb-3"
                         style="width: 150px; height: 150px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3"
                         style="width: 150px; height: 150px; font-size: 3rem;">
                        {{ strtoupper(substr($reviewer->name, 0, 1)) }}
                    </div>
                @endif
                <h4>{{ $reviewer->name }}</h4>
                <p class="text-muted">{{ $reviewer->email }}</p>
                
                @if($reviewer->institution)
                <div class="mb-2">
                    <small class="text-muted"><i class="bi bi-building"></i> {{ $reviewer->institution }}</small>
                </div>
                @endif
                
                @if($reviewer->position)
                <div class="mb-2">
                    <small class="text-muted"><i class="bi bi-briefcase"></i> {{ $reviewer->position }}</small>
                </div>
                @endif
                
                @if($reviewer->address)
                <div class="mb-2">
                    <small class="text-muted"><i class="bi bi-geo-alt"></i> {{ $reviewer->address }}</small>
                </div>
                @endif
                
                @if($reviewer->education_level)
                <div class="mb-2">
                    <span class="badge bg-primary">{{ $reviewer->education_level }}</span>
                </div>
                @endif
                
                @if($reviewer->phone)
                <div class="mt-3">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $reviewer->phone) }}" 
                       class="btn btn-success btn-sm" target="_blank">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Academic Info -->
        @if($reviewer->nidn || $reviewer->specialization)
        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <i class="bi bi-mortarboard-fill"></i> Informasi Akademik
            </div>
            <div class="card-body">
                @if($reviewer->nidn)
                <div class="mb-2">
                    <small class="text-muted">NIDN:</small>
                    <div><strong>{{ $reviewer->nidn }}</strong></div>
                </div>
                @endif
                
                @if($reviewer->specialization)
                <div class="mb-2">
                    <small class="text-muted">Spesialisasi:</small>
                    <div>{{ $reviewer->specialization }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Research Links -->
        @if($reviewer->google_scholar || $reviewer->sinta_id || $reviewer->scopus_id)
        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-link-45deg"></i> Profil Riset
            </div>
            <div class="card-body">
                @if($reviewer->google_scholar)
                <div class="mb-2">
                    <a href="{{ $reviewer->google_scholar }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-google"></i> Google Scholar
                    </a>
                </div>
                @endif
                
                @if($reviewer->sinta_id)
                <div class="mb-2">
                    <small class="text-muted">SINTA ID:</small>
                    <div><strong>{{ $reviewer->sinta_id }}</strong></div>
                </div>
                @endif
                
                @if($reviewer->scopus_id)
                <div class="mb-2">
                    <small class="text-muted">Scopus ID:</small>
                    <div><strong>{{ $reviewer->scopus_id }}</strong></div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Bio -->
        @if($reviewer->bio)
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-file-text"></i> Bio
            </div>
            <div class="card-body">
                <p class="small mb-0">{{ $reviewer->bio }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <!-- Stats Card -->
        <div class="card mb-3" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important; border: none;">
            <div class="card-body" style="color: white !important;">
                <h5 style="color: white !important;"><i class="bi bi-bar-chart-fill"></i> Statistik Performance</h5>
                <div class="d-flex gap-3 mt-3 flex-wrap">
                    <div>
                        <h4 class="mb-0" style="color: white !important;">{{ $reviewer->total_points }}</h4>
                        <small style="color: rgba(255,255,255,0.8) !important;">Total Points</small>
                    </div>
                    <div>
                        <h4 class="mb-0" style="color: white !important;">{{ $reviewer->available_points }}</h4>
                        <small style="color: rgba(255,255,255,0.8) !important;">Available Points</small>
                    </div>
                    <div>
                        <h4 class="mb-0" style="color: white !important;">{{ $reviewer->completed_reviews }}</h4>
                        <small style="color: rgba(255,255,255,0.8) !important;">Completed Reviews</small>
                    </div>
                </div>
                <div class="mt-3">
                    @foreach($reviewer->badges as $badge)
                    <span class="badge bg-warning text-dark me-1" style="font-size: 1.1rem;" title="{{ $badge->description }}">
                        {{ $badge->icon }} {{ $badge->name }}
                    </span>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="text-warning">Pending</h5>
                        <h2>{{ $reviewer->reviewAssignments->where('status', 'PENDING')->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="text-info">In Progress</h5>
                        <h2>{{ $reviewer->reviewAssignments->whereIn('status', ['ACCEPTED', 'ON_PROGRESS', 'SUBMITTED'])->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="text-success">Completed</h5>
                        <h2>{{ $reviewer->reviewAssignments->where('status', 'APPROVED')->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="text-danger">Rejected</h5>
                        <h2>{{ $reviewer->reviewAssignments->where('status', 'REJECTED')->count() }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clipboard-check"></i> Riwayat Review Assignments
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
                                <th>Tanggal Assignment</th>
                                <th>Tanggal Selesai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviewer->reviewAssignments as $assignment)
                            <tr>
                                <td>
                                    <strong>{{ Str::limit($assignment->journal->title, 50) }}</strong>
                                    @if($assignment->reviewResult)
                                        <br><small class="text-muted"><i class="bi bi-file-text"></i> Review submitted</small>
                                    @endif
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
                                <td>{{ $assignment->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    @if($assignment->approved_at)
                                        {{ $assignment->approved_at->format('d M Y H:i') }}
                                    @elseif($assignment->submitted_at)
                                        {{ $assignment->submitted_at->format('d M Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.assignments.show', $assignment) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada review assignment</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Point History -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Riwayat Points
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th>Deskripsi</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviewer->pointHistories()->latest()->take(20)->get() as $history)
                            <tr>
                                <td>{{ $history->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    @if($history->type == 'EARNED')
                                        <span class="badge bg-success">Earned</span>
                                    @else
                                        <span class="badge bg-danger">Redeemed</span>
                                    @endif
                                </td>
                                <td>{{ $history->description }}</td>
                                <td>
                                    @if($history->type == 'EARNED')
                                        <span class="text-success">+{{ $history->points }}</span>
                                    @else
                                        <span class="text-danger">-{{ $history->points }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada riwayat points</td>
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

