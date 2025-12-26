

<?php $__env->startSection('title', 'Leaderboard Reviewer - REVANA'); ?>
<?php $__env->startSection('page-title', '🏆 Leaderboard Reviewer'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?php echo e(route('admin.journals.index')); ?>" class="nav-link">
        <i class="bi bi-journal-text"></i> Jurnal
    </a>
    <a href="<?php echo e(route('admin.assignments.index')); ?>" class="nav-link">
        <i class="bi bi-clipboard-check"></i> Review Assignments
    </a>
    <a href="<?php echo e(route('admin.reviewers.index')); ?>" class="nav-link">
        <i class="bi bi-people"></i> Reviewers
    </a>
    <a href="<?php echo e(route('admin.leaderboard.index')); ?>" class="nav-link active">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="<?php echo e(route('admin.redemptions.index')); ?>" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
    <a href="<?php echo e(route('admin.points.index')); ?>" class="nav-link">
        <i class="bi bi-coin"></i> Point Management
    </a>
    <a href="<?php echo e(route('admin.rewards.index')); ?>" class="nav-link">
        <i class="bi bi-trophy"></i> Reward Management
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Info Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Reviewer</h6>
                        <h2 class="mb-0"><?php echo e($reviewers->count()); ?></h2>
                    </div>
                    <div class="text-primary" style="font-size: 2.5rem;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Platinum</h6>
                        <h2 class="mb-0"><?php echo e($reviewers->sum('platinum_count')); ?></h2>
                    </div>
                    <div style="font-size: 2.5rem;">💎</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Gold</h6>
                        <h2 class="mb-0"><?php echo e($reviewers->sum('gold_count')); ?></h2>
                    </div>
                    <div style="font-size: 2.5rem;">🥇</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Reviews</h6>
                        <h2 class="mb-0"><?php echo e($reviewers->sum('total_reviews')); ?></h2>
                    </div>
                    <div class="text-info" style="font-size: 2.5rem;">
                        <i class="bi bi-clipboard-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaderboard Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-trophy-fill"></i> Peringkat Reviewer Berdasarkan Reward</span>
        <span class="badge bg-primary">Diurutkan berdasarkan tier reward tertinggi</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="hide-mobile" width="80">🏅 Rank</th>
                        <th>Reviewer</th>
                        <th class="text-center hide-mobile">Total Reviews</th>
                        <th class="text-center">💎 Platinum</th>
                        <th class="text-center">🥇 Gold</th>
                        <th class="text-center hide-mobile">🥈 Silver</th>
                        <th class="text-center hide-mobile">🥉 Bronze</th>
                        <th class="text-center">Total Reward</th>
                        <th class="text-center hide-mobile">Points</th>
                        <th class="hide-mobile">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $reviewers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reviewer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="hide-mobile">
                            <?php if($reviewer->rank == 1): ?>
                                <span class="badge" style="background: linear-gradient(135deg, #ffd700, #ffed4e); color: #000; font-size: 1.1rem; padding: 0.5rem 0.75rem;">
                                    🥇 #<?php echo e($reviewer->rank); ?>

                                </span>
                            <?php elseif($reviewer->rank == 2): ?>
                                <span class="badge" style="background: linear-gradient(135deg, #c0c0c0, #e8e8e8); color: #000; font-size: 1.1rem; padding: 0.5rem 0.75rem;">
                                    🥈 #<?php echo e($reviewer->rank); ?>

                                </span>
                            <?php elseif($reviewer->rank == 3): ?>
                                <span class="badge" style="background: linear-gradient(135deg, #cd7f32, #b87333); color: #fff; font-size: 1.1rem; padding: 0.5rem 0.75rem;">
                                    🥉 #<?php echo e($reviewer->rank); ?>

                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary" style="font-size: 1rem; padding: 0.4rem 0.65rem;">
                                    #<?php echo e($reviewer->rank); ?>

                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-bold"><?php echo e($reviewer->name); ?></div>
                            <small class="text-muted"><?php echo e($reviewer->email); ?></small>
                            <div class="d-md-none mt-1">
                                <span class="badge bg-primary"><?php echo e($reviewer->total_reviews); ?> reviews</span>
                                <span class="badge bg-info"><?php echo e(number_format($reviewer->current_points)); ?> pts</span>
                            </div>
                        </td>
                        <td class="text-center hide-mobile">
                            <span class="badge bg-primary"><?php echo e($reviewer->total_reviews); ?></span>
                        </td>
                        <td class="text-center">
                            <?php if($reviewer->platinum_count > 0): ?>
                                <span class="badge" style="background: linear-gradient(135deg, #b7a1d8, #7c3aed); font-size: 0.95rem;">
                                    <?php echo e($reviewer->platinum_count); ?>

                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if($reviewer->gold_count > 0): ?>
                                <span class="badge" style="background: linear-gradient(135deg, #fcd34d, #f59e0b); font-size: 0.95rem;">
                                    <?php echo e($reviewer->gold_count); ?>

                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center hide-mobile">
                            <?php if($reviewer->silver_count > 0): ?>
                                <span class="badge" style="background: linear-gradient(135deg, #cbd5e1, #64748b); font-size: 0.95rem;">
                                    <?php echo e($reviewer->silver_count); ?>

                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center hide-mobile">
                            <?php if($reviewer->bronze_count > 0): ?>
                                <span class="badge" style="background: linear-gradient(135deg, #d97706, #92400e); color: white; font-size: 0.95rem;">
                                    <?php echo e($reviewer->bronze_count); ?>

                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success" style="font-size: 0.95rem;">
                                <?php echo e($reviewer->total_redemptions); ?>

                            </span>
                        </td>
                        <td class="text-center hide-mobile">
                            <div>
                                <span class="badge bg-warning text-dark"><?php echo e(number_format($reviewer->current_points)); ?></span>
                            </div>
                            <small class="text-muted">Earned: <?php echo e(number_format($reviewer->total_points_earned)); ?></small>
                        </td>
                        <td class="hide-mobile">
                            <a href="<?php echo e(route('admin.reviewers.show', $reviewer)); ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">Belum ada data reviewer</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Info Section -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Cara Perhitungan Rank
            </div>
            <div class="card-body">
                <p class="mb-2">Ranking dihitung berdasarkan <strong>Tier Score</strong>:</p>
                <ul class="mb-0">
                    <li>💎 <strong>Platinum</strong> = 1,000 poin</li>
                    <li>🥇 <strong>Gold</strong> = 100 poin</li>
                    <li>🥈 <strong>Silver</strong> = 10 poin</li>
                    <li>🥉 <strong>Bronze</strong> = 1 poin</li>
                </ul>
                <hr>
                <p class="small text-muted mb-0">
                    <strong>Contoh:</strong> Reviewer dengan 2 Platinum + 3 Gold = (2×1000) + (3×100) = 2,300 poin
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-trophy"></i> Top 3 Performers
            </div>
            <div class="card-body">
                <?php $__currentLoopData = $reviewers->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $top): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 <?php echo e($index < 2 ? 'border-bottom' : ''); ?>">
                    <div>
                        <span class="me-2">
                            <?php if($index == 0): ?> 🥇
                            <?php elseif($index == 1): ?> 🥈
                            <?php else: ?> 🥉
                            <?php endif; ?>
                        </span>
                        <strong><?php echo e($top->name); ?></strong>
                    </div>
                    <div>
                        <span class="badge bg-primary"><?php echo e($top->total_redemptions); ?> rewards</span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/admin/leaderboard/index.blade.php ENDPATH**/ ?>