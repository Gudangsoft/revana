@extends('layouts.app')

@section('title', 'Edit Reward - REVANA')
@section('page-title', 'Edit Reward')

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
    <a href="{{ route('admin.marketings.index') }}" class="nav-link">
        <i class="bi bi-megaphone"></i> Marketing
    </a>
    <a href="{{ route('admin.pics.index') }}" class="nav-link">
        <i class="bi bi-person-badge"></i> PIC
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy"></i> Form Edit Reward</span>
                @if($reward->redemptions_count > 0)
                <span class="badge bg-warning">
                    <i class="bi bi-exclamation-triangle"></i> {{ $reward->redemptions_count }} redemptions
                </span>
                @endif
            </div>
            <div class="card-body">
                <form action="{{ route('admin.rewards.update', $reward) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama Reward <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               name="name" 
                               value="{{ old('name', $reward->name) }}"
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
                                  placeholder="Jelaskan detail reward ini...">{{ old('description', $reward->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipe Reward <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('type') is-invalid @enderror" 
                               name="type" 
                               value="{{ old('type', $reward->type) }}"
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
                            <option value="Bronze" {{ old('tier', $reward->tier) == 'Bronze' ? 'selected' : '' }}>
                                🥉 Bronze (Entry Level)
                            </option>
                            <option value="Silver" {{ old('tier', $reward->tier) == 'Silver' ? 'selected' : '' }}>
                                🥈 Silver (Standard)
                            </option>
                            <option value="Gold" {{ old('tier', $reward->tier) == 'Gold' ? 'selected' : '' }}>
                                🥇 Gold (Premium)
                            </option>
                            <option value="Platinum" {{ old('tier', $reward->tier) == 'Platinum' ? 'selected' : '' }}>
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
                               value="{{ old('points_required', $reward->points_required) }}"
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
                               value="{{ old('value', $reward->value) }}"
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
                                   {{ old('is_active', $reward->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Aktifkan reward ini
                            </label>
                        </div>
                        <small class="text-muted">Reward yang aktif bisa ditukar oleh reviewer</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Update Reward
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
                <i class="bi bi-info-circle"></i> Info Reward
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td class="fw-bold">Status:</td>
                        <td>
                            @if($reward->is_active)
                            <span class="badge bg-success">Aktif</span>
                            @else
                            <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Total Redemptions:</td>
                        <td><span class="badge bg-primary">{{ $reward->redemptions_count }}</span></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Dibuat:</td>
                        <td>{{ $reward->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Terakhir Update:</td>
                        <td>{{ $reward->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-warning">
                <i class="bi bi-exclamation-triangle"></i> Perhatian
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    @if($reward->redemptions_count > 0)
                    <li class="text-danger">Reward ini sudah memiliki {{ $reward->redemptions_count }} redemptions</li>
                    <li>Hati-hati mengubah points required</li>
                    @else
                    <li>Reward ini belum ada yang menukar</li>
                    @endif
                    <li>Nonaktifkan jika reward sudah tidak tersedia</li>
                    <li>Perubahan langsung berlaku untuk reviewer</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

