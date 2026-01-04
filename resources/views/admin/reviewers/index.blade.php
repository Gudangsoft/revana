@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Data Reviewer')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stats-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Reviewer</h6>
                        <h2 class="mb-0">{{ $reviewers->total() }}</h2>
                    </div>
                    <div class="text-primary" style="font-size: 2.5rem;">
                        <i class="bi bi-people"></i>
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
                        <h6 class="text-muted mb-2">Total Reviews</h6>
                        <h2 class="mb-0">{{ $reviewers->sum('completed_reviews') }}</h2>
                    </div>
                    <div class="text-success" style="font-size: 2.5rem;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Points</h6>
                        <h2 class="mb-0">{{ $reviewers->sum('total_points') }}</h2>
                    </div>
                    <div class="text-warning" style="font-size: 2.5rem;">
                        <i class="bi bi-coin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reviewer List -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people"></i> Daftar Reviewer</span>
                <div>
                    <span class="badge bg-primary">Total: {{ $reviewers->total() }} Reviewer</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form action="{{ route('admin.reviewers.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama, email, institusi, atau bidang ilmu..." value="{{ $search ?? '' }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('admin.reviewers.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Reset
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. HP / WhatsApp</th>
                                <th>Total Points</th>
                                <th>Available Points</th>
                                <th>Completed Reviews</th>
                                <th>Active Tasks</th>
                                <th>Badges</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviewers as $reviewer)
                            <tr>
                                <td>
                                    <strong>{{ $reviewer->name }}</strong>
                                </td>
                                <td>{{ $reviewer->email }}</td>
                                <td>
                                    @if($reviewer->phone)
                                        @php
                                            // Konversi nomor ke format internasional
                                            $phoneNumber = preg_replace('/[^0-9]/', '', $reviewer->phone);
                                            // Jika diawali 0, ganti dengan 62
                                            if (substr($phoneNumber, 0, 1) === '0') {
                                                $phoneNumber = '62' . substr($phoneNumber, 1);
                                            }
                                            // Jika belum ada kode negara, tambahkan 62
                                            if (substr($phoneNumber, 0, 2) !== '62') {
                                                $phoneNumber = '62' . $phoneNumber;
                                            }
                                        @endphp
                                        <a href="https://wa.me/{{ $phoneNumber }}?text=Halo%20{{ urlencode($reviewer->name) }},%20" 
                                           target="_blank" 
                                           class="btn btn-sm wa-button-compact" 
                                           title="Chat WhatsApp dengan {{ $reviewer->name }}">
                                            <i class="bi bi-whatsapp"></i> {{ $reviewer->phone }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $reviewer->total_points }} pts</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ $reviewer->available_points }} pts</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $reviewer->completed_reviews }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $reviewer->review_assignments_count }}</span>
                                </td>
                                <td>
                                    @foreach($reviewer->badges as $badge)
                                    <span class="badge bg-secondary" title="{{ $badge->description }}">
                                        {{ $badge->icon }}
                                    </span>
                                    @endforeach
                                    @if($reviewer->badges->count() == 0)
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.reviewers.show', $reviewer) }}" class="btn btn-sm btn-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.reviewers.edit', $reviewer) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada data reviewer</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($reviewers->hasPages())
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        @foreach(range(1, $reviewers->lastPage()) as $page)
                            <li class="page-item {{ $page == $reviewers->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $reviewers->url($page) }}">{{ $page }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .wa-button-compact {
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        border: none;
        color: white !important;
        font-weight: 500;
        transition: all 0.3s ease;
        padding: 0.4rem 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        text-decoration: none;
        border-radius: 6px;
        font-size: 0.875rem;
        white-space: nowrap;
    }
    
    .wa-button-compact:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
        background: linear-gradient(135deg, #128C7E 0%, #075E54 100%);
        color: white !important;
    }
    
    .wa-button-compact i {
        font-size: 1.1rem;
    }
    
    .table td {
        vertical-align: middle;
    }
</style>
@endsection

