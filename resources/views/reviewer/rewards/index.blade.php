@extends('layouts.app')

@section('title', 'Rewards - REVANA')
@section('page-title', 'Tukar Points dengan Rewards')

@section('sidebar')
    <a href="{{ route('reviewer.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('reviewer.tasks.index') }}" class="nav-link">
        <i class="bi bi-clipboard-check"></i> My Tasks
    </a>
    <a href="{{ route('reviewer.rewards.index') }}" class="nav-link active">
        <i class="bi bi-gift"></i> Rewards
    </a>
    <a href="{{ route('reviewer.profile.edit') }}" class="nav-link">
        <i class="bi bi-person-circle"></i> My Profile
    </a>
@endsection

@section('content')
<!-- Points Summary -->
<div class="row">
    <div class="col-md-12">
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; border: none;">
            <div class="card-body" style="color: white !important;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 style="color: white !important;"><i class="bi bi-wallet2"></i> Points Saya</h3>
                        <div class="d-flex gap-4 mt-3">
                            <div>
                                <h2 class="mb-0" style="color: white !important;">{{ $user->total_points }}</h2>
                                <small style="color: rgba(255,255,255,0.8) !important;">Total Points</small>
                            </div>
                            <div>
                                <h2 class="mb-0" style="color: white !important;">{{ $user->available_points }}</h2>
                                <small style="color: rgba(255,255,255,0.8) !important;">Available Points</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <i class="bi bi-coin" style="font-size: 5rem; opacity: 0.5; color: white !important;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Available Rewards -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-gift"></i> Rewards Tersedia
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($rewards as $reward)
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 {{ $user->available_points >= $reward->points_required ? 'border-success' : 'border-secondary' }}">
                            <div class="card-header text-center {{ $user->available_points >= $reward->points_required ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                @if($reward->type == 'UANG')
                                    <i class="bi bi-cash-coin" style="font-size: 2rem;"></i>
                                @else
                                    <i class="bi bi-file-earmark-check" style="font-size: 2rem;"></i>
                                @endif
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $reward->name }}</h5>
                                <p class="card-text text-muted">{{ $reward->description }}</p>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Points Required:</span>
                                    <span class="badge bg-primary" style="font-size: 1.1rem;">{{ $reward->points_required }} pts</span>
                                </div>
                                @if($reward->type == 'UANG')
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Nilai:</span>
                                    <strong class="text-success">Rp {{ number_format($reward->value, 0, ',', '.') }}</strong>
                                </div>
                                @endif
                            </div>
                            <div class="card-footer">
                                @if($user->available_points >= $reward->points_required)
                                    <button type="button" class="btn btn-success w-100" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#redeemModal{{ $reward->id }}">
                                        <i class="bi bi-gift"></i> Tukar Sekarang
                                    </button>
                                @else
                                    <button class="btn btn-secondary w-100" disabled>
                                        <i class="bi bi-lock"></i> Points Tidak Cukup
                                    </button>
                                    <small class="text-muted">
                                        Butuh {{ $reward->points_required - $user->available_points }} points lagi
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Redeem Modal -->
                    <div class="modal fade" id="redeemModal{{ $reward->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Konfirmasi Penukaran</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('reviewer.rewards.redeem', $reward) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="text-center mb-3">
                                            <h4>{{ $reward->name }}</h4>
                                            <p class="text-muted">{{ $reward->description }}</p>
                                            <h3 class="text-success">{{ $reward->points_required }} Points</h3>
                                        </div>

                                        <div class="alert alert-info">
                                            <strong>Points Anda:</strong> {{ $user->available_points }}<br>
                                            <strong>Setelah penukaran:</strong> {{ $user->available_points - $reward->points_required }}
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Catatan (Opsional)</label>
                                            <textarea class="form-control" name="notes" rows="3" 
                                                      placeholder="Tambahkan informasi rekening atau catatan lainnya..."></textarea>
                                            <small class="text-muted">
                                                @if($reward->type == 'UANG')
                                                Sertakan nomor rekening untuk transfer
                                                @else
                                                Sertakan informasi jurnal yang akan disubmit
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-success">Tukar Sekarang</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-md-12">
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-2">Belum ada rewards tersedia</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- My Redemptions -->
@if($myRedemptions->count() > 0)
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Riwayat Penukaran Saya
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Reward</th>
                                <th>Points</th>
                                <th>Status</th>
                                <th>Catatan Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myRedemptions as $redemption)
                            <tr>
                                <td>{{ $redemption->created_at->format('d M Y') }}</td>
                                <td>{{ $redemption->reward->name }}</td>
                                <td><span class="badge bg-danger">-{{ $redemption->points_used }} pts</span></td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'PENDING' => 'warning',
                                            'APPROVED' => 'info',
                                            'COMPLETED' => 'success',
                                            'REJECTED' => 'danger'
                                        ];
                                        $color = $statusColors[$redemption->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $redemption->status }}</span>
                                </td>
                                <td>
                                    @if($redemption->admin_notes)
                                        <small class="text-muted">{{ $redemption->admin_notes }}</small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
