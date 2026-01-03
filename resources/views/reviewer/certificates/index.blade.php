@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Sertifikat Saya')

@section('sidebar')
    <a href="{{ route('reviewer.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('reviewer.tasks.index') }}" class="nav-link">
        <i class="bi bi-clipboard-check"></i> My Tasks
    </a>
    <a href="{{ route('reviewer.certificates.index') }}" class="nav-link active">
        <i class="bi bi-award-fill"></i> Sertifikat
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
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-award-fill"></i> Daftar Sertifikat
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($assignments->isEmpty())
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Anda belum memiliki review yang disetujui. Selesaikan review Anda untuk mendapatkan sertifikat.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Artikel</th>
                                    <th>Nomor Artikel</th>
                                    <th>Bahasa</th>
                                    <th>Tanggal Approved</th>
                                    <th>Posisi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignments as $assignment)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ Str::limit($assignment->article_title, 50) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $assignment->article_number }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $assignment->language }}</span>
                                    </td>
                                    <td>{{ $assignment->approved_at ? $assignment->approved_at->format('d M Y') : '-' }}</td>
                                    <td>
                                        @if($assignment->reviewer_id == auth()->id())
                                            <span class="badge bg-info">Reviewer 1</span>
                                        @elseif($assignment->reviewer2_id == auth()->id())
                                            <span class="badge bg-info">Reviewer 2</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('reviewer.certificates.download', $assignment) }}" class="btn btn-success btn-sm">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($templates->isNotEmpty())
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Informasi
            </div>
            <div class="card-body">
                <h6>Template Sertifikat yang Tersedia:</h6>
                <div class="row">
                    @foreach($templates as $template)
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <img src="{{ asset('storage/' . $template->file_path) }}" 
                                 class="card-img-top" 
                                 alt="{{ $template->name }}"
                                 style="max-height: 200px; object-fit: contain;">
                            <div class="card-body">
                                <h6 class="card-title">{{ $template->name }}</h6>
                                @if($template->description)
                                <p class="card-text small">{{ $template->description }}</p>
                                @endif
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
@endsection
