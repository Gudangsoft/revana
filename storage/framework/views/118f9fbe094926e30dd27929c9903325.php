

<?php $__env->startSection('title', 'Rewards - REVANA'); ?>
<?php $__env->startSection('page-title', 'Tukar Points dengan Rewards'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="<?php echo e(route('reviewer.dashboard')); ?>" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?php echo e(route('reviewer.tasks.index')); ?>" class="nav-link">
        <i class="bi bi-clipboard-check"></i> My Tasks
    </a>
    <a href="<?php echo e(route('reviewer.rewards.index')); ?>" class="nav-link active">
        <i class="bi bi-gift"></i> Rewards
    </a>
    <a href="<?php echo e(route('reviewer.profile.edit')); ?>" class="nav-link">
        <i class="bi bi-person-circle"></i> My Profile
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
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
                                <h2 class="mb-0" style="color: white !important;"><?php echo e($user->total_points); ?></h2>
                                <small style="color: rgba(255,255,255,0.8) !important;">Total Points</small>
                            </div>
                            <div>
                                <h2 class="mb-0" style="color: white !important;"><?php echo e($user->available_points); ?></h2>
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
                    <?php $__empty_1 = true; $__currentLoopData = $rewards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reward): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 <?php echo e($user->available_points >= $reward->points_required ? 'border-success' : 'border-secondary'); ?>">
                            <div class="card-header text-center <?php echo e($user->available_points >= $reward->points_required ? 'bg-success text-white' : 'bg-secondary text-white'); ?>">
                                <?php if($reward->type == 'UANG'): ?>
                                    <i class="bi bi-cash-coin" style="font-size: 2rem;"></i>
                                <?php else: ?>
                                    <i class="bi bi-file-earmark-check" style="font-size: 2rem;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo e($reward->name); ?></h5>
                                <p class="card-text text-muted"><?php echo e($reward->description); ?></p>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Points Required:</span>
                                    <span class="badge bg-primary" style="font-size: 1.1rem;"><?php echo e($reward->points_required); ?> pts</span>
                                </div>
                                <?php if($reward->type == 'UANG'): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Nilai:</span>
                                    <strong class="text-success">Rp <?php echo e(number_format($reward->value, 0, ',', '.')); ?></strong>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <?php if($user->available_points >= $reward->points_required): ?>
                                    <button type="button" class="btn btn-success w-100" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#redeemModal<?php echo e($reward->id); ?>">
                                        <i class="bi bi-gift"></i> Tukar Sekarang
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100" disabled>
                                        <i class="bi bi-lock"></i> Points Tidak Cukup
                                    </button>
                                    <small class="text-muted">
                                        Butuh <?php echo e($reward->points_required - $user->available_points); ?> points lagi
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Redeem Modal -->
                    <div class="modal fade" id="redeemModal<?php echo e($reward->id); ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Konfirmasi Penukaran</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="<?php echo e(route('reviewer.rewards.redeem', $reward)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <div class="modal-body">
                                        <div class="text-center mb-3">
                                            <h4><?php echo e($reward->name); ?></h4>
                                            <p class="text-muted"><?php echo e($reward->description); ?></p>
                                            <h3 class="text-success"><?php echo e($reward->points_required); ?> Points</h3>
                                        </div>

                                        <div class="alert alert-info">
                                            <strong>Points Anda:</strong> <?php echo e($user->available_points); ?><br>
                                            <strong>Setelah penukaran:</strong> <?php echo e($user->available_points - $reward->points_required); ?>

                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Catatan (Opsional)</label>
                                            <textarea class="form-control" name="notes" rows="3" 
                                                      placeholder="Tambahkan informasi rekening atau catatan lainnya..."></textarea>
                                            <small class="text-muted">
                                                <?php if($reward->type == 'UANG'): ?>
                                                Sertakan nomor rekening untuk transfer
                                                <?php else: ?>
                                                Sertakan informasi jurnal yang akan disubmit
                                                <?php endif; ?>
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
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-md-12">
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-2">Belum ada rewards tersedia</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- My Redemptions -->
<?php if($myRedemptions->count() > 0): ?>
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
                            <?php $__currentLoopData = $myRedemptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $redemption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($redemption->created_at->format('d M Y')); ?></td>
                                <td><?php echo e($redemption->reward->name); ?></td>
                                <td><span class="badge bg-danger">-<?php echo e($redemption->points_used); ?> pts</span></td>
                                <td>
                                    <?php
                                        $statusColors = [
                                            'PENDING' => 'warning',
                                            'APPROVED' => 'info',
                                            'COMPLETED' => 'success',
                                            'REJECTED' => 'danger'
                                        ];
                                        $color = $statusColors[$redemption->status] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo e($color); ?>"><?php echo e($redemption->status); ?></span>
                                </td>
                                <td>
                                    <?php if($redemption->admin_notes): ?>
                                        <small class="text-muted"><?php echo e($redemption->admin_notes); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/reviewer/rewards/index.blade.php ENDPATH**/ ?>