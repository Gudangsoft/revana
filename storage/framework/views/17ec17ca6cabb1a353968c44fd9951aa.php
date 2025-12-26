

<?php $__env->startSection('title', 'Data Reviewer - REVANA'); ?>
<?php $__env->startSection('page-title', 'Data Reviewer'); ?>

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
    <a href="<?php echo e(route('admin.reviewers.index')); ?>" class="nav-link active">
        <i class="bi bi-people"></i> Reviewers
    </a>
    <a href="<?php echo e(route('admin.leaderboard.index')); ?>" class="nav-link">
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
    <a href="<?php echo e(route('admin.marketings.index')); ?>" class="nav-link">
        <i class="bi bi-megaphone"></i> Marketing
    </a>
    <a href="<?php echo e(route('admin.pics.index')); ?>" class="nav-link">
        <i class="bi bi-person-badge"></i> PIC
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people"></i> Daftar Reviewer</span>
                <div>
                    <span class="badge bg-primary">Total: <?php echo e($reviewers->total()); ?> Reviewer</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Total Points</th>
                                <th>Available Points</th>
                                <th>Completed Reviews</th>
                                <th>Active Tasks</th>
                                <th>Badges</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $reviewers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reviewer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($reviewer->name); ?></strong>
                                </td>
                                <td><?php echo e($reviewer->email); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo e($reviewer->total_points); ?> pts</span>
                                </td>
                                <td>
                                    <span class="badge bg-success"><?php echo e($reviewer->available_points); ?> pts</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo e($reviewer->completed_reviews); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-warning"><?php echo e($reviewer->review_assignments_count); ?></span>
                                </td>
                                <td>
                                    <?php $__currentLoopData = $reviewer->badges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="badge bg-secondary" title="<?php echo e($badge->description); ?>">
                                        <?php echo e($badge->icon); ?>

                                    </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($reviewer->badges->count() == 0): ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('admin.reviewers.show', $reviewer)); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada data reviewer</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if($reviewers->hasPages()): ?>
            <div class="card-footer">
                <?php echo e($reviewers->links()); ?>

            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row">
    <div class="col-md-4">
        <div class="card stats-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Reviewer</h6>
                        <h2 class="mb-0"><?php echo e($reviewers->total()); ?></h2>
                    </div>
                    <div class="text-primary" style="font-size: 2.5rem;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Reviews</h6>
                        <h2 class="mb-0"><?php echo e($reviewers->sum('completed_reviews')); ?></h2>
                    </div>
                    <div class="text-success" style="font-size: 2.5rem;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Points</h6>
                        <h2 class="mb-0"><?php echo e($reviewers->sum('total_points')); ?></h2>
                    </div>
                    <div class="text-warning" style="font-size: 2.5rem;">
                        <i class="bi bi-coin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/admin/reviewers/index.blade.php ENDPATH**/ ?>