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
                <i class="bi bi-coin"></i> Pengaturan Point Review
            </h1>
            <p class="text-muted">Atur berapa point yang didapat reviewer per review</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistik Simple -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h2 class="mb-0">{{ number_format($stats['total_points_earned']) }}</h2>
                    <small>Total Point Diberikan</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h2 class="mb-0">{{ $stats['active_reviewers'] }}</h2>
                    <small>Reviewer Aktif</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h2 class="mb-0">{{ number_format($stats['total_points_spent']) }}</h2>
                    <small>Point Sudah Ditukar</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h2 class="mb-0">{{ number_format($stats['total_points_earned'] - $stats['total_points_spent']) }}</h2>
                    <small>Point Sisa Reviewer</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Form Pengaturan -->
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-sliders"></i> Pengaturan Utama
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.point-settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Setting 1: Nilai Point -->
                        <div class="card mb-3 border-0 bg-light">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-1-circle-fill"></i> Nilai 1 Point = Berapa Rupiah?
                                </h6>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" 
                                           class="form-control @error('point_value') is-invalid @enderror" 
                                           name="point_value" 
                                           value="{{ old('point_value', $settings['point_value'] ?? 1000) }}"
                                           min="100" step="100" required>
                                    @error('point_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="alert alert-info mt-3 mb-0">
                                    <small>
                                        <strong>Contoh:</strong> Jika diisi <code>1000</code>, artinya:<br>
                                        → 1 point = Rp 1.000<br>
                                        → 10 point = Rp 10.000<br>
                                        → 50 point = Rp 50.000
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Setting 2: Point per Review -->
                        <div class="card mb-3 border-0 bg-light">
                            <div class="card-body">
                                <h6 class="fw-bold text-success mb-3">
                                    <i class="bi bi-2-circle-fill"></i> Berapa Point per 1 Review Artikel?
                                </h6>
                                <div class="input-group input-group-lg">
                                    <input type="number" 
                                           class="form-control @error('points_per_review') is-invalid @enderror" 
                                           name="points_per_review" 
                                           value="{{ old('points_per_review', $settings['points_per_review'] ?? 5) }}"
                                           min="1" required>
                                    <span class="input-group-text">Point</span>
                                    @error('points_per_review')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="alert alert-success mt-3 mb-0">
                                    <small>
                                        <strong>Contoh:</strong> Jika diisi <code>5</code>, artinya:<br>
                                        → Reviewer selesai 1 review = dapat <strong>5 point</strong><br>
                                        → Selesai 2 review = dapat <strong>10 point</strong><br>
                                        → Selesai 10 review = dapat <strong>50 point</strong>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Hasil Perhitungan -->
                        <div class="card border-warning bg-warning bg-opacity-10">
                            <div class="card-body">
                                <h5 class="fw-bold mb-3">
                                    <i class="bi bi-calculator-fill text-warning"></i> Hasil Perhitungan
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded">
                                            <div>
                                                <strong>1 Review Selesai</strong>
                                            </div>
                                            <div class="text-end">
                                                <h4 class="mb-0 text-primary">
                                                    {{ $settings['points_per_review'] ?? 5 }} Point
                                                </h4>
                                                <small class="text-success fw-bold">
                                                    = Rp {{ number_format(($settings['point_value'] ?? 1000) * ($settings['points_per_review'] ?? 5), 0, ',', '.') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-center p-3 bg-white rounded">
                                            <small class="text-muted d-block">5 Review</small>
                                            <strong class="text-primary">{{ ($settings['points_per_review'] ?? 5) * 5 }} Point</strong><br>
                                            <small class="text-success">Rp {{ number_format(($settings['point_value'] ?? 1000) * ($settings['points_per_review'] ?? 5) * 5, 0, ',', '.') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-center p-3 bg-white rounded">
                                            <small class="text-muted d-block">10 Review</small>
                                            <strong class="text-primary">{{ ($settings['points_per_review'] ?? 5) * 10 }} Point</strong><br>
                                            <small class="text-success">Rp {{ number_format(($settings['point_value'] ?? 1000) * ($settings['points_per_review'] ?? 5) * 10, 0, ',', '.') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kriteria Point Tambahan (Opsional) -->
                        <div class="card border-info mt-3">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="bi bi-plus-circle"></i> Kriteria Point Tambahan (Opsional)
                                </h6>
                                <button type="button" class="btn btn-sm btn-light" onclick="addCriteria()">
                                    <i class="bi bi-plus-lg"></i> Tambah Kriteria
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="additionalCriteria">
                                    @if(isset($settings['points_bonus_fast']) && $settings['points_bonus_fast'] > 0)
                                    <div class="criteria-item mb-3 p-3 bg-light rounded">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="criteria_name[]" value="Bonus Review Cepat" placeholder="Nama kriteria">
                                            </div>
                                            <div class="col-md-4">
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="criteria_points[]" value="{{ $settings['points_bonus_fast'] }}" placeholder="Point" min="1">
                                                    <span class="input-group-text">Point</span>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeCriteria(this)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @if(isset($settings['points_bonus_quality']) && $settings['points_bonus_quality'] > 0)
                                    <div class="criteria-item mb-3 p-3 bg-light rounded">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="criteria_name[]" value="Bonus Review Berkualitas" placeholder="Nama kriteria">
                                            </div>
                                            <div class="col-md-4">
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="criteria_points[]" value="{{ $settings['points_bonus_quality'] }}" placeholder="Point" min="1">
                                                    <span class="input-group-text">Point</span>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeCriteria(this)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    @if(isset($settings['additional_criteria']) && is_array($settings['additional_criteria']))
                                        @foreach($settings['additional_criteria'] as $criteria)
                                        <div class="criteria-item mb-3 p-3 bg-light rounded">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control" name="criteria_name[]" value="{{ $criteria['name'] }}" placeholder="Nama kriteria">
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" name="criteria_points[]" value="{{ $criteria['points'] }}" placeholder="Point" min="1">
                                                        <span class="input-group-text">Point</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeCriteria(this)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="text-muted small mt-2">
                                    <i class="bi bi-info-circle"></i> Kriteria tambahan bersifat opsional. Contoh: Bonus artikel internasional, Bonus review kompleks, dll.
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields for bonus (keep default values) -->
                        <input type="hidden" name="points_bonus_fast" value="{{ $settings['points_bonus_fast'] ?? 0 }}">
                        <input type="hidden" name="points_bonus_quality" value="{{ $settings['points_bonus_quality'] ?? 0 }}">

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Info -->
        <div class="col-md-5">
            <!-- Cara Kerja -->
            <div class="card shadow mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle-fill"></i> Cara Kerja Point
                    </h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">
                            <strong>Admin</strong> tugaskan review ke reviewer
                        </li>
                        <li class="mb-2">
                            <strong>Reviewer</strong> kerjakan dan submit hasil review
                        </li>
                        <li class="mb-2">
                            <strong>Admin</strong> klik tombol "Review Selesai" untuk approve
                        </li>
                        <li class="mb-2">
                            <strong>Sistem</strong> otomatis berikan point ke reviewer
                        </li>
                        <li class="mb-0">
                            <strong>Reviewer</strong> bisa tukar point dengan reward
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Aktivitas Terbaru -->
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-clock-history"></i> Aktivitas Terbaru
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($stats['recent_activities']->take(5) as $activity)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-primary">{{ $activity->user->name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ Str::limit($activity->description, 30) }}</small>
                                    <br>
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="text-end">
                                    @if($activity->type === 'EARNED')
                                        <h5 class="mb-0 text-success">+{{ $activity->points }}</h5>
                                    @else
                                        <h5 class="mb-0 text-danger">-{{ $activity->points }}</h5>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mb-0 mt-2">Belum ada aktivitas</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="card shadow mt-3 border-warning">
                <div class="card-header bg-warning">
                    <h6 class="mb-0">
                        <i class="bi bi-lightbulb-fill"></i> Tips
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li class="mb-2">Sesuaikan nilai point dengan budget yang tersedia</li>
                        <li class="mb-2">Point yang sudah diberikan tidak akan berubah jika setting diubah</li>
                        <li class="mb-0">Evaluasi berkala untuk menjaga motivasi reviewer</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let criteriaCount = {{ isset($settings['points_bonus_fast']) && $settings['points_bonus_fast'] > 0 ? 1 : 0 }} + {{ isset($settings['points_bonus_quality']) && $settings['points_bonus_quality'] > 0 ? 1 : 0 }};

function addCriteria() {
    criteriaCount++;
    
    const criteriaHtml = `
        <div class="criteria-item mb-3 p-3 bg-light rounded">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="criteria_name[]" placeholder="Contoh: Bonus Artikel Internasional" required>
                    <small class="text-muted">Nama kriteria bonus point</small>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="number" class="form-control" name="criteria_points[]" placeholder="Point" min="1" required>
                        <span class="input-group-text">Point</span>
                    </div>
                    <small class="text-muted">Jumlah point bonus</small>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeCriteria(this)" title="Hapus kriteria">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('additionalCriteria').insertAdjacentHTML('beforeend', criteriaHtml);
}

function removeCriteria(button) {
    if (confirm('Hapus kriteria ini?')) {
        button.closest('.criteria-item').remove();
        criteriaCount--;
    }
}
</script>
@endpush
@endsection
