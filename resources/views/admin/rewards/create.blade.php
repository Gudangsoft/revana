@extends('layouts.app')

@section('title', 'Tambah Reward - REVANA')
@section('page-title', 'Tambah Reward Baru')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.journals.index') }}" class="nav-link">
        <i class="bi bi-journal-text"></i> Jurnal
    </a>
    <a href="{{ route('admin.assignments.index') }}" class="nav-link">
        <i class="bi bi-clipboard-check"></i> Review Assignments
    </a>
    <a href="{{ route('admin.reviewers.index') }}" class="nav-link">
        <i class="bi bi-people"></i> Reviewers
    </a>
    <a href="{{ route('admin.leaderboard.index') }}" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="{{ route('admin.redemptions.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
    <a href="{{ route('admin.points.index') }}" class="nav-link">
        <i class="bi bi-coin"></i> Point Management
    </a>
    <a href="{{ route('admin.rewards.index') }}" class="nav-link active">
        <i class="bi bi-trophy"></i> Reward Management
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-trophy"></i> Form Tambah Reward
            </div>
            <div class="card-body">
                <form action="{{ route('admin.rewards.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Nama Reward <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               name="name" 
                               value="{{ old('name') }}"
                               required
                               placeholder="Contoh: Voucher Pulsa 50K">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" rows="3"
                                  placeholder="Jelaskan detail reward ini...">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipe Reward <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('type') is-invalid @enderror" 
                               name="type" 
                               value="{{ old('type') }}"
                               required
                               placeholder="Contoh: Voucher, E-Wallet, Merchandise, Cash">
                        @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Tipe/kategori reward (Voucher, E-Wallet, Cash, dll)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Peringkat Reward <span class="text-danger">*</span></label>
                        <select class="form-select @error('tier') is-invalid @enderror" name="tier" required>
                            <option value="">Pilih Peringkat</option>
                            <option value="Bronze" {{ old('tier') == 'Bronze' ? 'selected' : '' }}>
                                🥉 Bronze (Entry Level)
                            </option>
                            <option value="Silver" {{ old('tier') == 'Silver' ? 'selected' : '' }}>
                                🥈 Silver (Standard)
                            </option>
                            <option value="Gold" {{ old('tier') == 'Gold' ? 'selected' : '' }}>
                                🥇 Gold (Premium)
                            </option>
                            <option value="Platinum" {{ old('tier') == 'Platinum' ? 'selected' : '' }}>
                                💎 Platinum (Exclusive)
                            </option>
                        </select>
                        @error('tier')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Tingkat/level reward untuk sistem ranking</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Points Required <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control @error('points_required') is-invalid @enderror" 
                               name="points_required" 
                               value="{{ old('points_required') }}"
                               min="1" 
                               required>
                        @error('points_required')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Jumlah poin yang dibutuhkan untuk menukar reward ini</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Value (Rupiah)</label>
                        <input type="number" 
                               class="form-control @error('value') is-invalid @enderror" 
                               name="value" 
                               value="{{ old('value') }}"
                               min="0"
                               step="0.01"
                               placeholder="Contoh: 50000">
                        @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Nilai reward dalam rupiah (opsional)</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="is_active" 
                                   id="is_active"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Aktifkan reward ini
                            </label>
                        </div>
                        <small class="text-muted">Reward yang aktif bisa ditukar oleh reviewer</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Simpan Reward
                        </button>
                        <a href="{{ route('admin.rewards.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Panduan
            </div>
            <div class="card-body">
                <h6>Contoh Reward:</h6>
                <ul class="small">
                    <li><strong>Voucher Pulsa 50K</strong><br>Tipe: Voucher, Points: 100</li>
                    <li><strong>Gopay 100K</strong><br>Tipe: E-Wallet, Points: 200</li>
                    <li><strong>Cash 500K</strong><br>Tipe: Cash, Points: 1000</li>
                    <li><strong>Sertifikat Review</strong><br>Tipe: Certificate, Points: 50</li>
                </ul>
                <hr>
                <p class="small mb-0">
                    Sesuaikan points required dengan value reward agar sistem point tetap seimbang.
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-warning">
                <i class="bi bi-lightbulb"></i> Tips
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Buat reward yang menarik untuk reviewer</li>
                    <li>Tetapkan points yang fair</li>
                    <li>Update deskripsi secara jelas</li>
                    <li>Nonaktifkan reward yang sudah tidak tersedia</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
