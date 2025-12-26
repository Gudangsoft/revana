

<?php $__env->startSection('title', 'Admin Dashboard - REVANA'); ?>
<?php $__env->startSection('page-title', 'Dashboard Admin'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link active">
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
<!-- Notification Alert for Submitted Reviews -->
<?php if($submittedReviews > 0): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-bell-fill me-2" style="font-size: 1.5rem;"></i>
        <div>
            <strong>Review Selesai Dikerjakan!</strong>
            <br>
            Ada <strong><?php echo e($submittedReviews); ?></strong> review yang telah diselesaikan reviewer dan menunggu validasi Anda.
            <a href="<?php echo e(route('admin.assignments.index')); ?>" class="alert-link">Lihat Review</a>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Notification Alert for Pending Redemptions -->
<?php if($pendingRedemptions > 0): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-gift-fill me-2" style="font-size: 1.5rem;"></i>
        <div>
            <strong>Penukaran Reward Menunggu!</strong>
            <br>
            Ada <strong><?php echo e($pendingRedemptions); ?></strong> penukaran reward yang menunggu persetujuan Anda.
            <a href="<?php echo e(route('admin.redemptions.index')); ?>" class="alert-link">Lihat Redemptions</a>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row">
    <div class="col-md-3">
        <div class="card stats-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Jurnal</h6>
                        <h2 class="mb-0"><?php echo e($totalJournals); ?></h2>
                    </div>
                    <div class="text-primary" style="font-size: 2.5rem;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Reviewers</h6>
                        <h2 class="mb-0"><?php echo e($totalReviewers); ?></h2>
                    </div>
                    <div class="text-success" style="font-size: 2.5rem;">
                        <i class="bi bi-people"></i>
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
                        <h6 class="text-muted mb-2">Review Pending</h6>
                        <h2 class="mb-0"><?php echo e($pendingReviews); ?></h2>
                    </div>
                    <div class="text-warning" style="font-size: 2.5rem;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Perlu Validasi</h6>
                        <h2 class="mb-0"><?php echo e($submittedReviews); ?></h2>
                    </div>
                    <div class="text-danger" style="font-size: 2.5rem;">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning"></i> Quick Actions
            </div>
            <div class="card-body">
                <a href="<?php echo e(route('admin.journals.create')); ?>" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> Tambah Jurnal
                </a>
                <a href="<?php echo e(route('admin.assignments.create')); ?>" class="btn btn-success me-2">
                    <i class="bi bi-person-plus"></i> Tugaskan Reviewer
                </a>
                <a href="<?php echo e(route('admin.redemptions.index')); ?>" class="btn btn-warning me-2">
                    <i class="bi bi-gift"></i> Kelola Reward
                    <?php if($pendingRedemptions > 0): ?>
                    <span class="badge bg-danger"><?php echo e($pendingRedemptions); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Assignments -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history"></i> Review Terbaru</span>
                <a href="<?php echo e(route('admin.assignments.index')); ?>" class="btn btn-sm btn-outline-primary">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Jurnal</th>
                                <th>Reviewer</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <strong><?php echo e(Str::limit($assignment->journal->title, 50)); ?></strong><br>
                                    <small class="text-muted"><?php echo e($assignment->journal->accreditation); ?> (<?php echo e($assignment->journal->points); ?> pts)</small>
                                </td>
                                <td><?php echo e($assignment->reviewer->name); ?></td>
                                <td>
                                    <?php
                                        $statusColors = [
                                            'PENDING' => 'warning',
                                            'ACCEPTED' => 'info',
                                            'REJECTED' => 'danger',
                                            'ON_PROGRESS' => 'primary',
                                            'SUBMITTED' => 'success',
                                            'APPROVED' => 'success',
                                            'REVISION' => 'secondary'
                                        ];
                                        $color = $statusColors[$assignment->status] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo e($color); ?>"><?php echo e($assignment->status); ?></span>
                                </td>
                                <td><?php echo e($assignment->created_at->format('d M Y')); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.assignments.show', $assignment)); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada assignment</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>