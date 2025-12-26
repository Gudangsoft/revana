

<?php $__env->startSection('title', 'Reviewer Dashboard - REVANA'); ?>
<?php $__env->startSection('page-title', 'Dashboard Reviewer'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="<?php echo e(route('reviewer.dashboard')); ?>" class="nav-link active">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?php echo e(route('reviewer.tasks.index')); ?>" class="nav-link">
        <i class="bi bi-clipboard-check"></i> My Tasks
    </a>
    <a href="<?php echo e(route('reviewer.rewards.index')); ?>" class="nav-link">
        <i class="bi bi-gift"></i> Rewards
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Notification Alert for New Tasks -->
<?php if($pendingTasks > 0): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-bell-fill me-2" style="font-size: 1.5rem;"></i>
        <div>
            <strong>Ada Tugas Baru!</strong>
            <br>
            Anda memiliki <strong><?php echo e($pendingTasks); ?></strong> tugas review yang menunggu untuk dikerjakan.
            <a href="<?php echo e(route('reviewer.tasks.index')); ?>" class="alert-link">Lihat Tugas</a>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Profile Card -->
<div class="row">
    <div class="col-md-12">
        <div class="card text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important; border: none;">
            <div class="card-body" style="color: white !important;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 style="color: white !important;"><i class="bi bi-person-circle"></i> <?php echo e($user->name); ?></h3>
                        <p class="mb-2" style="color: rgba(255,255,255,0.9) !important;"><?php echo e($user->email); ?></p>
                        <div class="d-flex gap-3 mt-3">
                            <div>
                                <h4 class="mb-0" style="color: white !important;"><?php echo e($user->total_points); ?></h4>
                                <small style="color: rgba(255,255,255,0.8) !important;">Total Points</small>
                            </div>
                            <div>
                                <h4 class="mb-0" style="color: white !important;"><?php echo e($user->available_points); ?></h4>
                                <small style="color: rgba(255,255,255,0.8) !important;">Available Points</small>
                            </div>
                            <div>
                                <h4 class="mb-0" style="color: white !important;"><?php echo e($user->completed_reviews); ?></h4>
                                <small style="color: rgba(255,255,255,0.8) !important;">Completed Reviews</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php $__currentLoopData = $user->badges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="badge bg-warning text-dark me-1" style="font-size: 1.2rem;" title="<?php echo e($badge->description); ?>">
                            <?php echo e($badge->icon); ?> <?php echo e($badge->name); ?>

                        </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Pending Tasks</h6>
                        <h2 class="mb-0"><?php echo e($pendingTasks); ?></h2>
                    </div>
                    <div class="text-warning" style="font-size: 2.5rem;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stats-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Active Tasks</h6>
                        <h2 class="mb-0"><?php echo e($activeTasks); ?></h2>
                    </div>
                    <div class="text-primary" style="font-size: 2.5rem;">
                        <i class="bi bi-clipboard-check"></i>
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
                        <h6 class="text-muted mb-2">Completed</h6>
                        <h2 class="mb-0"><?php echo e($completedTasks); ?></h2>
                    </div>
                    <div class="text-success" style="font-size: 2.5rem;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Available Rewards -->
<?php if($availableRewards->count() > 0): ?>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-gift"></i> Rewards yang Bisa Ditukar
            </div>
            <div class="card-body">
                <div class="row">
                    <?php $__currentLoopData = $availableRewards->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reward): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h5><?php echo e($reward->name); ?></h5>
                                <p class="text-muted"><?php echo e($reward->description); ?></p>
                                <h4 class="text-success"><?php echo e($reward->points_required); ?> Points</h4>
                                <a href="<?php echo e(route('reviewer.rewards.index')); ?>" class="btn btn-success btn-sm">
                                    Tukar Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Tasks -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history"></i> Tugas Terbaru</span>
                <a href="<?php echo e(route('reviewer.tasks.index')); ?>" class="btn btn-sm btn-outline-primary">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Jurnal</th>
                                <th>Akreditasi</th>
                                <th>Points</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <strong><?php echo e(Str::limit($assignment->journal->title, 50)); ?></strong>
                                </td>
                                <td><?php echo e($assignment->journal->accreditation); ?></td>
                                <td><span class="badge bg-info"><?php echo e($assignment->journal->points); ?> pts</span></td>
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
                                    <a href="<?php echo e(route('reviewer.tasks.show', $assignment)); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada tugas</td>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/reviewer/dashboard.blade.php ENDPATH**/ ?>