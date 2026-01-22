@extends('pic.layouts.app')

@section('title', 'Dashboard PIC Author')
@section('page-title', 'Dashboard PIC Author')

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

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-info-circle"></i> Selamat Datang, {{ auth()->guard('pic')->user()->name }}</h5>
                <p class="card-text">Anda login sebagai <strong>PIC Author</strong>. Gunakan dashboard ini untuk mengelola tugas dan monitoring artikel.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-speedometer2"></i> Menu Utama
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="{{ route('pic.submissions.monitoring') }}" class="text-decoration-none">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="bi bi-list-check text-primary" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Monitoring & Tugas</h5>
                                    <p class="card-text text-muted">Lihat dan kelola tugas yang ditugaskan</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('pic.journals.index') }}" class="text-decoration-none">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="bi bi-journal-text text-success" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Data Jurnal</h5>
                                    <p class="card-text text-muted">Kelola data jurnal</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('pic.points.index') }}" class="text-decoration-none">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <i class="bi bi-trophy-fill text-warning" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Point Saya</h5>
                                    <p class="card-text text-muted">Lihat perolehan point</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
