@extends('pic.layouts.app')

@section('title', 'Data Reviewer')
@section('page-title', 'Data Reviewer')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Search -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau institusi..." value="{{ $search ?? '' }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('pic.reviewers.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-person-check"></i> Daftar Reviewer</span>
        <span class="badge bg-primary">Total: {{ $reviewers->total() }} Reviewer</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No. HP</th>
                        <th>Institusi</th>
                        <th>Total Review</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviewers as $reviewer)
                    <tr>
                        <td>{{ $loop->iteration + ($reviewers->currentPage() - 1) * $reviewers->perPage() }}</td>
                        <td>
                            <strong>{{ $reviewer->name }}</strong>
                            @if($reviewer->specialization)
                                <br><small class="text-muted">{{ $reviewer->specialization }}</small>
                            @endif
                        </td>
                        <td>{{ $reviewer->email }}</td>
                        <td>
                            @if($reviewer->phone)
                                @php
                                    $phoneNumber = preg_replace('/[^0-9]/', '', $reviewer->phone);
                                    if (substr($phoneNumber, 0, 1) === '0') {
                                        $phoneNumber = '62' . substr($phoneNumber, 1);
                                    }
                                    if (substr($phoneNumber, 0, 2) !== '62') {
                                        $phoneNumber = '62' . $phoneNumber;
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $phoneNumber }}?text=Halo%20{{ urlencode($reviewer->name) }},%20" 
                                   target="_blank" 
                                   class="btn btn-sm btn-success" 
                                   title="Chat WhatsApp">
                                    <i class="bi bi-whatsapp"></i> {{ $reviewer->phone }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $reviewer->institution ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info">{{ $reviewer->review_assignments_count ?? 0 }} review</span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="mailto:{{ $reviewer->email }}" class="btn btn-sm btn-outline-primary" title="Kirim Email">
                                    <i class="bi bi-envelope"></i>
                                </a>
                                <form action="{{ route('pic.reviewers.login-as', $reviewer) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" title="Login sebagai {{ $reviewer->name }}" onclick="return confirm('Login sebagai {{ $reviewer->name }}?')">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-people" style="font-size: 2rem;"></i>
                            <p class="mb-0 mt-2">Belum ada data reviewer</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @include('partials.per-page-selector', ['paginator' => $reviewers])
    </div>
</div>
@endsection
