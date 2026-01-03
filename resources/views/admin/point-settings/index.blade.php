@extends('layouts.app')

@section('title', 'Pengaturan Point & Reward')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="bi bi-coin"></i> Pengaturan Point & Reward
            </h1>
            <p class="text-muted">Kelola nilai point dan reward untuk reviewer</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-gear"></i> Konfigurasi Point
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.point-settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-currency-dollar"></i> Nilai 1 Point (dalam Rupiah)
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" class="form-control @error('point_value') is-invalid @enderror" 
                                       name="point_value" 
                                       value="{{ old('point_value', $settings['point_value'] ?? 1000) }}"
                                       placeholder="1000" min="100" step="100" required>
                                @error('point_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Berapa rupiah nilai dari 1 point? (Contoh: 1000 = 1 point = Rp 1.000)
                            </small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-star"></i> Jumlah Point per Review Artikel
                            </label>
                            <div class="input-group input-group-lg">
                                <input type="number" class="form-control @error('points_per_review') is-invalid @enderror" 
                                       name="points_per_review" 
                                       value="{{ old('points_per_review', $settings['points_per_review'] ?? 5) }}"
                                       placeholder="5" min="1" required>
                                <span class="input-group-text bg-light">Point</span>
                                @error('points_per_review')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Berapa point yang didapat reviewer setelah menyelesaikan 1 artikel review?
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="bi bi-calculator"></i> Contoh Perhitungan
                            </h6>
                            <hr>
                            <ul class="mb-0">
                                <li><strong>1 Point</strong> = Rp {{ number_format($settings['point_value'] ?? 1000, 0, ',', '.') }}</li>
                                <li><strong>1 Review</strong> = {{ $settings['points_per_review'] ?? 5 }} Point</li>
                                <li class="text-primary fw-bold mt-2">
                                    <i class="bi bi-arrow-right"></i> 
                                    Maka 1 Review = Rp {{ number_format(($settings['point_value'] ?? 1000) * ($settings['points_per_review'] ?? 5), 0, ',', '.') }}
                                </li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-lightbulb"></i> Panduan
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Cara Kerja Point:</h6>
                    <ol class="small">
                        <li class="mb-2">Reviewer mendapatkan point setelah menyelesaikan review artikel</li>
                        <li class="mb-2">Point dapat ditukar dengan reward yang tersedia</li>
                        <li class="mb-2">Nilai point dalam rupiah menentukan nilai tukar reward</li>
                    </ol>
                    
                    <hr>
                    
                    <h6 class="fw-bold">Tips Pengaturan:</h6>
                    <ul class="small mb-0">
                        <li class="mb-2">Set nilai point minimal Rp 100</li>
                        <li class="mb-2">Sesuaikan jumlah point per review dengan tingkat kesulitan</li>
                        <li class="mb-2">Review nilai secara berkala untuk memotivasi reviewer</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm border-warning mt-3">
                <div class="card-header bg-warning">
                    <h6 class="mb-0">
                        <i class="bi bi-exclamation-triangle"></i> Perhatian
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-0">
                        Perubahan pengaturan point akan mempengaruhi perhitungan reward untuk review yang akan datang. 
                        Point yang sudah diberikan sebelumnya tidak akan berubah.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
